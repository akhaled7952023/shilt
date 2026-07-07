<?php

namespace App\Http\Controllers\Dashboard\Delegates;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\Delegates\StoreDelegateLeaveRequest;
use App\Http\Requests\Dashboard\Delegates\UpdateDelegateLeaveRequest;
use App\Models\Delegate;
use App\Models\LeaveEntry;
use App\Services\Dashboard\Delegates\IDelegateLeaveService;

class DelegateLeaveController extends Controller
{
    public function __construct(protected IDelegateLeaveService $leaveService) {}

    public function store(StoreDelegateLeaveRequest $request, Delegate $delegate)
    {
        $this->authorize('update', $delegate);

        $this->leaveService->create($delegate->id, $request->validated());

        flash()->success('تم إضافة الإجازة بنجاح');
        return redirect()->route('dashboard.delegates.show', $delegate)->with('tab', 'leaves');
    }

    public function edit(Delegate $delegate, LeaveEntry $leave)
    {
        $this->authorize('update', $delegate);

        abort_if($leave->delegate_id !== $delegate->id, 403);

        $leaveTypes = app(\App\Services\Dashboard\MasterData\LeaveTypes\ILeaveTypeService::class)->getAllActive();

        return view('dashboard.delegates.leave_edit', compact('delegate', 'leave', 'leaveTypes'));
    }

    public function update(UpdateDelegateLeaveRequest $request, Delegate $delegate, LeaveEntry $leave)
    {
        $this->authorize('update', $delegate);

        abort_if($leave->delegate_id !== $delegate->id, 403);

        $this->leaveService->update($leave->id, $request->validated());

        flash()->success('تم تحديث الإجازة بنجاح');
        return redirect()->route('dashboard.delegates.show', $delegate)->with('tab', 'leaves');
    }

    public function destroy(Delegate $delegate, LeaveEntry $leave)
    {
        $this->authorize('update', $delegate);

        abort_if($leave->delegate_id !== $delegate->id, 403);

        $this->leaveService->delete($leave->id);

        flash()->success('تم حذف الإجازة بنجاح');
        return redirect()->route('dashboard.delegates.show', $delegate)->with('tab', 'leaves');
    }
}
