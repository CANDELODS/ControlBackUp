<?php

namespace Controllers;

use Model\Usuario;
use MVC\Router;

class ConsejosController
{
    public static function consejos(Router $router)
    {
        // Render a la vista 
        $router->render('consejos/consejos', [
            'titulo' => 'Consejos'
        ]);
    }

        public static function consejosICB(Router $router)
    {
        // Render a la vista 
        $router->render('consejos/instalacionCobian', [
            'titulo' => 'Instalación de Cobian Backup'
        ]);
    }
}
