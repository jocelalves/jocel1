<?php 
// Conectando este arquivo ao banco de dados
require_once __DIR__ ."/conexao.php";

// função para capturar os dados passados de uma página a outra
function redirecWith($url,$params=[]){
// verifica se os os paramentros não vieram vazios
 if(!empty($params)){
// separar os parametros em espaços diferentes
$qs= http_build_query($params);
$sep = (strpos($url,'?') === false) ? '?': '&';
$url .= $sep . $qs;
}
// joga a url para o cabeçalho no navegador
header("Location:  $url");
// fecha o script
exit;
}

/* Lê arquivo de upload como blob (ou null) */
function readImageToBlob(?array $file): ?string {
  if (!$file || !isset($file['tmp_name']) || $file['error']
  !== UPLOAD_ERR_OK) return null;
  $content = file_get_contents($file['tmp_name']);
  return $content === false ? null : $content;
}

try{
  // SE O METODO DE ENVIO FOR DIFERENTE DO POST
    if($_SERVER["REQUEST_METHOD"] !== "POST"){
        //VOLTAR À TELA DE CADASTRO E EXIBIR ERRO
        redirecWith("../paginas_logista/cadastro_produtos_logista.html",
           ["erro"=> "Metodo inválido"]);
    }

    // criar as váriaveis
    $nome = $_POST["nome"];
    $Duracao = $_POST["Duracao"];
    $preco_servico = $_POST["preco_servico"];
    $detalhes_adicionais = $_POST["detalhes_adicionais"];
    $desconto = $_POST["desconto"];
    $categoria = $_POST["categoria"];
    $prestador_responsavel = $_POST["prestador responsavel"];

    //criar as váriaveis das imagens

    $img1   = readImageToBlob($_FILES["imgproduto1"] ?? null);
    $img2   = readImageToBlob($_FILES["imgproduto2"] ?? null);
    $img3   = readImageToBlob($_FILES["imgproduto3"] ?? null);

    // VALIDANDO OS CAMPOS
  $erros_validacao = [];
  if ($nome === "" || $preco_servico === 0 || $categoria ==="" || $prestador_responsavel ==="" ) {
    $erros_validacao[] = "Preencha o nome da marca.";
  }
  // se houver erros, volta para a tela com a mensagem
  if (!empty($erros_validacao)) {
    redirecWith("../paginas_logista/cadastro_produtos_logista.html",
      ["erro_marca" => implode(" ", $erros_validacao)]);
  }

  // é utilizado para fazer vinculo de transações 
  $pdo ->beginTransaction();

  // COMANDO DE ISERIR DENTRO DA TABELA PRODUTOS
  $sqlServicos =
   



} catch(Exception $e){
  redirecWith("../paginas_logista/cadastro_produtos_logista.html",
      ["erro" => "Erro no banco de dados: "
      .$e->getMessage()]);
}



?>