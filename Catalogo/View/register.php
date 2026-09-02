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
$nomePost  = $_POST["nome"]  ?? "";
$emailPost = $_POST["email"] ?? "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nome           = $_POST["nome"]            ?? "";
    $email          = $_POST["email"]           ?? "";
    $senha          = $_POST["senha"]           ?? "";
    $confirmarSenha = $_POST["confirmar_senha"] ?? "";

    $resultado = $usuarioController->cadastrarUsuario($nome, $email, $senha, $confirmarSenha);

    if ($resultado["ok"]) {
        header("Location: login.php?ok=" . urlencode("Conta criada! Faça login."));
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
    <title>Criar conta — Catálogo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../templates/css/global.css">
</head>
<body>
<div class="auth-wrap">
    <div class="auth-card">
        <div class="text-center mb-4">
            <div class="logo-circle mx-auto mb-3"><i class="bi bi-person-plus fs-4"></i></div>
            <h1 class="h4 fw-bold mb-1">Criar sua conta</h1>
            <p class="text-muted small mb-0">Leva menos de 30 segundos</p>
        </div>

        <?php if ($mensagemErro): ?>
            <div class="alerta toast-erro border mb-3"><i class="bi bi-exclamation-triangle me-1"></i><?= esc($mensagemErro) ?></div>
        <?php endif; ?>

        <form method="POST" action="register.php" novalidate>
            <div class="mb-3">
                <label for="nome" class="form-label">Nome</label>
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="bi bi-person"></i></span>
                    <input type="text" name="nome" id="nome" class="form-control" placeholder="Seu nome" required value="<?= esc($nomePost) ?>">
                </div>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">E-mail</label>
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="bi bi-envelope"></i></span>
                    <input type="email" name="email" id="email" class="form-control" placeholder="seu@email.com" required value="<?= esc($emailPost) ?>">
                </div>
            </div>
            <div class="mb-3">
                <label for="senha" class="form-label">Senha</label>
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="bi bi-lock"></i></span>
                    <input type="password" name="senha" id="senha" class="form-control" placeholder="Mínimo 6 caracteres" required>
                </div>
            </div>
            <div class="mb-4">
                <label for="confirmar_senha" class="form-label">Confirmar senha</label>
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="bi bi-lock-fill"></i></span>
                    <input type="password" name="confirmar_senha" id="confirmar_senha" class="form-control" placeholder="Repita a senha" required>
                </div>
            </div>
            <button type="submit" class="btn btn-primario w-100 py-2">Criar conta</button>
        </form>

        <p class="text-center small text-muted mt-4 mb-0">
            Já tem conta? <a href="login.php" class="fw-semibold">Faça login</a><br>
            <a href="../index.php" class="text-muted"><i class="bi bi-arrow-left me-1"></i>Voltar ao início</a>
        </p>
    </div>
</div>
</body>
</html>
