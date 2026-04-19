<?php

use App\Http\Controllers\ChatbotController;
use App\Http\Controllers\Control;
use Illuminate\Support\Facades\Route;

Route::get('/', [Control::class, 'dashboard'])->name('root');
Route::get('/dashboard', [Control::class, 'dashboard'])->name('dashboard');
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

Route::get('/login', [Control::class, 'showLoginForm'])->name('login');
Route::post('/login', [Control::class, 'processLogin'])->name('login.process');
Route::post('/logout', [Control::class, 'logout'])->name('logout');

Route::prefix('chatbot')->group(function () {
    Route::get('/context', [ChatbotController::class, 'context'])->name('chatbot.context');
    Route::post('/ask', [ChatbotController::class, 'ask'])->name('chatbot.ask');
    Route::post('/reset', [ChatbotController::class, 'reset'])->name('chatbot.reset');
});
