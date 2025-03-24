<?php

use App\Http\Controllers\Api\{
    EventController,
    FixtureController,
    PlayController
};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::name('api.')->group(function () {
    Route::get('/fixture', FixtureController::class)->name('fixture');
    Route::prefix('/play')->name('play.')->controller(PlayController::class)->group(function () {
        Route::patch('/next-week', 'runNextWeek')->name('next-week');
        Route::patch('/all-weeks', 'runAllWeeks')->name('all-weeks');
    });
    Route::prefix('/match')->name('event.')->controller(EventController::class)->group(function () {
        Route::patch('/{id}', 'modify')->name('modify');
    });
});
