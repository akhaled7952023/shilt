<?php

namespace App\Http\Controllers\Dashboard\MasterData;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\MasterData\StoreWarningTypeRequest;
use App\Http\Requests\Dashboard\MasterData\UpdateWarningTypeRequest;
use App\Services\Dashboard\MasterData\WarningTypes\IWarningTypeService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class WarningTypeController extends Controller
{
    public function __construct(protected IWarningTypeService $warningTypeService)
    {
    }

    public function index(Request $request)
    {
        $filters      = $request->only(['search']);
        $warningTypes = $this->warningTypeService->getAll($filters);

        return view('dashboard.master-data.warning-types.index', compact('warningTypes', 'filters'));
    }

    public function create()
    {
        return view('dashboard.master-data.warning-types.create');
    }

    public function store(StoreWarningTypeRequest $request)
    {
        $this->warningTypeService->create($request->validated());

        flash()->success('تم إضافة نوع الإنذار بنجاح');

        return redirect()->route('dashboard.master-data.warning-types.index');
    }

    public function show($id)
    {
        return redirect()->route('dashboard.master-data.warning-types.index');
    }

    public function edit($id)
    {
        $warningType = $this->warningTypeService->getById($id);

        return view('dashboard.master-data.warning-types.edit', compact('warningType'));
    }

    public function update(UpdateWarningTypeRequest $request, $id)
    {
        $this->warningTypeService->update($id, $request->validated());

        flash()->success('تم تحديث نوع الإنذار بنجاح');

        return redirect()->route('dashboard.master-data.warning-types.index');
    }

    public function destroy($id)
    {
        try {
            $this->warningTypeService->delete($id);
            flash()->success('تم حذف نوع الإنذار بنجاح');
        } catch (ValidationException $e) {
            flash()->error(collect($e->errors())->flatten()->first());
        }

        return redirect()->route('dashboard.master-data.warning-types.index');
    }

    public function toggle($id)
    {
        $this->warningTypeService->toggleActive($id);

        flash()->success('تم تحديث حالة نوع الإنذار بنجاح');

        return redirect()->back();
    }
}
