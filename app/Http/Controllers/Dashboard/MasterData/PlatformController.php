<?php

namespace App\Http\Controllers\Dashboard\MasterData;

use App\Http\Controllers\Controller;
use App\Services\Dashboard\MasterData\Platforms\IPlatformService;
use Illuminate\Http\Request;

class PlatformController extends Controller
{
    public function __construct(protected IPlatformService $platformService)
    {
    }

    public function index(Request $request)
    {
        $platforms = $this->platformService->getAll([]);

        return view('dashboard.master-data.platforms.index', compact('platforms'));
    }
}
