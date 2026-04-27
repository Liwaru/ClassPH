<?php

use App\Http\Controllers\ChatbotController;
use App\Http\Controllers\Control;
use Illuminate\Support\Facades\Route;

Route::get('/', [Control::class, 'dashboard'])->name('root');
Route::get('/dashboard', [Control::class, 'dashboard'])->name('dashboard');
Route::get('/profil', [Control::class, 'profile'])->name('profile.show');
Route::get('/keamanan', [Control::class, 'security'])->name('security.show');
Route::get('/ubah-password', [Control::class, 'changePassword'])->name('profile.password.edit');
Route::get('/profil-keamanan', [Control::class, 'profileSecurity'])->name('profile.security');
Route::get('/catatan-aktivitas', [Control::class, 'activityLogs'])->name('activity.logs');
Route::post('/profil-keamanan/profil', [Control::class, 'updateProfileIdentity'])->name('profile.identity.update');
Route::post('/profil-keamanan/password', [Control::class, 'updateProfilePassword'])->name('profile.password.update');
Route::post('/profil-keamanan/otp', [Control::class, 'updateProfileOtp'])->name('profile.otp.update');
Route::get('/kelas-saya', [Control::class, 'classInventory'])->name('class.inventory');
Route::get('/wali-kelas/kelas-saya', [Control::class, 'adminClassInventory'])->name('admin.class.inventory');
Route::get('/ajukan-permintaan', [Control::class, 'createRequest'])->name('requests.create');
Route::post('/ajukan-permintaan', [Control::class, 'storeRequest'])->name('requests.store');
Route::get('/riwayat-pengajuan', [Control::class, 'requestHistory'])->name('requests.history');
Route::get('/wali-kelas/riwayat-pengajuan', [Control::class, 'adminRequestHistory'])->name('admin.requests.history');
Route::get('/wali-kelas/pengajuan-masuk', [Control::class, 'adminRequestInbox'])->name('admin.requests.inbox');
Route::post('/wali-kelas/pengajuan-masuk/{requestId}/approve', [Control::class, 'adminApproveRequest'])->name('admin.requests.approve');
Route::post('/wali-kelas/pengajuan-masuk/{requestId}/reject', [Control::class, 'adminRejectRequest'])->name('admin.requests.reject');
Route::get('/kepala-sekolah/semua-ruangan', [Control::class, 'ownerRooms'])->name('owner.rooms');
Route::get('/kepala-sekolah/inventaris-sekolah', [Control::class, 'ownerInventories'])->name('owner.inventories');
Route::get('/kepala-sekolah/persetujuan-pengajuan', [Control::class, 'ownerRequestApproval'])->name('owner.requests.approval');
Route::post('/kepala-sekolah/persetujuan-pengajuan/{requestId}/approve', [Control::class, 'ownerApproveRequest'])->name('owner.requests.approve');
Route::post('/kepala-sekolah/persetujuan-pengajuan/{requestId}/reject', [Control::class, 'ownerRejectRequest'])->name('owner.requests.reject');
Route::get('/kepala-sekolah/laporan', [Control::class, 'ownerReports'])->name('owner.reports');
Route::get('/kepala-sekolah/laporan/export', [Control::class, 'ownerReportsExport'])->name('owner.reports.export');
Route::delete('/riwayat-pengajuan/{requestId}', [Control::class, 'destroyRequest'])->name('requests.destroy');
Route::get('/superadmin/data-user', [Control::class, 'superadminUsers'])->name('superadmin.users');
Route::post('/superadmin/data-user', [Control::class, 'superadminStoreUser'])->name('superadmin.users.store');
Route::post('/superadmin/data-user/{userId}/update', [Control::class, 'superadminUpdateUser'])->name('superadmin.users.update');
Route::post('/superadmin/data-user/{userId}/assignment', [Control::class, 'superadminAssignUserRoom'])->name('superadmin.users.assignment');
Route::get('/superadmin/data-ruangan', [Control::class, 'superadminRooms'])->name('superadmin.rooms');
Route::post('/superadmin/data-ruangan', [Control::class, 'superadminStoreRoom'])->name('superadmin.rooms.store');
Route::post('/superadmin/data-ruangan/{roomId}/update', [Control::class, 'superadminUpdateRoom'])->name('superadmin.rooms.update');
Route::post('/superadmin/data-ruangan/{roomId}/delete', [Control::class, 'superadminDeleteRoom'])->name('superadmin.rooms.delete');
Route::get('/superadmin/data-barang', [Control::class, 'superadminItems'])->name('superadmin.items');
Route::post('/superadmin/data-barang', [Control::class, 'superadminStoreItem'])->name('superadmin.items.store');
Route::post('/superadmin/data-barang/{inventoryId}/update', [Control::class, 'superadminUpdateItem'])->name('superadmin.items.update');
Route::post('/superadmin/data-barang/{inventoryId}/delete', [Control::class, 'superadminDeleteItem'])->name('superadmin.items.delete');
Route::get('/superadmin/tindak-lanjut-pengajuan', [Control::class, 'superadminRequestRealizations'])->name('superadmin.requests.realization');
Route::post('/superadmin/tindak-lanjut-pengajuan/{requestId}/realize', [Control::class, 'superadminRealizeRequest'])->name('superadmin.requests.realization.store');
Route::get('/superadmin/laporan', [Control::class, 'superadminReports'])->name('superadmin.reports');
Route::get('/superadmin/laporan/export', [Control::class, 'superadminReportsExport'])->name('superadmin.reports.export');
Route::get('/superadmin/hak-akses', [Control::class, 'hakAkses'])->name('hak_akses.index');
Route::post('/superadmin/hak-akses', [Control::class, 'updateHakAkses'])->name('hak_akses.update');

Route::prefix('auth')->group(function () {
    Route::get('/login', [Control::class, 'showLoginForm'])->name('login');
    Route::post('/login/password', [Control::class, 'processLogin'])->name('login.password');
    Route::get('/login/otp', [Control::class, 'showOtpEmailForm'])->name('login.otp.email');
    Route::get('/login/otp/verify', [Control::class, 'showOtpVerifyForm'])->name('login.otp.verify.form');
    Route::post('/login/otp/request', [Control::class, 'requestEmailOtp'])->name('login.otp.request');
    Route::post('/login/otp/verify', [Control::class, 'verifyEmailOtp'])->name('login.otp.verify');
    Route::get('/google/redirect', [Control::class, 'redirectToGoogle'])->name('login.google.redirect');
    Route::get('/google/callback', [Control::class, 'handleGoogleCallback'])->name('login.google.callback');
    Route::post('/logout', [Control::class, 'logout'])->name('logout');
});

Route::prefix('chatbot')->group(function () {
    Route::get('/context', [ChatbotController::class, 'context'])->name('chatbot.context');
    Route::post('/ask', [ChatbotController::class, 'ask'])->name('chatbot.ask');
    Route::post('/reset', [ChatbotController::class, 'reset'])->name('chatbot.reset');
});
