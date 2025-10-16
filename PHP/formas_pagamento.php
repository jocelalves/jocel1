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

// LISTAR FORMAS DE PAGAMENTO
if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET["listar"])) {
    try {
        // Comando de listagem ajustado ao seu banco
        $sqllistar = "SELECT idFormas_Pagamento AS id, nome 
                      FROM Formas_Pagamento 
                      ORDER BY nome";

        $stmtlistar = $pdo->query($sqllistar);
        $listar = $stmtlistar->fetchAll(PDO::FETCH_ASSOC);

        $formato = isset($_GET["format"]) ? strtolower($_GET["format"]) : "option";

        if ($formato === "json") {
            header("Content-Type: application/json; charset=utf-8");
            echo json_encode(["ok" => true, "formas_pagamento" => $listar], JSON_UNESCAPED_UNICODE);
            exit;
        }

        // RETORNO PADRÃO (options)
        header("Content-Type: text/html; charset=utf-8");
        foreach ($listar as $lista) {
            $id   = (int)$lista["id"];
            $nome = htmlspecialchars($lista["nome"], ENT_QUOTES, "UTF-8");
            echo "<option value=\"{$id}\">{$nome}</option>\n";
        }
        exit;

    } catch (Throwable $e) {
        if (isset($_GET["format"]) && strtolower($_GET["format"]) === "json") {
            header("Content-Type: application/json; charset=utf-8", true, 500);
            echo json_encode(
                ["ok" => false, "error" => "Erro ao listar formas de pagamento", "detail" => $e->getMessage()],
                JSON_UNESCAPED_UNICODE
            );
        } else {
            header("Content-Type: text/html; charset=utf-8", true, 500);
            echo "<option disabled>Erro ao carregar formas de pagamento</option>";
        }
        exit;
    }
}

// CADASTRAR NOVA FORMA DE PAGAMENTO
try{
    if($_SERVER["REQUEST_METHOD"] !== "POST"){
        redirecWith("../Paginas_Logista/frete_pagamento_logista.html", ["erro"=> "Metodo inválido"]);
    }

    $nomepagamento = $_POST["nomepagamento"] ?? "";

    $erros_validacao = [];
    if(trim($nomepagamento) === ""){
        $erros_validacao[] = "Preencha o campo";
    }    

    if(!empty($erros_validacao)){
        redirecWith("../Paginas_Logista/frete_pagamento_logista.html", ["erro" => implode(", ", $erros_validacao)]);
    }

    $sql ="INSERT INTO Formas_Pagamento (nome) VALUES (:nomepagamento)";
    $inserir = $pdo->prepare($sql)->execute([
        ":nomepagamento" => $nomepagamento,
    ]);

    if($inserir){
        redirecWith("../Paginas_Logista/frete_pagamento_logista.html", ["cadastro" => "ok"]);
    }else{
        redirecWith("../Paginas_Logista/frete_pagamento_logista.html", ["erro" =>"Erro ao cadastrar no banco de dados"]);
    }

}catch(Exception $e){
    redirecWith("../Paginas_Logista/frete_pagamento_logista.html", ["erro" => "Erro no banco de dados: ".$e->getMessage()]);
}
?>
