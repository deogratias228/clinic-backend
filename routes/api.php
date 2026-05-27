<?php

use App\Http\Controllers\Api\V1\AppointmentController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\ClinicController;
use App\Http\Controllers\Api\V1\ConsultationController;
use App\Http\Controllers\Api\V1\DoctorController;
use App\Http\Controllers\Api\V1\InvoiceController;
use App\Http\Controllers\Api\V1\PatientController;
use App\Http\Controllers\Api\V1\PaymentController;
use App\Http\Controllers\Api\V1\RoleController;
use App\Http\Controllers\Api\V1\ScheduleController;
use App\Http\Controllers\Api\V1\SlotController;
use App\Http\Controllers\Api\V1\UserController;
use App\Http\Controllers\Api\V1\VisitController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Routes publiques — accessibles sans authentification
|--------------------------------------------------------------------------
*/
Route::prefix('v1')->group(function () {

    // Infos cabinet + thème (chargé par le frontend au démarrage)
    Route::get('clinic', [ClinicController::class, 'public']);

    // Site public — médecins et booking
    Route::get('doctors', [DoctorController::class, 'index']);
    Route::get('doctors/{doctor}/slots', [SlotController::class, 'byDate']);
    Route::get('doctors/{doctor}/available-dates', [SlotController::class, 'availableDates']);
    Route::post('doctors/{doctor}/book', [AppointmentController::class, 'book']);

    // Authentification
    Route::post('auth/login', [AuthController::class, 'login']);

    /*
    |--------------------------------------------------------------------------
    | Routes admin — Sanctum requis
    |--------------------------------------------------------------------------
    */
    Route::middleware('auth:sanctum')->prefix('admin')->group(function () {

        // Auth
        Route::get('auth/me', [AuthController::class, 'me']);
        Route::post('auth/logout', [AuthController::class, 'logout']);

        // ── Rendez-vous ───────────────────────────────────────────────────────
        Route::middleware('permission:appointments.view')->group(function () {
            Route::get('appointments', [AppointmentController::class, 'index']);
            Route::get('appointments/{appointment}', [AppointmentController::class, 'show']);
        });
        Route::post('appointments', [AppointmentController::class, 'store'])
            ->middleware('permission:appointments.create');
        Route::patch('appointments/{appointment}', [AppointmentController::class, 'update'])
            ->middleware('permission:appointments.update');
        Route::delete('appointments/{appointment}', [AppointmentController::class, 'destroy'])
            ->middleware('permission:appointments.delete');

        // ── Patients ──────────────────────────────────────────────────────────
        Route::middleware('permission:patients.view')->group(function () {
            Route::get('patients', [PatientController::class, 'index']);
            Route::get('patients/{patient}', [PatientController::class, 'show']);
        });
        Route::post('patients', [PatientController::class, 'store'])
            ->middleware('permission:patients.create');
        Route::patch('patients/{patient}', [PatientController::class, 'update'])
            ->middleware('permission:patients.update');
        Route::delete('patients/{patient}', [PatientController::class, 'destroy'])
            ->middleware('permission:patients.delete');

        // ── Visites ───────────────────────────────────────────────────────────
        Route::middleware('permission:visits.view')->group(function () {
            Route::get('visits', [VisitController::class, 'index']);
            Route::get('visits/{visit}', [VisitController::class, 'show']);
        });
        Route::post('visits', [VisitController::class, 'store'])
            ->middleware('permission:visits.create');
        Route::post('appointments/{appointment}/visit', [VisitController::class, 'createFromAppointment'])
            ->middleware('permission:visits.create');
        Route::patch('visits/{visit}/advance', [VisitController::class, 'advance'])
            ->middleware('permission:visits.update');
        Route::patch('visits/{visit}/rollback', [VisitController::class, 'rollback'])
            ->middleware('permission:visits.update');

        // ── Consultations ─────────────────────────────────────────────────────
        Route::middleware('permission:consultations.view')->group(function () {
            Route::get('visits/{visit}/consultation', [ConsultationController::class, 'showByVisit']);
        });
        Route::post('visits/{visit}/consultation', [ConsultationController::class, 'store'])
            ->middleware('permission:consultations.create');
        Route::patch('consultations/{consultation}', [ConsultationController::class, 'update'])
            ->middleware('permission:consultations.update');
        Route::post('consultations/{consultation}/prescriptions', [ConsultationController::class, 'storePrescription'])
            ->middleware('permission:consultations.create');
        Route::patch('prescriptions/{prescription}/mark-printed', [ConsultationController::class, 'markPrinted'])
            ->middleware('permission:consultations.update');
        Route::delete('prescriptions/{prescription}', [ConsultationController::class, 'destroyPrescription'])
            ->middleware('permission:consultations.update');

        // ── Facturation ───────────────────────────────────────────────────────
        Route::middleware('permission:billing.view')->group(function () {
            Route::get('invoices', [InvoiceController::class, 'index']);
            Route::get('invoices/{invoice}', [InvoiceController::class, 'show']);
        });
        Route::post('visits/{visit}/invoice', [InvoiceController::class, 'store'])
            ->middleware('permission:billing.create');
        Route::patch('invoices/{invoice}', [InvoiceController::class, 'update'])
            ->middleware('permission:billing.update');
        Route::post('invoices/{invoice}/issue', [InvoiceController::class, 'issue'])
            ->middleware('permission:billing.update');
        Route::post('invoices/{invoice}/cancel', [InvoiceController::class, 'cancel'])
            ->middleware('permission:billing.delete');
        Route::post('invoices/{invoice}/items', [InvoiceController::class, 'addItem'])
            ->middleware('permission:billing.update');
        Route::delete('invoices/{invoice}/items/{item}', [InvoiceController::class, 'removeItem'])
            ->middleware('permission:billing.update');

        // Paiements
        Route::post('invoices/{invoice}/payments', [PaymentController::class, 'store'])
            ->middleware('permission:payments.create');
        Route::delete('payments/{payment}', [PaymentController::class, 'destroy'])
            ->middleware('permission:payments.create');

        // ── Médecins et horaires ──────────────────────────────────────────────
        Route::middleware('permission:doctors.view')->group(function () {
            Route::get('doctors', [DoctorController::class, 'adminIndex']);
            Route::get('doctors/{doctor}', [DoctorController::class, 'show']);
        });
        Route::post('doctors', [DoctorController::class, 'store'])->middleware('permission:doctors.manage');
        Route::post('doctors/{doctor}', [DoctorController::class, 'update'])->middleware('permission:doctors.manage'); // POST pour multipart/form-data
        Route::delete('doctors/{doctor}', [DoctorController::class, 'destroy'])->middleware('permission:doctors.manage');

        Route::get('doctors/{doctor}/schedules', [ScheduleController::class, 'index'])->middleware('permission:doctors.view');
        Route::put('doctors/{doctor}/schedules', [ScheduleController::class, 'sync'])->middleware('permission:schedules.manage');
        Route::get('doctors/{doctor}/unavailabilities', [ScheduleController::class, 'unavailabilities'])->middleware('permission:doctors.view');
        Route::post('doctors/{doctor}/unavailabilities', [ScheduleController::class, 'storeUnavailability'])->middleware('permission:schedules.manage');
        Route::delete('unavailabilities/{unavailability}', [ScheduleController::class, 'destroyUnavailability'])->middleware('permission:schedules.manage');

        // ── Utilisateurs et rôles ─────────────────────────────────────────────
        Route::middleware('permission:users.view')->group(function () {
            Route::get('users', [UserController::class, 'index']);
            Route::get('users/{user}', [UserController::class, 'show']);
        });
        Route::post('users', [UserController::class, 'store'])->middleware('permission:users.manage');
        Route::patch('users/{user}', [UserController::class, 'update'])->middleware('permission:users.manage');
        Route::patch('users/{user}/toggle-active', [UserController::class, 'toggleActive'])->middleware('permission:users.manage');
        Route::put('users/{user}/permissions', [UserController::class, 'updatePermissions'])->middleware('permission:users.manage');
        Route::delete('users/{user}', [UserController::class, 'destroy'])->middleware('permission:users.manage');

        Route::get('roles', [RoleController::class, 'index'])->middleware('permission:roles.manage');
        Route::get('roles/permissions', [RoleController::class, 'allPermissions'])->middleware('permission:roles.manage');
        Route::get('roles/{role}', [RoleController::class, 'show'])->middleware('permission:roles.manage');
        Route::post('roles', [RoleController::class, 'store'])->middleware('permission:roles.manage');
        Route::patch('roles/{role}', [RoleController::class, 'update'])->middleware('permission:roles.manage');
        Route::delete('roles/{role}', [RoleController::class, 'destroy'])->middleware('permission:roles.manage');

        // ── Paramètres ────────────────────────────────────────────────────────
        Route::get('settings', [ClinicController::class, 'adminIndex'])->middleware('permission:settings.manage');
        Route::put('settings', [ClinicController::class, 'update'])->middleware('permission:settings.manage');
    });
});
