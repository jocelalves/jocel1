<?php
require_once "conexao.php";

function redirecWith($url, $params = []) {
    if (!empty($params)) {
        $qs = http_build_query($params);
        $sep = (strpos($url,'?') === false) ? '?' : '&';
        $url .= $sep.$qs;
    }
    header("Location: $url");
    exit;
}

try {
    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        redirecWith("../paginas_logista/frete_pagamento_logista.html", ["erro" => "Método inválido"]);
    }

    $nomepagamento = trim($_POST["nomepagamento"]);

    if ($nomepagamento === "") {
        redirecWith("../paginas_logista/frete_pagamento_logista.html", ["erro" => "Preencha o campo nome"]);
    }

    // Verifica duplicidade
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM Formas_Pagamento WHERE nome = :nome");
    $stmt->execute([":nome" => $nomepagamento]);
    if ($stmt->fetchColumn() > 0) {
        redirecWith("../paginas_logista/frete_pagamento_logista.html", ["erro" => "Forma de pagamento já cadastrada"]);
    }

    // Inserção
    $sql = "INSERT INTO Formas_Pagamento (nome) VALUES (:nome)";
    $inserir = $pdo->prepare($sql)->execute([":nome" => $nomepagamento]);

    if ($inserir) {
        redirecWith("../paginas_logista/frete_pagamento_logista.html", ["cadastro" => "ok"]);
    } else {
        redirecWith("../paginas_logista/frete_pagamento_logista.html", ["erro" => "Erro ao cadastrar no banco"]);
    }

} catch (Exception $e) {
    redirecWith(".../paginas_logista/frete_pagamento_logista.html", ["erro" => "Erro no banco: ".$e->getMessage()]);
}
?>
