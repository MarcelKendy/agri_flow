<?php

// IMPORTS
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProductController;   
use App\Http\Controllers\CropController;                        
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FieldRecordController;
use App\Http\Controllers\ImplementController;
use App\Http\Controllers\PivotController;
use App\Http\Controllers\PlantingController;
use App\Http\Controllers\TractorController;
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

        // FIELD RECORD ROUTES
            //Route::get('/get_field_record/{field_record}', [FieldRecordController::class, 'getFieldRecord']);
            Route::get('/get_field_records', [FieldRecordController::class, 'getFieldRecords']);
            Route::post('/add_field_record', [FieldRecordController::class, 'addFieldRecord']);
            Route::put('/edit_field_record/{field_record}', [FieldRecordController::class, 'editFieldRecord']);
            Route::delete('/delete_field_record/{field_record}', [FieldRecordController::class, 'deleteFieldRecord']);
        // FIELD RECORD ROUTES

        // PLANTING ROUTES
            //Route::get('/get_planting/{planting}', [PlantingController::class, 'getPlanting']);
            Route::get('/get_plantings', [PlantingController::class, 'getPlantings']);
            Route::post('/add_planting', [PlantingController::class, 'addPlanting']);
            Route::put('/edit_planting/{planting}', [PlantingController::class, 'editPlanting']);
            Route::delete('/delete_planting/{planting}', [PlantingController::class, 'deletePlanting']);
        // PLANTING ROUTES

        // CROP ROUTES
            //Route::get('/get_product/{product}', [ProductController::class, 'getProduct']);
            Route::get('/get_crops', [CropController::class, 'getCrops']);
            Route::post('/add_crop', [CropController::class, 'addCrop']);
            Route::put('/edit_crop/{crop}', [CropController::class, 'editCrop']);
            Route::delete('/delete_crop/{crop}', [CropController::class, 'deleteCrop']);
        // CROP ROUTES

        // PIVOT ROUTES
            //Route::get('/get_pivot/{pivot}', [PivotController::class, 'getPivot']);
            Route::get('/get_pivots', [PivotController::class, 'getPivots']);
            Route::post('/add_pivot', [PivotController::class, 'addPivot']);
            Route::put('/edit_pivot/{pivot}', [PivotController::class, 'editPivot']);
            Route::delete('/delete_pivot/{pivot}', [PivotController::class, 'deletePivot']);
        // PIVOT ROUTES

        // TRACTOR ROUTES
            //Route::get('/get_tractor/{tractor}', [TractorController::class, 'getTractor']);
            Route::get('/get_tractors', [TractorController::class, 'getTractors']);
            Route::post('/add_tractor', [TractorController::class, 'addTractor']);
            Route::put('/edit_tractor/{tractor}', [TractorController::class, 'editTractor']);
            Route::delete('/delete_tractor/{tractor}', [TractorController::class, 'deleteTractor']);
        // TRACTOR ROUTES

        // IMPLEMENT ROUTES
            //Route::get('/get_implement/{implement}', [ImplementController::class, 'getImplement']);
            Route::get('/get_implements', [ImplementController::class, 'getImplements']);
            Route::post('/add_implement', [ImplementController::class, 'addImplement']);
            Route::put('/edit_implement/{implement}', [ImplementController::class, 'editImplement']);
            Route::delete('/delete_implement/{implement}', [ImplementController::class, 'deleteImplement']);
        // IMPLEMENT ROUTES

        // PRODUCT ROUTES
            //Route::get('/get_product/{product}', [ProductController::class, 'getProduct']);
            Route::get('/get_products', [ProductController::class, 'getProducts']);
            Route::post('/add_product', [ProductController::class, 'addProduct']);
            Route::post('/import_products', [ProductController::class, 'importProducts']);
            Route::put('/edit_product/{product}', [ProductController::class, 'editProduct']);
            Route::delete('/delete_product/{product}', [ProductController::class, 'deleteProduct']);
        // PRODUCT ROUTES

        // DASHBOARD ROUTES

        // DASHBOARD ROUTES

        // USER PROFILE ROUTES
            Route::get('/get_active_session', [UserProfileController::class, 'getActiveSession']);
        // USER PROFILE ROUTES

    });

// PROTECTED ROUTES