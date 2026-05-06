<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Reporte de Situación - {{ strtoupper($ccaa) }} - {{ \Carbon\Carbon::parse($fecha)->format('d/m/Y') }}</title>

<style>
    @page {
        margin: 2.5cm 2cm 3cm 2cm;
    }

    body {
        font-family: "Georgia", "Times New Roman", serif;
        font-size: 9.5pt;
        color: #1a1a1a;
        line-height: 1.65;
        margin: 0;
        padding: 0;
    }

    /* Cabecera */
    .cabeceraInstitucional {
        background-color: #1a3a5c;
        margin-top: -2.5cm;
        margin-left: -2cm;
        margin-right: -2cm;
        margin-bottom: 25px;
    }

    .cabeceraBandaSuperior {
        background-color: #0f2540;
        height: 6px;
    }

    .cabeceraContenido {
        padding: 18px 2cm 16px 2cm;
        display: table;
        width: 100%;
        box-sizing: border-box;
    }



    .cabeceraTextoCelda {
        display: table-cell;
        vertical-align: middle;
    }

    .cabeceraMinisterio {
        font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
        font-size: 7pt;
        color: #8eaec9;
        letter-spacing: 1.5px;
        text-transform: uppercase;
        margin: 0 0 3px 0;
    }

    .cabeceraOrganismo {
        font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
        font-size: 12.5pt;
        font-weight: bold;
        color: #ffffff;
        letter-spacing: 0.3px;
        margin: 0 0 1px 0;
    }

    .cabeceraSubtitulo {
        font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
        font-size: 7.5pt;
        color: #8eaec9;
        letter-spacing: 0.8px;
        text-transform: uppercase;
        margin: 0;
    }

    .cabeceraRefDoc {
        font-size: 7pt;
        color: #8eaec9;
        letter-spacing: 0.5px;
        text-transform: uppercase;
    }

    .cabeceraBandaInferior {
        background-color: #c8a84b;
        height: 3px;
    }

    /* Contenido principal */
    .contenidoPrincipal {
        padding: 0;
    }

    .tituloDocumento {
        margin-bottom: 28px;
        border-bottom: 1px solid #d0d8e0;
        padding-bottom: 16px;
    }

    .tituloTipoDocumento {
        font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
        font-size: 7pt;
        font-weight: bold;
        color: #1a3a5c;
        letter-spacing: 2px;
        text-transform: uppercase;
        margin: 0 0 6px 0;
    }

    .tituloPrincipal {
        font-family: "Georgia", serif;
        font-size: 17pt;
        font-weight: bold;
        color: #0f2540;
        margin: 0 0 4px 0;
        letter-spacing: -0.3px;
        line-height: 1.2;
    }

    .tituloSubtitulo {
        font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
        font-size: 9pt;
        color: #4a6070;
        margin: 0;
    }

    .fichaTable {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 32px;
        background-color: #f5f7fa;
        border: 1px solid #dde3ea;
    }

    .fichaTableHeader {
        background-color: #e8edf3;
        padding: 7px 14px;
        font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
        font-size: 7pt;
        font-weight: bold;
        color: #1a3a5c;
        letter-spacing: 1.5px;
        text-transform: uppercase;
        border-bottom: 1px solid #c8d0da;
    }

    .fichaTable td {
        padding: 9px 14px;
        border-bottom: 1px solid #e2e7ec;
        vertical-align: middle;
        word-wrap: break-word;
        word-break: break-word;
        overflow-wrap: break-word;
    }

    .fichaTable tr:last-child td {
        border-bottom: none;
    }

    .fichaLabel {
        width: 180px;
        font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
        font-size: 7.5pt;
        font-weight: bold;
        color: #4a6070;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .fichaValue {
        font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
        font-size: 9.5pt;
        color: #0f2540;
        font-weight: 500;
    }

    .seccion {
        margin-bottom: 28px;
    }

    .seccionCabecera {
        display: table;
        width: 100%;
        margin-bottom: 12px;
    }

    .seccionTitulo {
        display: table-cell;
        font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
        font-size: 8.5pt;
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: 1.2px;
        color: #1a3a5c;
        border-bottom: 1px solid #1a3a5c;
        padding-bottom: 6px;
    }

    .contenidoTexto {
        font-family: "Georgia", serif;
        font-size: 9.5pt;
        text-align: justify;
        color: #2a2a2a;
        line-height: 1.7;
        padding-left: 28px;
        word-wrap: break-word;
        word-break: break-word;
        overflow-wrap: break-word;
    }

    .contenidoTexto p {
        margin: 0 0 11px 0;
    }

    .contenidoTexto p:last-child {
        margin-bottom: 0;
    }

    /* Pie de pagina */
    .piePagina {
        position: fixed;
        bottom: -3cm;
        left: -2cm;
        right: -2cm;
        height: 2.2cm;
        background-color: #f5f7fa;
        border-top: 1px solid #d0d8e0;
    }

    .pieBanda {
        background-color: #1a3a5c;
        height: 3px;
    }

    .pieContenido {
        display: table;
        width: 100%;
        padding: 0 2cm;
        box-sizing: border-box;
        height: calc(2.2cm - 3px);
    }

    .pieIzquierda {
        display: table-cell;
        vertical-align: middle;
        font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
        font-size: 7pt;
        color: #6a7a8a;
    }

    .pieOrganismo {
        font-weight: bold;
        color: #1a3a5c;
    }

    .pieDerecha {
        display: table-cell;
        vertical-align: middle;
        text-align: right;
        font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
        font-size: 7pt;
        color: #6a7a8a;
    }

    .noBreak {
        page-break-inside: avoid;
    }

    .lineaDivisora {
        border: none;
        border-top: 1px solid #e0e6ec;
        margin: 24px 0;
    }
