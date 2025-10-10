<?php
// Caminho absoluto para a conexão com o banco
require_once __DIR__ . '/../conexao.php'; // sobe um nível e acessa conexao.php

/* Função para redirecionar com parâmetros */
function redirectWith(string $url, array $params = []): void {
    if (!empty($params)) {
        $qs = http_build_query($params);
        $sep = (strpos($url, '?') === false) ? '?' : '&';
        $url .= $sep . $qs;
    }
    header("Location: $url");
    exit;
}

try {
    // --- LISTAGEM DE CATEGORIAS ---
    if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET["listar"])) {

        $sqllistar = "SELECT * FROM Categorias_Servicos ORDER BY nome";
        $stmlistar = $pdo->query($sqllistar);
        $lista = $stmlistar->fetchAll(PDO::FETCH_ASSOC);

        $formato = isset($_GET["format"]) ? strtoupper($_GET["format"]) : "OPTION";

        if ($formato === "JSON") {
            header("Content-Type: application/json; charset=utf-8");
            echo json_encode(["ok" => true, "categorias" => $lista], JSON_UNESCAPED_UNICODE);
        } else {
            header("Content-Type: text/html; charset=utf-8"); // ✅ Corrigido
            foreach ($lista as $item) {
                $id = (int)$item["idCategorias_Servicos"];
                $nomecat = htmlspecialchars($item["nome"], ENT_QUOTES, "UTF-8");
                echo "<option value=\"{$id}\">{$nomecat}</option>\n";
            }
        }
        exit;
    }
} catch (Throwable $e) {
    // --- ERRO NA LISTAGEM ---
    if (isset($_GET['format']) && strtolower($_GET['format']) === 'json') {
        header('Content-Type: application/json; charset=utf-8', true, 500);
        echo json_encode([
            'ok' => false,
            'error' => 'Erro ao listar categorias',
            'detail' => $e->getMessage()
        ], JSON_UNESCAPED_UNICODE);
    } else {
        header('Content-Type: text/html; charset=utf-8', true, 500);
        echo "<option disabled>Erro ao carregar categorias</option>";
    }
    exit;
}

try {
    // --- CADASTRO DE CATEGORIAS ---
    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        redirectWith("../paginas_logista/cadastro_produtos_logista.html", ["erro" => "Método inválido."]);
    }

    $nome = trim($_POST["nome"] ?? "");
    $desconto = (float)($_POST["desconto"] ?? 0);

    if ($nome === "") {
        redirectWith("../paginas_logista/cadastro_produtos_logista.html", ["erro" => "O nome da categoria é obrigatório."]);
    }

    $pdo->beginTransaction();

    $sql = "INSERT INTO Categorias_Servicos (nome, desconto) VALUES (:nome, :desconto)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ":nome" => $nome,
        ":desconto" => $desconto
    ]);

    $pdo->commit();

    redirectWith("../paginas_logista/cadastro_produtos_logista.html", ["sucesso" => "Categoria cadastrada com sucesso!"]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    redirectWith("../paginas_logista/cadastro_produtos_logista.html", [
        "erro" => "Erro ao cadastrar categoria: " . $e->getMessage()
    ]);
}
?>
