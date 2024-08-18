<?php

use App\Http\Controllers\BirthdayController;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('form');
});

Route::controller(BirthdayController::class)->group(function () {
    Route::get('/',  'index');
    Route::post('/send-otp', 'sendOtp')->name('sendotp');
    Route::post('/save', 'save')->name('save');
});


Route::get('/migrate/{key}', function ($key) {
    if ($key !== '123456789') {
        return 'Invalid access key';
    }
    Artisan::call('optimize:clear');
    $optimizeOutput = Artisan::output();
    Artisan::call('migrate');
    $migrateOutput = Artisan::output();
    Artisan::call('db:seed');
    $seedOutput = Artisan::output();
    Artisan::call('storage:link');
    $linkOutput = Artisan::output();
    $output = "$optimizeOutput\n<br>";
    $output .= "$migrateOutput\n<br>";
    $output .= "$seedOutput\n<br>";
    $output .= "$linkOutput\n<br>";
    $output = str_replace('DONE', '<br>', $output);
    return response($output);
});
