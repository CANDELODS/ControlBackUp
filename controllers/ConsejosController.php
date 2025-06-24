<?php

namespace Controllers;

use Model\Usuario;
use MVC\Router;

class ConsejosController
{
    public static function consejos(Router $router)
    {


        $alertas = Usuario::getAlertas();
        // Render a la vista 
        $router->render('consejos/consejos', [
            'titulo' => 'Consejos',
            'alertas' => $alertas
        ]);
    }
}
