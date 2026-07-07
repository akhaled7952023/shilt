<?php

namespace App\Http\Controllers\Dashboard\Vehicles;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\Vehicles\StoreVehicleRentalRequest;
use App\Http\Requests\Dashboard\Vehicles\UpdateVehicleRentalRequest;
use App\Models\MonthlyPeriod;
use App\Models\Vehicle;
use App\Models\VehicleRental;
use App\Services\Dashboard\Delegates\IDelegateService;
use App\Services\Dashboard\Vehicles\IVehicleRentalService;
use Illuminate\Validation\ValidationException;

class VehicleRentalController extends Controller
{
    public function __construct(
        protected IVehicleRentalService $vehicleRentalService,
        protected IDelegateService      $delegateService,
    ) {}

    public function index(Vehicle $vehicle)
    {
        return redirect()->route('dashboard.vehicles.show', $vehicle);
    }

    public function create(Vehicle $vehicle)
    {
        return redirect()->route('dashboard.vehicles.show', $vehicle);
    }

    public function store(StoreVehicleRentalRequest $request, Vehicle $vehicle)
    {
        $this->authorize('update', $vehicle);

        try {
            $this->vehicleRentalService->create($vehicle->id, $request->validated());
            flash()->success('تم إضافة بيانات الإيجار بنجاح');
            return redirect()->route('dashboard.vehicles.rentals.index', $vehicle);
        } catch (ValidationException $e) {
            flash()->error(collect($e->errors())->flatten()->first());
            return redirect()->back()->withInput();
        }
    }

    public function edit(Vehicle $vehicle, VehicleRental $rental)
    {
        $this->authorize('update', $vehicle);

        abort_if($rental->vehicle_id !== $vehicle->id, 403);

        $periods   = MonthlyPeriod::orderByDesc('year')->orderByDesc('month')->get();
        $delegates = $this->delegateService->getActive();

        return view('dashboard.vehicles.rentals.edit', compact('vehicle', 'rental', 'periods', 'delegates'));
    }

    public function update(UpdateVehicleRentalRequest $request, Vehicle $vehicle, VehicleRental $rental)
    {
        $this->authorize('update', $vehicle);

        abort_if($rental->vehicle_id !== $vehicle->id, 403);

        try {
            $this->vehicleRentalService->update($rental->id, $request->validated());
            flash()->success('تم تحديث بيانات الإيجار بنجاح');
            return redirect()->route('dashboard.vehicles.rentals.index', $vehicle);
        } catch (ValidationException $e) {
            flash()->error(collect($e->errors())->flatten()->first());
            return redirect()->back()->withInput();
        }
    }

    public function destroy(Vehicle $vehicle, VehicleRental $rental)
    {
        $this->authorize('update', $vehicle);

        abort_if($rental->vehicle_id !== $vehicle->id, 403);

        try {
            $this->vehicleRentalService->delete($rental->id);
            flash()->success('تم حذف بيانات الإيجار بنجاح');
        } catch (ValidationException $e) {
            flash()->error(collect($e->errors())->flatten()->first());
        }

        return redirect()->route('dashboard.vehicles.rentals.index', $vehicle);
    }
}
