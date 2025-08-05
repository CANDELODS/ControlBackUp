<?php

namespace Controllers;

use TCPDF;

use MVC\Router;
use Model\Usuario;
use Classes\Paginacion;
use Model\CopiasDetalle;
use Model\CopiasEncabezado;
use Model\Equipos;

class IncrementalInformesController
{
    public static function get(Router $router)
    {
        if (!isAuth()) {
            header('Location: /login');
        }
        //Obtenemos la fecha de la URL
        $fecha = $_GET['fecha'] ?? '';
        //Inicializamos la paginación obteniendola página actual
        $pagina_actual = $_GET['page'] ?? 1;
        //Veriricamos que sea un número y que sea positivo
        $pagina_actual = filter_var($pagina_actual, FILTER_VALIDATE_INT);
        if (!$pagina_actual || $pagina_actual < 1) {
            header('Location: /incremental-descargar-diaria?page=1');
        }
        //Cantidad de registros que queremos mostraren la vista
        $registros_por_pagina = 8;
        //Cambiará su valor si al filtrar por fecha no se encuentran resultados
        $sin_resultados = false;

        //Si se filtra por fecha (Input Type Date) entonce:
        if ($fecha) {
            //Contamos el total de registros filtrados (Where (tabla) like (fecha) AND tipoDeCopia (1))
            $total_registros = CopiasEncabezado::totalWhereLike3('fecha', $fecha, 1);
            //Si no hay resultados entonces:
            if (!$total_registros) {
                //Cambiamos el valor de la variable para mostrar una alerta en la vista
                $sin_resultados = true;
                //Contamos el total de registros dependiendo del tipo de copia (Where (tabla) = (valor)) (Sin filtro de fecha)
                $total_registros = CopiasEncabezado::totalWhere('tipoDeCopia', 1);
                //Instanciamos la paginación con sus respectivos parámetros
                $paginacion = new Paginacion($pagina_actual, $registros_por_pagina, $total_registros);
                //Ordenamos y páginamos los registros dependiendo del tipo de copia (Where (tabla) = (valor) ORDER BY (columna) ASC LIMIT (porPagina) OFFSET (offset))
                $copias = CopiasEncabezado::paginarWhere('tipoDeCopia', 1, 'fecha', $registros_por_pagina, $paginacion->offset());
            }
            //Se encontraron registros, por lo cual instanciamos la paginación con sus atributos
            $paginacion = new Paginacion($pagina_actual, $registros_por_pagina, $total_registros);
            //Ordenamos y paginamos los datos filtrados
            $copias = CopiasEncabezado::whereLikePaginado('fecha', $fecha, 'fecha', $registros_por_pagina, $paginacion->offset());
        }
        //No se usó un filtro (No se utilizó el input type date)
        else {
            //Contamos todos los registros, instanciamos la paginación, ordenamos y paginamos los registros
            $total_registros = CopiasEncabezado::totalWhere('tipoDeCopia', 1);
            $paginacion = new Paginacion($pagina_actual, $registros_por_pagina, $total_registros);
            //Ordenamos y páginamos los registros dependiendo del tipo de copia (Where (tabla) = (valor) ORDER BY (columna) ASC LIMIT (porPagina) OFFSET (offset))
            $copias = CopiasEncabezado::paginarWhere('tipoDeCopia', 1, 'fecha', $registros_por_pagina, $paginacion->offset());
            // debuguear($total_registros);
        }
        // Validación extra: Ejmplo, totalPaginas = 20 y $pagina_Actual = 21
        //Si el totalPaginas es 20, el usuario no puede estar en la 21 ya que no existe
        if ($paginacion->totalPaginas() < $pagina_actual) {
            header('Location: /incremental-descargar-diaria?page=1');
        }
        //Convertimos los 0 y 1 de la columna tipoDeCopia en strings para usarlos en la vista
        foreach ($copias as $copia) {
            //Si hay algo (Por ejemplo 1) será incremental, de lo contrario (0) será completa
            $copia->tipoDeCopia = $copia->tipoDeCopia ? 'Incremental' : 'Completa';
        }
        $copiasDetalle = new CopiasDetalle;
        $copiasDetalle = CopiasDetalle::all();
        //Renderizamos la vista y mandamos las variables
        $router->render('incremental/descargarD', [
            'titulo' => 'Incremental - Descargar Diaria',
            'alertas' => Usuario::getAlertas(),
            'copias' => $copias,
            'copiasDetalle' => $copiasDetalle,
            'paginacion' => $paginacion->paginacion(),
            'sin_resultados' => $sin_resultados
        ]);
    }

