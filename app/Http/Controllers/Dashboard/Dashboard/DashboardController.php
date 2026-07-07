<?php

namespace App\Http\Controllers\Dashboard\Dashboard;

use App\Http\Controllers\Controller;
use App\Services\Dashboard\Dashboard\IDashboardService;

class DashboardController extends Controller
{
    public function __construct(protected IDashboardService $dashboardService)
    {
    }

    public function index()
    {
        return view('dashboard.dashboard.index');
    }
}
