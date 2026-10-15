<?php

namespace App\Http\Controllers;

use App\Models\Hauler;
use App\Support\Role;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Support\Phone;

/** Add and edit haulers. Used by the Hauler Management page (public/ops.js sends JSON). */
class HaulerController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $this->allow();
        $data = $this->validated($request);

        $hauler = Hauler::create([
            'name'       => $data['name'],
            'contact'    => $data['contact'] ?? null,
            'phone'      => Phone::formatMobile($data['phone'] ?? null),
            'address'    => $data['addr'],
            'created_by' => $request->user()->id,
        ]);

        return response()->json(['hauler' => $hauler->toOps()], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $this->allow();
        $hauler = Hauler::findOrFail($id);
        $data = $this->validated($request, $hauler->id);

        $hauler->update([
            'name'    => $data['name'],
            'contact' => $data['contact'] ?? null,
            'phone'   => Phone::formatMobile($data['phone'] ?? null),
            'address' => $data['addr'],
        ]);

        return response()->json(['hauler' => $hauler->fresh()->toOps()]);
    }

    /** Follows the interface being viewed: Admin / IT and Operations may manage haulers. */
    private function allow(): void
    {
        abort_unless(Role::can('haulers.manage'), 403);
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'name'    => ['required', 'string', 'max:255', Rule::unique('haulers', 'name')->ignore($ignoreId)],
            'contact' => ['nullable', 'string', 'max:255'],
                        'phone'   => ['nullable', 'string', 'max:30', function (string $attribute, mixed $value, \Closure $fail) {
                if (! Phone::isMobile($value)) {
                    $fail('Enter a mobile number like 09171234567 (11 digits, starting with 09).');
                }
            }],
            'addr'    => ['required', 'string', 'max:500'],
        ], [
            'name.required' => 'Enter the company name.',
            'name.unique'   => 'A hauler with this company name already exists.',
            'addr.required' => 'Enter the company address.',
           
        ]);
    }
}