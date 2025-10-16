<?php

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

// Lê arquivo de upload como blob
function readImageToBlob(?array $file): ?string {
    if (!$file || !isset($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) return null;
    $content = file_get_contents($file['tmp_name']);
    return $content === false ? null : $content;
}

try {
    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        redirecWith("../paginas_logista/cadastro_servicos.html", ["erro" => "Método inválido"]);
    }

    // Variáveis do serviço
    $nome = trim($_POST["nomeproduto"] ?? '');
    $descricao = trim($_POST["descricao"] ?? '');
    $preco = (float)($_POST["preco"] ?? 0);
    $categoria_id = (int)($_POST["categoria_id"] ?? 0); // ID da categoria do serviço

    // Validação
    $erros = [];
    if ($nome === '') $erros[] = "O nome do serviço é obrigatório.";
    if ($descricao === '') $erros[] = "A descrição do serviço é obrigatória.";
    if ($preco <= 0) $erros[] = "O preço do serviço deve ser maior que zero.";
    if ($categoria_id <= 0) $erros[] = "A categoria do serviço é obrigatória.";

    if (!empty($erros)) {
        redirecWith("../paginas_logista/cadastro_servicos.html", ["erro" => implode(" ", $erros)]);
    }

    // Imagens
    $imagens = [
        readImageToBlob($_FILES["imgproduto1"] ?? null),
        readImageToBlob($_FILES["imgproduto2"] ?? null),
        readImageToBlob($_FILES["imgproduto3"] ?? null)
    ];

    $pdo->beginTransaction();

    // Inserir serviço
    $sqlServico = "INSERT INTO Servicos (nome, descricao, preco_servico, Categorias_Servicos_idCategorias_Servicos)
                   VALUES (:nome, :descricao, :preco, :categoria_id)";
    $stmServico = $pdo->prepare($sqlServico);
    $stmServico->execute([
        ":nome" => $nome,
        ":descricao" => $descricao,
        ":preco" => $preco,
        ":categoria_id" => $categoria_id
    ]);

    $idServico = (int)$pdo->lastInsertId();

    // Inserir imagens e vincular ao serviço
    $sqlImagem = "INSERT INTO Imagem_Servicos (foto, descricao) VALUES (:foto, NULL)";
    $stmImagem = $pdo->prepare($sqlImagem);

    $sqlVinculo = "INSERT INTO Servicos_has_Imagem_Servicos (Servicos_idServicos, Imagem_Servicos_idImagem_Servicos)
                   VALUES (:idServico, :idImagem)";
    $stmVinculo = $pdo->prepare($sqlVinculo);

    foreach ($imagens as $img) {
        if ($img !== null) {
            $stmImagem->bindParam(":foto", $img, PDO::PARAM_LOB);
            $stmImagem->execute();
            $idImg = (int)$pdo->lastInsertId();

            $stmVinculo->execute([
                ":idServico" => $idServico,
                ":idImagem" => $idImg
            ]);
        }
    }

    $pdo->commit();
    redirecWith("../paginas_logista/cadastro_servicos.html", ["Cadastro" => "ok"]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    redirecWith("../paginas_logista/cadastro_servicos.html", ["erro" => "Erro no banco de dados: " . $e->getMessage()]);
}
