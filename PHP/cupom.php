<?php
// cadastro_cupom.php
require_once __DIR__ . '/conexao.php';

/* ---------------------- FUNÇÕES ---------------------- */
function redirect_with(string $url, array $params = []): void {
  if ($params) {
    $qs  = http_build_query($params);
    $url .= (strpos($url, '?') === false ? '?' : '&') . $qs;
  }
  header("Location: $url");
  exit;
}


/* Converte datas comuns (DD/MM/YYYY, YYYY-MM-DD) para 'Y-m-d' */
function normalize_date_to_ymd(?string $s): ?string {
  if (!$s) return null;
  $s = trim($s);

  // formato ISO
  if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $s)) {
    $dt = DateTime::createFromFormat('Y-m-d', $s);
    return ($dt && $dt->format('Y-m-d') === $s) ? $s : null;
  }

  // formato BR com /
  if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $s)) {
    [$d,$m,$y] = explode('/', $s);
    if (checkdate((int)$m, (int)$d, (int)$y))
      return sprintf('%04d-%02d-%02d', $y, $m, $d);
  }

  // formato BR com -
  if (preg_match('/^\d{2}-\d{2}-\d{4}$/', $s)) {
    [$d,$m,$y] = explode('-', $s);
    if (checkdate((int)$m, (int)$d, (int)$y))
      return sprintf('%04d-%02d-%02d', $y, $m, $d);
  }

  return null;
}



/* ===================== LISTAGEM ===================== */
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['listar'])) {
    try {
        $sql  = "SELECT idCupom, nome, valor, data_validade, quantidade 
                 FROM Cupom ORDER BY idCupom DESC";
        $stmt = $pdo->query($sql);
        $cupons = $stmt->fetchAll(PDO::FETCH_ASSOC);

        header("Content-Type: application/json; charset=utf-8");
        echo json_encode(['ok' => true, 'cupons' => $cupons]);
        exit;
    } catch (Throwable $e) {
        header("Content-Type: application/json", true, 500);
        echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
        exit;
    }
}

/* ===================== EDITAR ===================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'atualizar') {
    try {
        $id = (int)($_POST['id'] ?? 0);

        // Mapear os campos do formulário para o que o PHP espera
        $nome       = trim($_POST['nome'] ?? $_POST['CupomNome'] ?? '');
        $valorRaw   = $_POST['valor'] ?? $_POST['valor_cupom'] ?? '0';
        $valor      = (float) str_replace(',', '.', $valorRaw);
        $validade   = $_POST['data_validade'] ?? $_POST['validade_cupom'] ?? '';
        $quantidade = (int)($_POST['quantidade'] ?? $_POST['quantidade_cupom'] ?? 0);

        $erros = [];
        if ($id <= 0) $erros[] = 'ID inválido.';
        if ($nome === '') $erros[] = 'Nome obrigatório.';
        if ($valor <= 0) $erros[] = 'Valor obrigatório.';
        if ($validade === '') $erros[] = 'Data de validade obrigatória.';
        if ($quantidade < 0) $erros[] = 'Quantidade inválida.';

        if ($erros) throw new Exception(implode(' ', $erros));

        // Normaliza a data para Y-m-d
        $data_normalizada = normalize_date_to_ymd($validade);
        if (!$data_normalizada) throw new Exception('Data de validade inválida.');

        $sql = "UPDATE Cupom 
                SET nome = :nome, valor = :valor, data_validade = :validade, quantidade = :quantidade
                WHERE idCupom = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':nome', $nome, PDO::PARAM_STR);
        $stmt->bindValue(':valor', $valor);
        $stmt->bindValue(':validade', $data_normalizada);
        $stmt->bindValue(':quantidade', $quantidade, PDO::PARAM_INT);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        redirecWith('../PAGINAS_LOGISTA/promocoes_logista.html', ['msg' => 'Cupom atualizado com sucesso']);
    } catch (Throwable $e) {
        redirecWith('../PAGINAS_LOGISTA/promocoes_logista.html', ['erro_cupom' => $e->getMessage()]);
    }
}


/* ===================== EXCLUIR ===================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'excluir') {
    try {
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) throw new Exception('ID inválido para exclusão.');

        $stmt = $pdo->prepare("DELETE FROM Cupom WHERE idCupom = :id");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        redirecWith('../PAGINAS_LOGISTA/promocoes_logista.html', ['msg' => 'Cupom excluído com sucesso']);
    } catch (Throwable $e) {
        redirecWith('../PAGINAS_LOGISTA/promocoes_logista.html', ['erro_cupom' => $e->getMessage()]);
    }
}



/* ===================== CADASTRO ===================== */
try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        redirect_with('../PAGINAS_LOGISTA/promocoes_logista.html', ['erro_cupom' => 'Método inválido']);
    }

    $nome       = trim($_POST['CupomNome'] ?? '');
    $valorRaw   = $_POST['valor_cupom'] ?? '';
    $dataRaw    = $_POST['validade_cupom'] ?? '';
    $quantRaw   = $_POST['quantidade_cupom'] ?? '';

    $valor = (float)str_replace(',', '.', $valorRaw);
    $data  = normalize_date_to_ymd($dataRaw);
    $quant = (int)$quantRaw;

    $erros = [];
    if ($nome === '') $erros[] = 'Informe o nome do cupom.';
    elseif (mb_strlen($nome) > 45) $erros[] = 'Nome deve ter no máximo 45 caracteres.';
    if ($valor <= 0) $erros[] = 'Valor deve ser maior que zero.';
    if (!$data) $erros[] = 'Data de validade inválida.';
    if ($quant <= 0) $erros[] = 'Quantidade deve ser maior que zero.';

    if ($erros) {
        redirect_with('../PAGINAS_LOGISTA/promocoes_logista.html', ['erro_cupom' => implode(' ', $erros)]);
    }

    $sql = "INSERT INTO Cupom (nome, valor, data_validade, quantidade)
            VALUES (:n, :v, :d, :q)";
    $st = $pdo->prepare($sql);
    $st->bindValue(':n', $nome, PDO::PARAM_STR);
    $st->bindValue(':v', $valor, PDO::PARAM_STR);
    $st->bindValue(':d', $data, PDO::PARAM_STR);
    $st->bindValue(':q', $quant, PDO::PARAM_INT);
    $st->execute();

     // Sucesso -> redireciona com ID do cupom
    redirect_with('../PAGINAS_LOGISTA/promocoes_logista.html');
    exit;

} catch (Throwable $e) {
     // Erro no banco -> redireciona com mensagem
    redirect_with('../PAGINAS_LOGISTA/promocoes_logista.html', ['erro_cupom' => 'Erro no banco de dados: ' . $e->getMessage()]);
    exit;
}
?>