</style>
</head>

<body>

<div class="piePagina">
    <div class="pieBanda"></div>
    <div class="pieContenido">
        <div class="pieIzquierda">
            <div class="pieOrganismo">Confederación Hidrográfica del Tajo</div>
            <div>Ministerio para la Transición Ecológica y el Reto Demográfico</div>
        </div>
        <div class="pieDerecha">
            <div>Documento generado automáticamente</div>
            <div>{{ \Carbon\Carbon::now()->format('d/m/Y \a \l\a\s H:i') }}</div>
        </div>
    </div>
</div>

<div class="cabeceraInstitucional">
    <div class="cabeceraBandaSuperior"></div>
    <div class="cabeceraContenido">



        <div class="cabeceraTextoCelda">
            <div class="cabeceraOrganismo">Confederación Hidrográfica del Tajo</div>
            <div class="cabeceraMinisterio">Ministerio para la Transición Ecológica y el Reto Demográfico</div>

        </div>



    </div>
    <div class="cabeceraBandaInferior"></div>
</div>

<div class="contenidoPrincipal">

    <div class="tituloDocumento noBreak">
        <div class="tituloTipoDocumento">Informe Técnico &mdash; Reporte de Situación</div>
        <div class="tituloPrincipal">REPORTE DE SITUACIÓN AUTOMATIZADO</div>
        <div class="tituloSubtitulo">Cuenca Hidrográfica del Tajo &mdash; Área de Seguimiento y Emergencias</div>
    </div>

    {{-- Tabla que agrupa las variables que nos manda el Controlador --}}
    <table class="fichaTable noBreak">
        <tr>
            <td colspan="3" class="fichaTableHeader">Datos de identificación del evento</td>
        </tr>
        <tr>
            <td class="fichaLabel">Comunidad autónoma afectada:</td>
            <td class="fichaValue">{{ strtoupper($ccaa) }}</td>
        </tr>

        <tr>
            <td class="fichaLabel">Provincias afectadas:</td>
            {{-- Si se han marcado todas las provincias, el controlador mandará "Todas las provincias" --}}
            <td class="fichaValue">{{ $provincias }}</td>
        </tr>
        <tr>
            <td class="fichaLabel">Fecha del evento:</td>
            <td class="fichaValue">{{ \Carbon\Carbon::parse($fecha)->format('d/m/Y') }}</td>
        </tr>
        <tr>
            <td class="fichaLabel">Hora de registro:</td>
            <td class="fichaValue">{{ $hora }} h</td>
        </tr>
        <tr>
            <td class="fichaLabel">Fecha de emisión:</td>
            <td class="fichaValue">{{ \Carbon\Carbon::now()->format('d/m/Y \a \l\a\s H:i') }} h</td>
        </tr>
        <tr>
            <td class="fichaLabel">Organismo emisor:</td>
            <td class="fichaValue">Confederación Hidrográfica del Tajo — Sistema Automático</td>
        </tr>
    </table>

    <div class="seccion">
        <div class="seccionCabecera">
              <div class="seccionTitulo">Descripción del evento</div>
        </div>
        <div class="contenidoTexto">
            {{-- La funcion e() sanitiza el texto para evitar inyección de código. nl2br() transforma los saltos de línea del textarea en etiquetas <br> --}}
            {!! nl2br(e($texto)) !!}
        </div>
    </div>

    <hr class="lineaDivisora">

</div>

</body>
</html>
