<?php

use App\Http\Controllers\Api\SegnalazioneController;
use App\Mail\ApprovedNotificationMail;
use App\Models\Segnalazione;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('segnalazione.create');
})->name('home');

Route::get('/segnalazione/crea', function () {
    return view('segnalazione.create');
})->name('segnalazione.create');

Route::get('/mappa', function () {
    return view('mappa.index', [
        'pendingCount' => Segnalazione::where('status', 'pending')->count(),
        'approvedCount' => Segnalazione::where('status', 'approved')->count(),
        'rejectedCount' => Segnalazione::where('status', 'rejected')->count(),
        'withPhotosCount' => Segnalazione::whereJsonHas('foto')->count(),
    ]);
})->name('mappa');

Route::get('/admin/segnalazioni/export', function () {
    $status = request('status');
    $tipo = request('tipo');
    
    $query = Segnalazione::query();
    if ($status !== 'all' && $status !== '') {
        $query->where('status', $status);
    }
    if ($tipo) {
        $query->where('tipo', $tipo);
    }
    
    $segnalazioni = $query->get();
    
    $fileName = 'segnalazioni_' . now()->format('Ymd_His') . '.csv';
    
    $headers = [
        'Content-Type' => 'text/csv',
        'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
    ];
    
    $callback = function () use ($segnalazioni) {
        $file = fopen('php://output', 'w');
        
        // Write BOM for UTF-8
        fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Write header row
        fputcsv($file, ['ID', 'Tipo', 'Descrizione', 'Data', 'Stato', 'Lat', 'Lng', 'Foto']);
        
        // Write data rows
        foreach ($segnalazioni as $seg) {
            $statusLabel = match($seg->status) {
                'pending' => 'In sospeso',
                'approved' => 'Approvato',
                'rejected' => 'Rifiutato',
                default => $seg->status,
            };
            
            fputcsv($file, [
                $seg->id,
                $seg->tipo,
                $seg->descrizione,
                $seg->created_at->format('d/m/Y H:i'),
                $statusLabel,
                $seg->lat,
                $seg->lng,
                !empty($seg->foto) ? implode('|', $seg->foto) : '',
            ]);
        }
        
        fclose($file);
    };
    
    return response()->stream($callback, 200, $headers);
})->name('admin.segnalazioni.export');

Route::get('/admin/segnalazioni', function () {
    $status = request('status', 'pending');
    $tipo = request('tipo');
    $ajax = request('ajax', false);
    
    $query = Segnalazione::query();
    if ($status !== 'all' && $status !== '') {
        $query->where('status', $status);
    }
    if ($tipo) {
        $query->where('tipo', $tipo);
    }
    
    $segnalazioni = $query->get();
    
    $viewData = [
        'segnalazioni' => $segnalazioni,
        'count' => Segnalazione::count(),
        'pendingCount' => Segnalazione::where('status', 'pending')->count(),
        'approvedCount' => Segnalazione::where('status', 'approved')->count(),
        'rejectedCount' => Segnalazione::where('status', 'rejected')->count(),
        'currentStatus' => $status,
        'currentTipo' => $tipo,
    ];
    
    if ($ajax) {
        return response()->json([
            'html' => view('admin._list', $viewData)->render(),
            'count' => $segnalazioni->count(),
        ]);
    }
    
    return view('admin.segnalazioni', $viewData);
})->name('admin.segnalazioni');

Route::put('/admin/segnalazioni/{id}/status', function (Illuminate\Http\Request $request, $id) {
    $request->validate([
        'status' => 'required|string|in:pending,approved,rejected',
    ]);

    $segnalazione = Segnalazione::find($id);

    if (! $segnalazione) {
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

Route::delete('/admin/segnalazioni/{id}', function ($id) {
    $segnalazione = Segnalazione::find($id);

    if (! $segnalazione) {
        return response()->json(['error' => 'Segnalazione non trovata'], 404);
    }

    $segnalazione->delete();

    return response()->json(['message' => 'Segnalazione eliminata con successo']);
})->name('admin.segnalazioni.delete');
