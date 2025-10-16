const nomeCategoriaInput = document.getElementById('nomeCategoria');
  const cadastrarCategoriaBtn = document.getElementById('cadastrarCategoria');
  const categoriaSelect = document.getElementById('categoriaServico');

  cadastrarCategoriaBtn.addEventListener('click', () => {
    const nomeCategoria = nomeCategoriaInput.value.trim();

    if (nomeCategoria === "") {
      alert("Digite o nome da categoria!");
      return;
    }

    // Verifica se a categoria já existe no select
    const exists = Array.from(categoriaSelect.options).some(
      option => option.text.toLowerCase() === nomeCategoria.toLowerCase()
    );
    if (exists) {
      alert("Categoria já cadastrada!");
      return;
    }

    // Cria uma nova option e adiciona no select de serviços
    const option = document.createElement('option');
    option.value = nomeCategoria.toLowerCase().replace(/\s+/g, '-');
    option.text = nomeCategoria;
    categoriaSelect.appendChild(option);

    // Limpa o input
    nomeCategoriaInput.value = "";
  });