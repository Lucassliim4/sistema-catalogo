<?php

namespace Model;

use PDO;
use PDOException;

/**
 * Model responsável pelas operações da tabela "itens"
 * (livros, filmes e jogos) no banco de dados.
 */
class Item
{
    public const TIPO_LIVRO = "livro";
    public const TIPO_FILME = "filme";
    public const TIPO_JOGO  = "jogo";

    public const TIPOS_PERMITIDOS = [
        self::TIPO_LIVRO,
        self::TIPO_FILME,
        self::TIPO_JOGO,
    ];

    private PDO $db;

    public function __construct()
    {
        $this->db = Conexao::obterInstancia();
    }

    /**
     * Cadastra um novo item no catálogo.
     */
    public function cadastrarItem(
        int $idUsuario,
        string $titulo,
        string $tipo,
        string $descricao,
        int $ano,
        float $nota,
        ?string $nomeImagem
    ): bool {
        try {
            $sql = "INSERT INTO itens
                        (usuario_id, titulo, tipo, descricao, ano, nota, imagem, criado_em)
                    VALUES
                        (:usuario_id, :titulo, :tipo, :descricao, :ano, :nota, :imagem, NOW())";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(":usuario_id", $idUsuario, PDO::PARAM_INT);
            $stmt->bindValue(":titulo",     $titulo,     PDO::PARAM_STR);
            $stmt->bindValue(":tipo",       $tipo,       PDO::PARAM_STR);
            $stmt->bindValue(":descricao",  $descricao,  PDO::PARAM_STR);
            $stmt->bindValue(":ano",        $ano,        PDO::PARAM_INT);
            $stmt->bindValue(":nota",       $nota,       PDO::PARAM_STR);
            $stmt->bindValue(":imagem",     $nomeImagem, PDO::PARAM_STR);

            return $stmt->execute();
        } catch (PDOException $erro) {
            error_log("Erro ao cadastrar item: " . $erro->getMessage());
            return false;
        }
    }

    /**
     * Lista itens do catálogo com filtros opcionais
     * por tipo e busca por título.
     *
     * @param string|null $tipo  Filtra por tipo (livro/filme/jogo). Null = todos.
     * @param string|null $busca Filtra por trecho do título. Null = sem busca.
     * @return array
     */
    public function listarItens(
        ?string $tipo = null,
        ?string $busca = null,
        ?int $idUsuario = null,
        int $limite = 0
    ): array {
        try {
            $sql = "SELECT i.id,
                           i.titulo,
                           i.tipo,
                           i.descricao,
                           i.ano,
                           i.nota,
                           i.imagem,
                           i.criado_em,
                           i.usuario_id,
                           u.nome AS nome_usuario
                    FROM itens i
                    INNER JOIN usuarios u ON u.id = i.usuario_id
                    WHERE 1 = 1";

            $parametros = [];

            if ($tipo !== null && in_array($tipo, self::TIPOS_PERMITIDOS, true)) {
                $sql .= " AND i.tipo = :tipo";
                $parametros[":tipo"] = $tipo;
            }

            if ($idUsuario !== null) {
                $sql .= " AND i.usuario_id = :usuario_id";
                $parametros[":usuario_id"] = $idUsuario;
            }

            if ($busca !== null && trim($busca) !== "") {
                $sql .= " AND i.titulo LIKE :busca";
                $parametros[":busca"] = "%" . trim($busca) . "%";
            }

            $sql .= " ORDER BY i.criado_em DESC, i.id DESC";

            if ($limite > 0) {
                $sql .= " LIMIT " . (int) $limite;
            }

            $stmt = $this->db->prepare($sql);

            foreach ($parametros as $chave => $valor) {
                $tipoPDO = is_int($valor) ? PDO::PARAM_INT : PDO::PARAM_STR;
                $stmt->bindValue($chave, $valor, $tipoPDO);
            }

            $stmt->execute();

            return $stmt->fetchAll();
        } catch (PDOException $erro) {
            error_log("Erro ao listar itens: " . $erro->getMessage());
            return [];
        }
    }

    /**
     * Busca um único item pelo id.
     *
     * @return array|false
     */
    public function buscarPorId(int $idItem): array|false
    {
        try {
            $sql = "SELECT i.id,
                           i.titulo,
                           i.tipo,
                           i.descricao,
                           i.ano,
                           i.nota,
                           i.imagem,
                           i.criado_em,
                           i.usuario_id,
                           u.nome AS nome_usuario
                    FROM itens i
                    INNER JOIN usuarios u ON u.id = i.usuario_id
                    WHERE i.id = :id
                    LIMIT 1";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(":id", $idItem, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetch();
        } catch (PDOException $erro) {
            error_log("Erro ao buscar item: " . $erro->getMessage());
            return false;
        }
    }

    /**
     * Atualiza um item. Apenas o dono pode atualizar.
     */
    public function atualizarItem(
        int $idItem,
        int $idUsuario,
        string $titulo,
        string $tipo,
        string $descricao,
        int $ano,
        float $nota,
        ?string $nomeImagem,
        bool $manterImagem = true
    ): bool {
        try {
            $item = $this->buscarPorId($idItem);

            if (!$item || (int) $item["usuario_id"] !== $idUsuario) {
                return false;
            }

            if (!$manterImagem) {
                $sql = "UPDATE itens
                        SET titulo = :titulo,
                            tipo = :tipo,
                            descricao = :descricao,
                            ano = :ano,
                            nota = :nota,
                            imagem = :imagem
                        WHERE id = :id AND usuario_id = :usuario_id";
            } else {
                $sql = "UPDATE itens
                        SET titulo = :titulo,
                            tipo = :tipo,
                            descricao = :descricao,
                            ano = :ano,
                            nota = :nota
                        WHERE id = :id AND usuario_id = :usuario_id";
            }

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(":titulo",      $titulo,    PDO::PARAM_STR);
            $stmt->bindValue(":tipo",        $tipo,      PDO::PARAM_STR);
            $stmt->bindValue(":descricao",   $descricao, PDO::PARAM_STR);
            $stmt->bindValue(":ano",         $ano,       PDO::PARAM_INT);
            $stmt->bindValue(":nota",        $nota,      PDO::PARAM_STR);
            $stmt->bindValue(":id",          $idItem,    PDO::PARAM_INT);
            $stmt->bindValue(":usuario_id",  $idUsuario, PDO::PARAM_INT);

            if (!$manterImagem) {
                $stmt->bindValue(":imagem", $nomeImagem, PDO::PARAM_STR);
            }

            return $stmt->execute();
        } catch (PDOException $erro) {
            error_log("Erro ao atualizar item: " . $erro->getMessage());
            return false;
        }
    }

    /**
     * Exclui um item. Apenas o dono pode excluir.
     */
    public function excluirItem(int $idItem, int $idUsuario): bool
    {
        try {
            $sql = "DELETE FROM itens
                    WHERE id = :id AND usuario_id = :usuario_id";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(":id",         $idItem,    PDO::PARAM_INT);
            $stmt->bindValue(":usuario_id", $idUsuario, PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $erro) {
            error_log("Erro ao excluir item: " . $erro->getMessage());
            return false;
        }
    }
}
