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

// Função para ler imagem e converter em BLOB
function readImageToBlob(?array $file): ?string {
    if (!$file || !isset($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) return null;
    $content = file_get_contents($file['tmp_name']);
    return $content === false ? null : $content;
}

// ================= LISTAR BANNERS =================
if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET["listar"])) {
    try {
        $sql = "SELECT idBanner, descricao, link, categoria, validade, imagem FROM Banners ORDER BY validade DESC";
        $stmt = $pdo->query($sql);
        $banners = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Converte LONGBLOB em Base64 para exibir no <img src="">
        foreach ($banners as &$banner) {
            if (!empty($banner['imagem'])) {
                $banner['imagem'] = 'data:image/jpeg;base64,' . base64_encode($banner['imagem']);
            } else {
                $banner['imagem'] = null;
            }
        }

        header("Content-Type: application/json; charset=utf-8");
        echo json_encode(["ok" => true, "banners" => $banners], JSON_UNESCAPED_UNICODE);
        exit;

    } catch (Throwable $e) {
        header('Content-Type: application/json; charset=utf-8', true, 500);
        echo json_encode(['ok' => false, 'error' => 'Erro ao listar banners', 'detail' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

// ================= CADASTRAR BANNER =================
try {
    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        redirecWith("../paginas_logista/promocoes_logista.html", ["erro" => "Método inválido"]);
    }

    $descricao = trim($_POST["descricao"] ?? '');
    $link = trim($_POST["link"] ?? '');
    $categoria = trim($_POST["categoria"] ?? '');
    $validade = $_POST["validade"] ?? '';
    $imagem = readImageToBlob($_FILES["imgbanner"] ?? null);

    $erros = [];
    if ($descricao === '') $erros[] = "A descrição é obrigatória.";
    if ($validade === '') $erros[] = "A data de validade é obrigatória.";
    if ($categoria === '') $erros[] = "A categoria é obrigatória.";
    if ($imagem === null) $erros[] = "A imagem do banner é obrigatória.";

    if (!empty($erros)) {
        redirecWith("../paginas_logista/promocoes_logista.html", ["erro" => implode(" ", $erros)]);
    }

    $sql = "INSERT INTO Banners (descricao, link, categoria, validade, imagem)
            VALUES (:descricao, :link, :categoria, :validade, :imagem)";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(":descricao", $descricao);
    $stmt->bindParam(":link", $link);
    $stmt->bindParam(":categoria", $categoria);
    $stmt->bindParam(":validade", $validade);
    $stmt->bindParam(":imagem", $imagem, PDO::PARAM_LOB);
    $stmt->execute();

    redirecWith("../paginas_logista/promocoes_logista.html", ["cadastro" => "ok"]);

} catch (Exception $e) {
    redirecWith("../paginas_logista/promocoes_logista.html", ["erro" => "Erro no banco de dados: " . $e->getMessage()]);
}
?>
