<?php
namespace App\Repositories\Dashboard\Auth;

use App\Models\User;
use Ichtrojan\Otp\Otp;
use Illuminate\Support\Facades\Auth;

class AuthRepository implements IAuthRepository{

    protected $otp;
    public function __construct()
    {
        $this->otp = new Otp();
    }
    public function login($credentials , $guard , $remember)
    {
        return Auth::guard($guard)->attempt($credentials,$remember);
    }

    public function logout($guard)
    {
        return Auth::guard($guard)->logout();
    }

    public function getAdminByEmail($email)
    {
        $admin = User::where('email', $email)->first();
        return $admin;
    }

    public function verifyOtp($data)
    {
        $otp = $this->otp->validate($data['email'] , $data['code']);
        return $otp;
    }

    public function resetPassword($email , $password)
    {
        $admin = self::getAdminByEmail($email);
        $admin = $admin->update([
            'password'=>bcrypt($password),
        ]);
        return $admin;
    }
}
