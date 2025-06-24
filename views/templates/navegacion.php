 <header class="header">

     <a href="/" class="header__logo">
         <picture>
             <source srcset="build/img/logo.avif" type="image/avif">
             <source srcset="build/img/logo.webp" type="image/webp">
             <img src="build/img/logo.png" alt="Logo Ladrillera"
                 class="header__img" loading="lazy" width="200" heigth="300">
         </picture>
     </a>

     <div class="navegacion__movil" id="abrirMenu">
         <i class="fa-solid fa-bars navegacion__menu"></i>
     </div>

     <nav class="navegacion" id="navegacion">
         <ul class="navegacion__ul">
             <li class="navegacion__li">
                 <a class="navegacion__enlace" href="/" id="navegacionInicio">Inicio</a>
             </li>
             <li class="navegacion__li">
                 <a class="navegacion__enlace" href="/incremental" id="navegacionIncremental">Incremental</a>
             </li>
             <li class="navegacion__li">
                 <a class="navegacion__enlace" href="/completa" id="navegacionCompleta">Completa</a>
             </li>
             <li class="navegacion__li">
                 <a class="navegacion__enlace" href="/equipos" id="navegacionEquipos">Equipos</a>
             </li>
             <li class="navegacion__li">
                 <a class="navegacion__enlace" href="/consejos" id="navegacionConsejos">Consejos</a>
             </li>
             <li class="navegacion__li">
                 <form method="post" action="/logout" class="navegacion__form">
                    <input type="submit" class="navegacion__enlace navegacion__enlace--submit" value="Cerrar Sesión">
                 </form>
             </li>
         </ul>

         <div class="navegacion__btn-salir" id="cerrarMenu">
             Cerrar menú <i class="fa-solid fa-circle-xmark"></i>
         </div>
     </nav>
 </header>