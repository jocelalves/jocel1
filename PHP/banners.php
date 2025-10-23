<?php
require_once __DIR__ . "/conexao.php";

// Função para redirecionar (caso necessário)
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

// ================= LISTAGEM =================
if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET["listar"])) {
    try {
        $sql = "SELECT idBanner, descricao, link, categoria, validade, imagem FROM Banners ORDER BY validade DESC";
        $stmt = $pdo->query($sql);
        $banners = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($banners as &$b) {
            $b['imagem'] = !empty($b['imagem']) ? 'data:image/jpeg;base64,' . base64_encode($b['imagem']) : null;
        }

        header("Content-Type: application/json; charset=utf-8");
        echo json_encode(['ok' => true, 'banners' => $banners]);
        exit;
    } catch (Throwable $e) {
        header("Content-Type: application/json", true, 500);
        echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
        exit;
    }
}

// ================= EDITAR =================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'atualizar') {
    try {
        $id        = (int)($_POST['id'] ?? 0);
        $descricao = trim($_POST['descricao'] ?? '');
        $validade  = trim($_POST['validade'] ?? '');
        $link      = trim($_POST['link'] ?? '');
        $categoria = $_POST['categoria'] ?? null;
        $categoria = ($categoria === '' || $categoria === null) ? null : (int)$categoria;
        $imgBlob   = readImageToBlob($_FILES['imgbanner'] ?? null);

        if ($id <= 0) throw new Exception('ID inválido.');
        $erros = [];
        if ($descricao === '') $erros[] = 'Descrição obrigatória.';
        if ($validade === '') $erros[] = 'Validade obrigatória.';
        if ($erros) throw new Exception(implode(' ', $erros));

        $setSql = "descricao = :descricao, validade = :validade, link = :link, categoria = :categoria";
        if ($imgBlob !== null) $setSql = "imagem = :imagem, " . $setSql;

        $sql = "UPDATE Banners SET $setSql WHERE idBanner = :id";
        $stmt = $pdo->prepare($sql);

        if ($imgBlob !== null) $stmt->bindValue(':imagem', $imgBlob, PDO::PARAM_LOB);
        $stmt->bindValue(':descricao', $descricao, PDO::PARAM_STR);
        $stmt->bindValue(':validade', $validade, PDO::PARAM_STR);
        $stmt->bindValue(':link', $link !== '' ? $link : null, $link !== '' ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $stmt->bindValue(':categoria', $categoria, $categoria !== null ? PDO::PARAM_INT : PDO::PARAM_NULL);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        // Retorna o banner atualizado
        $stmt = $pdo->prepare("SELECT idBanner, descricao, link, categoria, validade, imagem FROM Banners WHERE idBanner = :id");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $banner = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($banner['imagem']) $banner['imagem'] = 'data:image/jpeg;base64,' . base64_encode($banner['imagem']);

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['ok' => true, 'banner' => $banner]);
        exit;

    } catch (Throwable $e) {
        header('Content-Type: application/json', true, 500);
        echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
        exit;
    }
}

// ================= EXCLUIR =================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'excluir') {
    try {
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) throw new Exception('ID inválido para exclusão.');

        $stmt = $pdo->prepare("DELETE FROM Banners WHERE idBanner = :id");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        // Verifica se realmente foi excluído
        $check = $pdo->prepare("SELECT COUNT(*) FROM Banners WHERE idBanner = :id");
        $check->bindValue(':id', $id, PDO::PARAM_INT);
        $check->execute();
        if ($check->fetchColumn() > 0) throw new Exception('Não foi possível excluir.');

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['ok' => true]);
        exit;

    } catch (Throwable $e) {
        header('Content-Type: application/json', true, 500);
        echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
        exit;
    }
}

// ================= CADASTRAR =================
try {
    if ($_SERVER["REQUEST_METHOD"] !== "POST") throw new Exception('Método inválido.');

    $descricao = trim($_POST["descricao"] ?? '');
    $link = trim($_POST["link"] ?? '');
    $categoria = trim($_POST["categoria"] ?? '');
    $validade = $_POST["validade"] ?? '';
    $imagem = readImageToBlob($_FILES["imgbanner"] ?? null);

    $erros = [];
    if ($descricao === '') $erros[] = "Descrição obrigatória.";
    if ($validade === '') $erros[] = "Validade obrigatória.";
    if ($categoria === '') $erros[] = "Categoria obrigatória.";
    if ($imagem === null) $erros[] = "Imagem obrigatória.";

    if ($erros) throw new Exception(implode(" ", $erros));

    $sql = "INSERT INTO Banners (descricao, link, categoria, validade, imagem)
            VALUES (:descricao, :link, :categoria, :validade, :imagem)";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(":descricao", $descricao);
    $stmt->bindParam(":link", $link);
    $stmt->bindParam(":categoria", $categoria);
    $stmt->bindParam(":validade", $validade);
    $stmt->bindParam(":imagem", $imagem, PDO::PARAM_LOB);
    $stmt->execute();

    // Retorna o banner cadastrado
    $id = $pdo->lastInsertId();
    $stmt = $pdo->prepare("SELECT idBanner, descricao, link, categoria, validade, imagem FROM Banners WHERE idBanner = :id");
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $banner = $stmt->fetch(PDO::FETCH_ASSOC);
    $banner['imagem'] = 'data:image/jpeg;base64,' . base64_encode($banner['imagem']);

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['ok' => true, 'banner' => $banner]);
    exit;

} catch (Exception $e) {
    header('Content-Type: application/json', true, 500);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}
?>
