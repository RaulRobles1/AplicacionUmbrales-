<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Previsión desembalse</title>
</head>
<body>
    <p>
        Por la presente les informamos de que en esta
        {{ $fechaPrevista->locale('es')->translatedFormat('d/m/Y H:i') }}
        está previsto empezar a desembalsar el embalse
        {{ $embalse->er_codigo }} - {{ $embalse->er_nombre }}
        a razón de {{ $caudal }}m3/s.
    </p>

    <p>
        Sin otro particular, reciba un cordial saludo.
    </p>
</body>
</html>
