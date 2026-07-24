<?php

use App\Livewire\MealPlanner;
use App\Livewire\Pantry;
use App\Livewire\ShoppingList;
use App\Livewire\Welcome;
use Illuminate\Support\Facades\Route;

Route::get('/', Welcome::class)->name('home');
Route::get('/planning', MealPlanner::class)->name('planning');
Route::get('/courses', ShoppingList::class)->name('courses');
Route::get('/reserves', Pantry::class)->name('reserves');
