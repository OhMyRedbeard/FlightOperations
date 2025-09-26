<?php

use Illuminate\Support\Facades\Route;

Route::group([
    'middleware' => ['api.auth'],
    'prefix' => 'flight-operations',
    'as' => 'api.flightoperations.'
], function () {
    // Keep aircraft loading as it's still needed for dynamic dropdowns
    Route::get('/get-fleet/{airlineId}', 'FlightOperationsController@getFleet')->name('get_fleet');
    Route::get('/bid-details', 'FlightOperationsController@getBidDetails')->name('bid_details');
    Route::get('/simbrief-options', 'FlightOperationsController@getSimbriefOptions')->name('simbrief_options');
    Route::get('/flight-stats', 'FlightOperationsController@getFlightStats')->name('flight_stats');
});
