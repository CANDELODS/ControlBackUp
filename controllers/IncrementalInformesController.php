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

class IncrementalInformesController
{
    //Usado en la lista de descargarD.php
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

    //Usado en la vista de descargarM.php
   public static function getM(Router $router)
{
    if (!isAuth()) {
        header('Location: /login');
        exit;
    }

    // Obtenemos el mes de la URL
    $mes = $_GET['mes'] ?? '';

    // Página actual
    $pagina_actual = $_GET['page'] ?? 1;
    $pagina_actual = filter_var($pagina_actual, FILTER_VALIDATE_INT);

    if (!$pagina_actual || $pagina_actual < 1) {
        header('Location: /incremental-descargar-mensual?page=1');
        exit;
    }

    $registros_por_pagina = 8;
    $sin_resultados = false;

    if ($mes) {
        // Cantidad de registros agrupados por mes
        $total_registros = CopiasEncabezado::totalPorMes('fecha', $mes, 'tipoDeCopia', 1);

        if (!$total_registros) {
            // No hay resultados para el mes seleccionado
            $sin_resultados = true;
            $copias = []; 
            $paginacion = null; 
        } else {
            // Sí hay registros
            $paginacion = new Paginacion($pagina_actual, $registros_por_pagina, $total_registros);
            $copias = CopiasEncabezado::porMesPaginado('fecha', $mes, $registros_por_pagina, $paginacion->offset(), 'tipoDeCopia', 1);
        }
    } else {
        // Si no se seleccionó mes, se muestran todos los meses
        $total_registros = CopiasEncabezado::totalMeses('fecha', 'tipoDeCopia', 1);
        $paginacion = new Paginacion($pagina_actual, $registros_por_pagina, $total_registros);
        $copias = CopiasEncabezado::mesesPaginados('fecha', $registros_por_pagina, $paginacion->offset(), 'tipoDeCopia', 1);
    }

    // Validación de página fuera de rango
    if ($paginacion && $paginacion->totalPaginas() < $pagina_actual) {
        header('Location: /incremental-descargar-mensual?page=1');
        exit;
    }

    //Convertimos los 0 y 1 de la columna tipoDeCopia en strings para usarlos en la vista
    foreach ($copias as $copia) {
        //Si hay algo (Por ejemplo 1) será incremental, de lo contrario (0) será completa
        $copia->tipoDeCopia = $copia->tipoDeCopia ? 'Incremental' : 'Completa';
    }

    // Obtenemos todos los detalles (luego se puede optimizar a "solo del mes")
    $copiasDetalle = CopiasDetalle::allWhereMes($mes, 1);
    // debuguear($copiasDetalle);

    $router->render('incremental/descargarM', [
        'titulo' => 'Incremental - Descargar Mensual',
        'alertas' => Usuario::getAlertas(),
        'copias' => $copias,
        'copiasDetalle' => $copiasDetalle,
        'paginacion' => $paginacion ? $paginacion->paginacion() : null,
        'sin_resultados' => $sin_resultados
    ]);
}





    //Exportar PDF de copias diarias incrementales
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
        // Buscamos la copiaEncabezado por fecha
        $copias = CopiasEncabezado::whereLike('fecha', $fecha);
        // Si no hay copias, redirigir o mostrar mensaje
        if (empty($copias)) {
            echo "<script>alert('No se encontraron registros para la fecha seleccionada.');window.location.href='/incremental-descargar-diaria';</script>";
            exit;
        }
        //Extraemos el Id de la copiaEncabezado para usarlo en la consulta de detalles
        $ids = array_map(fn($copia) => $copia->id, $copias);
        $id = $ids[0];

        // Obtenemos los copiaDetalle relacionados con la copiaEncabezado, ordenados por nombre de equipo
        //El método allWhere filtra los detalles por el id de la copiaEncabezado
        //1er parámetro: Nombre de la columna B, 2do parámetro: Tipo de copia, 3er parámetro: id de la copiaEncabezado, 4to parámetro: orden
        $detalles = CopiasDetalle::allWhere('copiasencabezado', 1, $id, 'ASC');

