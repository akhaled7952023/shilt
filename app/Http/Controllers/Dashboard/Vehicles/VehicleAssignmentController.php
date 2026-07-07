<?php

namespace App\Http\Controllers\Dashboard\Vehicles;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\Vehicles\StoreVehicleAssignmentRequest;
use App\Models\Vehicle;
use App\Services\Dashboard\Vehicles\IVehicleAssignmentService;
use Illuminate\Validation\ValidationException;

class VehicleAssignmentController extends Controller
{
    public function __construct(protected IVehicleAssignmentService $vehicleAssignmentService) {}

    public function index(Vehicle $vehicle)
    {
        return redirect()->route('dashboard.vehicles.show', $vehicle);
    }

    public function store(StoreVehicleAssignmentRequest $request, Vehicle $vehicle)
    {
        $this->authorize('update', $vehicle);

        try {
            $this->vehicleAssignmentService->assign($vehicle->id, $request->validated());
            flash()->success('تم تعيين المندوب على المركبة بنجاح');
        } catch (ValidationException $e) {
            flash()->error(collect($e->errors())->flatten()->first());
        }

        return redirect()->route('dashboard.vehicles.show', $vehicle)->with('tab', 'current-driver');
    }

    public function unassign(Vehicle $vehicle)
    {
        $this->authorize('update', $vehicle);

        try {
            $this->vehicleAssignmentService->unassign($vehicle->id);
            flash()->success('تم فصل المندوب عن المركبة بنجاح');
        } catch (ValidationException $e) {
            flash()->error(collect($e->errors())->flatten()->first());
        }

        return redirect()->route('dashboard.vehicles.show', $vehicle)->with('tab', 'current-driver');
    }
}
