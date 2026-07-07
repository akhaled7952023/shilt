<?php
namespace App\Repositories\Dashboard\Auth;


interface IAuthRepository{


    public function login($credentials , $guard , $remember);
    public function logOut($guard);
    public function getAdminByEmail($email);
    public function verifyOtp($data);
    public function resetPassword($email , $password);
}
