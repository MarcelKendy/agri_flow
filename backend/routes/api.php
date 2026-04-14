<?php

// IMPORTS
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProductController;   
use App\Http\Controllers\CropController;                        
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserProfileController;

// UNPROTECTED ROUTES

    // AUTHENTICATION ROUTES        
        Route::post('/send_otp', [AuthController::class, 'sendOtp']);
        Route::post('/resend_otp', [AuthController::class, 'resendOtp']);
        Route::post('/check_otp_n_login', [AuthController::class, 'checkOtpNLogin']);
        //Route::post('/login', [AuthController::class, 'login']);
        Route::post('/signin', [AuthController::class, 'signin']);
        Route::post('/send_password_reset_mail', [AuthController::class, 'sendPasswordResetMail']);
        Route::post('/verify_password_reset_token', [AuthController::class, 'verifyPasswordResetToken']);           
        Route::post('/reset_password_n_login', [AuthController::class, 'resetPasswordNLogin']);
        Route::get('/auth', [AuthController::class, 'validateSession']);
        Route::post('/delete_session', [AuthController::class, 'deleteSession']);
    // AUTHENTICATION ROUTES

// UNPROTECTED ROUTES

// PROTECTED ROUTES

    Route::middleware(['jwt.auth'])->group(function () {

        // USER ROUTES            
            Route::get('/get_users', [UserController::class, 'getUsers']);
            Route::get('/get_users_photos', [UserController::class, 'getUsersPhotos']);
            Route::get('/get_user/{user}', [UserController::class, 'getUser']);
            Route::get('/get_user_password_expire', [UserController::class, 'getUserPasswordExpire']);
            Route::post('/add_user', [UserController::class, 'addUser']);
            Route::post('/edit_profile', [UserController::class, 'editProfile']);
            Route::put('/edit_user/{user}', [UserController::class, 'editUser']);
            //Route::put('/reset_password/{user}', [UserController::class, 'resetPassword']);
        // USER ROUTES

        // CROP ROUTES
            //Route::get('/get_product/{product}', [ProductController::class, 'getProduct']);
            Route::get('/get_crops', [CropController::class, 'getCrops']);
            Route::post('/add_crop', [CropController::class, 'addCrop']);
            Route::put('/edit_crop/{crop}', [CropController::class, 'editCrop']);
            Route::delete('/delete_crop/{crop}', [CropController::class, 'deleteCrop']);
        // CROP ROUTES

        // PRODUCT ROUTES
            //Route::get('/get_product/{product}', [ProductController::class, 'getProduct']);
            Route::get('/get_products', [ProductController::class, 'getProducts']);
            Route::post('/add_product', [ProductController::class, 'addProduct']);
            Route::put('/edit_product/{product}', [ProductController::class, 'editProduct']);
            Route::delete('/delete_product/{product}', [ProductController::class, 'deleteProduct']);
        // PRODUCT ROUTES

        // DASHBOARD ROUTES
            Route::get('/get_campaigns_dashboard', [DashboardController::class, 'getCampaignsDashboard']);
            Route::get('/get_progress_bar_chart', [DashboardController::class, 'getProgressBarChart']);
            Route::get('/get_groups_ranking', [DashboardController::class, 'getGroupsRanking']);
            Route::get('/get_dashboard_saved', [DashboardController::class, 'getDashboardSaved']);
        // DASHBOARD ROUTES

        // USER PROFILE ROUTES
            Route::get('/get_active_session', [UserProfileController::class, 'getActiveSession']);
        // USER PROFILE ROUTES

    });

// PROTECTED ROUTES