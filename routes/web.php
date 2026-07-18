<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'pages.home');
Route::view('/about', 'pages.about');
Route::view('/hotels', 'pages.hotels');
Route::view('/flights', 'pages.flights');
Route::view('/cruises', 'pages.cruises');
Route::view('/staycation', 'pages.staycation');
Route::view('/offers', 'pages.offers');
Route::view('/contact', 'pages.contact');