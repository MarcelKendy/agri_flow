<?php

// IMPORTS
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CampaignController;
use App\Http\Controllers\ProductController;                        
use App\Http\Controllers\CampaignProductController;
use App\Http\Controllers\GroupController;
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

    // GROUPS ROUTES
        Route::get('/get_groups', [GroupController::class, 'getGroups']);
    // GROUPS ROUTES

// UNPROTECTED ROUTES

// PROTECTED ROUTES

    Route::middleware(['jwt.auth'])->group(function () {

        // SALE ROUTES
            //Route::get('/get_sale/{sale}', [SaleController::class, 'getSale']);
            Route::get('/get_sales', [SaleController::class, 'getSales']);            
            Route::post('/add_sale', [SaleController::class, 'addSale']);
            //Route::post('/add_sales', [SaleController::class, 'addSales']);
            Route::put('/edit_sale/{sale}', [SaleController::class, 'editSale']);
            //Route::post('/edit_sales', [SaleController::class, 'editSales']);
            Route::delete('/delete_sale/{sale}', [SaleController::class, 'deleteSale']);
            //Route::post('/delete_sales', [SaleController::class, 'deleteSales']);
        // SALE ROUTES

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

        // CAMPAIGN ROUTES
            //Route::get('/get_campaign/{campaign}', [CampaignController::class, 'getCampaign']);
            Route::get('/get_campaigns', [CampaignController::class, 'getCampaigns']);
            Route::post('/add_campaign', [CampaignController::class, 'addCampaign']);
            Route::put('/edit_campaign/{campaign}', [CampaignController::class, 'editCampaign']);
            Route::put('/save_campaign/{campaign}', [CampaignController::class, 'saveCampaign']);
            Route::delete('/delete_campaign/{campaign}', [CampaignController::class, 'deleteCampaign']);
        // CAMPAIGN ROUTES

        // PRODUCT ROUTES
            //Route::get('/get_product/{product}', [ProductController::class, 'getProduct']);
            Route::get('/get_products', [ProductController::class, 'getProducts']);
            Route::post('/add_product', [ProductController::class, 'addProduct']);
            Route::put('/edit_product/{product}', [ProductController::class, 'editProduct']);
            Route::delete('/delete_product/{product}', [ProductController::class, 'deleteProduct']);
        // PRODUCT ROUTES

        // CAMPAIGN PRODUCT ROUTES
            //Route::get('/get_campaign_product/{campaign_product}', [CampaignProductController::class, 'getCampaignProduct']);
            //Route::get('/get_campaign_products', [CampaignProductController::class, 'getCampaignProducts']);
            Route::post('/add_campaign_product', [CampaignProductController::class, 'addCampaignProduct']);
            Route::put('/edit_campaign_product/{campaign_product}', [CampaignProductController::class, 'editCampaignProduct']);
            Route::delete('/delete_campaign_product/{campaign_product}', [CampaignProductController::class, 'deleteCampaignProduct']);
        // CAMPAIGN PRODUCT ROUTES

        // GROUP ROUTES
            //Route::get('/get_group/{group}', [GroupController::class, 'getGroup']);
            Route::post('/add_group', [GroupController::class, 'addGroup']);
            Route::put('/edit_group/{group}', [GroupController::class, 'editGroup']);
            Route::delete('/delete_group/{group}', [GroupController::class, 'deleteGroup']);
        // GROUP ROUTES

        // DASHBOARD ROUTES
            Route::get('/get_campaigns_dashboard', [DashboardController::class, 'getCampaignsDashboard']);
            Route::get('/get_progress_bar_chart', [DashboardController::class, 'getProgressBarChart']);
            Route::get('/get_groups_ranking', [DashboardController::class, 'getGroupsRanking']);
            Route::get('/get_dashboard_saved', [DashboardController::class, 'getDashboardSaved']);
        // DASHBOARD ROUTES

        // USER PROFILE ROUTES
            Route::get('/get_top_sellers_bar_chart', [UserProfileController::class, 'getTopSellersBarChart']);
            Route::get('/get_active_session', [UserProfileController::class, 'getActiveSession']);
            Route::get('/get_campaigns_podium_card', [UserProfileController::class, 'getCampaignsPodiumCard']);
            Route::get('/get_registered_sales_card', [UserProfileController::class, 'getRegisteredSalesCard']);
        // USER PROFILE ROUTES

    });

// PROTECTED ROUTES