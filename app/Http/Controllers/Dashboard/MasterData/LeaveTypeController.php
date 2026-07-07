<?php

namespace App\Http\Controllers\Dashboard\MasterData;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\MasterData\StoreLeaveTypeRequest;
use App\Http\Requests\Dashboard\MasterData\UpdateLeaveTypeRequest;
use App\Services\Dashboard\MasterData\LeaveTypes\ILeaveTypeService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class LeaveTypeController extends Controller
{
    public function __construct(protected ILeaveTypeService $leaveTypeService)
    {
    }

    public function index(Request $request)
    {
        $filters    = $request->only(['search']);
        $leaveTypes = $this->leaveTypeService->getAll($filters);

        return view('dashboard.master-data.leave-types.index', compact('leaveTypes', 'filters'));
    }

    public function create()
    {
        return view('dashboard.master-data.leave-types.create');
    }

    public function store(StoreLeaveTypeRequest $request)
    {
        $this->leaveTypeService->create($request->validated());

        flash()->success('تم إضافة نوع الإجازة بنجاح');

        return redirect()->route('dashboard.master-data.leave-types.index');
    }

    public function show($id)
    {
        return redirect()->route('dashboard.master-data.leave-types.index');
    }

    public function edit($id)
    {
        $leaveType = $this->leaveTypeService->getById($id);

        return view('dashboard.master-data.leave-types.edit', compact('leaveType'));
    }

    public function update(UpdateLeaveTypeRequest $request, $id)
    {
        $this->leaveTypeService->update($id, $request->validated());

        flash()->success('تم تحديث نوع الإجازة بنجاح');

        return redirect()->route('dashboard.master-data.leave-types.index');
    }

    public function destroy($id)
    {
        try {
            $this->leaveTypeService->delete($id);
            flash()->success('تم حذف نوع الإجازة بنجاح');
        } catch (ValidationException $e) {
            flash()->error(collect($e->errors())->flatten()->first());
        }

        return redirect()->route('dashboard.master-data.leave-types.index');
    }

    public function toggle($id)
    {
        $this->leaveTypeService->toggleActive($id);

        flash()->success('تم تحديث حالة نوع الإجازة بنجاح');

        return redirect()->back();
    }
}
