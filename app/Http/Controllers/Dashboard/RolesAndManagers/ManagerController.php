<?php

namespace App\Http\Controllers\Dashboard\RolesAndManagers;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateManagerRequest;
use App\Http\Requests\EditProfileRequest;
use App\Http\Requests\UpdatRoleManagerRequest;
use App\Services\Dashboard\RolesAndManagers\Managers\IManagerServices;
use Illuminate\Http\Request;

class ManagerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    protected $managerServices;


    public function __construct(IManagerServices $managerServices)
    {
        $this->managerServices = $managerServices;

    }
    public function index()
    {
        $managers = $this->managerServices->getAllManagers();
        return view('dashboard.managers.index' , compact('managers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $roles = $this->managerServices->getAllRoles();
        return view('dashboard.managers.create', compact('roles'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateManagerRequest $request)
    {

        $manager = $this->managerServices->createManager($request);

        if(!$manager) {
            return back()->withErrors('error', __('dashboard.error_msg'));
        }

        return redirect()->route('dashboard.managers.index')->with('success', __('dashboard.success_msg'));
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // Use It For Edit Profile For User
        $manager = $this->managerServices->getManagerById($id);
        return view('dashboard.managers.editprofile', compact('manager'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $manager = $this->managerServices->getManagerById($id);
        $roles = $this->managerServices->getAllRoles();
        return view('dashboard.managers.edit', compact('roles','manager'));

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatRoleManagerRequest $request, string $id)
    {
        $manager = $this->managerServices->updateManagerRole($request , $id);
        if(!$manager){
            return back()->with('error', __('dashboard.error_msg'));
        }
        return redirect()->route('dashboard.managers.index')->with('success', __('dashboard.success_msg'));

    }

    public function updateprofile(EditProfileRequest $request, string $id)
    {
        $manager = $this->managerServices->updateUserProfile($request , $id);
        if(!$manager){
            return back()->with('error', __('dashboard.error_msg'));
        }
        return redirect()->route('dashboard.welcome')->with('success', __('dashboard.success_msg'));
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
{
    $manager = $this->managerServices->destroy($id);

    if (!$manager) {
        return back()->with('error', __('dashboard.error_msg'));
    }

    return redirect()->route('dashboard.managers.index')->with('success', __('dashboard.success_msg'));
}
}
