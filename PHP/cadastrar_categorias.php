<?php
require_once __DIR__ . "/conexao.php";

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
  // Verifica método de envio
  if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    redirectWith("../paginas_logista/cadastro_produtos_logista.html", ["erro" => "Método inválido."]);
  }

  // Captura de dados do formulário
  $nome = trim($_POST["nome"] ?? "");
  $desconto = (float)($_POST["desconto"] ?? 0);

  // Validação básica
  if ($nome === "") {
    redirectWith("../paginas_logista/cadastro_produtos_logista.html", ["erro" => "O nome da categoria é obrigatório."]);
  }

  // Inicia transação
  $pdo->beginTransaction();

  // SQL de inserção
  $sql = "INSERT INTO Categorias_Servicos (nome, desconto) VALUES (:nome, :desconto)";
  $stmt = $pdo->prepare($sql);
  $stmt->execute([
    ":nome" => $nome,
    ":desconto" => $desconto
  ]);

  // Confirma transação
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