        // AÑADIR NOMBRES DE EQUIPOS
        foreach ($detalles as $detalle) {
            //Se Crea Una LLave Llamada equipos Dentro Del Objeto De copiasDetalle Y La Buscamos Por Su Id(En La Tabla De Equipos)
            $detalle->equipos = Equipos::find($detalle->idEquipos);
        }

        // ORDENAR ALFABETICAMENTE POR NOMBRE DE EQUIPO
        usort($detalles, fn($a, $b) => strcmp($a->equipos->nombreEquipo, $b->equipos->nombreEquipo));

        // TOTALES
        $totalLocalSi = $totalLocalNo = $totalNubeSi = $totalNubeNo = 0;

        // Instanciamos TCPDF
        $pdf = new \TCPDF();
        $pdf->SetCreator(PDF_CREATOR);
        //Autor del documento
        $pdf->SetAuthor('TI Ladrillera Melendez SA');
        // Título del documento
        $pdf->SetTitle("Reporte Diario - Incremental $fecha");
        //Márgenes del PDF (izquierda, arriba, derecha).
        $pdf->SetMargins(10, 10, 10, true);
        //Agrega una página en blanco.
        $pdf->AddPage();

        // TÍTULO
        //Fuente, negrita y tamaño de letra
        $pdf->SetFont('helvetica', 'B', 16);
        //Crear una celda (una línea de texto).
        //0: Ancho de la celda (0 = Automático), 10: Alto de la celda, Texto, 0: Borde, 1: Salto de línea,
        //C: Alineación (C = Centrado)
        $pdf->Cell(0, 10, "Reporte Diario - Incremental ($fecha)", 0, 1, 'C');

        // Espacio
        $pdf->Ln(5);
        $pdf->SetFont('helvetica', '', 10);

        // ENCABEZADOS DE LA TABLA PRINCIPAL
        $html = '<table border="1" cellspacing="0" cellpadding="4">
            <thead>
                <tr style="background-color:#f2f2f2; font-weight:bold; text-align:center;">
                    <th>Equipo</th>
                    <th>Local</th>
                    <th>Nube</th>
                    <th>Observaciones</th>
                </tr>
            </thead>
            <tbody>';

        foreach ($detalles as $detalle) {
            $equipo = $detalle->equipos->nombreEquipo ?? '';
            $local = $detalle->copiaLocal == '1' ? 'Sí' : 'No';
            $nube = $detalle->copiaNube == '1' ? 'Sí' : 'No';
            $observaciones = htmlspecialchars($detalle->observaciones ?? '', ENT_QUOTES);

            // Contar totales
            if ($local === 'Sí') $totalLocalSi++;
            else $totalLocalNo++;
            if ($nube === 'Sí') $totalNubeSi++;
            else $totalNubeNo++;

            // Colores condicionales
            $colorLocal = $local === 'Sí' ? '#14b134ff' : '#cf0606ff';
            $colorNube = $nube === 'Sí' ? '#14b134ff' : '#cf0606ff';

            //LLENADO DE TABLA
            $html .= "<tr>
                    <td>{$equipo}</td>
                    <td style='background-color:{$colorLocal}; color:black; text-align:center;'>{$local}</td>
                    <td style='background-color:{$colorNube}; color:black; text-align:center;'>{$nube}</td>
                    <td>" . ($observaciones ?: '') . "</td>
                </tr>";
        }

        $html .= '</tbody></table>';

        // TABLA DE TOTALES
        $html .= '<br><br>
    <table border="1" cellpadding="4">
        <tr style="background-color:#f2f2f2; font-weight:bold; text-align:center;">
            <th colspan="2">Totales</th>
        </tr>
        <tr>
            <td>Local Sí:</td><td>' . $totalLocalSi . '</td>
        </tr>
        <tr>
            <td>Local No:</td><td>' . $totalLocalNo . '</td>
        </tr>
        <tr>
            <td>Nube Sí:</td><td>' . $totalNubeSi . '</td>
        </tr>
        <tr>
            <td>Nube No:</td><td>' . $totalNubeNo . '</td>
        </tr>
    </table>';

