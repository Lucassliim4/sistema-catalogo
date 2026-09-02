<?php

namespace Controller;

use Model\Item;

/**
 * Controller responsável pelas regras
 * de negócio dos itens do catálogo.
 */
class ItemController
{
    private Item $itemModel;

    public function __construct()
    {
        $this->itemModel = new Item();
    }

    /**
     * Processa o cadastro de um novo item.
     *
     * @return array{ok: bool, mensagem: string}
     */
    public function cadastrarItem(
        int $idUsuario,
        array $dados,
        array $arquivo
    ): array {
        $titulo    = trim($dados["titulo"]    ?? "");
        $tipo      = trim($dados["tipo"]      ?? "");
        $descricao = trim($dados["descricao"] ?? "");
        $ano       = (int) ($dados["ano"]     ?? 0);
        $nota      = (float) ($dados["nota"]  ?? 0);

        if ($titulo === "" || $descricao === "" || $tipo === "") {
            return ["ok" => false, "mensagem" => "Preencha todos os campos obrigatórios."];
        }

        if (!in_array($tipo, Item::TIPOS_PERMITIDOS, true)) {
            return ["ok" => false, "mensagem" => "Tipo de item inválido."];
        }

        if ($ano < 1800 || $ano > (int) date("Y") + 1) {
            return ["ok" => false, "mensagem" => "Informe um ano válido."];
        }

        if ($nota < 0 || $nota > 10) {
            return ["ok" => false, "mensagem" => "A nota deve estar entre 0 e 10."];
        }

        $nomeImagem = $this->processarImagem($arquivo);

        if ($this->getUltimoErroImagem()) {
            return ["ok" => false, "mensagem" => $this->getUltimoErroImagem()];
        }

        $salvou = $this->itemModel->cadastrarItem(
            $idUsuario,
            $titulo,
            $tipo,
            $descricao,
            $ano,
            $nota,
            $nomeImagem
        );

        if (!$salvou) {
            return ["ok" => false, "mensagem" => "Erro ao salvar o item. Tente novamente."];
        }

        return ["ok" => true, "mensagem" => "Item cadastrado com sucesso!"];
    }

    /**
     * Processa a edição de um item existente.
     *
     * @return array{ok: bool, mensagem: string}
     */
    public function editarItem(
        int $idItem,
        int $idUsuario,
        array $dados,
        array $arquivo
    ): array {
        $item = $this->itemModel->buscarPorId($idItem);

        if (!$item) {
            return ["ok" => false, "mensagem" => "Item não encontrado."];
        }

        if ((int) $item["usuario_id"] !== $idUsuario) {
            return ["ok" => false, "mensagem" => "Você não tem permissão para editar este item."];
        }

        $titulo    = trim($dados["titulo"]    ?? "");
        $tipo      = trim($dados["tipo"]      ?? "");
        $descricao = trim($dados["descricao"] ?? "");
        $ano       = (int) ($dados["ano"]     ?? 0);
        $nota      = (float) ($dados["nota"]  ?? 0);

        if ($titulo === "" || $descricao === "" || $tipo === "") {
            return ["ok" => false, "mensagem" => "Preencha todos os campos obrigatórios."];
        }

        if (!in_array($tipo, Item::TIPOS_PERMITIDOS, true)) {
            return ["ok" => false, "mensagem" => "Tipo de item inválido."];
        }

        if ($ano < 1800 || $ano > (int) date("Y") + 1) {
            return ["ok" => false, "mensagem" => "Informe um ano válido."];
        }

        if ($nota < 0 || $nota > 10) {
            return ["ok" => false, "mensagem" => "A nota deve estar entre 0 e 10."];
        }

        $manterImagem = true;
        $nomeImagem   = $item["imagem"];

        if (!empty($arquivo["name"]) && $arquivo["error"] !== UPLOAD_ERR_NO_FILE) {
            $novaImagem = $this->processarImagem($arquivo);

            if ($this->getUltimoErroImagem()) {
                return ["ok" => false, "mensagem" => $this->getUltimoErroImagem()];
            }

            if ($novaImagem !== null) {
                $manterImagem = false;
                $nomeImagem   = $novaImagem;

                $this->removerImagemAntiga($item["imagem"]);
            }
        }

        $atualizou = $this->itemModel->atualizarItem(
            $idItem,
            $idUsuario,
            $titulo,
            $tipo,
            $descricao,
            $ano,
            $nota,
            $nomeImagem,
            $manterImagem
        );

        if (!$atualizou) {
            return ["ok" => false, "mensagem" => "Erro ao atualizar o item."];
        }

        return ["ok" => true, "mensagem" => "Item atualizado com sucesso!"];
    }

