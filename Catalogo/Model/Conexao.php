<?php

namespace Model;

use PDO;
use PDOException;

require_once __DIR__ . "/../Config/configuration.php";

/**
 * Classe responsável por estabelecer a conexão
 * com o banco de dados MySQL utilizando PDO.
 */
class Conexao
{
    private static ?PDO $instancia = null;

    /**
     * Retorna a instância única da conexão PDO.
     *
     * @return PDO
     */
    public static function obterInstancia(): PDO
    {
        if (self::$instancia === null) {
            try {
                $dsn = "mysql:host=" . DB_HOST
                     . ";port=" . DB_PORT
                     . ";dbname=" . DB_NAME
                     . ";charset=utf8mb4";

                self::$instancia = new PDO($dsn, DB_USER, DB_PASSWORD, [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]);
            } catch (PDOException $erro) {
                die("Erro ao conectar ao banco de dados: " . $erro->getMessage());
            }
        }

        return self::$instancia;
    }
}
