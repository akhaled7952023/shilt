<?php
namespace App\Repositories\Dashboard\RolesAndManagers\Roles;


interface IRolesRepository{

    public function getAllRoles();
    public function getRole($id);
    public function createRole($request);
    public function updateRole($request , $role);
    public function destroy($role);



}


