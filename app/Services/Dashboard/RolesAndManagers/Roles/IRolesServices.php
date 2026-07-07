<?php
namespace App\Services\Dashboard\RolesAndManagers\Roles;

interface IRolesServices {

    public function getAllRoles();
    public function getRole($id);
    public function createRole($request);
    public function updateRole($request , $id);
    public function destroy($id);
}
