<?php

namespace App\Http\Controllers\Dashboard\Monthly;

use App\Http\Controllers\Controller;
use App\Services\Dashboard\Monthly\IDelegateMonthlyEntryService;

class DelegateMonthlyEntryController extends Controller
{
    public function __construct(protected IDelegateMonthlyEntryService $delegateMonthlyEntryService)
    {
    }

    public function index($period)
    {
        return view('dashboard.monthly.entries.index');
    }

    public function show($period, $entry)
    {
        return view('dashboard.monthly.entries.show');
    }

    public function createOrUpdate($period)
    {
        return redirect()->back();
    }

    public function recalculate($period, $entry)
    {
        return redirect()->back();
    }
}
