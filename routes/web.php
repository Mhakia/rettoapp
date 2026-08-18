<?php

use App\Http\Controllers\HomeController;
use App\Livewire\Actors\CreateStudent;
use App\Livewire\Actors\CreateTeacher;
use App\Livewire\Actors\EnrollActor;
use App\Livewire\Actors\ImportStudents;
use App\Livewire\Actors\ImportTeachers;
use App\Livewire\Actors\Roster;
use App\Livewire\Challenges\Catalog;
use App\Livewire\Challenges\Index as ChallengesIndex;
use App\Livewire\Challenges\ManageChallenges;
use App\Livewire\Challenges\Statistics;
use App\Livewire\Challenges\VerificationQueue;
use App\Livewire\Institutions\Create as InstitutionsCreate;
use App\Livewire\Institutions\Index as InstitutionsIndex;
use App\Livewire\Institutions\Show as InstitutionsShow;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)
    ->middleware('guest')
    ->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');

    Route::livewire('actors/enroll', EnrollActor::class)
        ->middleware('role:institution_admin')
        ->name('actors.enroll');

    Route::livewire('actors/roster', Roster::class)
        ->middleware('role:institution_admin')
        ->name('actors.roster');

    Route::livewire('actors/teachers/create', CreateTeacher::class)
        ->name('actors.teachers.create');

    Route::livewire('actors/students/create', CreateStudent::class)
        ->name('actors.students.create');

    Route::livewire('actors/teachers/import', ImportTeachers::class)
        ->name('actors.teachers.import');

    Route::livewire('actors/students/import', ImportStudents::class)
        ->name('actors.students.import');

    Route::livewire('institutions', InstitutionsIndex::class)
        ->middleware('permission:view-institutions')
        ->name('institutions.index');

    Route::livewire('institutions/create', InstitutionsCreate::class)
        ->middleware('permission:create-institution')
        ->name('institutions.create');

    Route::livewire('institutions/{institution}', InstitutionsShow::class)
        ->middleware('permission:view-institutions')
        ->name('institutions.show');

    Route::livewire('challenges', Catalog::class)
        ->middleware('role:student|teacher|guardian')
        ->name('challenges.catalog');

    Route::livewire('challenges/manage', ChallengesIndex::class)
        ->middleware('permission:create-challenge|update-challenge')
        ->name('challenges.manage');

    Route::livewire('challenges/manage/create', ManageChallenges::class)
        ->middleware('permission:create-challenge')
        ->name('challenges.manage.create');

    Route::livewire('challenges/manage/{challenge}/edit', ManageChallenges::class)
        ->middleware('permission:update-challenge')
        ->name('challenges.manage.edit');

    Route::livewire('challenges/verify', VerificationQueue::class)
        ->middleware('role:teacher')
        ->name('challenges.verify');

    Route::livewire('challenges/statistics', Statistics::class)
        ->middleware('role:pedagogue|super_admin')
        ->name('challenges.statistics');
});

require __DIR__.'/settings.php';
