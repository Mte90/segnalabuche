<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Segnalazione approvata</title>
</head>
<body>
    <h2>Segnalazione approvata</h2>
    
    <p>La seguente segnalazione è stata approvata e può essere visualizzata sulla mappa pubblica.</p>

    <h3>Dati della segnalazione</h3>
    <ul>
        <li><strong>ID:</strong> {{ $segnalazione->id }}</li>
        <li><strong>Tipo:</strong> {{ $segnalazione->tipo }}</li>
        <li><strong>Descrizione:</strong> {{ $segnalazione->descrizione ?? 'Nessuna' }}</li>
        <li><strong>Latitudine:</strong> {{ $segnalazione->lat }}</li>
        <li><strong>Longitudine:</strong> {{ $segnalazione->lng }}</li>
        <li><strong>Data invio:</strong> {{ $segnalazione->created_at->format('d/m/Y H:i') }}</li>
        <li><strong>Data approvazione:</strong> {{ now()->format('d/m/Y H:i') }}</li>
    </ul>

    <h3>Foto</h3>
    @if(!empty($segnalazione->foto))
        <ul>
            @foreach($segnalazione->foto as $foto)
                <li>{{ $foto }}</li>
            @endforeach
        </ul>
    @else
        <p>Nessuna foto allegata.</p>
    @endif

    <hr>
    <p>Questa segnalazione è ora visibile sulla mappa pubblica.</p>
</body>
</html>
