<?php
session_start();
require_once __DIR__ . "/../vendor/autoload.php";

use Controller\UsuarioController;

$usuarioController = new UsuarioController();
$usuarioController->logout();

header("Location: login.php?ok=" . urlencode("Você saiu da sua conta."));
exit();
