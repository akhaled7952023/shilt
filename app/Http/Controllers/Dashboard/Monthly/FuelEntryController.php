<?php

namespace App\Http\Controllers\Dashboard\Monthly;

use App\Http\Controllers\Controller;
use App\Services\Dashboard\Monthly\IFuelEntryService;

class FuelEntryController extends Controller
{
    public function __construct(protected IFuelEntryService $fuelEntryService)
    {
    }

    public function index($period)
    {
        return view('dashboard.monthly.fuel.index');
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
