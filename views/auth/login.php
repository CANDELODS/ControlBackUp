<main class="auth">
    <div class="auth__contenedor">
        <!-- Importamos las alertas -->
    <?php
        require_once __DIR__ . '/../templates/alertas.php';
    ?>
        <!-- Formulario con método post el cual manejamos en esta misma URL (/) -->
        <form method="post" action="/" class="formulario">
            <picture>
                <source srcset="build/img/logo.avif" type="image/avif">
                <source srcset="build/img/logo.webp" type="image/webp">
                <img src="build/img/logo.png" alt="Logo Ladrillera"
                class="formulario__img" loading="lazy" width="200" heigth="300">
            </picture>
            <div class="formulario__campo">
                <label for="email" class="formulario__label">Email:</label>
                <input
                    type="email"
                    class="formulario__input"
                    placeholder="Ingrese su correo"
                    id="email"
                    name="email">
            </div>

            <div class="formulario__campo">
                <label for="password" class="formulario__label">Contraseña:</label>
                <input
                    type="password"
                    class="formulario__input"
                    placeholder="Ingrese su contraseña"
                    id="password"
                    name="password">
            </div>

            <input type="submit" class="formulario__submit" value="Iniciar Sesión">
        </form>
    </div>

</main>