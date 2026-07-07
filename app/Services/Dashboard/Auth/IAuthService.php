<?php
namespace App\Services\Dashboard\Auth;

interface IAuthService {


    public function login($credenstials , $guard , $remember);
    public function  logout($guard);
    public function sendOtp($email);
    public function verifyOtp($data);
    public function resetPassword($email , $password);
}
