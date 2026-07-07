<?php
namespace App\Services\Dashboard\RolesAndManagers\Roles;

use App\Repositories\Dashboard\RolesAndManagers\Roles\IRolesRepository;

class RolesServices implements IRolesServices {

    protected IRolesRepository $roleRepository;


    public function __construct(IRolesRepository $authRepository)
    {
        $this->roleRepository = $authRepository;

    }
    public function getAllRoles(){
        return $this->roleRepository->getAllRoles();
    }
    public function getRole($id){

        return $this->roleRepository->getRole($id);
    }
    public function createRole($request){

        $role = $this->roleRepository->createRole($request);
        return $role;
    }
    public function updateRole($request , $id){

        $role = $this->roleRepository->getRole($id);
        if(!$role){
            return false;
        }
        return $this->roleRepository->updateRole($request , $role);
    }
    public function destroy($id)
    {
        $role = $this->roleRepository->getRole($id);

        if($role->admins->count()>0 || !$role){
            return false;
        }

        return $this->roleRepository->destroy($role);

    }
}
