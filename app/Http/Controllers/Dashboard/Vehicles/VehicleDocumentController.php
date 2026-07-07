<?php

namespace App\Http\Controllers\Dashboard\Vehicles;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\Vehicles\StoreVehicleDocumentRequest;
use App\Models\Vehicle;
use App\Models\VehicleDocument;
use App\Services\Dashboard\Vehicles\IVehicleDocumentService;
use Illuminate\Validation\ValidationException;

class VehicleDocumentController extends Controller
{
    public function __construct(protected IVehicleDocumentService $vehicleDocumentService) {}

    public function index(Vehicle $vehicle)
    {
        return redirect()->route('dashboard.vehicles.show', $vehicle);
    }

    public function store(StoreVehicleDocumentRequest $request, Vehicle $vehicle)
    {
        $this->authorize('update', $vehicle);

        try {
            $this->vehicleDocumentService->store(
                $vehicle->id,
                $request->validated(),
                $request->file('file')
            );
            flash()->success('تم رفع الوثيقة بنجاح');
        } catch (ValidationException $e) {
            flash()->error(collect($e->errors())->flatten()->first());
        }

        return redirect()->route('dashboard.vehicles.show', $vehicle);
    }

    public function destroy(Vehicle $vehicle, VehicleDocument $document)
    {
        $this->authorize('update', $vehicle);

        abort_if($document->vehicle_id !== $vehicle->id, 403);

        $this->vehicleDocumentService->delete($document->id);
        flash()->success('تم حذف الوثيقة بنجاح');

        return redirect()->route('dashboard.vehicles.show', $vehicle);
    }
}
