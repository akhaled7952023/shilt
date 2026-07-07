<?php
namespace App\Services\Dashboard\Auth;



use Ichtrojan\Otp\Otp;
use App\Notifications\SendOtpNotify;
use App\Repositories\Dashboard\Auth\IAuthRepository;
class AuthService implements IAuthService {

    protected IAuthRepository $authRepository;


    public function __construct(IAuthRepository $authRepository)
    {
        $this->authRepository = $authRepository;

    }
    public function login($credenstials , $guard , $remember)
    {
        return $this->authRepository->login($credenstials , $guard , $remember);
    }

    public function logout($guard)
    {
        return $this->authRepository->logout($guard);
    }

    public function sendOtp($email)
    {
        $admin = $this->authRepository->getAdminByEmail($email);
        if(!$admin){
            return false;
        }
        $admin->notify(new SendOtpNotify());
        return $admin;
    }

    public function verifyOtp($data)
    {
        $otp = $this->authRepository->verifyOtp($data);
        return $otp->status;
    }

    public function resetPassword($email , $password)
    {
        return $this->authRepository->resetPassword($email , $password);
    }
}
