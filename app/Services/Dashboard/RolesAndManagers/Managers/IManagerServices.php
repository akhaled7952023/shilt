<?php
namespace App\Services\Dashboard\RolesAndManagers\Managers;

interface IManagerServices {

    public function getAllManagers();
    public function getManagerByid($id);
    public function createManager($request);
    public function updateUserProfile($request , $manager);
    public function updateManagerRole($request, $manager);
    public function destroy($manager);
    public function getAllRoles();
 }

