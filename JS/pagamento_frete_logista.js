const inputNome = document.getElementById('fpNome');
const formPagamento = document.getElementById('formPagamento');
const tableBody = document.getElementById('formasTableBody');

// Carrega dados do banco
async function carregarFormas() {
  const res = await fetch('listar_formas_pagamento.php');
  const formas = await res.json();
  atualizarTabela(formas);
}

// Atualiza tabela
function atualizarTabela(formas) {
  tableBody.innerHTML = '';
  formas.forEach(f => {
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td>${f.id}</td>
      <td>${f.nome}</td>
      <td class="text-end">
        <button class="btn btn-sm btn-outline-secondary editar">Editar</button>
        <button class="btn btn-sm btn-outline-danger excluir">Excluir</button>
      </td>
    `;
    tr.querySelector('.editar').addEventListener('click', () => editar(f));
    tr.querySelector('.excluir').addEventListener('click', () => excluir(f.id));
    tableBody.appendChild(tr);
  });
}

document.getElementById('cadastrar').addEventListener('click', async () => {
    const nome = document.getElementById('nome').value;

    if (!nome) {
        alert('Escreva um nome antes de cadastrar!');
        return;
    }

    const res = await fetch('cadastro_formas_pagamento.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `nome=${encodeURIComponent(nome)}`
    });

    const data = await res.json();

    if (data.success) {
        // Adiciona o brinquedo na lista da página
        const li = document.createElement('li');
        li.textContent = nome;
        document.getElementById('lista').appendChild(li);
        document.getElementById('nome').value = ''; // limpa o campo
    } else {
        alert(data.message);
    }
});


// Editar
async function editar(f) {
  const novoNome = prompt('Editar forma de pagamento:', f.nome);
  if (!novoNome || novoNome.trim() === '') return;

  const res = await fetch('editar_formas_pagamento.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({id: f.id, nome: novoNome.trim()})
  });
  const data = await res.json();
  if (data.success) carregarFormas();
  else alert(data.message);
}

// Excluir
async function excluir(id) {
  if (!confirm('Deseja realmente excluir esta forma de pagamento?')) return;

  const res = await fetch('excluir_formas_pagamento.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({id})
  });
  const data = await res.json();
  if (data.success) carregarFormas();
  else alert(data.message);
}

// Inicializa
carregarFormas();
