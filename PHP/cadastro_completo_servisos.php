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

/* Função para ler imagem como blob */
function readImageToBlob(?array $file): ?string {
    if (!$file || !isset($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
        return null;
    }
    $content = file_get_contents($file['tmp_name']);
    return $content === false ? null : $content;
}

try {
    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        redirectWith("../paginas_logista/cadastro_produtos_logista.html", ["erro" => "Método inválido."]);
    }

    // Captura dos dados do formulário
    $nome = trim($_POST["nome"] ?? "");
    $descricao = trim($_POST["descricao"] ?? "");
    $preco_servico = (float)($_POST["preco_servico"] ?? 0);
    $desconto = (float)($_POST["desconto"] ?? 0);

    // Categoria fixa temporariamente
    $categoria = 1;
    if ($categoria <= 0) {
        redirectWith("../paginas_logista/cadastro_produtos_logista.html", ["erro" => "Categoria inválida."]);
    }

    // Captura imagens
    $imagens = [
        readImageToBlob($_FILES["imagem1"] ?? null),
        readImageToBlob($_FILES["imagem2"] ?? null),
        readImageToBlob($_FILES["imagem3"] ?? null)
    ];

    // Validação básica
    if ($nome === "" || $descricao === "" || $preco_servico <= 0) {
        redirectWith("../paginas_logista/cadastro_produtos_logista.html", ["erro" => "Preencha todos os campos obrigatórios."]);
    }

    $pdo->beginTransaction();

    // Inserir serviço
    $sqlServico = "INSERT INTO Servicos (
                      nome, descricao, preco_servico, desconto, Categorias_Servicos_idCategorias_Servicos
                   ) VALUES (
                      :nome, :descricao, :preco_servico, :desconto, :categoria
                   )";

    $stmtServico = $pdo->prepare($sqlServico);
    $stmtServico->execute([
        ":nome" => $nome,
        ":descricao" => $descricao,
        ":preco_servico" => $preco_servico,
        ":desconto" => $desconto,
        ":categoria" => $categoria
    ]);

    $idServico = (int)$pdo->lastInsertId();

    // Inserir imagens e criar relacionamento
    $sqlImagem = "INSERT INTO Imagem_Servicos (foto, descricao) VALUES (:foto, :descricao)";
    $stmtImagem = $pdo->prepare($sqlImagem);

    $sqlRelacao = "INSERT INTO Servicos_has_Imagem_Servicos (Servicos_idServicos, Imagem_Servicos_idImagem_Servicos)
                   VALUES (:idServico, :idImagem)";
    $stmtRelacao = $pdo->prepare($sqlRelacao);

    foreach ($imagens as $index => $img) {
        if ($img !== null) {
            // Inserir imagem
            $descricaoImagem = "Imagem " . ($index + 1);
            $stmtImagem->bindParam(":foto", $img, PDO::PARAM_LOB);
            $stmtImagem->bindParam(":descricao", $descricaoImagem, PDO::PARAM_STR);
            $stmtImagem->execute();

            // Pegar id da imagem inserida
            $idImagem = (int)$pdo->lastInsertId();

            // Criar relação com o serviço
            $stmtRelacao->execute([
                ":idServico" => $idServico,
                ":idImagem" => $idImagem
            ]);
        }
    }

    $pdo->commit();

    redirectWith("../paginas_logista/cadastro_produtos_logista.html", ["sucesso" => "Serviço cadastrado com sucesso!"]);

} catch (Exception $e) {
    try {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
    } catch (Exception $rollbackError) {}
    redirectWith("../paginas_logista/cadastro_produtos_logista.html", [
        "erro" => "Erro ao cadastrar: " . $e->getMessage()
    ]);
}
?>
