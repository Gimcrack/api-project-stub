<?php

use Illuminate\Support\Facades\Route;

Route::post("users/{user}/promote", "UserPromotionController@store");
Route::post("users/{user}/demote", "UserPromotionController@destroy");

Route::apiResource('users','UserController');




