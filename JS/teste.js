/* variavel: é como uma caixa que serve para
armazana informações. é um espaço no progama/compudador que armazena dados. */


/* o let e uma forma de chamar uma variavel para armazena alquem dado. EX: quarda um nome.
no caso eu vou quarda a o nome da variavel (let nomePessoa = "",)*/



/* let (forma de variavel mais nova)
   var (forma antiga de criar uma variavel)
*/


// Operações matematicas
let soma= 3+5; // 8
let subtracao= 3-5;// 2
let divisao= 3/5;// 5
let multiplicacao= 3*5;  //15

// poder mudar o valor dentro do código a qualquer momento
let nomevasiavel = 1; // Numero INTEIRO (INT)
let nomevasiavel2 = "jocel"; // tipo varchar
let nomevasiavel22 = 1.7; // Numeros quebrados (DOUBLE)
let nomevasiavel222 = true; // verdadeiro ou falso (Boolean)

// variavel constante, que não altera o valor
const CPF = "066.459.191-40";

// juntar texto

let nomeP ="jocel";
let sobrenome = "Alves";
let nomeCompleto = nomeP + sobrenome ;



// funções

/* funçãos sem parametro:
que não recebe dados dentro do () */

//função ela imprime ola mundo 
function imprimirMsg (){
    // console é utilizado para mostrar textos 
    console.log("Olá criolinhos!");
    console.log("Bem vindo" + nomeP);
}
// função com parametros
122,33
function somarValores (soma1,soma2){
    let soma = soma1 + soma2 ;
    console.log("O resultado da soma é :"+ soma);

}


imprimirMsg();
somarValores();




// calcular o imc
function imc(altura,peso,nomePessoa){
    let resultado = (altura/peso) *altura;
    console.log (nomePessoa+"O seu IMC é: +resuldado");

}

imc(1.80,70,"Rhauam");



// condicional 

/* *É uma ação que é executada com base no critério
- se chover irei ao cinema
- se fizer sol irei a praia */

// if - (SE) - Else (Senão)

let n1 = 15;
let n2 = 45;
// se n1 for mairo que 10
if(n1>10 ){
    console.log("Irei a praia!");
    
}else{
    console.log("fico em casa");
}
// se n1 for maior que 10 e n2 for menor que 40
if(n1>10 & n2<40 ){
    console.log("Irei a praia!");
    
}else{
    console.log("fico em casa");
}



if(n1>10 ||  n2<40 ){
    console.log("Irei a praia!");
    
}else{
    console.log("fico em casa");
}

/* se n1 for maior que 10 e n1 for maior que n2 e n2 for maior que 45 */


if(n1>10 & n1>n2 &  n2<40 ){
    console.log("Irei a praia!");
    
}else{
    console.log("fico em casa");
}



// if aninhado
// se n1 é maior que 12 E n2 maior que 48
if(n1>12 && n2>48){
    console.log("Irei à praia!");
// se n1 é maior ou igual a 15 E n2 menor que 45
}else if(n1>=15 && n2<45){
    console.log("Vou ao cinema!");
/* se n1 é maior que 14 E n2 igual a 45
   E
   se n2 for maior que n1 OU n1 maior ou igual a 15
*/
}else if((n1>14 && n2==45) & (n2>n1 || n1>=15)){
    console.log("Vou ao shopping!");
}
