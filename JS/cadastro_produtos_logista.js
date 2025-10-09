
// (#) para id
// para classe (.)

(async() =>{
    // selecionando o elemento html da tela de cadastro de produtos
  const selec = document.querySelector("#srvCategoria");

  try{
    // criando a váriavel que guardar os dados vindo do php
const r= await fetch("../PHP/cadastrar_categorias.php?listar=1");
// se o retorno do php vinher falso, significa que não foi possivel listar dados.
    if (!r.ok) throw new Erro("Falha ao lista categorias!");

    selec.innerHTML = await r.text();
/*     se vier dados do php, ele joga as
       informações dentro do campo html em formato de texto
       innerHTML- inserir dados em elementos html
*/
   
  }catch(e){
    // se der errado na listagem, aparecera Erro ao cadastrar dentro do campo html
    selec.innerHTML ="<option disable> Erro ao carregar. </option>"
  }
    
})();


