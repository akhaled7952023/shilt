<?php

namespace App\Http\Controllers\Dashboard\Monthly;

use App\Http\Controllers\Controller;
use App\Services\Dashboard\Monthly\IAdvanceEntryService;

class AdvanceEntryController extends Controller
{
    public function __construct(protected IAdvanceEntryService $advanceEntryService)
    {
    }

    public function index($period)
    {
        return view('dashboard.monthly.advances.index');
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
