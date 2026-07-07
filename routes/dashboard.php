<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Dashboard\Auth\AuthController;
use App\Http\Controllers\Dashboard\Dashboard\DashboardController;
use App\Http\Controllers\Dashboard\RolesAndManagers\ManagerController;
use App\Http\Controllers\Dashboard\RolesAndManagers\RoleController;
use App\Http\Controllers\Dashboard\Settings\SystemSettingController;
use App\Http\Controllers\WelcomeController;

Route::group(
    [
        'prefix' => 'dashboard',
        'as' => 'dashboard.',
    ],
    function () {
        ################# Auth ##############################
        Route::get('login', [AuthController::class, 'showLoginForm'])->name('login');
        Route::post('login', [AuthController::class, 'login'])->name('login.post');
        Route::post('logout', [AuthController::class, 'logout'])->name('logout');

        ################################# Reset Password #############################
        Route::group(['prefix' => 'password', 'as' => 'password.'], function () {
            Route::controller(AuthController::class)->group(function () {
                Route::get('email', 'showEmailForm')->name('email');
                Route::post('email', 'sendOtp')->name('email.post');
                Route::get('verify', 'showOtpForm')->name('verify');
                Route::post('verify', 'verifyOtp')->name('verify.post');
                Route::get('reset', 'showResetForm')->name('reset');
                Route::post('reset', 'resetPassword')->name('reset.post');
            });
        });
        ################################## End Pssword #################################

        ################# Protected Routed  ##############################
        Route::group(['middleware' => 'auth'], function () {
            Route::get('/', [DashboardController::class, 'index'])->name('index');

            Route::middleware('permission:settings')->group(function () {
                Route::get('settings', [SystemSettingController::class, 'index'])->name('settings.index');
                Route::get('settings/{key}/edit', [SystemSettingController::class, 'edit'])->name('settings.edit');
                Route::put('settings/{key}', [SystemSettingController::class, 'update'])->name('settings.update');
            });

            // ####################################### Welcome Routes #######################################
            Route::get('welcome', [WelcomeController::class, 'index'])->name('welcome');

            ####################################### Welcome Routes #######################################
            Route::resource('roles', RoleController::class)->middleware('permission:admins');
            Route::resource('managers', ManagerController::class)->middleware('permission:admins');

            Route::patch('/managers/{id}/role', [ManagerController::class, 'updateprofile'])->name('managers.updateprofile');


        });
    },
);
