<?php
require_once __DIR__ . '/../../conexao.php';


try {
    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        echo json_encode(['success' => false, 'message' => 'Método inválido']);
        exit;
    }

    $data = json_decode(file_get_contents('php://input'), true);
    $nomepagamento = trim($data['nomepagamento'] ?? '');

    if ($nomepagamento === "") {
        echo json_encode(['success' => false, 'message' => 'Preencha o campo nome']);
        exit;
    }

    // Verifica duplicidade
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM Formas_Pagamento WHERE nome = :nome");
    $stmt->execute([":nome" => $nomepagamento]);
    if ($stmt->fetchColumn() > 0) {
        echo json_encode(['success' => false, 'message' => 'Forma de pagamento já cadastrada']);
        exit;
    }

    // Inserção
    $sql = "INSERT INTO Formas_Pagamento (nome) VALUES (:nome)";
    $inserir = $pdo->prepare($sql)->execute([":nome" => $nomepagamento]);

    if ($inserir) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao cadastrar no banco']);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erro no banco: '.$e->getMessage()]);
}
?>