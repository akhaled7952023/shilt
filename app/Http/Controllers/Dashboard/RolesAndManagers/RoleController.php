<?php

namespace App\Http\Controllers\Dashboard\RolesAndManagers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\RoleRequest;
use App\Services\Dashboard\RolesAndManagers\Roles\IRolesServices;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */

     protected $roleServices;


     public function __construct(IRolesServices $roleServices)
     {
         $this->roleServices = $roleServices;

     }

    public function index()
    {
        $roles = $this->roleServices->getAllRoles();
        return view('dashboard.roles.index' , compact('roles'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('dashboard.roles.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(RoleRequest $request)
    {
        $role = $this->roleServices->createRole($request);
        if(!$role){
            return back()->with('error', __('dashboard.error_msg'));
        }
        return redirect()->route('dashboard.roles.index')->with('success', __('dashboard.success_msg'));


    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {

    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {

        $role = $this->roleServices->getRole($id);
        if(!$role){
            return back()->with('error', __('dashboard.error_msg'));
        }
        return view('dashboard.roles.edit' , compact('role'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(RoleRequest $request, string $id)
    {
        $role = $this->roleServices->updateRole($request , $id);
        if(!$role){
            return back()->with('error', __('dashboard.error_msg'));
        }
        return redirect()->route('dashboard.roles.index')->with('success', __('dashboard.success_msg'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
       $role = $this->roleServices->destroy($id);
       if(!$role){
            return back()->with('error', __('dashboard.error_msg'));
       }
       return redirect()->back()->with('success' , __('dashboard.success_msg'));

    }
}
