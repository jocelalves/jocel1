<?php
require_once __DIR__ . "/conexao.php";

/* ============================ FUNÇÕES AUXILIARES ============================ */

// Redireciona para uma página com query string de parâmetros
function redirect_with($url, $params = []) {
    if (!empty($params)) {
        $qs  = http_build_query($params);
        $sep = (strpos($url, '?') === false) ? '?' : '&';
        $url .= $sep . $qs;
    }
    header("Location: $url");
    exit;
}

// Lê arquivo de imagem e retorna conteúdo em BLOB
function readImageToBlob(?array $file): ?string {
    if (!$file || !isset($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) return null;
    $content = file_get_contents($file['tmp_name']);
    return $content === false ? null : $content;
}

// -------------------- LISTAR SERVIÇOS --------------------
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['listar'])) {
    header('Content-Type: application/json; charset=utf-8');
    try {
        $sql = "SELECT s.idServicos, s.nome, s.descricao, s.preco_servico, s.desconto,
                       s.Categorias_Servicos_idCategorias_Servicos,
                       GROUP_CONCAT(i.idImagem_Servicos) AS imagens_ids,
                       GROUP_CONCAT(TO_BASE64(i.foto) SEPARATOR '|') AS imagens_base64
                  FROM Servicos s
                  LEFT JOIN Imagem_Servicos i ON i.Servicos_idServicos = s.idServicos
                 GROUP BY s.idServicos
                 ORDER BY s.idServicos DESC";
        $stmt = $pdo->query($sql);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $data = [];
        foreach ($rows as $row) {
            $imagens = [];
            if ($row['imagens_base64']) {
                $bases = explode('|', $row['imagens_base64']);
                $ids   = explode(',', $row['imagens_ids']);
                foreach ($bases as $k => $b64) {
                    $imagens[] = [
                        'id' => $ids[$k],
                        'foto' => $b64
                    ];
                }
            }
            $data[] = [
                'idServicos' => (int)$row['idServicos'],
                'nome' => $row['nome'],
                'descricao' => $row['descricao'],
                'preco' => (float)$row['preco_servico'],
                'desconto' => $row['desconto'] !== null ? (float)$row['desconto'] : null,
                'categoriaId' => $row['Categorias_Servicos_idCategorias_Servicos'] !== null ? (int)$row['Categorias_Servicos_idCategorias_Servicos'] : null,
                'imagens' => $imagens
            ];
        }

        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    } catch (Throwable $e) {
        echo json_encode(['erro' => 'Falha ao listar serviços: ' . $e->getMessage()]);
        exit;
    }
}


/* ============================ EXCLUSÃO ============================ */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'excluir') {
    try {
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            redirect_with('../Paginas_Logista/cadastro_produtos_logista.html', ['erro_servico' => 'ID inválido para exclusão.']);
        }

        // Exclui serviço (imagens vinculadas devem ter ON DELETE CASCADE)
        $st = $pdo->prepare("DELETE FROM Servicos WHERE idServicos = :id");
        $st->bindValue(':id', $id, PDO::PARAM_INT);
        $st->execute();

        redirect_with('../Paginas_Logista/cadastro_produtos_logista.html', ['excluir_servico' => 'ok']);
        
    } catch (Throwable $e) {
        redirect_with('../Paginas_Logista/cadastro_produtos_logista.html', ['erro_servico' => 'Erro ao excluir: ' . $e->getMessage()]);
    }
}

