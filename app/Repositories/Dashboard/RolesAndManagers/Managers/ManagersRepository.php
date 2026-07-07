<?php
namespace App\Repositories\Dashboard\RolesAndManagers\Managers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class ManagersRepository implements IManagersRepository
{
    public function getAllManagers()
    {
        $managers = User::select('id', 'name', 'email', 'role_id')
        ->where('id', '!=', 1)
        ->paginate(10);
        return $managers;
    }
    public function getManagerByid($id)
    {
        $manager = User::find($id);
        return $manager;
    }
    public function createManager($request)
    {
        $manager = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'role_id' => $request->role_id,
            'password' => Hash::make($request->password),
        ]);

        return $manager;
    }
    public function updateUserProfile($request, $manager)
    {
        $manager = $manager->update([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        return $manager;
    }

    public function updateManagerRole($request, $manager)
    {
        $manager->update([
            'role_id' => $request->role_id,
        ]);

        return $manager;
    }

    public function destroy($manager)
    {
        return $manager->delete();
    }

    public function getAllRoles()
    {
        return Role::select('id', 'name')->get();
    }
}
