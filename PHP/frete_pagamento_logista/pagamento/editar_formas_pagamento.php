<?php
require_once __DIR__ . '/../../conexao.php';


$data = json_decode(file_get_contents('php://input'), true);
$id = (int)($data['id'] ?? 0);
$nome = trim($data['nome'] ?? '');

if (!$id || $nome === '') {
    echo json_encode(['success' => false, 'message' => 'Dados inválidos']);
    exit;
}

// Verifica duplicado
$stmt = $pdo->prepare("SELECT COUNT(*) FROM Formas_Pagamento WHERE nome = :nome AND id != :id");
$stmt->execute([':nome' => $nome, ':id' => $id]);
if ($stmt->fetchColumn() > 0) {
    echo json_encode(['success' => false, 'message' => 'Forma de pagamento já cadastrada']);
    exit;
}

// Atualiza
$stmt = $pdo->prepare("UPDATE Formas_Pagamento SET nome = :nome WHERE id = :id");
$stmt->execute([':nome' => $nome, ':id' => $id]);

echo json_encode(['success' => true]);
exit;
?>
