<?php

namespace App\Http\Controllers\Dashboard\Vehicles;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\Vehicles\StoreVehicleViolationRequest;
use App\Http\Requests\Dashboard\Vehicles\UpdateVehicleViolationRequest;
use App\Models\Vehicle;
use App\Models\VehicleViolation;
use App\Services\Dashboard\Vehicles\IVehicleViolationService;
use Illuminate\Validation\ValidationException;

class VehicleViolationController extends Controller
{
    public function __construct(protected IVehicleViolationService $violationService) {}

    public function index(Vehicle $vehicle)
    {
        return redirect()->route('dashboard.vehicles.show', $vehicle);
    }

    public function store(StoreVehicleViolationRequest $request, Vehicle $vehicle)
    {
        $this->authorize('update', $vehicle);

        try {
            $this->violationService->create($vehicle->id, $request->validated());
            flash()->success('تم إضافة المخالفة بنجاح');
        } catch (ValidationException $e) {
            flash()->error(collect($e->errors())->flatten()->first());
        }

        return redirect()->route('dashboard.vehicles.show', $vehicle)->with('tab', 'violations');
    }

    public function edit(Vehicle $vehicle, VehicleViolation $violation)
    {
        abort_if($violation->vehicle_id !== $vehicle->id, 403);

        return view('dashboard.vehicles.violation_edit', compact('vehicle', 'violation'));
    }

    public function update(UpdateVehicleViolationRequest $request, Vehicle $vehicle, VehicleViolation $violation)
    {
        $this->authorize('update', $vehicle);
        abort_if($violation->vehicle_id !== $vehicle->id, 403);

        try {
            $this->violationService->update($violation->id, $request->validated());
            flash()->success('تم تحديث المخالفة بنجاح');
        } catch (ValidationException $e) {
            flash()->error(collect($e->errors())->flatten()->first());
        }

        return redirect()->route('dashboard.vehicles.show', $vehicle)->with('tab', 'violations');
    }

    public function destroy(Vehicle $vehicle, VehicleViolation $violation)
    {
        $this->authorize('update', $vehicle);
        abort_if($violation->vehicle_id !== $vehicle->id, 403);

        $this->violationService->delete($violation->id);
        flash()->success('تم حذف المخالفة بنجاح');

        return redirect()->route('dashboard.vehicles.show', $vehicle)->with('tab', 'violations');
    }
}
