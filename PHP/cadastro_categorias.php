<?php
// Conectando ao banco de dados
require_once __DIR__ . "/conexao.php";

// Função para redirecionar com parâmetros
function redirecWith($url, $params = []) {
    if (!empty($params)) {
        $qs  = http_build_query($params);
        $sep = (strpos($url, '?') === false) ? '?' : '&';
        $url .= $sep . $qs;
    }
    header("Location: $url");
    exit;
}

// Códigos de listagem de categorias
if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET["listar"])) {
    try {
        $sqlListar = "SELECT idCategorias_Servicos AS id, nome FROM Categorias_Servicos ORDER BY nome";

        $stmtListar = $pdo->query($sqlListar);   
        $listar = $stmtListar->fetchAll(PDO::FETCH_ASSOC);

        $formato = isset($_GET["format"]) ? strtolower($_GET["format"]) : "option";

        if ($formato === "json") {
            header("Content-Type: application/json; charset=utf-8");
            echo json_encode(["ok" => true, "categorias" => $listar], JSON_UNESCAPED_UNICODE);
            exit;
        }

        // Retorno padrão em <option>
        header('Content-Type: text/html; charset=utf-8');
        foreach ($listar as $lista) {
            $id = (int)$lista["id"];
            $nome = htmlspecialchars($lista["nome"], ENT_QUOTES, "UTF-8");
            echo "<option value=\"{$id}\">{$nome}</option>\n";
        }
        exit;

    } catch (Throwable $e) {
        if (isset($_GET['format']) && strtolower($_GET['format']) === 'json') {
            header('Content-Type: application/json; charset=utf-8', true, 500);
            echo json_encode(['ok' => false, 'error' => 'Erro ao listar categorias', 'detail' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
        } else {
            header('Content-Type: text/html; charset=utf-8', true, 500);
            echo "<option disabled>Erro ao carregar categorias</option>";
        }
        exit;
    }
}

// Código de cadastro de categoria
try {
    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        redirecWith("../paginas_logista/cadastro_produtos_logista.html", ["erro" => "Método inválido"]);
    }

    $nome = trim($_POST["nome"] ?? '');
    $desconto = (float)($_POST["desconto"] ?? 0);

    $erros = [];
    if ($nome === '') $erros[] = "Preencha todos os campos.";

    if (!empty($erros)) {
        redirecWith("../paginas_logista/cadastro_produtos_logista.html", ["erro" => implode(" ", $erros)]);
    }

    $sql = "INSERT INTO Categorias_Servicos (nome, desconto) VALUES (:nome, :desconto)";
    $inserir = $pdo->prepare($sql)->execute([
        ":nome" => $nome,
        ":desconto" => $desconto
    ]);

    if ($inserir) {
        redirecWith("../paginas_logista/cadastro_produtos_logista.html", ["cadastro" => "ok"]);
    } else {
        redirecWith("../paginas_logista/cadastro_produtos_logista.html", ["erro" => "Erro ao cadastrar no banco de dados"]);
    }

} catch (Exception $e) {
    redirecWith("../paginas_logista/cadastro_produtos_logista.html", ["erro" => "Erro no banco de dados: " . $e->getMessage()]);
}

// Última listagem para gerar <option> (opcional)
try {
    $sql = "SELECT idCategorias_Servicos, nome FROM Categorias_Servicos ORDER BY nome";
    foreach ($pdo->query($sql) as $row) {
        $id = (int)$row['idCategorias_Servicos'];
        $nome = htmlspecialchars($row['nome'], ENT_QUOTES, 'UTF-8');
        echo "<option value=\"{$id}\">{$nome}</option>\n";
    }
} catch (Throwable $e) {
    http_response_code(500);
}
?>