<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Nuova segnalazione da approvare</title>
</head>
<body>
    <h2>Nuova segnalazione da approvare</h2>
    
    <p>Ciao, è stata inviata una nuova segnalazione per la tua approvazione.</p>

    <h3>Dati della segnalazione</h3>
    <ul>
        <li><strong>ID:</strong> {{ $segnalazione->id }}</li>
        <li><strong>Tipo:</strong> {{ $segnalazione->tipo }}</li>
        <li><strong>Descrizione:</strong> {{ $segnalazione->descrizione ?? 'Nessuna' }}</li>
        <li><strong>Latitudine:</strong> {{ $segnalazione->lat }}</li>
        <li><strong>Longitudine:</strong> {{ $segnalazione->lng }}</li>
        <li><strong>Data invio:</strong> {{ $segnalazione->created_at->format('d/m/Y H:i') }}</li>
    </ul>

    <h3> foto</h3>
    @if(!empty($segnalazione->foto))
        <ul>
            @foreach($segnalazione->foto as $foto)
                <li>{{ $foto }}</li>
            @endforeach
        </ul>
    @else
        <p>Nessuna foto allegata.</p>
    @endif

    <h3>Controllo duplicati</h3>
    @if(!empty($duplicates))
        <p>Sono stati trovati {{ count($duplicates) }} duplicati potenziali entro 100 metri:</p>
        <ul>
            @foreach($duplicates as $dup)
                <li>
                    ID: {{ $dup['id'] }} - Tipo: {{ $dup['tipo'] }} - 
                    Distanza: {{ round($dup['distanza'], 2) }} metri
                </li>
            @endforeach
        </ul>
    @else
        <p>Nessun duplicato trovato entro 100 metri.</p>
    @endif

    <hr>
    <p><a href="{{ url('/admin/segnalazioni') }}">Vai alla dashboard di approvazione</a></p>
</body>
</html>
