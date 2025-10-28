<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/conexao.php';

// lê JSON ou $_POST
$raw  = file_get_contents('php://input');
$data = json_decode($raw, true);
if (!is_array($data)) {
    $data = $_POST;
}

// DEBUG opcional
file_put_contents(__DIR__.'/login_log.txt', json_encode($data).PHP_EOL, FILE_APPEND);

$cpfOrUser = isset($data['cpf']) ? trim($data['cpf']) : '';
$senha     = isset($data['senha']) ? (string)$data['senha'] : '';

if ($cpfOrUser === '' || $senha === '') {
    echo json_encode(['ok' => false, 'msg' => 'Informe CPF/usuário e senha.']);
    exit;
}

$onlyDigits = preg_replace('/\D+/', '', $cpfOrUser);

try {
    // 1) Cliente
    $sql = "SELECT idCliente, nome, senha FROM Cliente WHERE cpf = :cpf LIMIT 1";
    $st  = $pdo->prepare($sql);
    $st->bindValue(':cpf', $onlyDigits);
    $st->execute();
    $cli = $st->fetch(PDO::FETCH_ASSOC);

    if ($cli) {
        if ((strlen($cli['senha']) >= 60 && password_verify($senha, $cli['senha'])) || $cli['senha'] === $senha) {
            session_regenerate_id(true);
            $_SESSION['auth']      = true;
            $_SESSION['user_type'] = 'cliente';
            $_SESSION['user_id']   = (int)$cli['idCliente'];
            $_SESSION['nome']      = $cli['nome'];

            echo json_encode(['ok' => true, 'redirect' => '../Paginas_Cliente/telahomeProdutos.html']);
            exit;
        } else {
            echo json_encode(['ok' => false, 'msg' => 'Senha incorreta.']);
            exit;
        }
    }

    // 2) Empresa
    $sql = "SELECT idEmpresa, nome_fantasia, senha, cnpj, usuario FROM Empresa
            WHERE usuario = :u OR cnpj = :u_digits LIMIT 1";
    $st  = $pdo->prepare($sql);
    $st->bindValue(':u', $cpfOrUser);
    $st->bindValue(':u_digits', $onlyDigits);
    $st->execute();
    $emp = $st->fetch(PDO::FETCH_ASSOC);

    if ($emp) {
        if ((strlen($emp['senha']) >= 60 && password_verify($senha, $emp['senha'])) || $emp['senha'] === $senha) {
            session_regenerate_id(true);
            $_SESSION['auth']      = true;
            $_SESSION['user_type'] = 'empresa';
            $_SESSION['user_id']   = (int)$emp['idEmpresa'];
            $_SESSION['nome']      = $emp['nome_fantasia'];

            echo json_encode(['ok' => true, 'redirect' => '../Paginas_Logista/home_lojista.html']);
            exit;
        } else {
            echo json_encode(['ok' => false, 'msg' => 'Senha incorreta.']);
            exit;
        }
    }

    echo json_encode(['ok' => false, 'msg' => 'Credenciais inválidas.']);
    exit;

} catch (Throwable $e) {
    echo json_encode(['ok' => false, 'msg' => 'Erro interno ao processar login.']);
    exit;
}
?>
