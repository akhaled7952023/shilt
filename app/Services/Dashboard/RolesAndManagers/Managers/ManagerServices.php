<?php
namespace App\Services\Dashboard\RolesAndManagers\Managers;

use App\Repositories\Dashboard\RolesAndManagers\Managers\IManagersRepository;

class ManagerServices implements IManagerServices {


    protected IManagersRepository $managerRepository;


    public function __construct(IManagersRepository $managerRepository)
    {
        $this->managerRepository = $managerRepository;

    }

    public function getAllManagers(){
        return $this->managerRepository->getAllManagers();
    }
    public function getManagerByid($id){
        return $this->managerRepository->getManagerByid($id);
    }
    public function getAllRoles(){
       return $this->managerRepository->getAllRoles();
    }
    public function createManager($request){
        return $this->managerRepository->createManager($request);
    }
    public function updateUserProfile($request , $manager){
        $manager = $this->managerRepository->getManagerByid($manager);
        if(!$manager){
            return false;
        }
        return $this->managerRepository->updateUserProfile($request , $manager);
    }
    public function updateManagerRole($request, $manager){

        $manager = $this->managerRepository->getManagerByid($manager);
        if(!$manager){
            return false;
        }
        return $this->managerRepository->updateManagerRole($request , $manager);
    }
    public function destroy($manager){

        $manager = $this->managerRepository->getManagerByid($manager);

        if(!$manager){
            return false;
        }


        return $this->managerRepository->destroy($manager);
    }
 }
