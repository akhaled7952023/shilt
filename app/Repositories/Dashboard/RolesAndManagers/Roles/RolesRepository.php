<?php
namespace App\Repositories\Dashboard\RolesAndManagers\Roles;

use App\Models\Role;

class RolesRepository implements IRolesRepository
{
    public function getAllRoles()
    {
        $roles = Role::select('id', 'name', 'permissions')->paginate(10);
        return $roles;
    }
    public function getRole($id)
    {
        $role = Role::find($id);
        return $role;
    }

    public function createRole($request)
    {
        return Role::create([
            'name' => $request->name,
            'permissions' => $request->permissions,
        ]);
    }

    public function updateRole($request, $role)
    {
        return $role->update([
            'name' => $request->name,
            'permissions' => $request->permissions,
        ]);
    }

    public function destroy($role)
    {
        return $role->delete();
    }
}
