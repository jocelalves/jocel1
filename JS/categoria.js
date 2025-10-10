document.addEventListener("DOMContentLoaded", carregarCategorias);

const inputNome = document.getElementById("nomeCategoria");
const selectCat = document.getElementById("listaCategorias");
const btnCadastrar = document.getElementById("btnCadastrar");
const confirmEditar = document.getElementById("confirmEditar");
const confirmExcluir = document.getElementById("confirmExcluir");
const alerta = document.getElementById("alerta");

let categorias = []; // guarda a lista carregada para o autopreenchimento

function mostrarAlerta(mensagem, tipo = "success") {
  alerta.className = `alert alert-${tipo}`;
  alerta.textContent = mensagem;
  alerta.classList.remove("d-none");

  setTimeout(() => alerta.classList.add("d-none"), 3000);
}

function carregarCategorias() {
  fetch("../php/categorias_listar.php")
    .then(res => res.json())
    .then(data => {
      categorias = data;
      selectCat.innerHTML = '<option value="">Selecione...</option>';
      data.forEach(cat => {
        const opt = document.createElement("option");
        opt.value = cat.id;
        opt.textContent = cat.nome;
        selectCat.appendChild(opt);
      });
    });
}

// ✅ Preenche automaticamente o campo ao selecionar
selectCat.addEventListener("change", () => {
  const id = selectCat.value;
  const categoria = categorias.find(c => c.id === id);
  inputNome.value = categoria ? categoria.nome : "";
});

// 📌 Cadastrar nova categoria
btnCadastrar.addEventListener("click", () => {
  const nome = inputNome.value.trim();
  if (nome === "") return mostrarAlerta("Digite o nome da categoria!", "warning");

  fetch("../php/categorias_cadastrar.php", {
    method: "POST",
    body: new URLSearchParams({ nome })
  }).then(() => {
    inputNome.value = "";
    carregarCategorias();
    mostrarAlerta("Categoria cadastrada com sucesso!");
  });
});

// ✏️ Confirmar edição
confirmEditar.addEventListener("click", () => {
  const id = selectCat.value;
  const nome = inputNome.value.trim();
  if (!id || nome === "") return mostrarAlerta("Selecione e digite o novo nome!", "warning");

  fetch("../php/categorias_editar.php", {
    method: "POST",
    body: new URLSearchParams({ id, nome })
  }).then(() => {
    inputNome.value = "";
    carregarCategorias();
    mostrarAlerta("Categoria editada com sucesso!");
    bootstrap.Modal.getInstance(document.getElementById("modalEditar")).hide();
  });
});

// ❌ Confirmar exclusão
confirmExcluir.addEventListener("click", () => {
  const id = selectCat.value;
  if (!id) return mostrarAlerta("Selecione uma categoria para excluir!", "warning");

  fetch("../php/categorias_excluir.php", {
    method: "POST",
    body: new URLSearchParams({ id })
  }).then(() => {
    carregarCategorias();
    inputNome.value = "";
    mostrarAlerta("Categoria excluída com sucesso!", "danger");
    bootstrap.Modal.getInstance(document.getElementById("modalExcluir")).hide();
  });
});
