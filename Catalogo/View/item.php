<?php
session_start();
require_once __DIR__ . "/../vendor/autoload.php";
use Controller\ItemController;
use Controller\UsuarioController;

$usuarioController = new UsuarioController();
$itemController    = new ItemController();

$idItem = isset($_GET["id"]) ? (int) $_GET["id"] : 0;
if ($idItem <= 0) { header("Location: catalog.php"); exit(); }

$item = $itemController->buscarPorId($idItem);
if (!$item) { header("Location: catalog.php"); exit(); }

$estaLogado = $usuarioController->estaLogado();
$idLogado   = $usuarioController->obterIdUsuarioLogado();
$eDono      = $estaLogado && (int)$item["usuario_id"] === $idLogado;
$msg        = $_GET["msg"] ?? "";
$erro       = $_GET["erro"] ?? "";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["excluir"])) {
    if (!$estaLogado) { header("Location: login.php"); exit(); }
    $token = $_POST["token_csrf"] ?? null;
    if (!$usuarioController->validarTokenCsrf($token)) { $erro = "Token inválido."; }
    else {
        $res = $itemController->excluirItem($idItem, $idLogado);
        if ($res["ok"]) { header("Location: meus-itens.php?msg=".urlencode($res["mensagem"])); exit(); }
        $erro = $res["mensagem"];
    }
}

function esc(string $v): string { return htmlspecialchars($v, ENT_QUOTES, "UTF-8"); }
function iconeTipo(string $t): string { return match($t){"livro"=>"bi-book","filme"=>"bi-film","jogo"=>"bi-controller",default=>"bi-tag"}; }
function classeBadge(string $t): string { return match($t){"livro"=>"badge-livro","filme"=>"badge-filme","jogo"=>"badge-jogo",default=>"badge-livro"}; }
$tokenCsrf = $estaLogado ? $usuarioController->gerarTokenCsrf() : "";
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= esc($item["titulo"]) ?> — Catálogo</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../templates/css/global.css">
</head>
<body>
<nav class="navbar-catalogo">
<div class="container py-2 d-flex align-items-center justify-content-between gap-3 flex-wrap">
<a href="../index.php" class="brand d-flex align-items-center gap-2 text-decoration-none"><span class="logo-circle" style="width:38px;height:38px;font-size:1.1rem"><i class="bi bi-collection-play"></i></span>CATÁLOGO</a>
<div class="d-flex gap-2 flex-wrap">
<a href="catalog.php" class="btn btn-contorno btn-sm">Catálogo</a>
<?php if($estaLogado): ?><a href="meus-itens.php" class="btn btn-contorno btn-sm">Meus itens</a><a href="logout.php" class="btn btn-contorno btn-sm">Sair</a><?php else: ?><a href="login.php" class="btn btn-contorno btn-sm">Entrar</a><a href="register.php" class="btn btn-primario btn-sm">Criar conta</a><?php endif; ?>
</div></div></nav>
<main class="container py-4">
<?php if($msg): ?><div class="alerta toast-sucesso border mb-3"><?= esc($msg) ?></div><?php endif; ?>
<?php if($erro): ?><div class="alerta toast-erro border mb-3"><?= esc($erro) ?></div><?php endif; ?>
<a href="catalog.php" class="btn btn-contorno btn-sm mb-3"><i class="bi bi-arrow-left me-1"></i>Voltar</a>
<div class="row g-4">
<div class="col-lg-5">
<div class="detalhe-capa">
<?php if(!empty($item["imagem"])): ?><img src="../storage/uploads/capas/<?= esc($item["imagem"]) ?>" alt="<?= esc($item["titulo"]) ?>"><?php else: ?><div class="d-flex align-items-center justify-content-center p-5" style="min-height:320px;background:linear-gradient(135deg,#ede9fe,#fce7f3)"><i class="bi <?= iconeTipo($item["tipo"]) ?>" style="font-size:4rem;opacity:.4"></i></div><?php endif; ?>
</div>
</div>
<div class="col-lg-7">
<div class="info-box">
<span class="badge-tipo <?= classeBadge($item["tipo"]) ?>"><?= esc($item["tipo"]) ?></span>
<h1 class="h3 fw-bold mt-2 mb-2"><?= esc($item["titulo"]) ?></h1>
<div class="meta mb-3">
<span><i class="bi bi-calendar me-1"></i><?= (int)$item["ano"] ?></span>
<span class="nota"><i class="bi bi-star-fill me-1"></i><?= number_format((float)$item["nota"],1,",",".") ?> / 10</span>
</div>
<p class="mb-3" style="white-space:pre-wrap;line-height:1.7"><?= esc($item["descricao"]) ?></p>
<hr>
<div class="small text-muted">
<div><i class="bi bi-person me-1"></i>Cadastrado por <strong><?= esc($item["nome_usuario"]) ?></strong></div>
<div><i class="bi bi-clock me-1"></i><?= date("d/m/Y \à\s H:i", strtotime($item["criado_em"])) ?></div>
</div>
<?php if($eDono): ?>
<hr>
<div class="d-flex gap-2 flex-wrap">
<a href="edit.php?id=<?= (int)$item["id"] ?>" class="btn btn-primario"><i class="bi bi-pencil me-1"></i>Editar</a>
<button class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#modalExcluir"><i class="bi bi-trash me-1"></i>Excluir</button>
</div>
<?php endif; ?>
</div>
</div>
</div>
</main>
<?php if($eDono): ?>
<div class="modal fade" id="modalExcluir" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><div class="modal-content" style="border-radius:16px">
<div class="modal-header"><h5 class="modal-title">Excluir item?</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
<div class="modal-body">Tem certeza que deseja excluir <strong><?= esc($item["titulo"]) ?></strong>? Esta ação não pode ser desfeita.</div>
<div class="modal-footer">
<button type="button" class="btn btn-contorno" data-bs-dismiss="modal">Cancelar</button>
<form method="POST" action="item.php?id=<?= (int)$item["id"] ?>">
<input type="hidden" name="token_csrf" value="<?= esc($tokenCsrf) ?>">
<button type="submit" name="excluir" value="1" class="btn btn-danger">Excluir</button>
</form>
</div></div></div></div>
<?php endif; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
