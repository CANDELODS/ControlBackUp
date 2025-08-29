<?php

namespace Controllers;

use TCPDF;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;


use MVC\Router;
use Model\Usuario;
use Classes\Paginacion;
use Model\CopiasDetalle;
use Model\CopiasEncabezado;
use Model\Equipos;

class InformeCompletoController
{
    public static function get(Router $router)
    {
        if (!isAuth()) {
            header('Location: /');
            exit;
        }

        // Obtenemos el mes de la URL
        $mes = $_GET['mes'] ?? '';

        // Página actual
        $pagina_actual = $_GET['page'] ?? 1;
        $pagina_actual = filter_var($pagina_actual, FILTER_VALIDATE_INT);

        if (!$pagina_actual || $pagina_actual < 1) {
            header('Location: /completo-descargar?page=1');
            exit;
        }

        $registros_por_pagina = 8;
        $sin_resultados = false;

        if ($mes) {
            // Cantidad de registros agrupados por mes
            $total_registros = CopiasEncabezado::totalPorMesC('fecha', $mes);

            if (!$total_registros) {
                // No hay resultados para el mes seleccionado
                $sin_resultados = true;
                $copias = [];
                $paginacion = null;
            } else {
                // Sí hay registros
                $paginacion = new Paginacion($pagina_actual, $registros_por_pagina, $total_registros);
                $copias = CopiasEncabezado::porMesPaginadoC('fecha', $mes, $registros_por_pagina, $paginacion->offset());
            }
        } else {
            // Si no se seleccionó mes, se muestran todos los meses
            $total_registros = CopiasEncabezado::totalMesesC('fecha');
            $paginacion = new Paginacion($pagina_actual, $registros_por_pagina, $total_registros);
            $copias = CopiasEncabezado::mesesPaginadosC('fecha', $registros_por_pagina, $paginacion->offset());
        }

        // Validación de página fuera de rango
        if ($paginacion && $paginacion->totalPaginas() < $pagina_actual) {
            header('Location: /completo-descargar?page=1');
            exit;
        }

        // Obtenemos todos los detalles del mes seleccionado
        $copiasDetalle = CopiasDetalle::allWhereMesC($mes);

        $router->render('informeCompleto/completo', [
            'titulo' => 'Informe Completo',
            'alertas' => Usuario::getAlertas(),
            'copias' => $copias,
            'copiasDetalle' => $copiasDetalle,
            'paginacion' => $paginacion ? $paginacion->paginacion() : null,
            'sin_resultados' => $sin_resultados
        ]);
    }

