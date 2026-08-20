<?php

use App\Http\Controllers\HomeController;
use App\Livewire\Actors\CreateGuardian;
use App\Livewire\Actors\CreateStudent;
use App\Livewire\Actors\CreateTeacher;
use App\Livewire\Actors\GuardiansRoster;
use App\Livewire\Actors\ImportStudents;
use App\Livewire\Actors\ImportTeachers;
use App\Livewire\Actors\StaffGuardiansRoster;
use App\Livewire\Actors\StaffStudentsRoster;
use App\Livewire\Actors\StaffTeachersRoster;
use App\Livewire\Actors\StudentsRoster;
use App\Livewire\Actors\TeachersRoster;
use App\Livewire\Admin\CreateInternalUser;
use App\Livewire\Admin\PermissionsIndex;
use App\Livewire\Admin\RolePermissions;
use App\Livewire\Admin\RolesIndex;
use App\Livewire\Admin\UsersIndex;
use App\Livewire\Alerts\CreateAlert;
use App\Livewire\Alerts\Index as AlertsIndex;
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

    Route::livewire('admin/users', UsersIndex::class)
        ->middleware('role:super_admin')
        ->name('admin.users.index');

    Route::livewire('admin/users/create', CreateInternalUser::class)
        ->middleware('role:super_admin')
        ->name('admin.users.create');

    Route::livewire('admin/users/{user}/edit', CreateInternalUser::class)
        ->middleware('role:super_admin')
        ->name('admin.users.edit');

    Route::livewire('admin/roles', RolesIndex::class)
        ->middleware('role:super_admin')
        ->name('admin.roles.index');

    Route::livewire('admin/roles/{role}/permissions', RolePermissions::class)
        ->middleware('role:super_admin')
        ->name('admin.roles.permissions');

    Route::livewire('admin/permissions', PermissionsIndex::class)
        ->middleware('role:super_admin')
        ->name('admin.permissions.index');

    Route::livewire('actors/students', StudentsRoster::class)
        ->middleware('role:institution_admin')
        ->name('actors.students.index');

    Route::livewire('actors/teachers', TeachersRoster::class)
        ->middleware('role:institution_admin')
        ->name('actors.teachers.index');

    Route::livewire('actors/guardians', GuardiansRoster::class)
        ->middleware('role:institution_admin')
        ->name('actors.guardians.index');

    Route::livewire('actors/teachers/create', CreateTeacher::class)
        ->name('actors.teachers.create');

    Route::livewire('actors/teachers/{teacher}/edit', CreateTeacher::class)
        ->name('actors.teachers.edit');

    Route::livewire('actors/students/create', CreateStudent::class)
        ->name('actors.students.create');

    Route::livewire('actors/students/{student}/edit', CreateStudent::class)
        ->name('actors.students.edit');

    Route::livewire('actors/guardians/create', CreateGuardian::class)
        ->name('actors.guardians.create');

    Route::livewire('actors/teachers/import', ImportTeachers::class)
        ->name('actors.teachers.import');

    Route::livewire('actors/students/import', ImportStudents::class)
        ->name('actors.students.import');

    Route::livewire('directory/students', StaffStudentsRoster::class)
        ->middleware('permission:view-institution-members')
        ->name('directory.students');

    Route::livewire('directory/teachers', StaffTeachersRoster::class)
        ->middleware('permission:view-institution-members')
        ->name('directory.teachers');

    Route::livewire('directory/guardians', StaffGuardiansRoster::class)
        ->middleware('permission:view-institution-members')
        ->name('directory.guardians');

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

    Route::livewire('alerts', AlertsIndex::class)
        ->middleware('role:institution_admin|teacher')
        ->name('alerts.index');

    Route::livewire('alerts/create', CreateAlert::class)
        ->middleware('role:institution_admin|teacher')
        ->name('alerts.create');
});

require __DIR__.'/settings.php';
