<?php
session_start();
require_once __DIR__ . "/../vendor/autoload.php";
use Controller\ItemController;
use Controller\UsuarioController;

$usuarioController = new UsuarioController();
$itemController    = new ItemController();
$usuarioController->exigirLogin();

$idUsuario = $usuarioController->obterIdUsuarioLogado();
$idItem    = isset($_GET["id"]) ? (int)$_GET["id"] : 0;
if ($idItem <= 0) { header("Location: meus-itens.php"); exit(); }
$item = $itemController->buscarPorId($idItem);
if (!$item) { header("Location: meus-itens.php?erro=".urlencode("Item não encontrado.")); exit(); }
if ((int)$item["usuario_id"] !== $idUsuario) { header("Location: catalog.php?erro=".urlencode("Sem permissão.")); exit(); }

$mensagemErro = "";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $token = $_POST["token_csrf"] ?? null;
    if (!$usuarioController->validarTokenCsrf($token)) {
        $mensagemErro = "Token inválido. Recarregue a página.";
    } else {
        $res = $itemController->editarItem($idItem, $idUsuario, $_POST, $_FILES["imagem"] ?? []);
        if ($res["ok"]) { header("Location: item.php?id=".$idItem."&msg=".urlencode($res["mensagem"])); exit(); }
        $mensagemErro = $res["mensagem"];
    }
}

function esc(string $v): string { return htmlspecialchars($v, ENT_QUOTES, "UTF-8"); }
$tokenCsrf = $usuarioController->gerarTokenCsrf();
$tituloVal = $_POST["titulo"]    ?? $item["titulo"];
$tipoVal   = $_POST["tipo"]      ?? $item["tipo"];
$descVal   = $_POST["descricao"] ?? $item["descricao"];
$anoVal    = $_POST["ano"]       ?? $item["ano"];
$notaVal   = $_POST["nota"]      ?? $item["nota"];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Editar — <?= esc($item["titulo"]) ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../templates/css/global.css">
</head>
<body>
<nav class="navbar-catalogo"><div class="container py-2 d-flex justify-content-between align-items-center flex-wrap gap-3">
<a href="../index.php" class="brand d-flex align-items-center gap-2 text-decoration-none"><span class="logo-circle" style="width:38px;height:38px;font-size:1.1rem"><i class="bi bi-collection-play"></i></span>CATÁLOGO</a>
<div class="d-flex gap-2 flex-wrap"><a href="catalog.php" class="btn btn-contorno btn-sm">Catálogo</a><a href="meus-itens.php" class="btn btn-contorno btn-sm">Meus itens</a><a href="logout.php" class="btn btn-contorno btn-sm">Sair</a></div>
</div></nav>
<main class="container py-4" style="max-width:720px">
<a href="item.php?id=<?= (int)$item["id"] ?>" class="btn btn-contorno btn-sm mb-3"><i class="bi bi-arrow-left me-1"></i>Voltar</a>
<div class="form-catalogo">
<h1 class="h4 fw-bold mb-1">Editar item</h1>
<p class="text-muted small mb-4">Atualize os dados do seu item.</p>
<?php if($mensagemErro): ?><div class="alerta toast-erro border mb-3"><?= esc($mensagemErro) ?></div><?php endif; ?>
<?php if(!empty($item["imagem"])): ?><div class="mb-3"><small class="text-muted">Capa atual:</small><br><img src="../storage/uploads/capas/<?= esc($item["imagem"]) ?>" alt="capa atual" style="max-width:160px;border-radius:12px;border:1px solid #e5e7eb;margin-top:.4rem"></div><?php endif; ?>
<form method="POST" action="edit.php?id=<?= (int)$item["id"] ?>" enctype="multipart/form-data" novalidate>
<input type="hidden" name="token_csrf" value="<?= esc($tokenCsrf) ?>">
<div class="mb-3"><label for="titulo" class="form-label">Título *</label><input type="text" name="titulo" id="titulo" class="form-control" required maxlength="190" value="<?= esc((string)$tituloVal) ?>"></div>
<div class="row g-3 mb-3">
<div class="col-md-6"><label for="tipo" class="form-label">Tipo *</label><select name="tipo" id="tipo" class="form-select" required><option value="livro" <?= $tipoVal==="livro"?"selected":"" ?>>📚 Livro</option><option value="filme" <?= $tipoVal==="filme"?"selected":"" ?>>🎬 Filme</option><option value="jogo"  <?= $tipoVal==="jogo"?"selected":"" ?>>🎮 Jogo</option></select></div>
<div class="col-md-3"><label for="ano" class="form-label">Ano *</label><input type="number" name="ano" id="ano" class="form-control" required min="1800" max="<?= (int)date("Y")+1 ?>" value="<?= esc((string)$anoVal) ?>"></div>
<div class="col-md-3"><label for="nota" class="form-label">Nota *</label><input type="number" name="nota" id="nota" class="form-control" required min="0" max="10" step="0.1" value="<?= esc((string)$notaVal) ?>"></div>
</div>
<div class="mb-3"><label for="descricao" class="form-label">Descrição *</label><textarea name="descricao" id="descricao" class="form-control" rows="4" required><?= esc((string)$descVal) ?></textarea></div>
<div class="mb-4"><label for="imagem" class="form-label">Nova capa (opcional — deixe em branco para manter a atual)</label><input type="file" name="imagem" id="imagem" class="form-control" accept="image/jpeg,image/png,image/webp"></div>
<div class="d-flex gap-2"><button type="submit" class="btn btn-primario px-4">Salvar alterações</button><a href="item.php?id=<?= (int)$item["id"] ?>" class="btn btn-contorno">Cancelar</a></div>
</form>
</div>
</main>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
