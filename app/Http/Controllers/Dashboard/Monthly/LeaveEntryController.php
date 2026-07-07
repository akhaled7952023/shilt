<?php

namespace App\Http\Controllers\Dashboard\Monthly;

use App\Http\Controllers\Controller;
use App\Services\Dashboard\Monthly\ILeaveEntryService;

class LeaveEntryController extends Controller
{
    public function __construct(protected ILeaveEntryService $leaveEntryService)
    {
    }

    public function index($period)
    {
        return view('dashboard.monthly.leaves.index');
    }

    public function store($period)
    {
        return redirect()->back();
    }

    public function update($period, $id)
    {
        return redirect()->back();
    }

    public function destroy($period, $id)
    {
        return redirect()->back();
    }
}