     public static function exportarPDF()
    {
        if (!isAuth()) {
            header('Location: /');
            exit;
        }

        $fecha = $_GET['fecha'] ?? '';
        if (!$fecha) {
            header('Location: /completo-descargar');
            exit;
        }
        // Buscamos la copiaEncabezado por fecha
        $copias = CopiasEncabezado::whereLikeC('fecha', $fecha);
        if (empty($copias)) {
            echo "<script>alert('No se encontraron registros para la fecha seleccionada.');window.location.href='/completa-descargar-diaria';</script>";
            exit;
        }
        // Obtenemos los copiaDetalle relacionados con el mes seleccionado (1er parámetro: mes)
        $detalles = CopiasDetalle::allWhereMesC($fecha);
        //Creamos la llave equipos dentro del objeto detalle y la buscamos por su id en la tabla equipos
    //     foreach ($detalles as $detalle) {
    //         $detalle->equipos = Equipos::find($detalle->idEquipos);
    //     }

    //     // Ordenar por nombre de equipo
    //     usort($detalles, fn($a, $b) => strcmp($a->equipos->nombreEquipo, $b->equipos->nombreEquipo));

    //     // Creamos un resumen de cada equipo
    //     $resumen = [];
    //     //Iteramos cada detalle
    //     foreach ($detalles as $detalle) {
    //         //Agregamos el nombre de cada uno de los equipos
    //         $equipoNombre = $detalle->equipos->nombreEquipo ?? '';

    //         if (!isset($resumen[$equipoNombre])) {
    //             $resumen[$equipoNombre] = [
    //                 'local'             => 0,
    //                 'nube'              => 0,
    //                 'total'             => 0,
    //                 'evaluacion'        => '',
    //                 // Validamos si el equipo tiene habilitado hacer copia local y/o en nube
    //                 'habilitado_local'  => (int)($detalle->equipos->local ?? 0),
    //                 'habilitado_nube'   => (int)($detalle->equipos->nube ?? 0),
    //             ];
    //         }

    //         //Contamos la cantidad de copias locales y en nube
    //         if ($detalle->copiaLocal == 1) {
    //             $resumen[$equipoNombre]['local']++;
    //         }
    //         if ($detalle->copiaNube == 1) {
    //             $resumen[$equipoNombre]['nube']++;
    //         }

    //         //Sumamos la cantidad de copias locales y en nube para obtener el total
    //         $resumen[$equipoNombre]['total'] =
    //             $resumen[$equipoNombre]['local'] + $resumen[$equipoNombre]['nube'];
    //     }

    //     // Creamos unos criterios de evaluación
    //     //Excente (20 o más), Bien (10 a 19), Mal (menos de 10)
    //     foreach ($resumen as $eq => &$d) {
    //         $t = $d['total'];
    //         $d['evaluacion'] = $t >= 4 ? 'Excelente' : ($t >= 2 ? 'Bien' : 'Mal');
    //     }
    //     //Destruimos la variable para que no haya referencias inesperadas
    //     unset($d);

    //     // ======== Estadísticas (filtrando por habilitación) ========
    //     // LOCAL
    //     //filtramos los equipos que tienen habilitado hacer copia local
    //     $ordenLocal = array_filter($resumen, fn($d) => !empty($d['habilitado_local']));
    //     //Ordenamos de mayor a menor
    //     uasort($ordenLocal, fn($a, $b) => $b['local'] <=> $a['local']);

    //     // NUBE
    //     //filtramos los equipos que tienen habilitado hacer copia en nube
    //     $ordenNube = array_filter($resumen, fn($d) => !empty($d['habilitado_nube']));
    //     //Ordenamos de mayor a menor
    //     uasort($ordenNube, fn($a, $b) => $b['nube'] <=> $a['nube']);

    //     // EXTRAER VALORES
    //     //array_key_first: Devuelve la primera clave de un array sin afectar el puntero interno del array.
    //     $masLocal   = !empty($ordenLocal) ? array_key_first($ordenLocal) : null;
    //     //array_key_last: Devuelve la última clave de un array sin afectar el puntero interno del array.
    //     $menosLocal = !empty($ordenLocal) ? array_key_last($ordenLocal)  : null;

    //     $masNube    = !empty($ordenNube) ? array_key_first($ordenNube)   : null;
    //     $menosNube  = !empty($ordenNube) ? array_key_last($ordenNube)    : null;

    //     // Top/Bottom 3 (preservar claves)
    //     //array_slice: Nos da un subarray con los 3 primeros elementos (conservando las claves si se pone true).
    //     $top3Local     = array_slice($ordenLocal, 0, 3, true);
    //     $bottom3Local  = array_slice($ordenLocal, -3, 3, true);

    //     $top3Nube      = array_slice($ordenNube, 0, 3, true);
    //     $bottom3Nube   = array_slice($ordenNube, -3, 3, true);

    //     // TOTALES
    //     //Obtenemos el valor total de las copias locales y en nube
    //     $totalLocal  = array_sum(array_column($resumen, 'local'));
    //     $totalNube   = array_sum(array_column($resumen, 'nube'));
    //     //Obtenermos el total de todas las copias (local + nube)
    //     $totalGlobal = $totalLocal + $totalNube;

    //     // ================= PDF =================
    //     $pdf = new \TCPDF();
    //     $pdf->AddPage();

    //     $pdf->SetFont('helvetica', 'B', 14);
    //     $pdf->Cell(0, 10, "Informe Mensual Completas - $fecha", 0, 1, 'C');

    //     // Tabla principal
    //     $html = '<table border="1" cellpadding="4">
    //             <tr>
    //                 <th>Equipo</th>
    //                 <th>Local</th>
    //                 <th>Nube</th>
    //                 <th>Total</th>
    //                 <th>Evaluación</th>
    //             </tr>';
    //     foreach ($resumen as $equipo => $datos) {
    //         //Definimos el color de fondo dependiendo de la evaluación de cada equipo
    //         $color = ($datos['evaluacion'] == 'Mal') ? ' style="background-color:#f8d7da;"'
    //             : (($datos['evaluacion'] == 'Bien') ? ' style="background-color:#fff3cd;"'
    //                 : ' style="background-color:#d4edda;"');
    //         //Llenamos la tabla con la información de cada equipo
    //         $html .= "<tr>
    //                 <td>{$equipo}</td>
    //                 <td>{$datos['local']}</td>
    //                 <td>{$datos['nube']}</td>
    //                 <td>{$datos['total']}</td>
    //                 <td{$color}>{$datos['evaluacion']}</td>
    //               </tr>";
    //     }
    //     $html .= '</table>';
    //     $pdf->writeHTML($html);

    //     // Resumen estadístico
    //     $pdf->Ln(10);
    //     $pdf->SetFont('helvetica', 12);
    //     $pdf->Cell(0, 10, "Resumen Estadístico", 0, 1);

    //     $masLocalTxt   = $masLocal   ? "$masLocal ({$resumen[$masLocal]['local']})"   : 'Sin equipos habilitados';
    //     $menosLocalTxt = $menosLocal ? "$menosLocal ({$resumen[$menosLocal]['local']})" : 'Sin equipos habilitados';
    //     $masNubeTxt    = $masNube    ? "$masNube ({$resumen[$masNube]['nube']})"      : 'Sin equipos habilitados';
    //     $menosNubeTxt  = $menosNube  ? "$menosNube ({$resumen[$menosNube]['nube']})"  : 'Sin equipos habilitados';

    //     $html = "
    // <ul>
    //     <li><b>Total de copias locales:</b> $totalLocal</li>
    //     <li><b>Total de copias en nube:</b> $totalNube</li>
    //     <li><b>Sumatoria de totales (Local + Nube):</b> $totalGlobal</li>
    //     <li><b>Equipo con más copias locales:</b> $masLocalTxt</li>
    //     <li><b>Equipo con menos copias locales:</b> $menosLocalTxt</li>
    //     <li><b>Equipo con más copias en nube:</b> $masNubeTxt</li>
    //     <li><b>Equipo con menos copias en nube:</b> $menosNubeTxt</li>
    // </ul>";
    //     $pdf->writeHTML($html);

    //     // Listas Top Equipos
    //     $pdf->Ln(5);
    //     $pdf->Cell(0, 10, "¿Cuales fueron los 3 equipos que hicieron más copias locales?", 0, 1);
    //     if (empty($top3Local)) {
    //         $pdf->Write(0, "Sin equipos habilitados para copias locales.", '', 0, '', true);
    //     } else {
    //         $html = '<ol>';
    //         foreach ($top3Local as $eq => $d) {
    //             $html .= "<li>$eq ({$d['local']})</li>";
    //         }
    //         $html .= '</ol>';
    //         $pdf->writeHTML($html);
    //     }

    //     $pdf->Cell(0, 10, "¿Cuales fueron los 3 equipos que hicieron menos copias locales?", 0, 1);
    //     if (empty($bottom3Local)) {
    //         $pdf->Write(0, "Sin equipos habilitados para copias locales.", '', 0, '', true);
    //     } else {
    //         $html = '<ol>';
    //         foreach ($bottom3Local as $eq => $d) {
    //             $html .= "<li>$eq ({$d['local']})</li>";
    //         }
    //         $html .= '</ol>';
    //         $pdf->writeHTML($html);
    //     }

    //     $pdf->Cell(0, 10, "¿Cuales fueron los 3 equipos que hicieron más copias en nube?", 0, 1);
    //     if (empty($top3Nube)) {
    //         $pdf->Write(0, "Sin equipos habilitados para copias en nube.", '', 0, '', true);
    //     } else {
    //         $html = '<ol>';
    //         foreach ($top3Nube as $eq => $d) {
    //             $html .= "<li>$eq ({$d['nube']})</li>";
    //         }
    //         $html .= '</ol>';
    //         $pdf->writeHTML($html);
    //     }

    //     $pdf->Cell(0, 10, "¿Cuales fueron los 3 equipos que hicieron menos copias en nube?", 0, 1);
    //     if (empty($bottom3Nube)) {
    //         $pdf->Write(0, "Sin equipos habilitados para copias en nube.", '', 0, '', true);
    //     } else {
    //         $html = '<ol>';
    //         foreach ($bottom3Nube as $eq => $d) {
    //             $html .= "<li>$eq ({$d['nube']})</li>";
    //         }
    //         $html .= '</ol>';
    //         $pdf->writeHTML($html);
    //     }
    //     //Mostramos el PDF en el navegador
    //     $pdf->Output("Informe_Mensual_Completas_$fecha.pdf", 'I');
    }

}