/* ============================ CADASTRO ============================ */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'cadastrar') {
    try {
        $nome        = trim($_POST['nomeservico'] ?? '');
        $descricao   = trim($_POST['descricao_servico'] ?? '');
        $preco       = (float)($_POST['preco_servico'] ?? 0);
        $desconto    = ($_POST['desconto_servico'] === '' ? null : (float)$_POST['desconto_servico']);
        $categoriaId = ($_POST['categoria_Servicos'] ?? '');

        // Validações
        $erros = [];
        if ($nome === '') $erros[] = 'Informe o nome do serviço.';
        elseif (mb_strlen($nome) > 45) $erros[] = 'Nome deve ter no máximo 45 caracteres.';
        if ($descricao === '') $erros[] = 'Informe a descrição.';
        if ($preco <= 0) $erros[] = 'Informe um preço válido.';
        if ($categoriaId === '') $erros[] = 'Selecione uma categoria.';

        if ($erros) {
            redirect_with('../Paginas_Logista/cadastro_produtos_logista.html', ['erro_servico' => implode(' ', $erros)]);
        }

        // Inserir serviço
        $sql = "INSERT INTO Servicos (nome, descricao, preco_servico, desconto, Categorias_Servicos_idCategorias_Servicos)
                VALUES (:nome, :desc, :preco, :descnt, :cat)";
        $st = $pdo->prepare($sql);
        $st->bindValue(':nome', $nome, PDO::PARAM_STR);
        $st->bindValue(':desc', $descricao, PDO::PARAM_STR);
        $st->bindValue(':preco', $preco, PDO::PARAM_STR);
        $st->bindValue(':descnt', $desconto, $desconto === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $st->bindValue(':cat', (int)$categoriaId, PDO::PARAM_INT);
        $st->execute();

        $idServico = $pdo->lastInsertId();

        // Inserir imagens dinamicamente
        foreach ($_FILES as $key => $file) {
            if (strpos($key, 'imgproduto') === 0) {
                $blob = readImageToBlob($file);
                if ($blob !== null) {
                    $sqlImg = "INSERT INTO Imagem_Servicos (foto, descricao, Servicos_idServicos)
                               VALUES (:foto, :desc, :idServ)";
                    $stmImg = $pdo->prepare($sqlImg);
                    $stmImg->bindValue(':foto', $blob, PDO::PARAM_LOB);
                    $stmImg->bindValue(':desc', $nome, PDO::PARAM_STR);
                    $stmImg->bindValue(':idServ', $idServico, PDO::PARAM_INT);
                    $stmImg->execute();
                }
            }
        }

        redirect_with('../Paginas_Logista/cadastro_produtos_logista.html', ['cadastrar_servico' => 'ok']);

    } catch (Throwable $e) {
        redirect_with('../Paginas_Logista/cadastro_produtos_logista.html', ['erro_servico' => 'Erro ao cadastrar: ' . $e->getMessage()]);
    }
}

/* ============================ ATUALIZAÇÃO ============================ */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['acao'] ?? '') === 'atualizar') {
    try {
        $id          = (int)($_POST['id'] ?? 0);
        $nome        = trim($_POST['nomeservico'] ?? '');
        $descricao   = trim($_POST['descricao_servico'] ?? '');
        $preco       = (float)($_POST['preco_servico'] ?? 0);
        $desconto    = ($_POST['desconto_servico'] === '' ? null : (float)$_POST['desconto_servico']);
        $categoriaId = ($_POST['categoria_Servicos'] ?? '');

        if ($id <= 0) {
            redirect_with('../Paginas_Logista/cadastro_produtos_logista.html', ['erro_servico' => 'ID inválido para edição.']);
        }

        // Validações
        $erros = [];
        if ($nome === '') $erros[] = 'Informe o nome do serviço.';
        if ($descricao === '') $erros[] = 'Informe a descrição.';
        if ($preco <= 0) $erros[] = 'Informe um preço válido.';
        if ($categoriaId === '') $erros[] = 'Selecione uma categoria.';
        if ($erros) {
            redirect_with('../Paginas_Logista/cadastro_produtos_logista.html', ['erro_servico' => implode(' ', $erros)]);
        }

        // Atualiza serviço
        $sql = "UPDATE Servicos
                  SET nome = :nome,
                      descricao = :desc,
                      preco_servico = :preco,
                      desconto = :descnt,
                      Categorias_Servicos_idCategorias_Servicos = :cat
                WHERE idServicos = :id";
        $st = $pdo->prepare($sql);
        $st->bindValue(':id', $id, PDO::PARAM_INT);
        $st->bindValue(':nome', $nome, PDO::PARAM_STR);
        $st->bindValue(':desc', $descricao, PDO::PARAM_STR);
        $st->bindValue(':preco', $preco, PDO::PARAM_STR);
        $st->bindValue(':descnt', $desconto, $desconto === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $st->bindValue(':cat', (int)$categoriaId, PDO::PARAM_INT);
        $st->execute();

        // Inserir novas imagens dinamicamente
        foreach ($_FILES as $key => $file) {
            if (strpos($key, 'imgproduto') === 0) {
                $blob = readImageToBlob($file);
                if ($blob !== null) {
                    $sqlImg = "INSERT INTO Imagem_Servicos (foto, descricao, Servicos_idServicos)
                               VALUES (:foto, :desc, :idServ)";
                    $stmImg = $pdo->prepare($sqlImg);
                    $stmImg->bindValue(':foto', $blob, PDO::PARAM_LOB);
                    $stmImg->bindValue(':desc', $nome, PDO::PARAM_STR);
                    $stmImg->bindValue(':idServ', $id, PDO::PARAM_INT);
                    $stmImg->execute();
                }
            }
        }

        redirect_with('../Paginas_Logista/cadastro_produtos_logista.html', ['editar_servico' => 'ok']);

    } catch (Throwable $e) {
        redirect_with('../Paginas_Logista/cadastro_produtos_logista.html', ['erro_servico' => 'Erro ao editar: ' . $e->getMessage()]);
    }
}
?>