        // Agregamos el contenido al PDF (Convierte el HTML de la tabla a PDF)
        $pdf->writeHTML($html, true, false, true, false, '');
        // Mostramos o descargamos
        $pdf->Output("Reporte Diario - Incremental {$fecha}.pdf", 'I'); // 'I' para mostrar en navegador, 'D' para forzar descarga

    }

    //Exportar PDF de copias mensuales incrementales
    public static function exportarPDFM()
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
        // Buscamos la copiaEncabezado por fecha
        $copias = CopiasEncabezado::whereLike('fecha', $fecha);
        // Si no hay copias, redirigir o mostrar mensaje
        if (empty($copias)) {
            echo "<script>alert('No se encontraron registros para la fecha seleccionada.');window.location.href='/incremental-descargar-diaria';</script>";
            exit;
        }
        //Extraemos el Id de la copiaEncabezado para usarlo en la consulta de detalles
        $ids = array_map(fn($copia) => $copia->id, $copias);
        $id = $ids[0];

        // Obtenemos los copiaDetalle relacionados con la copiaEncabezado, ordenados por nombre de equipo
        //El método allWhere filtra los detalles por el id de la copiaEncabezado
        //1er parámetro: Nombre de la columna B, 2do parámetro: Tipo de copia, 3er parámetro: id de la copiaEncabezado, 4to parámetro: orden
        $detalles = CopiasDetalle::allWhere('copiasencabezado', 1, $id, 'ASC');
        // AÑADIR NOMBRES DE EQUIPOS
        foreach ($detalles as $detalle) {
            //Se Crea Una LLave Llamada equipos Dentro Del Objeto De copiasDetalle Y La Buscamos Por Su Id(En La Tabla De Equipos)
            $detalle->equipos = Equipos::find($detalle->idEquipos);
        }

        // ORDENAR ALFABETICAMENTE POR NOMBRE DE EQUIPO
        usort($detalles, fn($a, $b) => strcmp($a->equipos->nombreEquipo, $b->equipos->nombreEquipo));

        // TOTALES
        $totalLocalSi = $totalLocalNo = $totalNubeSi = $totalNubeNo = 0;

        // Instanciamos TCPDF
        $pdf = new \TCPDF();
        $pdf->SetCreator(PDF_CREATOR);
        //Autor del documento
        $pdf->SetAuthor('TI Ladrillera Melendez SA');
        // Título del documento
        $pdf->SetTitle("Reporte Mensual - Incremental $fecha");
        //Márgenes del PDF (izquierda, arriba, derecha).
        $pdf->SetMargins(10, 10, 10, true);
        //Agrega una página en blanco.
        $pdf->AddPage();

        // TÍTULO
        //Fuente, negrita y tamaño de letra
        $pdf->SetFont('helvetica', 'B', 16);
        //Crear una celda (una línea de texto).
        //0: Ancho de la celda (0 = Automático), 10: Alto de la celda, Texto, 0: Borde, 1: Salto de línea,
        //C: Alineación (C = Centrado)
        $pdf->Cell(0, 10, "Reporte Mensual - Incremental ($fecha)", 0, 1, 'C');

        // Espacio
        $pdf->Ln(5);
        $pdf->SetFont('helvetica', '', 10);

        // ENCABEZADOS DE LA TABLA PRINCIPAL
        $html = '<table border="1" cellspacing="0" cellpadding="4">
            <thead>
                <tr style="background-color:#f2f2f2; font-weight:bold; text-align:center;">
                    <th>Equipo</th>
                    <th>Local</th>
                    <th>Nube</th>
                    <th>Observaciones</th>
                </tr>
            </thead>
            <tbody>';

        foreach ($detalles as $detalle) {
            $equipo = $detalle->equipos->nombreEquipo ?? '';
            $local = $detalle->copiaLocal == '1' ? 'Sí' : 'No';
            $nube = $detalle->copiaNube == '1' ? 'Sí' : 'No';
            $observaciones = htmlspecialchars($detalle->observaciones ?? '', ENT_QUOTES);

            // Contar totales
            if ($local === 'Sí') $totalLocalSi++;
            else $totalLocalNo++;
            if ($nube === 'Sí') $totalNubeSi++;
            else $totalNubeNo++;

            // Colores condicionales
            $colorLocal = $local === 'Sí' ? '#14b134ff' : '#cf0606ff';
            $colorNube = $nube === 'Sí' ? '#14b134ff' : '#cf0606ff';

            //LLENADO DE TABLA
            $html .= "<tr>
                    <td>{$equipo}</td>
                    <td style='background-color:{$colorLocal}; color:black; text-align:center;'>{$local}</td>
                    <td style='background-color:{$colorNube}; color:black; text-align:center;'>{$nube}</td>
                    <td>" . ($observaciones ?: '') . "</td>
                </tr>";
        }

        $html .= '</tbody></table>';

        // TABLA DE TOTALES
        $html .= '<br><br>
    <table border="1" cellpadding="4">
        <tr style="background-color:#f2f2f2; font-weight:bold; text-align:center;">
            <th colspan="2">Totales</th>
        </tr>
        <tr>
            <td>Local Sí:</td><td>' . $totalLocalSi . '</td>
        </tr>
        <tr>
            <td>Local No:</td><td>' . $totalLocalNo . '</td>
        </tr>
        <tr>
            <td>Nube Sí:</td><td>' . $totalNubeSi . '</td>
        </tr>
        <tr>
            <td>Nube No:</td><td>' . $totalNubeNo . '</td>
        </tr>
    </table>';

        // Agregamos el contenido al PDF (Convierte el HTML de la tabla a PDF)
        $pdf->writeHTML($html, true, false, true, false, '');
        // Mostramos o descargamos
        $pdf->Output("Reporte Mensual - Incremental {$fecha}.pdf", 'I'); // 'I' para mostrar en navegador, 'D' para forzar descarga

    }

    //Exportar Excel de copias diarias incrementales
    public static function exportarExcel()
    {
        if (!isAuth()) {
            header('Location: /login');
            exit;
        }

        $fecha = $_GET['fecha'] ?? '';
        $nombreArchivo = "Reporte Diario - Incremental {$fecha}.xlsx";
        if (!$fecha) {
            header('Location: /incremental-descargar-diaria');
            exit;
        }

        // Buscar copias encabezado por fecha
        $copias = CopiasEncabezado::whereLike('fecha', $fecha);
        if (empty($copias)) {
            echo "<script>alert('No se encontraron registros para la fecha seleccionada.');window.location.href='/incremental-descargar-diaria';</script>";
            exit;
        }

        //Extraemos el Id de la copiaEncabezado para usarlo en la consulta de detalles
        $ids = array_map(fn($copia) => $copia->id, $copias);
        $id = $ids[0];

        //Obtenemos los copiaDetalle relacionados con la copiaEncabezado
        //El método allWhere filtra los detalles por el id de la copiaEncabezado
        //1er parámetro: Nombre de la columna B, 2do parámetro: Tipo de copia, 3er parámetro: id de la copiaEncabezado, 4to parámetro: orden
        $detalles = CopiasDetalle::allWhere('copiasencabezado', 1, $id, 'DESC');

        // Añadir nombres de equipos para ordenar
        foreach ($detalles as $detalle) {
            //Se Crea Una LLave Llamada equipos Dentro Del Objeto De copiasDetalle Y La Buscamos Por Su Id(En La Tabla De Equipos)
            $detalle->equipos = Equipos::find($detalle->idEquipos);
            $detalle->nombreEquipo = $detalle->equipos->nombreEquipo;
        }

        // ORDENAR ALFABETICAMENTE
        //Ordenamos el arreglo $detalles usando $nobreEquipo como llave de comparación
        //La función strcmp compara dos cadenas de texto y devuelve 0 si son iguales, un valor menor que 0 si la primera es menor que la segunda, o un valor mayor que 0 si la primera es mayor que la segunda.
        //La función usort nos permite ordernar un arreglo dependiendo de una función de comparación fn, usort no devuelve una copia ordenada del array, sino que modifica el array original.
        usort($detalles, fn($a, $b) => strcmp($a->nombreEquipo, $b->nombreEquipo));

        // CREAR EXCEL
        //Creamos el objeto de PhpSpreadsheet 
        $spreadsheet = new Spreadsheet();
        //Obtenemos la hoja activa en donde se escribirán los datos
        $sheet = $spreadsheet->getActiveSheet();

        // ENCABEZADOS
        //Todos estos irán en la primera fila
        $sheet->setCellValue('A1', 'Equipo');
        $sheet->setCellValue('B1', 'Copia Local');
        $sheet->setCellValue('C1', 'Copia Nube');
        $sheet->setCellValue('D1', 'Observaciones');

        // ESTILOS DEL ENCABEZADO
        //Fuente en negrita, color de texto blanco, centra horizontalmente y aplicamos un fondo de color azul #4F81BD al rango A1:D1.
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'color' => ['rgb' => '4F81BD']
            ]
        ];
        $sheet->getStyle('A1:D1')->applyFromArray($headerStyle);

        // LLENADO DE DATOS
        //Iniciamos la fila en 2 porque la 1ra fila ya tiene los encabezados
        $fila = 2;
        //Inicializamos los contadores para los totales de copias locales y en la nube
        $totalLocalSi = $totalLocalNo = $totalNubeSi = $totalNubeNo = 0;

        //Iteramos sobre los detalles de las copias
        foreach ($detalles as $detalle) {
            //Con la función setCellValue() establecemos el valor de la celda
            //Parámetros: 1er = Columna, 2do = Fila, 3ero = Valor
            $sheet->setCellValue('A' . $fila, $detalle->nombreEquipo);
            //Obtenemos los valores de copiaLocal y copiaNube, los convertimos a 'Sí' o 'No'
            $valorLocal = $detalle->copiaLocal ? 'Sí' : 'No';
            $valorNube = $detalle->copiaNube ? 'Sí' : 'No';
            //Contamos los totales de copias locales y en la nube
            if ($valorLocal === 'Sí') $totalLocalSi++;
            else $totalLocalNo++;
            if ($valorNube === 'Sí') $totalNubeSi++;
            else $totalNubeNo++;
            //Rellenamos el resto de las celdas: $valorLocal && $valorNube = Si || No, Observaciones = Cadena de texto, si no hay texto estará vacía
            $sheet->setCellValue('B' . $fila, $valorLocal);
            $sheet->setCellValue('C' . $fila, $valorNube);
            $sheet->setCellValue('D' . $fila, $detalle->observaciones ?: '');
            //Aumentamos la fila para la siguiente iteración
            //De esta forma, la siguiente iteración escribirá en la siguiente fila
            $fila++;
        }

        $ultimaFila = $fila - 1;

        // FORMATO CONDICIONAL PARA COLUMNAS B (Copia Local) Y C (Copia Nube)
        //PhpSpreadsheet espera que la condición de texto vaya entre comillas. Por eso se pasa la cadena con comillas internas.
        //Condicional para cuando halla un 'Si'
        $condicionalSi = new \PhpOffice\PhpSpreadsheet\Style\Conditional();
        $condicionalSi->setConditionType(\PhpOffice\PhpSpreadsheet\Style\Conditional::CONDITION_CELLIS)
            ->setOperatorType(\PhpOffice\PhpSpreadsheet\Style\Conditional::OPERATOR_EQUAL)
            ->addCondition('"Sí"');
        $condicionalSi->getStyle()->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('00FF00'); // Fondo verde
        $condicionalSi->getStyle()->getFont()->getColor()->setRGB('000000'); // Texto negro

        $condicionalNo = new \PhpOffice\PhpSpreadsheet\Style\Conditional();
        $condicionalNo->setConditionType(\PhpOffice\PhpSpreadsheet\Style\Conditional::CONDITION_CELLIS)
            ->setOperatorType(\PhpOffice\PhpSpreadsheet\Style\Conditional::OPERATOR_EQUAL)
            ->addCondition('"No"');
        $condicionalNo->getStyle()->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('FF0000'); // Fondo rojo
        $condicionalNo->getStyle()->getFont()->getColor()->setRGB('000000'); // Texto negro

        //Le pasamos los estilos condicionales a todas las filas de las columnas B Y C
        $sheet->getStyle("B2:B{$ultimaFila}")->setConditionalStyles([$condicionalSi, $condicionalNo]);
        $sheet->getStyle("C2:C{$ultimaFila}")->setConditionalStyles([$condicionalSi, $condicionalNo]);


        // RESUMEN CON TOTALES
        $filaResumen = $ultimaFila + 2;
        $sheet->setCellValue("A{$filaResumen}", 'Totales:');
        $sheet->setCellValue("B{$filaResumen}", "Local Sí: {$totalLocalSi}");
        $sheet->setCellValue("B" . ($filaResumen + 1), "Local No: {$totalLocalNo}");
        $sheet->setCellValue("C{$filaResumen}", "Nube Sí: {$totalNubeSi}");
        $sheet->setCellValue("C" . ($filaResumen + 1), "Nube No: {$totalNubeNo}");

        // BORDES PARA LA TABLA CON LOS DATOS
        //Aplicamos todos los bordes y le asignamos el color negro
        $sheet->getStyle("A1:D{$ultimaFila}")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['rgb' => '000000']
                ]
            ]
        ]);

        // AUTO FILTRO
        //Activamos el autofiltro para las columnas A a D
        $sheet->setAutoFilter('A1:D1');

        // AUTOAJUSTE DE COLUMNAS
        //Ajustamos automáticamente el ancho de las columnas A a D
        foreach (range('A', 'D') as $col) {
            //getColumnDimension obtiene la dimensión de la columna y setAutoSize ajusta el ancho automáticamente
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // DESCARGAR EL ARCHIVO
        $writer = new Xlsx($spreadsheet);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment;filename=\"{$nombreArchivo}\"");
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit;
    }

    //Exportar Excel de copias mensuales incrementales
    // public static function exportarExcelM()
    // {
    //     if (!isAuth()) {
    //         header('Location: /login');
    //         exit;
    //     }

    //     $fecha = $_GET['fecha'] ?? '';
    //     $nombreArchivo = "Reporte Diario - Incremental {$fecha}.xlsx";
    //     if (!$fecha) {
    //         header('Location: /incremental-descargar-diaria');
    //         exit;
    //     }

    //     // Buscar copias encabezado por fecha
    //     $copias = CopiasEncabezado::whereLike('fecha', $fecha);
    //     if (empty($copias)) {
    //         echo "<script>alert('No se encontraron registros para la fecha seleccionada.');window.location.href='/incremental-descargar-diaria';</script>";
    //         exit;
    //     }

    //     //Extraemos el Id de la copiaEncabezado para usarlo en la consulta de detalles
    //     $ids = array_map(fn($copia) => $copia->id, $copias);
    //     $id = $ids[0];

    //     //Obtenemos los copiaDetalle relacionados con la copiaEncabezado
    //     //El método allWhere filtra los detalles por el id de la copiaEncabezado
    //     //1er parámetro: Nombre de la columna B, 2do parámetro: Tipo de copia, 3er parámetro: id de la copiaEncabezado, 4to parámetro: orden
    //     $detalles = CopiasDetalle::allWhere('copiasencabezado', 1, $id, 'DESC');

    //     // Añadir nombres de equipos para ordenar
    //     foreach ($detalles as $detalle) {
    //         //Se Crea Una LLave Llamada equipos Dentro Del Objeto De copiasDetalle Y La Buscamos Por Su Id(En La Tabla De Equipos)
    //         $detalle->equipos = Equipos::find($detalle->idEquipos);
    //         $detalle->nombreEquipo = $detalle->equipos->nombreEquipo;
    //     }

    //     // ORDENAR ALFABETICAMENTE
    //     //Ordenamos el arreglo $detalles usando $nobreEquipo como llave de comparación
    //     //La función strcmp compara dos cadenas de texto y devuelve 0 si son iguales, un valor menor que 0 si la primera es menor que la segunda, o un valor mayor que 0 si la primera es mayor que la segunda.
    //     //La función usort nos permite ordernar un arreglo dependiendo de una función de comparación fn, usort no devuelve una copia ordenada del array, sino que modifica el array original.
    //     usort($detalles, fn($a, $b) => strcmp($a->nombreEquipo, $b->nombreEquipo));

    //     // CREAR EXCEL
    //     //Creamos el objeto de PhpSpreadsheet 
    //     $spreadsheet = new Spreadsheet();
    //     //Obtenemos la hoja activa en donde se escribirán los datos
    //     $sheet = $spreadsheet->getActiveSheet();

    //     // ENCABEZADOS
    //     //Todos estos irán en la primera fila
    //     $sheet->setCellValue('A1', 'Equipo');
    //     $sheet->setCellValue('B1', 'Copia Local');
    //     $sheet->setCellValue('C1', 'Copia Nube');
    //     $sheet->setCellValue('D1', 'Observaciones');

    //     // ESTILOS DEL ENCABEZADO
    //     //Fuente en negrita, color de texto blanco, centra horizontalmente y aplicamos un fondo de color azul #4F81BD al rango A1:D1.
    //     $headerStyle = [
    //         'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
    //         'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
    //         'fill' => [
    //             'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
    //             'color' => ['rgb' => '4F81BD']
    //         ]
    //     ];
    //     $sheet->getStyle('A1:D1')->applyFromArray($headerStyle);

    //     // LLENADO DE DATOS
    //     //Iniciamos la fila en 2 porque la 1ra fila ya tiene los encabezados
    //     $fila = 2;
    //     //Inicializamos los contadores para los totales de copias locales y en la nube
    //     $totalLocalSi = $totalLocalNo = $totalNubeSi = $totalNubeNo = 0;

    //     //Iteramos sobre los detalles de las copias
    //     foreach ($detalles as $detalle) {
    //         //Con la función setCellValue() establecemos el valor de la celda
    //         //Parámetros: 1er = Columna, 2do = Fila, 3ero = Valor
    //         $sheet->setCellValue('A' . $fila, $detalle->nombreEquipo);
    //         //Obtenemos los valores de copiaLocal y copiaNube, los convertimos a 'Sí' o 'No'
    //         $valorLocal = $detalle->copiaLocal ? 'Sí' : 'No';
    //         $valorNube = $detalle->copiaNube ? 'Sí' : 'No';
    //         //Contamos los totales de copias locales y en la nube
    //         if ($valorLocal === 'Sí') $totalLocalSi++;
    //         else $totalLocalNo++;
    //         if ($valorNube === 'Sí') $totalNubeSi++;
    //         else $totalNubeNo++;
    //         //Rellenamos el resto de las celdas: $valorLocal && $valorNube = Si || No, Observaciones = Cadena de texto, si no hay texto estará vacía
    //         $sheet->setCellValue('B' . $fila, $valorLocal);
    //         $sheet->setCellValue('C' . $fila, $valorNube);
    //         $sheet->setCellValue('D' . $fila, $detalle->observaciones ?: '');
    //         //Aumentamos la fila para la siguiente iteración
    //         //De esta forma, la siguiente iteración escribirá en la siguiente fila
    //         $fila++;
    //     }

    //     $ultimaFila = $fila - 1;

    //     // FORMATO CONDICIONAL PARA COLUMNAS B (Copia Local) Y C (Copia Nube)
    //     //PhpSpreadsheet espera que la condición de texto vaya entre comillas. Por eso se pasa la cadena con comillas internas.
    //     //Condicional para cuando halla un 'Si'
    //     $condicionalSi = new \PhpOffice\PhpSpreadsheet\Style\Conditional();
    //     $condicionalSi->setConditionType(\PhpOffice\PhpSpreadsheet\Style\Conditional::CONDITION_CELLIS)
    //         ->setOperatorType(\PhpOffice\PhpSpreadsheet\Style\Conditional::OPERATOR_EQUAL)
    //         ->addCondition('"Sí"');
    //     $condicionalSi->getStyle()->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
    //         ->getStartColor()->setRGB('00FF00'); // Fondo verde
    //     $condicionalSi->getStyle()->getFont()->getColor()->setRGB('000000'); // Texto negro

    //     $condicionalNo = new \PhpOffice\PhpSpreadsheet\Style\Conditional();
    //     $condicionalNo->setConditionType(\PhpOffice\PhpSpreadsheet\Style\Conditional::CONDITION_CELLIS)
    //         ->setOperatorType(\PhpOffice\PhpSpreadsheet\Style\Conditional::OPERATOR_EQUAL)
    //         ->addCondition('"No"');
    //     $condicionalNo->getStyle()->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
    //         ->getStartColor()->setRGB('FF0000'); // Fondo rojo
    //     $condicionalNo->getStyle()->getFont()->getColor()->setRGB('000000'); // Texto negro

    //     //Le pasamos los estilos condicionales a todas las filas de las columnas B Y C
    //     $sheet->getStyle("B2:B{$ultimaFila}")->setConditionalStyles([$condicionalSi, $condicionalNo]);
    //     $sheet->getStyle("C2:C{$ultimaFila}")->setConditionalStyles([$condicionalSi, $condicionalNo]);


    //     // RESUMEN CON TOTALES
    //     $filaResumen = $ultimaFila + 2;
    //     $sheet->setCellValue("A{$filaResumen}", 'Totales:');
    //     $sheet->setCellValue("B{$filaResumen}", "Local Sí: {$totalLocalSi}");
    //     $sheet->setCellValue("B" . ($filaResumen + 1), "Local No: {$totalLocalNo}");
    //     $sheet->setCellValue("C{$filaResumen}", "Nube Sí: {$totalNubeSi}");
    //     $sheet->setCellValue("C" . ($filaResumen + 1), "Nube No: {$totalNubeNo}");

    //     // BORDES PARA LA TABLA CON LOS DATOS
    //     //Aplicamos todos los bordes y le asignamos el color negro
    //     $sheet->getStyle("A1:D{$ultimaFila}")->applyFromArray([
    //         'borders' => [
    //             'allBorders' => [
    //                 'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
    //                 'color' => ['rgb' => '000000']
    //             ]
    //         ]
    //     ]);

    //     // AUTO FILTRO
    //     //Activamos el autofiltro para las columnas A a D
    //     $sheet->setAutoFilter('A1:D1');

    //     // AUTOAJUSTE DE COLUMNAS
    //     //Ajustamos automáticamente el ancho de las columnas A a D
    //     foreach (range('A', 'D') as $col) {
    //         //getColumnDimension obtiene la dimensión de la columna y setAutoSize ajusta el ancho automáticamente
    //         $sheet->getColumnDimension($col)->setAutoSize(true);
    //     }

    //     // DESCARGAR EL ARCHIVO
    //     $writer = new Xlsx($spreadsheet);
    //     header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    //     header("Content-Disposition: attachment;filename=\"{$nombreArchivo}\"");
    //     header('Cache-Control: max-age=0');
    //     $writer->save('php://output');
    //     exit;
    // }
}
