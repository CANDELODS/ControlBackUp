<?php

namespace Controllers;

use Model\Usuario;
use MVC\Router;

class PrincipalController
{
    public static function principal(Router $router)
    {


        $alertas = Usuario::getAlertas();
        // Render a la vista 
        $router->render('principal/principal', [
            'titulo' => 'Principal',
            'alertas' => $alertas
        ]);
    }
}
