<?php
require_once __DIR__ . '/../../conexao.php';


$data = json_decode(file_get_contents('php://input'), true);
$id = (int)($data['id'] ?? 0);

if (!$id) {
    echo json_encode(['success' => false, 'message' => 'ID inválido']);
    exit;
}

$stmt = $pdo->prepare("DELETE FROM Formas_Pagamento WHERE id = :id");
$stmt->execute([':id' => $id]);

echo json_encode(['success' => true]);
exit;
?>