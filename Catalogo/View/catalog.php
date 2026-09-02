<?php
session_start();
require_once __DIR__ . "/../vendor/autoload.php";

use Controller\ItemController;
use Controller\UsuarioController;

$usuarioController = new UsuarioController();
$itemController    = new ItemController();

$estaLogado = $usuarioController->estaLogado();
$tipo  = isset($_GET["tipo"])  ? trim($_GET["tipo"])  : null;
$busca = isset($_GET["busca"]) ? trim($_GET["busca"]) : null;

$tiposValidos = ["livro","filme","jogo"];
if ($tipo !== null && $tipo !== "" && !in_array($tipo, $tiposValidos, true)) {
    $tipo = null;
}
if ($tipo === "") $tipo = null;
if ($busca === "") $busca = null;

$itens = $itemController->listarItens($tipo, $busca);

function esc(string $v): string { return htmlspecialchars($v, ENT_QUOTES, "UTF-8"); }
function iconeTipo(string $t): string { return match($t){"livro"=>"bi-book","filme"=>"bi-film","jogo"=>"bi-controller",default=>"bi-tag"}; }
function classeBadge(string $t): string { return match($t){"livro"=>"badge-livro","filme"=>"badge-filme","jogo"=>"badge-jogo",default=>"badge-livro"}; }
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catálogo — Livros, Filmes e Jogos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../templates/css/global.css">
</head>
<body>

