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
    $descricao = $_POST["descricao"];
    $preco_servico = (double)$_POST["preco_servico"];
    $desconto = (DOUBLE)$_POST["desconto"];
    $categoria = $_POST["categoria"];
    $prestador_responsavel = $_POST["prestador responsavel"];

    //criar as váriaveis das imagens

    $img1   = readImageToBlob($_FILES["imgproduto1"] ?? null);
    $img2   = readImageToBlob($_FILES["imgproduto2"] ?? null);
    $img3   = readImageToBlob($_FILES["imgproduto3"] ?? null);

    

    // VALIDANDO OS CAMPOS
  $erros_validacao = [];
  if ($nome === "" || $preco_servico <= 0 || $categoria ==="" || $prestador_responsavel ==="" || $preco_servico <=0 || $descricao === "") {
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
  $sqlServicos = "INSERT INTO servicos(nome, descriacao,preco_servico,desconto,categoria,prestador_responsavel)
   VALUES (:nome,:descricao,:preco_servico,:desconto,:categorias,:prestador_responsavel)";

   $stmServicos = $pdo -> prepare($sqlServicos);
  
   $inserirServicos= $stmServicos -> execute([
   ":nome"=>$nome;
   ":descricao"=>$descricao;
   ":preco_servico"=>$preco_servicos;
   ":desconto"=>$desconto;
   ":categoria"=>$categoria;
   ":prestador_responsavel"=>$prestador_responsavel;
     
]);

  if ($inserirServicos) {
    $pdo ->rollBack();
    redirecWith("../paginas_logista/cadastro_produtos_logista.html",
    [ "Erro" =>"Falha ao cadastrar produtos"]);
  }


  $idServico=(int)$pdo->lastInsertId();

  // inserir imagens
  $sqlImagens = "INSERT INTO Imagem_Produtos (foto)
  values (:imagem1),(:imagem2),(imagem3)";

  $stmImagens=$pdo -> prepare($sqlImagens);

  $InserirImagens= $stmImagens -> execute([
    ":imagem1"=> $img1,
    ":imagem2"=> $img2,
    ":imagem3"=> $img3,
  ]);
   //preparando o comando SQL para ser executado
  $stmImagens = $pdo -> prepare($sqlImagens);

   // Bind como log quando houver conteudo; se null, o pdo envia null corretamente.
   if ($img1!== null){
    $stmImagens -> bindParam (' :$img1', $img1, PDO::PARAM_LOB);

   }else{
    $stmImagens->BindValue('', $img1, null, PDO::PARAM_NULL);
   }


   if ($img2!== null){
    $stmImagens -> bindParam (' :$img2', $img2, PDO::PARAM_LOB);

   }else{
    $stmImagens->BindValue('', $img2, null, PDO::PARAM_NULL);
   }



   if ($img3!== null){
    $stmImagens -> bindParam (' :$img3', $img3, PDO::PARAM_LOB);

   }else{
    $stmImagens->BindValue('', $img3, null, PDO::PARAM_NULL);
   }

   $inserirImagens= $stmImagens->execute();
   




  // verirficar se o inserir imagens deu errado

  if ($inserirImagens) {
    $pdo ->rollBack();
    redirecWith("../paginas_logista/cadastro_produtos_logista.html",
    [ "Erro"=> "falha ao cadastrar imagens"]);
  }

  //caso tenha dado certo, capture o id da imagem cadastrada
  $idImg = (int) $pdo->lastInsertId();


  // vincular a imagem com o serviço
  $sqlVincularServicosImg = " INSERT INTO Servicos_has_Imagem_produtos
  (Servicos_idServicos,Imagem_produtos_idImagem_produtos) Values 
  (:idpro,idimg)";


  $stmVincularServicosImg= $pdo -> prepare("$sqlVincularServicosImg");
  
  



   



} catch(Exception $e){
  redirecWith("../paginas_logista/cadastro_produtos_logista.html",
      ["erro" => "Erro no banco de dados: "
      .$e->getMessage()]);
}



?>