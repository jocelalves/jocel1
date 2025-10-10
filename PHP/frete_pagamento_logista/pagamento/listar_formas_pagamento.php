<?php
require_once __DIR__ . '/../../conexao.php';


$stmt = $pdo->query("SELECT * FROM Formas_Pagamento ORDER BY id");
$formas = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($formas);

?>