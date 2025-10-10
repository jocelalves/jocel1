<?php
require_once __DIR__ . '/../../conexao.php';




$data = json_decode(file_get_contents('php://input'), true);

$bairro = trim($data['bairro'] ?? '');
$valor = $data['valor'] ?? '';
$transportadora = trim($data['transportadora'] ?? '');

if (!$bairro || !$valor) {
    echo json_encode(['success' => false, 'message' => 'Preencha todos os campos obrigatórios']);
    exit;
}

// Evita duplicidade
$stmt = $pdo->prepare("SELECT COUNT(*) FROM Frete WHERE bairro = ?");
$stmt->execute([$bairro]);
if ($stmt->fetchColumn() > 0) {
    echo json_encode(['success' => false, 'message' => 'Frete para esse bairro já cadastrado']);
    exit;
}

$stmt = $pdo->prepare("INSERT INTO Frete (bairro, valor, transportadora) VALUES (?, ?, ?)");
$stmt->execute([$bairro, $valor, $transportadora ?: null]);

echo json_encode(['success' => true]);
exit;
?>