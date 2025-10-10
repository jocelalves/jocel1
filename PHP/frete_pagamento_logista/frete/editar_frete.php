<?php
require_once __DIR__ . '/../../conexao.php';



$data = json_decode(file_get_contents('php://input'), true);

$id = (int)($data['id'] ?? 0);
$bairro = trim($data['bairro'] ?? '');
$valor = $data['valor'] ?? '';
$transportadora = trim($data['transportadora'] ?? '');

if (!$id || !$bairro || !$valor) {
    echo json_encode(['success' => false, 'message' => 'Dados inválidos']);
    exit;
}

// Evita duplicidade
$stmt = $pdo->prepare("SELECT COUNT(*) FROM Frete WHERE bairro = ? AND id != ?");
$stmt->execute([$bairro, $id]);
if ($stmt->fetchColumn() > 0) {
    echo json_encode(['success' => false, 'message' => 'Frete para esse bairro já cadastrado']);
    exit;
}

$stmt = $pdo->prepare("UPDATE Frete SET bairro = ?, valor = ?, transportadora = ? WHERE id = ?");
$stmt->execute([$bairro, $valor, $transportadora ?: null, $id]);

echo json_encode(['success' => true]);

?>