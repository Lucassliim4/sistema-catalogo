<?php

namespace Model;

use PDO;
use PDOException;

/**
 * Model responsável por todas as operações
 * da tabela "usuarios" no banco de dados.
 */
class Usuario
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Conexao::obterInstancia();
    }

    /**
     * Cadastra um novo usuário no banco de dados.
     */
    public function cadastrarUsuario(
        string $nome,
        string $email,
        string $senhaHash
    ): bool {
        try {
            $sql = "INSERT INTO usuarios (nome, email, senha, criado_em)
                    VALUES (:nome, :email, :senha, NOW())";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(":nome",  $nome,      PDO::PARAM_STR);
            $stmt->bindValue(":email", $email,     PDO::PARAM_STR);
            $stmt->bindValue(":senha", $senhaHash, PDO::PARAM_STR);

            return $stmt->execute();
        } catch (PDOException $erro) {
            error_log("Erro ao cadastrar usuário: " . $erro->getMessage());
            return false;
        }
    }

    /**
     * Busca um usuário pelo e-mail.
     *
     * @return array|false
     */
    public function buscarPorEmail(string $email): array|false
    {
        try {
            $sql = "SELECT id, nome, email, senha, criado_em
                    FROM usuarios
                    WHERE email = :email
                    LIMIT 1";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(":email", $email, PDO::PARAM_STR);
            $stmt->execute();

            return $stmt->fetch();
        } catch (PDOException $erro) {
            error_log("Erro ao buscar usuário: " . $erro->getMessage());
            return false;
        }
    }

    /**
     * Busca dados públicos de um usuário pelo id.
     *
     * @return array|false
     */
    public function buscarPorId(int $idUsuario): array|false
    {
        try {
            $sql = "SELECT id, nome, email, criado_em
                    FROM usuarios
                    WHERE id = :id
                    LIMIT 1";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(":id", $idUsuario, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetch();
        } catch (PDOException $erro) {
            error_log("Erro ao buscar usuário por id: " . $erro->getMessage());
            return false;
        }
    }
}
