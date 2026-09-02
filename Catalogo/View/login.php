<?php
session_start();
require_once __DIR__ . "/../vendor/autoload.php";

use Controller\UsuarioController;

$usuarioController = new UsuarioController();

if ($usuarioController->estaLogado()) {
    header("Location: ../index.php");
    exit();
}

$mensagemErro = "";
$mensagemOk   = $_GET["ok"] ?? "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = $_POST["email"] ?? "";
    $senha = $_POST["senha"] ?? "";

    $resultado = $usuarioController->login($email, $senha);

    if ($resultado["ok"]) {
        header("Location: ../index.php");
        exit();
    }

    $mensagemErro = $resultado["mensagem"];
}

function esc(string $v): string { return htmlspecialchars($v, ENT_QUOTES, "UTF-8"); }
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Entrar — Catálogo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../templates/css/global.css">
</head>
<body>
<div class="auth-wrap">
    <div class="auth-card">
        <div class="text-center mb-4">
            <div class="logo-circle mx-auto mb-3"><i class="bi bi-collection-play fs-4"></i></div>
            <h1 class="h4 fw-bold mb-1">Entrar na sua conta</h1>
            <p class="text-muted small mb-0">Acesse seu catálogo de livros, filmes e jogos</p>
        </div>

        <?php if ($mensagemOk): ?>
            <div class="alerta toast-sucesso border mb-3"><?= esc($mensagemOk) ?></div>
        <?php endif; ?>

        <?php if ($mensagemErro): ?>
            <div class="alerta toast-erro border mb-3"><i class="bi bi-exclamation-triangle me-1"></i><?= esc($mensagemErro) ?></div>
        <?php endif; ?>

        <form method="POST" action="login.php" novalidate>
            <div class="mb-3">
                <label for="email" class="form-label">E-mail</label>
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="bi bi-envelope"></i></span>
                    <input type="email" name="email" id="email" class="form-control" placeholder="seu@email.com" required value="<?= esc($_POST["email"] ?? "") ?>">
                </div>
            </div>
            <div class="mb-4">
                <label for="senha" class="form-label">Senha</label>
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="bi bi-lock"></i></span>
                    <input type="password" name="senha" id="senha" class="form-control" placeholder="Sua senha" required>
                </div>
            </div>
            <button type="submit" class="btn btn-primario w-100 py-2">Entrar</button>
        </form>

        <p class="text-center small text-muted mt-4 mb-0">
            Não tem conta? <a href="register.php" class="fw-semibold">Cadastre-se aqui</a><br>
            <a href="../index.php" class="text-muted"><i class="bi bi-arrow-left me-1"></i>Voltar ao início</a>
        </p>
    </div>
</div>
</body>
</html>