<nav class="navbar-catalogo">
    <div class="container py-2 d-flex align-items-center justify-content-between gap-3 flex-wrap">
        <a href="../index.php" class="brand d-flex align-items-center gap-2 text-decoration-none">
            <span class="logo-circle" style="width:38px;height:38px;font-size:1.1rem"><i class="bi bi-collection-play"></i></span>
            CATÁLOGO
        </a>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <a href="../index.php" class="btn btn-contorno btn-sm">Início</a>
            <a href="catalog.php" class="btn btn-primario btn-sm"><i class="bi bi-grid me-1"></i> Catálogo</a>
            <?php if ($estaLogado): ?>
                <a href="meus-itens.php" class="btn btn-contorno btn-sm">Meus itens</a>
                <a href="create.php" class="btn btn-contorno btn-sm"><i class="bi bi-plus-lg me-1"></i> Cadastrar</a>
                <a href="logout.php" class="btn btn-contorno btn-sm">Sair</a>
            <?php else: ?>
                <a href="login.php" class="btn btn-contorno btn-sm">Entrar</a>
                <a href="register.php" class="btn btn-primario btn-sm">Criar conta</a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<main class="container py-4">

    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
        <div>
            <h1 class="h4 fw-bold mb-1">Catálogo</h1>
            <p class="text-muted small mb-0">Explore livros, filmes e jogos cadastrados pela comunidade.</p>
        </div>
        <?php if ($estaLogado): ?>
            <a href="create.php" class="btn btn-primario"><i class="bi bi-plus-lg me-1"></i> Cadastrar item</a>
        <?php endif; ?>
    </div>

    <form method="GET" action="catalog.php" class="mb-4">
        <div class="row g-2">
            <div class="col-md-6 busca-wrap">
                <i class="bi bi-search"></i>
                <input type="text" name="busca" value="<?= esc($busca ?? "") ?>" class="form-control busca-input" placeholder="Buscar por título...">
            </div>
            <div class="col-md-3">
                <select name="tipo" class="form-select" style="border-radius:999px">
                    <option value="">Todos os tipos</option>
                    <option value="livro" <?= $tipo==="livro"?"selected":"" ?>>📚 Livros</option>
                    <option value="filme" <?= $tipo==="filme"?"selected":"" ?>>🎬 Filmes</option>
                    <option value="jogo"  <?= $tipo==="jogo" ?"selected":"" ?>>🎮 Jogos</option>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primario flex-grow-1">Buscar</button>
                <?php if ($tipo || $busca): ?>
                    <a href="catalog.php" class="btn btn-contorno">Limpar</a>
                <?php endif; ?>
            </div>
        </div>
        <div class="filtros d-flex gap-2 mt-3 flex-wrap">
            <a href="catalog.php<?= $busca ? '?busca='.urlencode($busca) : '' ?>" class="btn btn-sm <?= $tipo===null?'active btn-dark':'btn-outline-secondary' ?>">Todos</a>
            <a href="catalog.php?tipo=livro<?= $busca ? '&busca='.urlencode($busca) : '' ?>" class="btn btn-sm <?= $tipo==='livro'?'active btn-dark':'btn-outline-secondary' ?>">📚 Livros</a>
            <a href="catalog.php?tipo=filme<?= $busca ? '&busca='.urlencode($busca) : '' ?>" class="btn btn-sm <?= $tipo==='filme'?'active btn-dark':'btn-outline-secondary' ?>">🎬 Filmes</a>
            <a href="catalog.php?tipo=jogo<?= $busca ? '&busca='.urlencode($busca) : '' ?>" class="btn btn-sm <?= $tipo==='jogo'?'active btn-dark':'btn-outline-secondary' ?>">🎮 Jogos</a>
        </div>
    </form>

    <div class="d-flex align-items-center justify-content-between mb-3">
        <span class="text-muted small"><?= count($itens) ?> itens encontrados</span>
        <?php if ($tipo || $busca): ?>
            <span class="small">
                Filtros:
                <?php if ($tipo): ?><span class="badge bg-dark"><?= esc($tipo) ?></span><?php endif; ?>
                <?php if ($busca): ?><span class="badge bg-secondary">busca: <?= esc($busca) ?></span><?php endif; ?>
            </span>
        <?php endif; ?>
    </div>

    <?php if (empty($itens)): ?>
        <div class="info-box text-center py-5">
            <div class="fs-1 mb-2">🔍</div>
            <p class="text-muted mb-3">Nenhum item encontrado.</p>
            <?php if ($estaLogado): ?>
                <a href="create.php" class="btn btn-primario">Cadastrar um item</a>
            <?php else: ?>
                <a href="register.php" class="btn btn-primario">Crie sua conta</a>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="row g-3">
            <?php foreach ($itens as $item): ?>
                <div class="col-12 col-sm-6 col-lg-4">
                    <a href="item.php?id=<?= (int)$item["id"] ?>" class="text-decoration-none text-dark">
                        <div class="card-catalogo">
                            <div class="capa-wrap">
                                <?php if (!empty($item["imagem"])): ?>
                                    <img src="../storage/uploads/capas/<?= esc($item["imagem"]) ?>" alt="<?= esc($item["titulo"]) ?>">
                                <?php else: ?>
                                    <span class="capa-placeholder"><i class="bi <?= iconeTipo($item["tipo"]) ?>"></i></span>
                                <?php endif; ?>
                            </div>
                            <div class="card-body-catalogo">
                                <span class="badge-tipo <?= classeBadge($item["tipo"]) ?>"><?= esc($item["tipo"]) ?></span>
                                <h3 class="card-titulo"><?= esc($item["titulo"]) ?></h3>
                                <div class="meta mb-2">
                                    <span><i class="bi bi-calendar me-1"></i><?= (int)$item["ano"] ?></span>
                                    <span class="nota"><i class="bi bi-star-fill me-1"></i><?= number_format((float)$item["nota"],1,",",".") ?></span>
                                </div>
                                <p class="card-desc mb-0"><?= esc(mb_strimwidth($item["descricao"],0,110,"...")) ?></p>
                                <small class="text-muted mt-2 d-block"><i class="bi bi-person me-1"></i><?= esc($item["nome_usuario"]) ?> · <?= date("d/m/Y", strtotime($item["criado_em"])) ?></small>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</main>

<footer class="container py-4 mt-4 text-center text-muted small border-top">
    Catálogo — Projeto acadêmico • PHP 8.3 • PDO • MVC • POO
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
