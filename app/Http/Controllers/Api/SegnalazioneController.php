<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Segnalazione;
use App\Services\DuplicateChecker;
use App\Mail\ApprovalRequestMail;
use App\Mail\ApprovedNotificationMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class SegnalazioneController extends Controller
{
    protected $duplicateChecker;

    public function __construct(DuplicateChecker $duplicateChecker)
    {
        $this->duplicateChecker = $duplicateChecker;
        $this->middleware('throttle.rate_limit')->only('store');
        $this->middleware('validate.honeypot')->only('store');
    }

    public function store(Request $request)
    {
        $request->validate([
            'foto' => 'nullable|array|max:3',
            'foto.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:8192',
            'tipo' => 'required|string|in:perdita d\'acqua,tombino attappato,buca stradale,illuminazione pubblica,altro',
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
            'descrizione' => 'nullable|string|max:1000',
        ], [
            'foto.*.image' => 'Uno o più file non sono immagini valide.',
            'foto.*.mimes' => 'Le foto devono essere in formato JPEG, PNG o GIF.',
            'foto.*.max' => 'Ogni foto deve essere massimo 8MB.',
            'foto.max' => 'Puoi caricare massimo 3 foto.',
            'tipo.required' => 'Seleziona il tipo di guasto.',
            'tipo.in' => 'Tipo di guasto non valido.',
            'lat.required' => 'La latitudine è richiesta.',
            'lat.numeric' => 'La latitudine deve essere un numero.',
            'lat.between' => 'La latitudine deve essere tra -90 e 90.',
            'lng.required' => 'La longitudine è richiesta.',
            'lng.numeric' => 'La longitudine deve essere un numero.',
            'lng.between' => 'La longitudine deve essere tra -180 e 180.',
        ]);

        $fotoPaths = [];
        
        if ($request->hasFile('foto')) {
            foreach ($request->file('foto') as $file) {
                $filename = 'segnalazione_' . Str::uuid() . '.' . $file->getClientOriginalExtension();
                $filepath = $file->storeAs('segnalazioni', $filename, 'public');
                $fotoPaths[] = $filepath;
            }
        }

        $duplicates = $this->duplicateChecker->check($request->tipo, $request->lat, $request->lng, 100);

        $segnalazione = Segnalazione::create([
            'foto' => $fotoPaths,
            'tipo' => $request->tipo,
            'lat' => $request->lat,
            'lng' => $request->lng,
            'descrizione' => $request->descrizione,
            'status' => 'pending',
            'is_resolved' => false,
        ]);

        Mail::to(config('guasto_mail.admin_email', 'admin@comune.rieti.it'))->send(new ApprovalRequestMail($segnalazione, $duplicates));

        return response()->json([
            'message' => 'Segnalazione inviata con successo',
            'id' => $segnalazione->id,
            'duplicates' => $duplicates,
        ], 201);
    }

    public function list(Request $request)
    {
        $query = Segnalazione::query();

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('tipo')) {
            $query->where('tipo', $request->tipo);
        }

        if ($request->has('is_resolved')) {
            $query->where('is_resolved', filter_var($request->is_resolved, FILTER_VALIDATE_BOOLEAN));
        }

        $segnalazioni = $query->orderBy('created_at', 'desc')->get();

        return response()->json([
            'data' => $segnalazioni->map(function ($segnalazione) {
                return [
                    'id' => $segnalazione->id,
                    'foto' => $segnalazione->foto,
                    'tipo' => $segnalazione->tipo,
                    'lat' => $segnalazione->lat,
                    'lng' => $segnalazione->lng,
                    'descrizione' => $segnalazione->descrizione,
                    'status' => $segnalazione->status,
                    'is_resolved' => $segnalazione->is_resolved,
                    'created_at' => $segnalazione->created_at->toISOString(),
                    'approved_at' => $segnalazione->approved_at ? $segnalazione->approved_at->toISOString() : null,
                ];
            }),
        ]);
    }

    public function updateStatus(Request $request, $id)
    {
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
            Mail::to(self::getEmailForTipo($segnalazione->tipo))->send(
                new ApprovedNotificationMail($segnalazione)
            );
        }

        return response()->json([
            'message' => 'Stato aggiornato con successo',
            'segnalazione' => $segnalazione,
        ]);
    }

    public static function getEmailForTipo(string $tipo): string
    {
        $config = config('guasto_mail');
        return $config[$tipo]['email'] ?? 'protocollo@comune.rieti.it';
    }
}
