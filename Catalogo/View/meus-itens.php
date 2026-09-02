<?php
session_start();
require_once __DIR__ . "/../vendor/autoload.php";
use Controller\ItemController;
use Controller\UsuarioController;

$usuarioController = new UsuarioController();
$itemController    = new ItemController();
$usuarioController->exigirLogin();

$idUsuario = $usuarioController->obterIdUsuarioLogado();
$nome      = $usuarioController->obterNomeUsuarioLogado();
$msg       = $_GET["msg"]  ?? "";
$erro      = $_GET["erro"] ?? "";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["excluir_id"])) {
    $token = $_POST["token_csrf"] ?? null;
    if (!$usuarioController->validarTokenCsrf($token)) { $erro = "Token inválido."; }
    else {
        $res = $itemController->excluirItem((int)$_POST["excluir_id"], $idUsuario);
        if ($res["ok"]) { header("Location: meus-itens.php?msg=".urlencode($res["mensagem"])); exit(); }
        $erro = $res["mensagem"];
    }
}

$meusItens = $itemController->listarItensDoUsuario($idUsuario);
function esc(string $v): string { return htmlspecialchars($v, ENT_QUOTES, "UTF-8"); }
function iconeTipo(string $t): string { return match($t){"livro"=>"bi-book","filme"=>"bi-film","jogo"=>"bi-controller",default=>"bi-tag"}; }
function classeBadge(string $t): string { return match($t){"livro"=>"badge-livro","filme"=>"badge-filme","jogo"=>"badge-jogo",default=>"badge-livro"}; }
$tokenCsrf = $usuarioController->gerarTokenCsrf();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Meus itens — Catálogo</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../templates/css/global.css">
</head>
<body>
<nav class="navbar-catalogo"><div class="container py-2 d-flex justify-content-between align-items-center flex-wrap gap-3">
<a href="../index.php" class="brand d-flex align-items-center gap-2 text-decoration-none"><span class="logo-circle" style="width:38px;height:38px;font-size:1.1rem"><i class="bi bi-collection-play"></i></span>CATÁLOGO</a>
<div class="d-flex gap-2 flex-wrap"><a href="../index.php" class="btn btn-contorno btn-sm">Início</a><a href="catalog.php" class="btn btn-contorno btn-sm">Catálogo</a><a href="create.php" class="btn btn-primario btn-sm"><i class="bi bi-plus-lg me-1"></i> Cadastrar</a><a href="logout.php" class="btn btn-contorno btn-sm">Sair</a></div>
</div></nav>
<main class="container py-4">
<div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
<div><h1 class="h4 fw-bold mb-1">Olá, <?= esc($nome) ?>! 👋</h1><p class="text-muted small mb-0">Gerencie seus itens cadastrados. Você só pode editar e excluir o que é seu.</p></div>
<a href="create.php" class="btn btn-primario"><i class="bi bi-plus-lg me-1"></i> Novo item</a>
</div>
<?php if($msg): ?><div class="alerta toast-sucesso border mb-3"><?= esc($msg) ?></div><?php endif; ?>
<?php if($erro): ?><div class="alerta toast-erro border mb-3"><?= esc($erro) ?></div><?php endif; ?>
<div class="d-flex gap-2 mb-3 flex-wrap">
<a href="catalog.php" class="btn btn-contorno btn-sm"><i class="bi bi-grid me-1"></i> Ver catálogo completo</a>
<span class="text-muted small align-self-center"><?= count($meusItens) ?> itens cadastrados por você</span>
</div>
<?php if(empty($meusItens)): ?>
<div class="info-box text-center py-5"><div class="fs-1 mb-2">📦</div><p class="text-muted mb-3">Você ainda não cadastrou nenhum item.</p><a href="create.php" class="btn btn-primario">Cadastrar meu primeiro item</a></div>
<?php else: ?>
<div class="row g-3">
<?php foreach($meusItens as $item): ?>
<div class="col-12 col-sm-6 col-lg-4">
<div class="card-catalogo">
<div class="capa-wrap">
<?php if(!empty($item["imagem"])): ?><img src="../storage/uploads/capas/<?= esc($item["imagem"]) ?>" alt="<?= esc($item["titulo"]) ?>"><?php else: ?><span class="capa-placeholder"><i class="bi <?= iconeTipo($item["tipo"]) ?>"></i></span><?php endif; ?>
</div>
<div class="card-body-catalogo">
<span class="badge-tipo <?= classeBadge($item["tipo"]) ?>"><?= esc($item["tipo"]) ?></span>
<h3 class="card-titulo"><a href="item.php?id=<?= (int)$item["id"] ?>" class="text-dark text-decoration-none"><?= esc($item["titulo"]) ?></a></h3>
<div class="meta mb-2"><span><i class="bi bi-calendar me-1"></i><?= (int)$item["ano"] ?></span><span class="nota"><i class="bi bi-star-fill me-1"></i><?= number_format((float)$item["nota"],1,",",".") ?></span></div>
<p class="card-desc mb-3"><?= esc(mb_strimwidth($item["descricao"],0,90,"...")) ?></p>
<div class="d-flex gap-2 mt-auto">
<a href="item.php?id=<?= (int)$item["id"] ?>" class="btn btn-contorno btn-sm flex-grow-1">Ver</a>
<a href="edit.php?id=<?= (int)$item["id"] ?>" class="btn btn-primario btn-sm flex-grow-1"><i class="bi bi-pencil me-1"></i>Editar</a>
<button class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#excluir<?= (int)$item["id"] ?>"><i class="bi bi-trash"></i></button>
</div>
</div>
</div>
<div class="modal fade" id="excluir<?= (int)$item["id"] ?>" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><div class="modal-content" style="border-radius:16px">
<div class="modal-header"><h5 class="modal-title">Excluir?</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
<div class="modal-body">Excluir <strong><?= esc($item["titulo"]) ?></strong> permanentemente?</div>
<div class="modal-footer">
<button type="button" class="btn btn-contorno" data-bs-dismiss="modal">Cancelar</button>
<form method="POST" action="meus-itens.php"><input type="hidden" name="token_csrf" value="<?= esc($tokenCsrf) ?>"><input type="hidden" name="excluir_id" value="<?= (int)$item["id"] ?>"><button type="submit" class="btn btn-danger">Excluir</button></form>
</div></div></div></div>
</div>
<?php endforeach; ?>
</div>
<?php endif; ?>
</main>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
