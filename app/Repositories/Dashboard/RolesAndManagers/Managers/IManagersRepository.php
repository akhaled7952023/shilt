<?php
namespace App\Repositories\Dashboard\RolesAndManagers\Managers;


interface IManagersRepository{

    public function getAllManagers();
    public function getManagerByid($id);
    public function createManager($request);
    public function updateUserProfile($request , $manager);
    public function updateManagerRole($request, $manager);
    public function destroy($manager);
    public function getAllRoles();




}
