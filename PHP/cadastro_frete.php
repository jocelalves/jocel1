<?php

// Conectando este arquivo ao banco de dados
require_once __DIR__ ."/conexao.php";

// função para capturar os dados passados de uma página a outra
function redirecWith($url,$params=[]){
    if(!empty($params)){
        $qs= http_build_query($params);
        $sep = (strpos($url,'?') === false) ? '?': '&';
        $url .= $sep . $qs;
    }
    header("Location:  $url");
    exit;
}

// LISTAR FRETES
if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET["listar"])) {
    try {
        // Ajuste do nome da tabela e ID para o banco ArrumaJa
        $sqllistar = "SELECT idFrete AS id, bairro, valor, transportadora
                      FROM Frete
                      ORDER BY bairro, valor";

        $stmtlistar = $pdo->query($sqllistar);
        $listar = $stmtlistar->fetchAll(PDO::FETCH_ASSOC);

        $formato = isset($_GET["format"]) ? strtolower($_GET["format"]) : "option";

        if ($formato === "json") {
            $saida = array_map(function ($item) {
                return [
                    "id"            => (int)$item["id"],
                    "bairro"        => $item["bairro"],
                    "valor"         => (float)$item["valor"],
                    "transportadora"=> $item["transportadora"],
                ];
            }, $listar);

            header("Content-Type: application/json; charset=utf-8");
            echo json_encode(["ok" => true, "fretes" => $saida], JSON_UNESCAPED_UNICODE);
            exit;
        }

        header("Content-Type: text/html; charset=utf-8");
        foreach ($listar as $lista) {
            $id     = (int)$lista["id"];
            $bairro = htmlspecialchars($lista["bairro"], ENT_QUOTES, "UTF-8");
            $transp = $lista["transportadora"] !== null && $lista["transportadora"] !== ""
                        ? " (" . htmlspecialchars($lista["transportadora"], ENT_QUOTES, "UTF-8") . ")"
                        : "";
            $valorFmt = number_format((float)$lista["valor"], 2, ",", ".");
            $label = "{$bairro}{$transp} - R$ {$valorFmt}";
            echo "<option value=\"{$id}\">{$label}</option>\n";
        }
        exit;

    } catch (Throwable $e) {
        if (isset($_GET["format"]) && strtolower($_GET["format"]) === "json") {
            header("Content-Type: application/json; charset=utf-8", true, 500);
            echo json_encode(
                ["ok" => false, "error" => "Erro ao listar fretes", "detail" => $e->getMessage()],
                JSON_UNESCAPED_UNICODE
            );
        } else {
            header("Content-Type: text/html; charset=utf-8", true, 500);
            echo "<option disabled>Erro ao carregar fretes</option>";
        }
        exit;
    }
}

// CADASTRAR FRETE
try{
    if($_SERVER["REQUEST_METHOD"] !== "POST"){
        redirecWith("../Paginas_Logista/frete_pagamento_logista.html",
           ["erro"=> "Metodo inválido"]);
    }

    $bairro = $_POST["bairro"];
    $valor = (double)$_POST["valor"];
    $transportadora = $_POST["transportadora"];

    $erros_validacao=[];
    if($bairro === "" || $valor === ""){
        $erros_validacao[]="Preencha todos os campos";
    }

    if(!empty($erros_validacao)){
        redirecWith("../Paginas_Logista/frete_pagamento_logista.html",
                    ["erro" => implode(", ", $erros_validacao)]);
    }

    // Ajuste do nome da tabela
    $sql ="INSERT INTO Frete (bairro, valor, transportadora)
           VALUES (:bairro, :valor, :transportadora)";
    $inserir = $pdo->prepare($sql)->execute([
        ":bairro" => $bairro,
        ":valor"=> $valor,
        ":transportadora"=> $transportadora,     
    ]);

    if($inserir){
        redirecWith("../Paginas_Logista/frete_pagamento_logista.html",
        ["cadastro" => "ok"]) ;
    }else{
        redirecWith("../Paginas_Logista/frete_pagamento_logista.html",
                    ["erro" =>"Erro ao cadastrar no banco de dados"]);
    }

}catch(\Exception $e){
    redirecWith("../Paginas_Logista/frete_pagamento_logista.html",
          ["erro" => "Erro no banco de dados: ".$e->getMessage()]);
}

?>