    /**
     * Exclui um item.
     *
     * @return array{ok: bool, mensagem: string}
     */
    public function excluirItem(int $idItem, int $idUsuario): array
    {
        $item = $this->itemModel->buscarPorId($idItem);

        if (!$item) {
            return ["ok" => false, "mensagem" => "Item não encontrado."];
        }

        if ((int) $item["usuario_id"] !== $idUsuario) {
            return ["ok" => false, "mensagem" => "Você não tem permissão para excluir este item."];
        }

        $excluiu = $this->itemModel->excluirItem($idItem, $idUsuario);

        if (!$excluiu) {
            return ["ok" => false, "mensagem" => "Erro ao excluir o item."];
        }

        $this->removerImagemAntiga($item["imagem"]);

        return ["ok" => true, "mensagem" => "Item excluído com sucesso!"];
    }

    /**
     * Lista itens com filtros.
     *
     * @return array
     */
    public function listarItens(?string $tipo, ?string $busca): array
    {
        return $this->itemModel->listarItens($tipo, $busca);
    }

    /**
     * Lista itens recentes (limite).
     */
    public function listarRecentes(int $limite = 6): array
    {
        return $this->itemModel->listarItens(null, null, null, $limite);
    }

    /**
     * Lista itens do usuário logado.
     */
    public function listarItensDoUsuario(int $idUsuario): array
    {
        return $this->itemModel->listarItens(null, null, $idUsuario);
    }

    /**
     * Busca um item pelo id.
     */
    public function buscarPorId(int $idItem): array|false
    {
        return $this->itemModel->buscarPorId($idItem);
    }

    private ?string $ultimoErroImagem = null;

    private function processarImagem(array $arquivo): ?string
    {
        $this->ultimoErroImagem = null;

        if (empty($arquivo["name"]) || ($arquivo["error"] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        if ($arquivo["error"] !== UPLOAD_ERR_OK) {
            $this->ultimoErroImagem = "Erro ao enviar a imagem.";
            return null;
        }

        if ($arquivo["size"] > 5 * 1024 * 1024) {
            $this->ultimoErroImagem = "A imagem deve ter no máximo 5 MB.";
            return null;
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime  = $finfo->file($arquivo["tmp_name"]);

        $permitidos = [
            "image/jpeg" => "jpg",
            "image/png"  => "png",
            "image/webp" => "webp",
        ];

        if (!array_key_exists($mime, $permitidos)) {
            $this->ultimoErroImagem = "Formato de imagem inválido (use JPG, PNG ou WEBP).";
            return null;
        }

        if (!getimagesize($arquivo["tmp_name"])) {
            $this->ultimoErroImagem = "O arquivo enviado não é uma imagem válida.";
            return null;
        }

        $extensao    = $permitidos[$mime];
        $novoNome    = uniqid("capa_", true) . "." . $extensao;
        $diretorio   = __DIR__ . "/../storage/uploads/capas/";

        if (!is_dir($diretorio)) {
            mkdir($diretorio, 0755, true);
        }

        if (!move_uploaded_file($arquivo["tmp_name"], $diretorio . $novoNome)) {
            $this->ultimoErroImagem = "Não foi possível salvar a imagem.";
            return null;
        }

        return $novoNome;
    }

    private function getUltimoErroImagem(): ?string
    {
        return $this->ultimoErroImagem;
    }

    private function removerImagemAntiga(?string $nomeImagem): void
    {
        if (empty($nomeImagem)) {
            return;
        }

        $caminho = __DIR__ . "/../storage/uploads/capas/" . $nomeImagem;

        if (is_file($caminho)) {
            @unlink($caminho);
        }
    }
}
