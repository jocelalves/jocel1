<?php

//conexão
$host ="localhost";//servidor do banco
$db = "ArrumaJa"; // nome do banco de dados
$user = "root";// usuario do MySQL
$pass = ""; // senha do MySQL (ajuste se houver)

try{
    $pdo = new PDO("mysql:host=$host;dbname=$db;
    chatset=utf8mb4", $user, $pass);
    // verificado se deu certo ou não
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // imprimindo mensagem caso tenha dado certo
    echo "Conexão bem-sucedida!"; //(opicional para teste)
    
} catch(Exception $e){
   // caso dê erro, ele executa o catch e imprime a mensagem
   die("Erro ao conectar ao banco de dados:". 
   $e->getMessage());
}




?>