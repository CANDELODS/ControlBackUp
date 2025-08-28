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
            $total_registros = CopiasEncabezado::totalPorMes('fecha', $mes, 'tipoDeCopia', 0);

            if (!$total_registros) {
                // No hay resultados para el mes seleccionado
                $sin_resultados = true;
                $copias = [];
                $paginacion = null;
            } else {
                // Sí hay registros
                $paginacion = new Paginacion($pagina_actual, $registros_por_pagina, $total_registros);
                $copias = CopiasEncabezado::porMesPaginado('fecha', $mes, $registros_por_pagina, $paginacion->offset(), 'tipoDeCopia', 0);
            }
        } else {
            // Si no se seleccionó mes, se muestran todos los meses
            $total_registros = CopiasEncabezado::totalMeses('fecha', 'tipoDeCopia', 0);
            $paginacion = new Paginacion($pagina_actual, $registros_por_pagina, $total_registros);
            $copias = CopiasEncabezado::mesesPaginados('fecha', $registros_por_pagina, $paginacion->offset(), 'tipoDeCopia', 0);
        }

        // Validación de página fuera de rango
        if ($paginacion && $paginacion->totalPaginas() < $pagina_actual) {
            header('Location: /completo-descargar?page=1');
            exit;
        }

        //Convertimos los 0 y 1 de la columna tipoDeCopia en strings para usarlos en la vista
        foreach ($copias as $copia) {
            //Si hay algo (Por ejemplo 1) será incremental, de lo contrario (0) será completa
            $copia->tipoDeCopia = $copia->tipoDeCopia ? 'Incremental' : 'Completa';
        }

        // Obtenemos todos los detalles (luego se puede optimizar a "solo del mes")
        $copiasDetalle = CopiasDetalle::allWhereMes($mes, 0);
        // debuguear($copiasDetalle);

        $router->render('informeCompleto/completo', [
            'titulo' => 'Informe Completo',
            'alertas' => Usuario::getAlertas(),
            'copias' => $copias,
            'copiasDetalle' => $copiasDetalle,
            'paginacion' => $paginacion ? $paginacion->paginacion() : null,
            'sin_resultados' => $sin_resultados
        ]);
    }

}
