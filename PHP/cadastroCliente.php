<?php
require_once __DIR__ . "/conexao.php";

function redirecWith($url, $params = []) {
    if (!empty($params)) {
        $qs  = http_build_query($params);
        $sep = (strpos($url, '?') === false) ? '?' : '&';
        $url .= $sep . $qs;
    }
    header("Location: $url");
    exit;
}

try {
    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        redirecWith("../paginas/telacadastro.html", ["erro" => "Método inválido"]);
    }

    // Campos vindos do form
    $nome     = trim($_POST["nome"] ?? "");
    $email    = trim($_POST["email"] ?? "");
    $senha    = $_POST["senha"] ?? "";
    $telefone = $_POST["telefone"] ?? "";
    $cpf      = $_POST["cpf"] ?? "";
    $confirma = $_POST["confirma-senha"] ?? ""; // <- nome certo

    // Normaliza números
    $telefoneNum = preg_replace('/\D/', '', $telefone);
    $cpfNum      = preg_replace('/\D/', '', $cpf);

    // Validações
    $erros = [];
    if ($nome === "" || $email === "" || $senha === "" || $telefone === "" || $cpf === "" || $confirma === "") {
        $erros[] = "Preencha todos os campos.";
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erros[] = "E-mail inválido.";
    }
    if ($senha !== $confirma) {
        $erros[] = "As senhas não conferem.";
    }
    if (mb_strlen($senha) < 8) {
        $erros[] = "Senha deve ter pelo menos 8 caracteres.";
    }
    if (strlen($telefoneNum) < 11) {
        $erros[] = "Telefone incorreto.";
    }
    if (strlen($cpfNum) != 11) {
        $erros[] = "CPF inválido.";
    }

    if ($erros) {
        redirecWith("../paginas/telacadastro.html", ["erro" => $erros[0]]);
    }

    // Verifica CPF duplicado (tabela correta: cliente)
    $stmt = $pdo->prepare("SELECT 1 FROM cliente WHERE cpf = :cpf LIMIT 1");
    $stmt->execute([':cpf' => $cpfNum]);
    if ($stmt->fetch()) {
        redirecWith("../paginas/telacadastro.html", ["erro" => "CPF já está cadastrado."]);
    }

   

    // INSERT com placeholders nomeados 1:1
    $sql = "INSERT INTO cliente (nome, cpf, telefone, email, senha)
            VALUES (:nome, :cpf, :telefone, :email, :senha)";
    $params = [
        ':nome'     => $nome,
        ':cpf'      => $cpfNum,
        ':telefone' => $telefoneNum,
        ':email'    => $email,
        ':senha'    => $senha,
    ];

    // (Opcional, só pra sua depuração) — confira contagens:
    // if (preg_match_all('/:\w+/', $sql, $m) && count($m[0]) !== count($params)) {
    //     throw new Exception('Placeholders ≠ parâmetros: ' . json_encode($m[0]) . ' vs ' . json_encode(array_keys($params)));
    // }

    $ok = $pdo->prepare($sql)->execute($params);

    if ($ok) {
        redirecWith("../paginas/index.html", ["cadastro" => "ok"]);
    } else {
        redirecWith("../paginas/telacadastro.html", ["erro" => "Erro ao cadastrar no banco de dados."]);
    }

} catch (Throwable $e) {
    // Evite expor detalhes em produção
    redirecWith("../paginas/telacadastro.html", ["erro" => "Erro no banco de dados."]);
}
