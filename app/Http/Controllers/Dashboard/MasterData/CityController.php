<?php

namespace App\Http\Controllers\Dashboard\MasterData;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\MasterData\StoreCityRequest;
use App\Http\Requests\Dashboard\MasterData\UpdateCityRequest;
use App\Services\Dashboard\MasterData\Cities\ICityService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CityController extends Controller
{
    public function __construct(protected ICityService $cityService)
    {
    }

    public function index(Request $request)
    {
        $filters = $request->only(['search']);
        $cities  = $this->cityService->getAll($filters);

        return view('dashboard.master-data.cities.index', compact('cities', 'filters'));
    }

    public function create()
    {
        return view('dashboard.master-data.cities.create');
    }

    public function store(StoreCityRequest $request)
    {
        $this->cityService->create($request->validated());

        flash()->success('تم إضافة المدينة بنجاح');

        return redirect()->route('dashboard.master-data.cities.index');
    }

    public function show($id)
    {
        return redirect()->route('dashboard.master-data.cities.index');
    }

    public function edit($id)
    {
        $city = $this->cityService->getById($id);

        return view('dashboard.master-data.cities.edit', compact('city'));
    }

    public function update(UpdateCityRequest $request, $id)
    {
        $this->cityService->update($id, $request->validated());

        flash()->success('تم تحديث المدينة بنجاح');

        return redirect()->route('dashboard.master-data.cities.index');
    }

    public function destroy($id)
    {
        try {
            $this->cityService->delete($id);
            flash()->success('تم حذف المدينة بنجاح');
        } catch (ValidationException $e) {
            flash()->error(collect($e->errors())->flatten()->first());
        }

        return redirect()->route('dashboard.master-data.cities.index');
    }

    public function toggle($id)
    {
        $this->cityService->toggleActive($id);

        flash()->success('تم تحديث حالة المدينة بنجاح');

        return redirect()->back();
    }
}
