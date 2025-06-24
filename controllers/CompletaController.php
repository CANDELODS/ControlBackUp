<?php

namespace Controllers;

use Model\Usuario;
use MVC\Router;

class CompletaController
{
    public static function completa(Router $router)
    {


        $alertas = Usuario::getAlertas();
        // Render a la vista 
        $router->render('completa/completa', [
            'titulo' => 'Completa',
            'alertas' => $alertas
        ]);
    }
}
