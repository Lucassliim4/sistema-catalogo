<?php
session_start();
require_once __DIR__ . "/vendor/autoload.php";

use Controller\ItemController;
use Controller\UsuarioController;

$usuarioController = new UsuarioController();
$itemController    = new ItemController();

$estaLogado  = $usuarioController->estaLogado();
$nomeUsuario = $estaLogado ? $usuarioController->obterNomeUsuarioLogado() : "";

$tipo  = isset($_GET["tipo"])  ? trim($_GET["tipo"])  : null;
$busca = isset($_GET["busca"]) ? trim($_GET["busca"]) : null;

$tiposValidos = ["livro", "filme", "jogo"];
if ($tipo !== null && !in_array($tipo, $tiposValidos, true)) {
    $tipo = null;
}

$itensRecentes = $itemController->listarRecentes(6);
$itensFiltrados = null;

if ($tipo !== null || ($busca !== null && $busca !== "")) {
    $itensFiltrados = $itemController->listarItens($tipo, $busca);
}

function escapar(string $valor): string {
    return htmlspecialchars($valor, ENT_QUOTES, "UTF-8");
}

function iconeTipo(string $tipo): string {
    return match ($tipo) {
        "livro" => "bi-book",
        "filme" => "bi-film",
        "jogo"  => "bi-controller",
        default => "bi-tag"
    };
}

function classeBadge(string $tipo): string {
    return match ($tipo) {
        "livro" => "badge-livro",
        "filme" => "badge-filme",
        "jogo"  => "badge-jogo",
        default => "badge-livro"
    };
}
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
    <link rel="stylesheet" href="templates/css/global.css">
</head>
<body>

