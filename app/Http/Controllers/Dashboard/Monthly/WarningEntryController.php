<?php

namespace App\Http\Controllers\Dashboard\Monthly;

use App\Http\Controllers\Controller;
use App\Services\Dashboard\Monthly\IWarningEntryService;

class WarningEntryController extends Controller
{
    public function __construct(protected IWarningEntryService $warningEntryService)
    {
    }

    public function index($period)
    {
        return view('dashboard.monthly.warnings.index');
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
