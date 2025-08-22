<?php 

require_once __DIR__ . '/../includes/app.php';

use MVC\Router;
use Controllers\AuthController;
use Controllers\CompletaController;
use Controllers\ConsejosController;
use Controllers\CopiasController;
use Controllers\EquiposController;
use Controllers\IncrementalController;
use Controllers\IncrementalInformesController;
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
$router->post('/consejos', [ConsejosController::class, 'consejos']);

$router->comprobarRutas();