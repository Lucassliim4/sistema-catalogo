<?php

namespace Controller;

use Model\Usuario;

/**
 * Controller responsável pelas regras
 * de negócio do usuário (cadastro, login, sessão).
 */
class UsuarioController
{
    private Usuario $usuarioModel;

    public function __construct()
    {
        $this->usuarioModel = new Usuario();
    }

    /**
     * Verifica se o usuário está autenticado.
     */
    public function estaLogado(): bool
    {
        return isset($_SESSION["id_usuario"]);
    }

    /**
     * Bloqueia páginas que exigem login.
     */
    public function exigirLogin(): void
    {
        if (!$this->estaLogado()) {
            header("Location: " . BASE_URL . "/index.php");
            exit();
        }
    }

    /**
     * Cadastra um novo usuário após validar os dados.
     *
     * @return array{ok: bool, mensagem: string}
     */
    public function cadastrarUsuario(
        string $nome,
        string $email,
        string $senha,
        string $confirmarSenha
    ): array {
        $nome  = trim($nome);
        $email = trim($email);

        if ($nome === "" || $email === "" || $senha === "") {
            return ["ok" => false, "mensagem" => "Preencha todos os campos."];
        }

        if (!$this->validarEmail($email)) {
            return ["ok" => false, "mensagem" => "E-mail inválido."];
        }

        if (!$this->validarSenha($senha)) {
            return [
                "ok" => false,
                "mensagem" => "A senha deve ter no mínimo 6 caracteres."
            ];
        }

        if ($senha !== $confirmarSenha) {
            return ["ok" => false, "mensagem" => "As senhas não coincidem."];
        }

        if ($this->usuarioModel->buscarPorEmail($email)) {
            return [
                "ok" => false,
                "mensagem" => "Já existe uma conta cadastrada com este e-mail."
            ];
        }

        $senhaHash = $this->gerarHashSenha($senha);

        $cadastrou = $this->usuarioModel->cadastrarUsuario($nome, $email, $senhaHash);

        if (!$cadastrou) {
            return ["ok" => false, "mensagem" => "Erro ao cadastrar. Tente novamente."];
        }

        return ["ok" => true, "mensagem" => "Conta criada com sucesso!"];
    }

    /**
     * Realiza o login do usuário.
     *
     * @return array{ok: bool, mensagem: string}
     */
    public function login(string $email, string $senha): array
    {
        $email = trim($email);

        if ($email === "" || $senha === "") {
            return ["ok" => false, "mensagem" => "Informe e-mail e senha."];
        }

        $usuario = $this->usuarioModel->buscarPorEmail($email);

        if (!$usuario || !password_verify($senha, $usuario["senha"])) {
            return ["ok" => false, "mensagem" => "E-mail ou senha inválidos."];
        }

        $_SESSION["id_usuario"] = (int) $usuario["id"];
        $_SESSION["nome"]       = $usuario["nome"];
        $_SESSION["email"]      = $usuario["email"];

        return ["ok" => true, "mensagem" => "Login realizado com sucesso."];
    }

    /**
     * Encerra a sessão do usuário.
     */
    public function logout(): void
    {
        $_SESSION = [];

        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                "",
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }

        session_destroy();
    }

    /**
     * Retorna o id do usuário logado.
     */
    public function obterIdUsuarioLogado(): ?int
    {
        return $this->estaLogado() ? (int) $_SESSION["id_usuario"] : null;
    }

    /**
     * Retorna o nome do usuário logado.
     */
    public function obterNomeUsuarioLogado(): string
    {
        return $_SESSION["nome"] ?? "Visitante";
    }

    /**
     * Gera o token CSRF da sessão.
     */
    public function gerarTokenCsrf(): string
    {
        if (empty($_SESSION["token_csrf"])) {
            $_SESSION["token_csrf"] = bin2hex(random_bytes(32));
        }

        return $_SESSION["token_csrf"];
    }

    /**
     * Valida o token CSRF recebido por POST.
     */
    public function validarTokenCsrf(?string $token): bool
    {
        return !empty($_SESSION["token_csrf"])
            && !empty($token)
            && hash_equals($_SESSION["token_csrf"], $token);
    }

    private function validarEmail(string $email): bool
    {
        return (bool) filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    private function validarSenha(string $senha): bool
    {
        return strlen($senha) >= 6;
    }

    private function gerarHashSenha(string $senha): string
    {
        return password_hash($senha, PASSWORD_DEFAULT);
    }
}
