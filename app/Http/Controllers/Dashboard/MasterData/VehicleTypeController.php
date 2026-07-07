<?php

namespace App\Http\Controllers\Dashboard\MasterData;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\MasterData\StoreVehicleTypeRequest;
use App\Http\Requests\Dashboard\MasterData\UpdateVehicleTypeRequest;
use App\Services\Dashboard\MasterData\VehicleTypes\IVehicleTypeService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class VehicleTypeController extends Controller
{
    public function __construct(protected IVehicleTypeService $vehicleTypeService)
    {
    }

    public function index(Request $request)
    {
        $filters      = $request->only(['search']);
        $vehicleTypes = $this->vehicleTypeService->getAll($filters);

        return view('dashboard.master-data.vehicle-types.index', compact('vehicleTypes', 'filters'));
    }

    public function create()
    {
        return view('dashboard.master-data.vehicle-types.create');
    }

    public function store(StoreVehicleTypeRequest $request)
    {
        $this->vehicleTypeService->create($request->validated());

        flash()->success('تم إضافة نوع المركبة بنجاح');

        return redirect()->route('dashboard.master-data.vehicle-types.index');
    }

    public function show($id)
    {
        return redirect()->route('dashboard.master-data.vehicle-types.index');
    }

    public function edit($id)
    {
        $vehicleType = $this->vehicleTypeService->getById($id);

        return view('dashboard.master-data.vehicle-types.edit', compact('vehicleType'));
    }

    public function update(UpdateVehicleTypeRequest $request, $id)
    {
        $this->vehicleTypeService->update($id, $request->validated());

        flash()->success('تم تحديث نوع المركبة بنجاح');

        return redirect()->route('dashboard.master-data.vehicle-types.index');
    }

    public function destroy($id)
    {
        try {
            $this->vehicleTypeService->delete($id);
            flash()->success('تم حذف نوع المركبة بنجاح');
        } catch (ValidationException $e) {
            flash()->error(collect($e->errors())->flatten()->first());
        }

        return redirect()->route('dashboard.master-data.vehicle-types.index');
    }

    public function toggle($id)
    {
        $this->vehicleTypeService->toggleActive($id);

        flash()->success('تم تحديث حالة نوع المركبة بنجاح');

        return redirect()->back();
    }
}
