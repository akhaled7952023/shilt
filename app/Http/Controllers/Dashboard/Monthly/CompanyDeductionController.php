<?php

namespace App\Http\Controllers\Dashboard\Monthly;

use App\Http\Controllers\Controller;
use App\Services\Dashboard\Monthly\ICompanyDeductionService;

class CompanyDeductionController extends Controller
{
    public function __construct(protected ICompanyDeductionService $companyDeductionService)
    {
    }

    public function sync($period, $entry)
    {
        return redirect()->back();
    }
}
