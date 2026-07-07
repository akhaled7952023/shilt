<?php

namespace App\Http\Controllers\Dashboard\Vehicles;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\Vehicles\StoreVehicleRequest;
use App\Http\Requests\Dashboard\Vehicles\UpdateVehicleRequest;
use App\Models\Vehicle;
use App\Services\Dashboard\MasterData\VehicleTypes\IVehicleTypeService;
use App\Services\Dashboard\Vehicles\IVehicleService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class VehicleController extends Controller
{
    public function __construct(
        protected IVehicleService     $vehicleService,
        protected IVehicleTypeService $vehicleTypeService,
    ) {}

    public function index(Request $request)
    {
        $filters      = $request->only(['status', 'vehicle_type_id', 'search']);
        $vehicles     = $this->vehicleService->getAll($filters);
        $vehicleTypes = $this->vehicleTypeService->getAllActive();

        return view('dashboard.vehicles.index', compact('vehicles', 'vehicleTypes', 'filters'));
    }

    public function create()
    {
        $this->authorize('create', Vehicle::class);

        $vehicleTypes = $this->vehicleTypeService->getAllActive();

        return view('dashboard.vehicles.create', compact('vehicleTypes'));
    }

    public function store(StoreVehicleRequest $request)
    {
        $this->authorize('create', Vehicle::class);

        $this->vehicleService->create(
            $request->validated(),
            $request->file('vehicle_image'),
            $request->file('registration_image'),
            $request->file('insurance_image')
        );

        flash()->success('تم إضافة المركبة بنجاح');
        return redirect()->route('dashboard.vehicles.index');
    }

    public function show(Vehicle $vehicle)
    {
        $this->authorize('view', $vehicle);

        $vehicle = $this->vehicleService->getWithRelations($vehicle->id);

        return view('dashboard.vehicles.show', compact('vehicle'));
    }

    public function edit(Vehicle $vehicle)
    {
        $this->authorize('update', $vehicle);

        $vehicleTypes = $this->vehicleTypeService->getAllActive();

        return view('dashboard.vehicles.edit', compact('vehicle', 'vehicleTypes'));
    }

    public function update(UpdateVehicleRequest $request, Vehicle $vehicle)
    {
        $this->authorize('update', $vehicle);

        try {
            $this->vehicleService->update(
                $vehicle->id,
                $request->validated(),
                $request->file('vehicle_image'),
                $request->file('registration_image'),
                $request->file('insurance_image')
            );

            flash()->success('تم تحديث بيانات المركبة بنجاح');
            return redirect()->route('dashboard.vehicles.show', $vehicle);
        } catch (ValidationException $e) {
            flash()->error(collect($e->errors())->flatten()->first());
            return redirect()->back()->withInput();
        }
    }

    public function destroy(Vehicle $vehicle)
    {
        $this->authorize('delete', $vehicle);

        try {
            $this->vehicleService->delete($vehicle->id);
            flash()->success('تم حذف المركبة بنجاح');
        } catch (ValidationException $e) {
            flash()->error(collect($e->errors())->flatten()->first());
        }

        return redirect()->route('dashboard.vehicles.index');
    }
}
