<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Models\Hauler;
use App\Models\Truck;
use App\Support\Phone;
use App\Support\Role;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Edit a driver's details from the Drivers tab. Drivers are created from the truck form. */
class DriverController extends Controller
{
    public function update(Request $request, int $id): JsonResponse
    {
        abort_unless(Role::can('drivers.manage'), 403);

        $driver = Driver::findOrFail($id);

        $data = $request->validate([
            'name'  => ['required', 'string', 'max:255', Rule::unique('drivers', 'name')->where('hauler_id', $driver->hauler_id)->ignore($driver->id)],
            'phone' => ['nullable', 'string', 'max:30', Phone::rule()],
        ], [
            'name.required' => 'Enter the driver\'s full name.',
            'name.unique'   => 'This hauler already has a driver with this name.',
        ]);

        $driver->update([
            'name'  => trim($data['name']),
            'phone' => Phone::formatMobile($data['phone'] ?? null),
        ]);

        return response()->json([
            'haulers' => Hauler::forOps(),
            'drivers' => Driver::forOps(),
            'trucks'  => Truck::forOps(),
        ]);
    }
}