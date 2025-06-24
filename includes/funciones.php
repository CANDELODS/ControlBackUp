<?php

function debuguear($variable): string
{
    echo "<pre>";
    var_dump($variable);
    echo "</pre>";
    exit;
}
function s($html): string
{
    $s = htmlspecialchars($html);
    return $s;
}

function isAuth(): bool
{
    if (!isset($_SESSION)) {
        session_start();
    }

    return isset($_SESSION['email']) && !empty($_SESSION);
}
