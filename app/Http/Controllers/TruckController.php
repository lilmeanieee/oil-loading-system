<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Models\Hauler;
use App\Models\Truck;
use App\Models\TruckDocument;
use App\Support\Phone;
use App\Support\Role;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Throwable;

/**
 * Register and edit trucks, with their driver, substitute driver and the three documents (OR, CR, Calibration).
 * A brand-new hauler typed in the truck form is created in the same request, all or nothing.
 */
class TruckController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        abort_unless(Role::can('trucks.manage'), 403);

        return $this->save($request, null);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        abort_unless(Role::can('trucks.manage'), 403);

        return $this->save($request, Truck::with('documents')->findOrFail($id));
    }

    /** Opens a saved OR / CR / Calibration file. Only signed-in people who may view trucks can open it. */
    public function document(int $id, string $type)
    {
        abort_unless(Role::can('trucks.view'), 403);
        abort_unless(isset(TruckDocument::TYPES[$type]), 404);

                $doc = TruckDocument::where('truck_id', $id)->where('type', $type)->firstOrFail();

        /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
        $disk = Storage::disk(TruckDocument::DISK);

        abort_unless($doc->file_path && $disk->exists($doc->file_path), 404);

        return $disk->response($doc->file_path, $doc->original_name ?: basename($doc->file_path));
    }

    private function save(Request $request, ?Truck $truck): JsonResponse
    {
        $isNew = $truck === null;
        $request->merge(['plate' => strtoupper(preg_replace('/\s+/', ' ', trim((string) $request->input('plate'))))]);

        // A document that is new or changed must expire after today. A date of today or earlier means it is expired.
        $today = Carbon::today('Asia/Manila')->toDateString();

        $rules = [
            'plate'           => ['required', 'string', 'max:20', Rule::unique('trucks', 'plate')->ignore($truck?->id)],
            'hauler_id'       => ['nullable', 'string', 'regex:/^H?\d+$/'],
            'hauler_name'     => ['nullable', 'string', 'max:255'],
            'new_addr'        => ['nullable', 'string', 'max:500'],
            'new_contact'     => ['nullable', 'string', 'max:255'],
            'new_phone'       => ['nullable', 'string', 'max:30', Phone::rule()],
            'driver_name'     => ['required', 'string', 'max:255'],
            'substitute_name' => ['nullable', 'string', 'max:255'],
            'capacity'        => ['required', 'integer', 'min:1', 'max:200000'],
            'compartments'    => ['required', 'array', 'min:1', 'max:12'],
            'compartments.*'  => ['required', 'integer', 'min:1'],
        ];

        $messages = [
            'plate.required'       => 'Enter the plate number.',
            'plate.unique'         => 'A truck with this plate number is already registered.',
            'driver_name.required' => 'Enter the driver\'s full name.',
            'capacity.required'    => 'Enter the total capacity in liters.',
            'compartments.required' => 'Enter the number of compartments and the liters of each.',
        ];

        foreach (TruckDocument::TYPES as $type => $meta) {
            $label = $meta['label'];
            $existing = $truck?->documents->firstWhere('type', $type);
            $changed = $request->hasFile($type.'_file')
                || ! $existing
                || $existing->expiry_date?->format('Y-m-d') !== $request->input($type.'_expiry');

            $rules[$type.'_expiry'] = array_values(array_filter([
                'required',
                'date_format:Y-m-d',
                $changed ? 'after:'.$today : null,
            ]));
            $rules[$type.'_file'] = [$existing ? 'nullable' : 'required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'];

            $messages[$type.'_expiry.required']    = "Enter the expiry date of the {$label}.";
            $messages[$type.'_expiry.date_format'] = "Enter a valid expiry date for the {$label}.";
            $messages[$type.'_expiry.after']       = "The {$label} expiry date must be after today. A date that is today or earlier means the document has expired.";
            $messages[$type.'_file.required']      = "Upload the {$label}.";
            $messages[$type.'_file.mimes']         = "The {$label} file must be a PDF, JPG or PNG.";
            $messages[$type.'_file.max']           = "The {$label} file must be 5 MB or smaller.";
            $messages[$type.'_file.uploaded']      = "The {$label} file could not be uploaded. Check that it is 5 MB or smaller.";
        }

        $data = $request->validate($rules, $messages);

        $compartments = array_map('intval', $data['compartments']);
        if (array_sum($compartments) !== (int) $data['capacity']) {
            throw ValidationException::withMessages([
                'compartments' => 'The compartments add up to '.number_format(array_sum($compartments)).' L, but the truck capacity is '.number_format((int) $data['capacity']).' L.',
            ]);
        }

        // ---- the hauler: an existing one, or a new one created together with the truck
        $hauler = null;
        $newHauler = null;

        if (! empty($data['hauler_id'])) {
            $hauler = Hauler::find((int) ltrim($data['hauler_id'], 'Hh'));
            if (! $hauler) {
                throw ValidationException::withMessages(['hauler_id' => 'This hauler is no longer on file. Refresh the page and try again.']);
            }
        } else {
            $haulerName = trim((string) ($data['hauler_name'] ?? ''));
            if ($haulerName === '') {
                throw ValidationException::withMessages(['hauler_name' => 'Select a hauler, or type a new company name.']);
            }

            $hauler = Hauler::where('name', $haulerName)->first(); // someone may have added it a moment ago
            if (! $hauler) {
                if (blank($data['new_addr'] ?? null)) {
                    throw ValidationException::withMessages(['hauler_name' => 'This hauler is not on file yet. Enter its company address to register it.']);
                }

                $newHauler = [
                    'name'    => $haulerName,
                    'contact' => $data['new_contact'] ?? null,
                    'phone'   => Phone::formatMobile($data['new_phone'] ?? null),
                    'address' => trim($data['new_addr']),
                ];
            }
        }

        // ---- the driver and the optional substitute driver
        $driverName = trim(preg_replace('/\s+/', ' ', $data['driver_name']));
        $subName = trim(preg_replace('/\s+/', ' ', (string) ($data['substitute_name'] ?? '')));

        if ($subName !== '' && Str::lower($subName) === Str::lower($driverName)) {
            throw ValidationException::withMessages(['substitute_name' => 'The substitute driver must be a different person from the driver.']);
        }

        $plan1 = $this->planDriver($driverName, $hauler?->id, 'driver', $truck, $truck?->driver_id);
        $plan2 = $subName !== '' ? $this->planDriver($subName, $hauler?->id, 'sub', $truck, $truck?->substitute_driver_id) : null;

        if ($plan2 && $plan1['id'] && $plan1['id'] === $plan2['id']) {
            throw ValidationException::withMessages(['substitute_name' => 'The substitute driver must be a different person from the driver.']);
        }

        // ---- save everything, or nothing
        $newPaths = [];
        $oldPaths = [];

        try {
            DB::transaction(function () use ($request, $data, $truck, $isNew, $hauler, $newHauler, $plan1, $plan2, $compartments, &$newPaths, &$oldPaths) {
                $hauler ??= Hauler::create($newHauler + ['created_by' => $request->user()->id]);

                $driverId = $this->applyDriver($plan1, $hauler->id);
                $subId = $plan2 ? $this->applyDriver($plan2, $hauler->id) : null;

                $truck ??= new Truck();
                $truck->fill([
                    'hauler_id'            => $hauler->id,
                    'driver_id'            => $driverId,
                    'substitute_driver_id' => $subId,
                    'capacity'             => (int) $data['capacity'],
                    'compartments'         => $compartments,
                ]);

                if ($isNew) {
                    $truck->plate = $data['plate'];
                    $truck->created_by = $request->user()->id;
                }

                $truck->save();

                foreach (TruckDocument::TYPES as $type => $meta) {
                    $doc = $truck->documents->firstWhere('type', $type)
                        ?? new TruckDocument(['truck_id' => $truck->id, 'type' => $type]);

                    $doc->expiry_date = $data[$type.'_expiry'];

                    if ($file = $request->file($type.'_file')) {
                        $name = Str::slug($truck->plate).'_'.now('Asia/Manila')->format('Ymd-His').'_'.Str::lower(Str::random(6)).'.'.$file->extension();
                        $path = $file->storeAs(TruckDocument::ROOT.'/'.$meta['folder'], $name, TruckDocument::DISK);

                        $newPaths[] = $path;
                        if ($doc->file_path) {
                            $oldPaths[] = $doc->file_path;
                        }

                        $doc->file_path = $path;
                        $doc->original_name = $file->getClientOriginalName();
                        $doc->uploaded_by = $request->user()->id;
                    }

                    $doc->save();
                }
            });
        } catch (Throwable $e) {
            // Nothing was saved, so remove any file that was already written.
            foreach ($newPaths as $path) {
                Storage::disk(TruckDocument::DISK)->delete($path);
            }

            throw $e;
        }

        // The old files are only removed once the new ones are safely saved.
        foreach ($oldPaths as $path) {
            Storage::disk(TruckDocument::DISK)->delete($path);
        }

        return response()->json([
            'haulers' => Hauler::forOps(),
            'drivers' => Driver::forOps(),
            'trucks'  => Truck::forOps(),
        ], $isNew ? 201 : 200);
    }

    /**
     * Decides what to do with a driver typed by name (same rules as the page):
     * reuse the driver already on file for this hauler, otherwise rename the truck's current driver,
     * otherwise create a new one. Nothing is saved here.
     */
    private function planDriver(string $name, ?int $haulerId, string $role, ?Truck $truck, ?int $currentId): array
    {
        $existing = $haulerId ? Driver::where('hauler_id', $haulerId)->where('name', $name)->first() : null;

        if ($existing) {
            if ($role === 'driver') {
                $other = Truck::where('driver_id', $existing->id)
                    ->when($truck, fn ($q) => $q->where('id', '!=', $truck->id))
                    ->first();

                if ($other) {
                    throw ValidationException::withMessages([
                        'driver_name' => "{$existing->name} is already the driver of {$other->plate}. Each driver has one truck.",
                    ]);
                }
            }

            return ['id' => $existing->id, 'rename' => null, 'create' => null];
        }

        if ($currentId) {
            $current = Driver::find($currentId);
            $shared = $role === 'sub'
                && Truck::where('substitute_driver_id', $currentId)
                    ->when($truck, fn ($q) => $q->where('id', '!=', $truck->id))
                    ->exists();

            if ($current && ! $shared) {
                return ['id' => $current->id, 'rename' => $name, 'create' => null];
            }
        }

        return ['id' => null, 'rename' => null, 'create' => $name];
    }

    private function applyDriver(array $plan, int $haulerId): int
    {
        if ($plan['id']) {
            $driver = Driver::findOrFail($plan['id']);
            $driver->hauler_id = $haulerId;
            if ($plan['rename']) {
                $driver->name = $plan['rename'];
            }
            $driver->save();

            return $driver->id;
        }

        return Driver::create(['hauler_id' => $haulerId, 'name' => $plan['create']])->id;
    }
}