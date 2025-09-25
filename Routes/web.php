<?php

use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['auth']], function () {
    Route::prefix('flightoperations')->group(function () {
        Route::get('/', 'FlightOperationsController@index')->name('flightoperations.index');
        Route::get('/get-fleet/{airlineId}', 'FlightOperationsController@getFleet')->name('flightoperations.get-fleet');
        Route::post('/create-flight', 'FlightOperationsController@createFlight')->name('flightoperations.create-flight');
        Route::post('/delete-bid/{bidId}', 'FlightOperationsController@deleteBid')->name('flightoperations.delete-bid');
        });
    });