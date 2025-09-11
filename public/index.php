<?php 

require_once __DIR__ . '/../includes/app.php';

use MVC\Router;
use Controllers\AuthController;
use Controllers\CompletaController;
use Controllers\CompletaInformesController;
use Controllers\ConsejosController;
use Controllers\CopiasController;
use Controllers\EquiposController;
use Controllers\IncrementalController;
use Controllers\IncrementalInformesController;
use Controllers\InformeCompletoController;
use Controllers\PrincipalController;

$router = new Router();


// Login
$router->get('/', [AuthController::class, 'login']);
$router->post('/', [AuthController::class, 'login']);
$router->post('/logout', [AuthController::class, 'logout']);

//Principal
$router->get('/principal', [PrincipalController::class, 'principal']);
// $router->post('/', [PrincipalController::class, 'principal']);

// Incremental
$router->get('/incremental', [IncrementalController::class, 'incremental']);
$router->post('/incremental', [IncrementalController::class, 'incremental']);

//Incremental Informes
$router->get('/incremental-descargar-diaria', [IncrementalInformesController::class, 'get']);
$router->get('/incremental-descargar-mensual', [IncrementalInformesController::class, 'getM']);
$router->get('/descargar-iid', [IncrementalInformesController::class, 'exportarPDF']);
$router->get('/descargar-iim', [IncrementalInformesController::class, 'exportarPDFM']);
$router->get('/descargar-iide', [IncrementalInformesController::class, 'exportarExcel']);
$router->get('/descargar-iieM', [IncrementalInformesController::class, 'exportarExcelM']);

//Completa Informes
$router->get('/completa-descargar-diaria', [CompletaInformesController::class, 'get']);
$router->get('/completa-descargar-mensual', [CompletaInformesController::class, 'getM']);
$router->get('/descargar-icd', [CompletaInformesController::class, 'exportarPDF']);
$router->get('/descargar-icm', [CompletaInformesController::class, 'exportarPDFM']);
$router->get('/descargar-icde', [CompletaInformesController::class, 'exportarExcel']);
$router->get('/descargar-iceM', [CompletaInformesController::class, 'exportarExcelM']);

//Informe Completo
$router->get('/completo-descargar', [InformeCompletoController::class, 'get']);
$router->get('/completo-dpdf', [InformeCompletoController::class, 'exportarPDF']);
$router->get('/completo-dxls', [InformeCompletoController::class, 'exportarExcel']);


// Completa
$router->get('/completa', [CompletaController::class, 'completa']);
$router->post('/completa', [CompletaController::class, 'completa']);

// Equipos
$router->get('/equipos', [EquiposController::class, 'equipos']);
$router->post('/crear-equipo', [EquiposController::class, 'crear']);
$router->get('/crear-equipo', [EquiposController::class, 'crear']);
$router->post('/editar-equipo', [EquiposController::class, 'editar']);
$router->get('/editar-equipo', [EquiposController::class, 'editar']);
$router->post('/eliminar-equipo', [EquiposController::class, 'eliminar']);

//Copias
$router->get('/copias', [CopiasController::class, 'copias']);
$router->post('/editar-copia', [CopiasController::class, 'editar']);
$router->get('/editar-copia', [CopiasController::class, 'editar']);
$router->post('/eliminar-copia', [CopiasController::class, 'eliminar']);

// Consejos
$router->get('/consejos', [ConsejosController::class, 'consejos']);
$router->get('/consejos/instalacion-cobian', [ConsejosController::class, 'consejosICB']);
$router->get('/consejos/error-VSC', [ConsejosController::class, 'consejosEVSC']);
$router->get('/consejos/crear-tarea', [ConsejosController::class, 'consejosCT']);
$router->get('/consejos/recuperar-informacion', [ConsejosController::class, 'consejosRI']);

$router->comprobarRutas();