<nav class="navbar-catalogo">
    <div class="container py-2 d-flex align-items-center justify-content-between gap-3 flex-wrap">
        <a href="index.php" class="brand d-flex align-items-center gap-2 text-decoration-none">
            <span class="logo-circle" style="width:38px;height:38px;font-size:1.1rem"><i class="bi bi-collection-play"></i></span>
            CATÁLOGO
        </a>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <a href="View/catalog.php" class="btn btn-contorno btn-sm"><i class="bi bi-grid me-1"></i> Catálogo</a>
            <?php if ($estaLogado): ?>
                <a href="View/meus-itens.php" class="btn btn-contorno btn-sm"><i class="bi bi-person me-1"></i> Meus itens</a>
                <a href="View/create.php" class="btn btn-primario btn-sm"><i class="bi bi-plus-lg me-1"></i> Cadastrar</a>
                <a href="View/logout.php" class="btn btn-contorno btn-sm">Sair</a>
            <?php else: ?>
                <a href="View/login.php" class="btn btn-contorno btn-sm">Entrar</a>
                <a href="View/register.php" class="btn btn-primario btn-sm">Criar conta</a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<main class="container py-4">

    <?php if ($estaLogado): ?>
        <div class="alert alerta toast-sucesso border d-flex align-items-center gap-2 mb-3" role="alert">
            <i class="bi bi-emoji-smile"></i> Olá, <strong><?= escapar($nomeUsuario) ?></strong>! Bem-vindo de volta.
        </div>
    <?php endif; ?>

    <section class="hero mb-4">
        <div style="position:relative;z-index:1">
            <span class="badge bg-white text-dark rounded-pill px-3 py-2 mb-3" style="font-weight:700;letter-spacing:.04em">
                <i class="bi bi-stars me-1"></i> LIVROS · FILMES · JOGOS
            </span>
            <h1 class="display-5 mb-3">Seu catálogo de<br>cultura pop</h1>
            <p class="lead mb-4">Crie sua conta, faça login e compartilhe seus livros, filmes e jogos favoritos com a comunidade. Tudo salvo de verdade no MySQL.</p>
            <div class="d-flex gap-2 flex-wrap">
                <?php if ($estaLogado): ?>
                    <a href="View/catalog.php" class="btn btn-light rounded-pill px-4 py-2 fw-semibold">Explorar catálogo</a>
                    <a href="View/create.php" class="btn btn-outline-light rounded-pill px-4 py-2 fw-semibold">Cadastrar item</a>
                <?php else: ?>
                    <a href="View/register.php" class="btn btn-light rounded-pill px-4 py-2 fw-semibold">Criar conta grátis</a>
                    <a href="View/catalog.php" class="btn btn-outline-light rounded-pill px-4 py-2 fw-semibold">Ver catálogo</a>
                <?php endif; ?>
            </div>
            <div class="d-flex gap-4 mt-4 flex-wrap" style="opacity:.92">
                <span><i class="bi bi-book me-1"></i> Livros</span>
                <span><i class="bi bi-film me-1"></i> Filmes</span>
                <span><i class="bi bi-controller me-1"></i> Jogos</span>
            </div>
        </div>
    </section>

    <section class="mb-4">
        <form method="GET" action="index.php" class="row g-2 align-items-end">
            <div class="col-md-5 busca-wrap">
                <i class="bi bi-search"></i>
                <input type="text" name="busca" value="<?= escapar($busca ?? "") ?>" class="form-control busca-input" placeholder="Buscar por título...">
            </div>
            <div class="col-md-3">
                <select name="tipo" class="form-select" style="border-radius:999px">
                    <option value="">Todos os tipos</option>
                    <option value="livro" <?= $tipo==="livro"?"selected":"" ?>>📚 Livros</option>
                    <option value="filme" <?= $tipo==="filme"?"selected":"" ?>>🎬 Filmes</option>
                    <option value="jogo"  <?= $tipo==="jogo" ?"selected":"" ?>>🎮 Jogos</option>
                </select>
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-primario flex-grow-1">Buscar</button>
                <?php if ($tipo || $busca): ?>
                    <a href="index.php" class="btn btn-contorno">Limpar</a>
                <?php endif; ?>
            </div>
        </form>
    </section>

    <?php if ($itensFiltrados !== null): ?>
        <section class="mb-5">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h2 class="h5 fw-bold m-0">Resultados da busca</h2>
                <span class="text-muted small"><?= count($itensFiltrados) ?> itens encontrados</span>
            </div>
            <?php if (empty($itensFiltrados)): ?>
                <div class="info-box text-center text-muted py-4">Nenhum item encontrado para sua busca.</div>
            <?php else: ?>
                <div class="row g-3">
                    <?php foreach ($itensFiltrados as $item): ?>
                        <div class="col-12 col-sm-6 col-lg-4">
                            <a href="View/item.php?id=<?= (int)$item["id"] ?>" class="text-decoration-none text-dark">
                                <div class="card-catalogo">
                                    <div class="capa-wrap">
                                        <?php if (!empty($item["imagem"])): ?>
                                            <img src="storage/uploads/capas/<?= escapar($item["imagem"]) ?>" alt="<?= escapar($item["titulo"]) ?>">
                                        <?php else: ?>
                                            <span class="capa-placeholder"><i class="bi <?= iconeTipo($item["tipo"]) ?>"></i></span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="card-body-catalogo">
                                        <span class="badge-tipo <?= classeBadge($item["tipo"]) ?>"><?= escapar($item["tipo"]) ?></span>
                                        <h3 class="card-titulo"><?= escapar($item["titulo"]) ?></h3>
                                        <div class="meta mb-2">
                                            <span><i class="bi bi-calendar me-1"></i><?= (int)$item["ano"] ?></span>
                                            <span class="nota"><i class="bi bi-star-fill me-1"></i><?= number_format((float)$item["nota"],1,",",".") ?></span>
                                        </div>
                                        <p class="card-desc mb-0"><?= escapar(mb_strimwidth($item["descricao"],0,110,"...")) ?></p>
                                        <small class="text-muted mt-2 d-block"><i class="bi bi-person me-1"></i><?= escapar($item["nome_usuario"]) ?></small>
                                    </div>
                                </div>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            <hr class="my-4">
        </section>
    <?php endif; ?>

    <section>
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h2 class="h5 fw-bold m-0"><i class="bi bi-fire me-2 text-warning"></i>Itens recentes</h2>
            <a href="View/catalog.php" class="btn btn-contorno btn-sm">Ver tudo</a>
        </div>

        <?php if (empty($itensRecentes)): ?>
            <div class="info-box text-center py-5">
                <p class="text-muted mb-3">Nenhum item cadastrado ainda.</p>
                <?php if ($estaLogado): ?>
                    <a href="View/create.php" class="btn btn-primario">Cadastrar primeiro item</a>
                <?php else: ?>
                    <a href="View/register.php" class="btn btn-primario">Crie sua conta e cadastre</a>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="row g-3">
                <?php foreach ($itensRecentes as $item): ?>
                    <div class="col-12 col-sm-6 col-lg-4">
                        <a href="View/item.php?id=<?= (int)$item["id"] ?>" class="text-decoration-none text-dark">
                            <div class="card-catalogo">
                                <div class="capa-wrap">
                                    <?php if (!empty($item["imagem"])): ?>
                                        <img src="storage/uploads/capas/<?= escapar($item["imagem"]) ?>" alt="<?= escapar($item["titulo"]) ?>">
                                    <?php else: ?>
                                        <span class="capa-placeholder"><i class="bi <?= iconeTipo($item["tipo"]) ?>"></i></span>
                                    <?php endif; ?>
                                </div>
                                <div class="card-body-catalogo">
                                    <span class="badge-tipo <?= classeBadge($item["tipo"]) ?>"><?= escapar($item["tipo"]) ?></span>
                                    <h3 class="card-titulo"><?= escapar($item["titulo"]) ?></h3>
                                    <div class="meta mb-2">
                                        <span><i class="bi bi-calendar me-1"></i><?= (int)$item["ano"] ?></span>
                                        <span class="nota"><i class="bi bi-star-fill me-1"></i><?= number_format((float)$item["nota"],1,",",".") ?></span>
                                    </div>
                                    <p class="card-desc mb-0"><?= escapar(mb_strimwidth($item["descricao"],0,110,"...")) ?></p>
                                    <small class="text-muted mt-2 d-block"><i class="bi bi-person me-1"></i><?= escapar($item["nome_usuario"]) ?></small>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>

    <section class="row g-3 mt-4">
        <div class="col-md-4">
            <div class="info-box h-100 text-center">
                <div class="fs-2 mb-2">📚</div>
                <h3 class="h6 fw-bold">Livros</h3>
                <p class="text-muted small mb-0">Romances, clássicos e lançamentos que marcaram sua leitura.</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="info-box h-100 text-center">
                <div class="fs-2 mb-2">🎬</div>
                <h3 class="h6 fw-bold">Filmes</h3>
                <p class="text-muted small mb-0">Do cinema clássico aos blockbusters. Avalie e compartilhe.</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="info-box h-100 text-center">
                <div class="fs-2 mb-2">🎮</div>
                <h3 class="h6 fw-bold">Jogos</h3>
                <p class="text-muted small mb-0">Seus jogos favoritos com nota, ano e capa.</p>
            </div>
        </div>
    </section>

</main>

<footer class="container py-4 mt-4 text-center text-muted small border-top">
    Catálogo — Projeto acadêmico • PHP 8.3 • PDO • MVC • POO &nbsp;|&nbsp; Feito com ♥
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
