<?php
require_once __DIR__ . '/../../conexao.php';




$stmt = $pdo->query("SELECT * FROM Frete ORDER BY id");
$fretes = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($fretes);
exit;

?>
