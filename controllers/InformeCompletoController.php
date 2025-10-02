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
        if ($paginacion->totalPaginas() > 0 && $pagina_actual > $paginacion->totalPaginas()) {
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
        foreach ($detalles as $detalle) {
            $detalle->equipos = Equipos::find($detalle->idEquipos);
        }


        // Ordenar por nombre de equipo
        usort($detalles, fn($a, $b) => strcmp($a->equipos->nombreEquipo, $b->equipos->nombreEquipo));

        // Creamos un resumen de cada equipo
        $resumen = [];
        //Iteramos cada detalle
        foreach ($detalles as $detalle) {
            //Agregamos el nombre de cada uno de los equipos
            $equipoNombre = $detalle->equipos->nombreEquipo ?? '';

            if (!isset($resumen[$equipoNombre])) {
                $resumen[$equipoNombre] = [
                    'local'             => 0,
                    'nube'              => 0,
                    'total'             => 0,
                    'evaluacion'        => '',
                    // Validamos si el equipo tiene habilitado hacer copia local y/o en nube
                    'habilitado_local'  => (int)($detalle->equipos->local ?? 0),
                    'habilitado_nube'   => (int)($detalle->equipos->nube ?? 0),
                    'critico'           => (int)($detalle->equipos->critico ?? 0),
                ];
            }

            //Contamos la cantidad de copias locales y en nube
            if ($detalle->copiaLocal == 1) {
                $resumen[$equipoNombre]['local']++;
            }
            if ($detalle->copiaNube == 1) {
                $resumen[$equipoNombre]['nube']++;
            }

            //Sumamos la cantidad de copias locales y en nube para obtener el total
            $resumen[$equipoNombre]['total'] =
                $resumen[$equipoNombre]['local'] + $resumen[$equipoNombre]['nube'];
        }

        // Creamos unos criterios de evaluación
        //Excelente (20 o más), Bien (10 a 19), Mal (menos de 10)
        foreach ($resumen as $eq => &$d) {
            $t = $d['total'];
            $d['evaluacion'] = $t >= 20 ? 'Excelente' : ($t >= 15 ? 'Bien' : 'Mal');
        }
        //Destruimos la variable para que no haya referencias inesperadas
        unset($d);

        // ======== Estadísticas (filtrando por habilitación) ========
        // LOCAL
        //filtramos los equipos que tienen habilitado hacer copia local
        $ordenLocal = array_filter($resumen, fn($d) => !empty($d['habilitado_local']));
        //Ordenamos de mayor a menor
        uasort($ordenLocal, fn($a, $b) => $b['local'] <=> $a['local']);

        // NUBE
        //filtramos los equipos que tienen habilitado hacer copia en nube
        $ordenNube = array_filter($resumen, fn($d) => !empty($d['habilitado_nube']));
        //Ordenamos de mayor a menor
        uasort($ordenNube, fn($a, $b) => $b['nube'] <=> $a['nube']);

        // EXTRAER VALORES
        //array_key_first: Devuelve la primera clave de un array sin afectar el puntero interno del array.
        $masLocal   = !empty($ordenLocal) ? array_key_first($ordenLocal) : null;
        //array_key_last: Devuelve la última clave de un array sin afectar el puntero interno del array.
        $menosLocal = !empty($ordenLocal) ? array_key_last($ordenLocal)  : null;

        $masNube    = !empty($ordenNube) ? array_key_first($ordenNube)   : null;
        $menosNube  = !empty($ordenNube) ? array_key_last($ordenNube)    : null;

        // Top/Bottom 3 (preservar claves)
        //array_slice: Nos da un subarray con los 3 primeros elementos (conservando las claves si se pone true).
        $top3Local     = array_slice($ordenLocal, 0, 3, true);
        $bottom3Local  = array_slice($ordenLocal, -3, 3, true);

        $top3Nube      = array_slice($ordenNube, 0, 3, true);
        $bottom3Nube   = array_slice($ordenNube, -3, 3, true);

        // TOTALES
        //Obtenemos el valor total de las copias locales y en nube
        $totalLocal  = array_sum(array_column($resumen, 'local'));
        $totalNube   = array_sum(array_column($resumen, 'nube'));
        //Obtenermos el total de todas las copias (local + nube)
        $totalGlobal = $totalLocal + $totalNube;

        // ================= PDF =================
        $pdf = new \TCPDF();
        $pdf->AddPage();

        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->Cell(0, 10, "Informe Mensual Completo - $fecha", 0, 1, 'C');

        // Tabla principal
        $html = '<table border="1" cellpadding="4">
                <tr>
                    <th>Equipo</th>
                    <th>Local</th>
                    <th>Nube</th>
                    <th>Total</th>
                    <th>Evaluación</th>
                </tr>';
        foreach ($resumen as $equipo => $datos) {
            //Definimos el color de fondo dependiendo de la evaluación de cada equipo
            $color = ($datos['evaluacion'] == 'Mal') ? ' style="background-color:#f8d7da;"'
                : (($datos['evaluacion'] == 'Bien') ? ' style="background-color:#fff3cd;"'
                    : ' style="background-color:#d4edda;"');

            // Resaltar equipos críticos (resalta la fila completa)
            $rowStyle = $datos['critico'] ? ' style="background-color:#FFF176;"' : '';
            //Llenamos la tabla con la información de cada equipo
            $html .= "<tr{$rowStyle}>
                    <td>{$equipo}" . ($datos['critico'] ? ' (Crítico)' : '') . "</td>
                    <td>{$datos['local']}</td>
                    <td>{$datos['nube']}</td>
                    <td>{$datos['total']}</td>
                    <td{$color}>{$datos['evaluacion']}</td>
                  </tr>";
        }
        $html .= '</table>';
        $pdf->writeHTML($html);

        // Resumen estadístico
        $pdf->Ln(10);
        $pdf->SetFont('helvetica', 12);
        $pdf->Cell(0, 10, "Resumen Estadístico", 0, 1);

        $masLocalTxt   = $masLocal   ? "$masLocal ({$resumen[$masLocal]['local']})"   : 'Sin equipos habilitados';
        $menosLocalTxt = $menosLocal ? "$menosLocal ({$resumen[$menosLocal]['local']})" : 'Sin equipos habilitados';
        $masNubeTxt    = $masNube    ? "$masNube ({$resumen[$masNube]['nube']})"      : 'Sin equipos habilitados';
        $menosNubeTxt  = $menosNube  ? "$menosNube ({$resumen[$menosNube]['nube']})"  : 'Sin equipos habilitados';

        $html = "
    <ul>
        <li><b>Total de copias locales:</b> $totalLocal</li>
        <li><b>Total de copias en nube:</b> $totalNube</li>
        <li><b>Sumatoria de totales (Local + Nube):</b> $totalGlobal</li>
        <li><b>Equipo con más copias locales:</b> $masLocalTxt</li>
        <li><b>Equipo con menos copias locales:</b> $menosLocalTxt</li>
        <li><b>Equipo con más copias en nube:</b> $masNubeTxt</li>
        <li><b>Equipo con menos copias en nube:</b> $menosNubeTxt</li>
    </ul>";
        $pdf->writeHTML($html);

        // Listas Top Equipos
        $pdf->Ln(5);
        $pdf->Cell(0, 10, "¿Cuales fueron los 3 equipos que hicieron más copias locales?", 0, 1);
        if (empty($top3Local)) {
            $pdf->Write(0, "Sin equipos habilitados para copias locales.", '', 0, '', true);
        } else {
            $html = '<ol>';
            foreach ($top3Local as $eq => $d) {
                $html .= "<li>$eq ({$d['local']})</li>";
            }
            $html .= '</ol>';
            $pdf->writeHTML($html);
        }

        $pdf->Cell(0, 10, "¿Cuales fueron los 3 equipos que hicieron menos copias locales?", 0, 1);
        if (empty($bottom3Local)) {
            $pdf->Write(0, "Sin equipos habilitados para copias locales.", '', 0, '', true);
        } else {
            $html = '<ol>';
            foreach ($bottom3Local as $eq => $d) {
                $html .= "<li>$eq ({$d['local']})</li>";
            }
            $html .= '</ol>';
            $pdf->writeHTML($html);
        }

        $pdf->Cell(0, 10, "¿Cuales fueron los 3 equipos que hicieron más copias en nube?", 0, 1);
        if (empty($top3Nube)) {
            $pdf->Write(0, "Sin equipos habilitados para copias en nube.", '', 0, '', true);
        } else {
            $html = '<ol>';
            foreach ($top3Nube as $eq => $d) {
                $html .= "<li>$eq ({$d['nube']})</li>";
            }
            $html .= '</ol>';
            $pdf->writeHTML($html);
        }

        $pdf->Cell(0, 10, "¿Cuales fueron los 3 equipos que hicieron menos copias en nube?", 0, 1);
        if (empty($bottom3Nube)) {
            $pdf->Write(0, "Sin equipos habilitados para copias en nube.", '', 0, '', true);
        } else {
            $html = '<ol>';
            foreach ($bottom3Nube as $eq => $d) {
                $html .= "<li>$eq ({$d['nube']})</li>";
            }
            $html .= '</ol>';
            $pdf->writeHTML($html);
        }
        //Mostramos el PDF en el navegador
        $pdf->Output("Informe_Completo_$fecha.pdf", 'I');
    }

    public static function exportarExcel()
    {
        if (!isAuth()) {
            header('Location: /');
            exit;
        }

        $fecha = $_GET['fecha'] ?? '';
        $nombreArchivo = "Informe Completo {$fecha}.xlsx";
        if (!$fecha) {
            header('Location: /completo-descargar');
            exit;
        }

        // Encabezado por fecha
        $copias = CopiasEncabezado::whereLikeC('fecha', $fecha);
        if (empty($copias)) {
            echo "<script>alert('No se encontraron registros para la fecha seleccionada.');window.location.href='/completa-descargar-diaria';</script>";
            exit;
        }

        // ===== Datos base =====
        $detalles = CopiasDetalle::allWhereMesC($fecha);
        foreach ($detalles as $detalle) {
            $detalle->equipos = Equipos::find($detalle->idEquipos);
        }

        // Ordenar por nombre de equipo
        usort($detalles, fn($a, $b) => strcmp($a->equipos->nombreEquipo, $b->equipos->nombreEquipo));

        // Resumen por equipo
        $resumen = [];
        foreach ($detalles as $detalle) {
            $equipo = $detalle->equipos->nombreEquipo ?? '';
            if (!isset($resumen[$equipo])) {
                $resumen[$equipo] = [
                    'local' => 0,
                    'nube'  => 0,
                    'total' => 0,
                    'evaluacion' => '',
                    'habilitado_local' => (int)($detalle->equipos->local ?? 0),
                    'habilitado_nube'  => (int)($detalle->equipos->nube ?? 0),
                    'critico' => (int)($detalle->equipos->critico ?? 0),
                ];
            }
            if ($detalle->copiaLocal == 1) $resumen[$equipo]['local']++;
            if ($detalle->copiaNube  == 1) $resumen[$equipo]['nube']++;
            $resumen[$equipo]['total'] = $resumen[$equipo]['local'] + $resumen[$equipo]['nube'];
        }

        foreach ($resumen as &$d) {
            $t = $d['total'];
            $d['evaluacion'] = $t >= 20 ? 'Excelente' : ($t >= 15 ? 'Bien' : 'Mal');
        }
        unset($d);

        // ===== Estadísticas =====
        $ordenLocal = array_filter($resumen, fn($d) => !empty($d['habilitado_local']));
        uasort($ordenLocal, fn($a, $b) => $b['local'] <=> $a['local']);

        $ordenNube  = array_filter($resumen, fn($d) => !empty($d['habilitado_nube']));
        uasort($ordenNube,  fn($a, $b) => $b['nube']  <=> $a['nube']);

        $masLocal   = !empty($ordenLocal) ? array_key_first($ordenLocal) : null;
        $menosLocal = !empty($ordenLocal) ? array_key_last($ordenLocal)  : null;
        $masNube    = !empty($ordenNube)  ? array_key_first($ordenNube)  : null;
        $menosNube  = !empty($ordenNube)  ? array_key_last($ordenNube)   : null;

        $top3Local     = array_slice($ordenLocal, 0, 3, true);
        $bottom3Local  = array_slice($ordenLocal, -3, 3, true);
        $top3Nube      = array_slice($ordenNube,  0, 3, true);
        $bottom3Nube   = array_slice($ordenNube,  -3, 3, true);

        $totalLocal  = array_sum(array_column($resumen, 'local'));
        $totalNube   = array_sum(array_column($resumen, 'nube'));
        $totalGlobal = $totalLocal + $totalNube;

        // Textos “Más/Menos” (como en el PDF)
        $masLocalTxt   = $masLocal   ? "{$masLocal} ({$ordenLocal[$masLocal]['local']})"       : 'Sin equipos habilitados';
        $menosLocalTxt = $menosLocal ? "{$menosLocal} ({$ordenLocal[$menosLocal]['local']})"   : 'Sin equipos habilitados';
        $masNubeTxt    = $masNube    ? "{$masNube} ({$ordenNube[$masNube]['nube']})"           : 'Sin equipos habilitados';
        $menosNubeTxt  = $menosNube  ? "{$menosNube} ({$ordenNube[$menosNube]['nube']})"       : 'Sin equipos habilitados';

        // ===== Excel =====
        $spreadsheet = new Spreadsheet();

        // === HOJA 1: Tabla principal ===
        $sheet = $spreadsheet->getActiveSheet();
        $sheet1 = $spreadsheet->getSheet(0);
        $sheet1->setShowGridlines(false);
        $sheet->setTitle("Copias");

        $sheet->setCellValue('A1', "Informe Mensual Completas - $fecha");
        $sheet->mergeCells('A1:E1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $sheet->fromArray(['Equipo', 'Local', 'Nube', 'Total', 'Evaluación'], null, 'A3');

        $fila = 4;
        foreach ($resumen as $equipo => $d) {
            $sheet->setCellValue("A{$fila}", $equipo);
            $sheet->setCellValue("B{$fila}", $d['local']);
            $sheet->setCellValue("C{$fila}", $d['nube']);
            $sheet->setCellValue("D{$fila}", $d['total']);
            $sheet->setCellValue("E{$fila}", $d['evaluacion']);

            $color = $d['evaluacion'] === 'Mal' ? 'F8D7DA' : ($d['evaluacion'] === 'Bien' ? 'FFF3CD' : 'D4EDDA');
            $sheet->getStyle("E{$fila}")->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setRGB($color);

            // ✅ Resaltar equipos críticos (solo columnas A-D)
            if (isset($d['critico']) && $d['critico'] == 1) {
                $sheet->getStyle("A{$fila}:D{$fila}")->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('FFF176'); // amarillo alerta
            }
            $fila++;
        }

        $sheet->getStyle("A3:E3")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['rgb' => '4F81BD']]
        ]);

        $sheet->getStyle("A3:E" . ($fila - 1))->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['rgb' => '000000']]]
        ]);

        foreach (range('A', 'E') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // === HOJA 2: Resumen y gráficos ===
        $sheet2 = $spreadsheet->createSheet();
        $sheet1 = $spreadsheet->getSheet(1);
        $sheet1->setShowGridlines(false);
        $sheet2->setTitle("Resumen");

        // Título
        $sheet2->setCellValue("A1", "Resumen Estadístico - $fecha");
        $sheet2->getStyle("A1")->getFont()->setBold(true)->setSize(14);

        // Totales
        $sheet2->setCellValue("A3", "• Total de copias locales:");
        $sheet2->setCellValue("B3", "{$totalLocal}");
        $sheet2->getStyle("A3")->getFont()->setBold(true);
        $sheet2->setCellValue("A4", "• Total de copias en nube:");
        $sheet2->setCellValue("B4", "{$totalNube}");
        $sheet2->getStyle("A4")->getFont()->setBold(true);
        $sheet2->setCellValue("A5", "• Sumatoria de totales (Local + Nube):");
        $sheet2->setCellValue("B5", "{$totalGlobal}");
        $sheet2->getStyle("A5")->getFont()->setBold(true);

        // Más / Menos (en UNA celda cada línea)
        $sheet2->setCellValue("A7",  "• Equipo con más copias locales:");
        $sheet2->setCellValue("B7",  "{$masLocalTxt}");
        $sheet2->getStyle("A7")->getFont()->setBold(true);
        $sheet2->setCellValue("A8",  "• Equipo con menos copias locales:");
        $sheet2->setCellValue("B8",  "{$menosLocalTxt}");
        $sheet2->getStyle("A8")->getFont()->setBold(true);
        $sheet2->setCellValue("A9",  "• Equipo con más copias en nube:");
        $sheet2->setCellValue("B9",  "{$masNubeTxt}");
        $sheet2->getStyle("A9")->getFont()->setBold(true);
        $sheet2->setCellValue("A10", "• Equipo con menos copias en nube:");
        $sheet2->setCellValue("B10", "{$menosNubeTxt}");
        $sheet2->getStyle("A10")->getFont()->setBold(true);

        // Ajustes para que no se corte el texto
        $sheet2->getStyle("A3:A10")->getAlignment()->setWrapText(true);
        $sheet2->getColumnDimension('A')->setAutoSize(true);
        for ($r = 3; $r <= 10; $r++) {
            $sheet2->getRowDimension($r)->setRowHeight(-1); // auto
        }

        // === Top/Bottom 3 (tablas) ===
        $filaTB = 12;

        // Encabezados secciones
        $sheet2->setCellValue("A{$filaTB}", "Equipos con más copias locales");
        $sheet2->getStyle("A{$filaTB}")->getFont()->setBold(true);
        $filaTB++;

        $sheet2->fromArray(['Equipo', 'Local'], null, "A{$filaTB}");
        $sheet2->getStyle("A{$filaTB}:B{$filaTB}")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['rgb' => '4F81BD']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
        ]);
        $filaTB++;

        foreach ($top3Local as $eq => $d) {
            $sheet2->setCellValue("A{$filaTB}", $eq);
            $sheet2->setCellValue("B{$filaTB}", $d['local']);
            $filaTB++;
        }
        $sheet2->getStyle("A" . ($filaTB - count($top3Local)) . ":B" . ($filaTB - 1))->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]]
        ]);

        $filaTB += 1;
        $sheet2->setCellValue("A{$filaTB}", "Equipos con menos copias locales");
        $sheet2->getStyle("A{$filaTB}")->getFont()->setBold(true);
        $filaTB++;

        $sheet2->fromArray(['Equipo', 'Local'], null, "A{$filaTB}");
        $sheet2->getStyle("A{$filaTB}:B{$filaTB}")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['rgb' => '4F81BD']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
        ]);
        $filaTB++;

        foreach ($bottom3Local as $eq => $d) {
            $sheet2->setCellValue("A{$filaTB}", $eq);
            $sheet2->setCellValue("B{$filaTB}", $d['local']);
            $filaTB++;
        }
        $sheet2->getStyle("A" . ($filaTB - count($bottom3Local)) . ":B" . ($filaTB - 1))->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]]
        ]);

        $filaTB += 1;
        $sheet2->setCellValue("D12", "Equipos con más copias en nube");
        $sheet2->getStyle("D12")->getFont()->setBold(true);

        $sheet2->fromArray(['Equipo', 'Nube'], null, "D13");
        $sheet2->getStyle("D13:E13")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['rgb' => '4F81BD']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
        ]);

        $filaN = 14;
        foreach ($top3Nube as $eq => $d) {
            $sheet2->setCellValue("D{$filaN}", $eq);
            $sheet2->setCellValue("E{$filaN}", $d['nube']);
            $filaN++;
        }
        $sheet2->getStyle("D14:E" . ($filaN - 1))->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]]
        ]);

        $sheet2->setCellValue("D" . ($filaN + 1), "Equipos con menos copias en nube");
        $sheet2->getStyle("D" . ($filaN + 1))->getFont()->setBold(true);

        $sheet2->fromArray(['Equipo', 'Nube'], null, "D" . ($filaN + 2));
        $sheet2->getStyle("D" . ($filaN + 2) . ":E" . ($filaN + 2))->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['rgb' => '4F81BD']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
        ]);

        $filaN2 = $filaN + 3;
        foreach ($bottom3Nube as $eq => $d) {
            $sheet2->setCellValue("D{$filaN2}", $eq);
            $sheet2->setCellValue("E{$filaN2}", $d['nube']);
            $filaN2++;
        }
        $sheet2->getStyle("D" . ($filaN + 3) . ":E" . ($filaN2 - 1))->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]]
        ]);

        // Autoajuste columnas hoja 2
        foreach (['A', 'B', 'C', 'D', 'E'] as $col) {
            $sheet2->getColumnDimension($col)->setAutoSize(true);
        }

        // === Gráfico circular Local vs Nube ===
        $dataSeriesLabels = [
            new \PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues('String', 'Resumen!$A$3', null, 1),
            new \PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues('String', 'Resumen!$A$4', null, 1),
        ];
        $xAxisTickValues = [
            new \PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues('String', 'Resumen!$A$3:$A$4', null, 2),
        ];
        $dataSeriesValues = [
            new \PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues('Number', 'Resumen!$A$3:$A$4', null, 2), // etiquetas en A (pero valores numéricos están embebidos en el texto)
        ];
        // Para el pie usamos los números reales: B3:B4 (creamos números espejo)
        $sheet2->setCellValue('B3', $totalLocal);
        $sheet2->setCellValue('B4', $totalNube);
        $dataSeriesValues = [
            new \PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues('Number', 'Resumen!$B$3:$B$4', null, 2),
        ];

        $series = new \PhpOffice\PhpSpreadsheet\Chart\DataSeries(
            \PhpOffice\PhpSpreadsheet\Chart\DataSeries::TYPE_PIECHART,
            null,
            range(0, count($dataSeriesValues) - 1),
            $dataSeriesLabels,
            $xAxisTickValues,
            $dataSeriesValues
        );
        $layout = new \PhpOffice\PhpSpreadsheet\Chart\Layout();
        $layout->setShowVal(true)->setShowPercent(true);
        $plotArea = new \PhpOffice\PhpSpreadsheet\Chart\PlotArea($layout, [$series]);
        $legend  = new \PhpOffice\PhpSpreadsheet\Chart\Legend(\PhpOffice\PhpSpreadsheet\Chart\Legend::POSITION_RIGHT, null, false);
        $title   = new \PhpOffice\PhpSpreadsheet\Chart\Title('Distribución Local vs Nube');

        $chart1 = new \PhpOffice\PhpSpreadsheet\Chart\Chart('chart1', $title, $legend, $plotArea);
        $chart1->setTopLeftPosition('G3');
        $chart1->setBottomRightPosition('N20');
        $sheet2->addChart($chart1);

        // === Gráfico barras Top 5 por total ===
        uasort($resumen, fn($a, $b) => $b['total'] <=> $a['total']);
        $top5 = array_slice($resumen, 0, 5, true);

        $sheet2->setCellValue("G22", "Equipo");
        $sheet2->setCellValue("H22", "Local");
        $sheet2->setCellValue("I22", "Nube");
        $r = 23;
        foreach ($top5 as $eq => $d) {
            $sheet2->setCellValue("G{$r}", $eq);
            $sheet2->setCellValue("H{$r}", $d['local']);
            $sheet2->setCellValue("I{$r}", $d['nube']);
            $r++;
        }

        $lbls = [
            new \PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues('String', 'Resumen!$H$22', null, 1),
            new \PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues('String', 'Resumen!$I$22', null, 1),
        ];
        $cats = [
            new \PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues('String', 'Resumen!$G$23:$G$' . ($r - 1), null, 5),
        ];
        $vals = [
            new \PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues('Number', 'Resumen!$H$23:$H$' . ($r - 1), null, 5),
            new \PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues('Number', 'Resumen!$I$23:$I$' . ($r - 1), null, 5),
        ];

        $series2 = new \PhpOffice\PhpSpreadsheet\Chart\DataSeries(
            \PhpOffice\PhpSpreadsheet\Chart\DataSeries::TYPE_BARCHART,
            \PhpOffice\PhpSpreadsheet\Chart\DataSeries::GROUPING_CLUSTERED,
            range(0, count($vals) - 1),
            $lbls,
            $cats,
            $vals
        );
        $series2->setPlotDirection(\PhpOffice\PhpSpreadsheet\Chart\DataSeries::DIRECTION_COL);

        $plotArea2 = new \PhpOffice\PhpSpreadsheet\Chart\PlotArea(null, [$series2]);
        $legend2   = new \PhpOffice\PhpSpreadsheet\Chart\Legend(\PhpOffice\PhpSpreadsheet\Chart\Legend::POSITION_RIGHT, null, false);
        $title2    = new \PhpOffice\PhpSpreadsheet\Chart\Title('Top 5 Equipos - Local/Nube');

        $chart2 = new \PhpOffice\PhpSpreadsheet\Chart\Chart('chart2', $title2, $legend2, $plotArea2);
        $chart2->setTopLeftPosition('G22');
        $chart2->setBottomRightPosition('N40');
        $sheet2->addChart($chart2);

        // === Descargar ===
        $writer = new Xlsx($spreadsheet);
        $writer->setIncludeCharts(true);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment;filename=\"{$nombreArchivo}\"");
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit;
    }
}
