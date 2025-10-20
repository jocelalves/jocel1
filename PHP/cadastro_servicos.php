<?php
require_once __DIR__ . "/conexao.php";

// Redireciona com parâmetros na URL
function redirecWith($url, $params = []) {
    if (!empty($params)) {
        $qs  = http_build_query($params);
        $sep = (strpos($url, '?') === false) ? '?' : '&';
        $url .= $sep . $qs;
    }
    header("Location: $url");
    exit;
}

// Converte arquivo para blob
function readImageToBlob(?array $file): ?string {
    if (!$file || !isset($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) return null;
    $content = file_get_contents($file['tmp_name']);
    return $content === false ? null : $content;
}

try {
    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        redirecWith("../Paginas_Logista/cadastro_produtos_logista.html", ["erro" => "Método inválido"]);
    }

    // Campos do formulário
    $nome = trim($_POST["nomeservico"] ?? '');
    $descricao = trim($_POST["descricao_servico"] ?? '');
    $preco = filter_var($_POST["preco_servico"], FILTER_VALIDATE_FLOAT);
    $desconto = filter_var($_POST["desconto_servico"], FILTER_VALIDATE_FLOAT) ?? 0;
    $categoria_id = (int)($_POST["categoria_Servicos"] ?? 0);

    // Validação
    $erros = [];
    if ($nome === '') $erros[] = "O nome do serviço é obrigatório.";
    if ($descricao === '') $erros[] = "A descrição do serviço é obrigatória.";
    if ($preco === false || $preco <= 0) $erros[] = "O preço do serviço deve ser maior que zero.";
    if ($categoria_id <= 0) $erros[] = "A categoria do serviço é obrigatória.";
    if ($desconto < 0 || $desconto > 100) $erros[] = "O desconto deve estar entre 0 e 100.";

    if (!empty($erros)) {
        redirecWith("../Paginas_Logista/cadastro_produtos_logista.html", ["erro" => implode(" ", $erros)]);
    }

    // Imagens
    $imagens = [
        readImageToBlob($_FILES["imgproduto1"] ?? null),
        readImageToBlob($_FILES["imgproduto2"] ?? null),
        readImageToBlob($_FILES["imgproduto3"] ?? null)
    ];

    $pdo->beginTransaction();

    // Inserir serviço
    $sqlServico = "INSERT INTO Servicos (nome, descricao, preco_servico, desconto, Categorias_Servicos_idCategorias_Servicos)
                   VALUES (:nome, :descricao, :preco, :desconto, :categoria_id)";
    $stmServico = $pdo->prepare($sqlServico);
    $stmServico->execute([
        ":nome" => $nome,
        ":descricao" => $descricao,
        ":preco" => $preco,
        ":desconto" => $desconto,
        ":categoria_id" => $categoria_id
    ]);

    $idServico = (int)$pdo->lastInsertId();

    // Inserir imagens e vincular
    $sqlImagem = "INSERT INTO Imagem_Servicos (foto, descricao) VALUES (:foto, NULL)";
    $stmImagem = $pdo->prepare($sqlImagem);

    $sqlVinculo = "INSERT INTO Servicos_has_Imagem_Servicos (Servicos_idServicos, Imagem_Servicos_idImagem_Servicos)
                   VALUES (:idServico, :idImagem)";
    $stmVinculo = $pdo->prepare($sqlVinculo);

    foreach ($imagens as $img) {
        if ($img !== null) {
            $stmImagem->execute([":foto" => $img]);
            $idImg = (int)$pdo->lastInsertId();

            $stmVinculo->execute([
                ":idServico" => $idServico,
                ":idImagem" => $idImg
            ]);
        }
    }

    $pdo->commit();
    redirecWith("../Paginas_Logista/cadastro_produtos_logista.html", ["sucesso" => "Serviço cadastrado com sucesso!"]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    redirecWith("../Paginas_Logista/cadastro_produtos_logista.html", ["erro" => "Erro: " . $e->getMessage()]);
}
?>