    public static function exportarPDF()
    {
        if (!isAuth()) {
            header('Location: /login');
            exit;
        }

        $fecha = $_GET['fecha'] ?? '';
        if (!$fecha) {
            header('Location: /incremental-descargar-diaria');
            exit;
        }

        // Obtenemos todas las copias incrementales de esa fecha
        $copias = CopiasEncabezado::whereLike('fecha', $fecha);
        $copias = array_filter($copias, fn($copia) => $copia->tipoDeCopia == 1);

        // Si no hay copias, redirigir o mostrar mensaje
        if (empty($copias)) {
            echo "<script>alert('No se encontraron registros para la fecha seleccionada.');window.location.href='/incremental-descargar-diaria';</script>";
            exit;
        }

        // Cargamos los detalles (puedes optimizar esto con una consulta con JOIN si deseas)
        //OPTIMIZAR PARA TRAER SOLO LOS DETALLES DE LAS COPIAS QUE SE VAN A MOSTRAR
        $detalles = CopiasDetalle::all();

        // Instanciamos TCPDF
        $pdf = new \TCPDF();
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Tu Sistema');
        $pdf->SetTitle("Reporte Diario - Incremental $fecha");
        $pdf->SetMargins(10, 10, 10, true);
        $pdf->AddPage();

        // Título
        $pdf->SetFont('helvetica', 'B', 16);
        $pdf->Cell(0, 10, "Reporte Diario - Incremental ($fecha)", 0, 1, 'C');

        // Espacio
        $pdf->Ln(5);
        $pdf->SetFont('helvetica', '', 10);

        // Encabezado de la tabla
        $html = '<table border="1" cellspacing="0" cellpadding="4">
                <thead>
                    <tr style="background-color:#f2f2f2;">
                        <th>Fecha</th>
                        <th>Equipo</th>
                        <th>Local</th>
                        <th>Nube</th>
                        <th>Observaciones</th>
                    </tr>
                </thead>
                <tbody>';

        foreach ($copias as $copia) {
            $detalleFiltrado = array_filter($detalles, fn($detalle) => $detalle->idCopiasEncabezado == $copia->id);
            foreach ($detalleFiltrado as $detalle) {
                //Se Crea Una LLave Llamada equipos Dentro Del Objeto De copiasDetalle Y La Buscamos Por Su Id(En La Tabla De Equipos)
                $detalle->equipos = Equipos::find($detalle->idEquipos);
                $equipo = $detalle->equipos->nombreEquipo; // Aquí puedes hacer un JOIN para obtener el nombre real
                $local = $detalle->copiaLocal == '1' ? 'Sí' : 'No';
                $nube = $detalle->copiaNube == '1' ? 'Sí' : 'No';
                $observaciones = htmlspecialchars($detalle->observaciones ?? '', ENT_QUOTES);

                $html .= "<tr>
                        <td>{$copia->fecha}</td>
                        <td>{$equipo}</td>
                        <td>{$local}</td>
                        <td>{$nube}</td>
                        <td>{$observaciones}</td>
                    </tr>";
            }
        }

        $html .= '</tbody></table>';

        // Agregamos el contenido al PDF
        $pdf->writeHTML($html, true, false, true, false, '');

        // Mostramos o descargamos
        $pdf->Output("reporte_incremental_{$fecha}.pdf", 'I'); // 'I' para mostrar en navegador, 'D' para forzar descarga
    }
}
