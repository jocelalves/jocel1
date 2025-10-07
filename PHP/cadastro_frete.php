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

    $bairro = trim($_POST["bairro"]);
    $valor = $_POST["valor"];
    $transportadora = trim($_POST["transportadora"]);

    if ($bairro === "" || $valor === "") {
        redirecWith("../paginas_logista/frete_pagamento_logista.html", ["erro" => "Preencha todos os campos obrigatórios"]);
    }

    // Evitar duplicidade do mesmo bairro
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM Frete WHERE bairro = :bairro");
    $stmt->execute([":bairro" => $bairro]);
    if ($stmt->fetchColumn() > 0) {
        redirecWith("../paginas_logista/frete_pagamento_logista.html", ["erro" => "Frete para esse bairro já cadastrado"]);
    }

    $sql = "INSERT INTO Frete (bairro, valor, transportadora) VALUES (:bairro, :valor, :transportadora)";
    $inserir = $pdo->prepare($sql)->execute([
        ":bairro" => $bairro,
        ":valor" => $valor,
        ":transportadora" => $transportadora ?: null
    ]);

    if ($inserir) {
        redirecWith("../paginas_logista/frete_pagamento_logista.html", ["cadastro" => "ok"]);
    } else {
        redirecWith("../paginas_logista/frete_pagamento_logista.html", ["erro" => "Erro ao cadastrar no banco"]);
    }

} catch (Exception $e) {
    redirecWith("../paginas_logista/frete_pagamento_logista.html", ["erro" => "Erro no banco: ".$e->getMessage()]);
}
?>
