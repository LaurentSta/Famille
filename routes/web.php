<?php

use App\Livewire\AssistantCuisine;
use App\Livewire\Auth\Connexion;
use App\Livewire\Auth\Inscription;
use App\Livewire\GestionCuisine;
use App\Livewire\PlanificateurRepas;
use App\Livewire\ListeCourses;
use App\Livewire\Accueil;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/connexion', Connexion::class)->name('login');
    Route::get('/inscription', Inscription::class)->name('inscription');
});

Route::post('/deconnexion', function () {
    Auth::logout();
    session()->invalidate();
    session()->regenerateToken();

    return redirect()->route('login');
})->name('deconnexion')->middleware('auth');

Route::middleware('auth')->group(function () {
    Route::get('/', Accueil::class)->name('accueil');
    Route::get('/planning', PlanificateurRepas::class)->name('planning');
    Route::get('/courses', ListeCourses::class)->name('courses');
    Route::get('/ia', AssistantCuisine::class)->name('assistant-cuisine');
    Route::get('/gestion', GestionCuisine::class)->name('gestion-cuisine');
});
