<?php

namespace App\Http\Controllers\Dashboard\Vehicles;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\Vehicles\StoreVehicleMaintenanceRequest;
use App\Http\Requests\Dashboard\Vehicles\UpdateVehicleMaintenanceRequest;
use App\Models\Vehicle;
use App\Models\VehicleMaintenance;
use App\Services\Dashboard\Vehicles\IVehicleMaintenanceService;
use Illuminate\Validation\ValidationException;

class VehicleMaintenanceController extends Controller
{
    public function __construct(protected IVehicleMaintenanceService $maintenanceService) {}

    public function index(Vehicle $vehicle)
    {
        return redirect()->route('dashboard.vehicles.show', $vehicle);
    }

    public function store(StoreVehicleMaintenanceRequest $request, Vehicle $vehicle)
    {
        $this->authorize('update', $vehicle);

        try {
            $this->maintenanceService->create($vehicle->id, $request->validated());
            flash()->success('تم إضافة سجل الصيانة بنجاح');
        } catch (ValidationException $e) {
            flash()->error(collect($e->errors())->flatten()->first());
        }

        return redirect()->route('dashboard.vehicles.show', $vehicle)->with('tab', 'maintenance');
    }

    public function edit(Vehicle $vehicle, VehicleMaintenance $maintenance)
    {
        abort_if($maintenance->vehicle_id !== $vehicle->id, 403);

        return view('dashboard.vehicles.maintenance_edit', compact('vehicle', 'maintenance'));
    }

    public function update(UpdateVehicleMaintenanceRequest $request, Vehicle $vehicle, VehicleMaintenance $maintenance)
    {
        $this->authorize('update', $vehicle);
        abort_if($maintenance->vehicle_id !== $vehicle->id, 403);

        try {
            $this->maintenanceService->update($maintenance->id, $request->validated());
            flash()->success('تم تحديث سجل الصيانة بنجاح');
        } catch (ValidationException $e) {
            flash()->error(collect($e->errors())->flatten()->first());
        }

        return redirect()->route('dashboard.vehicles.show', $vehicle)->with('tab', 'maintenance');
    }

    public function destroy(Vehicle $vehicle, VehicleMaintenance $maintenance)
    {
        $this->authorize('update', $vehicle);
        abort_if($maintenance->vehicle_id !== $vehicle->id, 403);

        $this->maintenanceService->delete($maintenance->id);
        flash()->success('تم حذف سجل الصيانة بنجاح');

        return redirect()->route('dashboard.vehicles.show', $vehicle)->with('tab', 'maintenance');
    }
}
