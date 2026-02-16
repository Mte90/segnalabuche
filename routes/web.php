<?php

use Illuminate\Support\Facades\Route;
use App\Models\Segnalazione;
use App\Mail\ApprovedNotificationMail;
use App\Http\Controllers\Api\SegnalazioneController;
use Illuminate\Support\Facades\Mail;

Route::get('/', function () {
    return view('segnalazione.create');
})->name('home');

Route::get('/mappa', function () {
    return view('mappa.index');
})->name('mappa');

Route::get('/admin/segnalazioni', function () {
    return view('admin.segnalazioni', [
        'segnalazioni' => Segnalazione::where('status', 'pending')->get(),
        'count' => Segnalazione::count(),
        'pendingCount' => Segnalazione::where('status', 'pending')->count(),
        'approvedCount' => Segnalazione::where('status', 'approved')->count(),
        'rejectedCount' => Segnalazione::where('status', 'rejected')->count(),
    ]);
})->name('admin.segnalazioni');

Route::put('/admin/segnalazioni/{id}/status', function (Illuminate\Http\Request $request, $id) {
    $request->validate([
        'status' => 'required|string|in:pending,approved,rejected',
    ]);
    
    $segnalazione = Segnalazione::find($id);
    
    if (!$segnalazione) {
        return response()->json(['error' => 'Segnalazione non trovata'], 404);
    }
    
    $oldStatus = $segnalazione->status;
    $segnalazione->status = $request->status;
    $segnalazione->save();
    
    if ($request->status === 'approved' && $oldStatus !== 'approved') {
        Mail::to(SegnalazioneController::getEmailForTipo($segnalazione->tipo))
            ->send(new ApprovedNotificationMail($segnalazione));
    }
    
    return response()->json([
        'message' => 'Stato aggiornato con successo',
        'segnalazione' => $segnalazione,
    ]);
})->name('admin.segnalazioni.status.update');
