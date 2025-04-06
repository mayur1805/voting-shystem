<?php

use App\Http\Controllers\VoteController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/', [VoteController::class, 'showForm'])->name('vote.form');
Route::post('/send-otp', [VoteController::class, 'sendOtp'])->name('vote.sendOtp');
Route::post('/verify-otp', [VoteController::class, 'verifyOtp'])->name('vote.verifyOtp');
Route::post('/submit-vote', [VoteController::class, 'storeVote'])->name('vote.store');
// Route::get('/admin/votes', [VoteController::class, 'adminView'])->name('admin.votes');
Route::post('/check-user', [VoteController::class, 'checkExistsUser'])->name('check.user');

Auth::routes();

// Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
