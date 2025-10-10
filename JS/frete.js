const formFrete = document.getElementById('formFrete');
const tableBody = document.getElementById('freteTableBody');

async function carregarFretes() {
  const res = await fetch('listar_frete.php');
  const fretes = await res.json();
  atualizarTabela(fretes);
}

function atualizarTabela(fretes) {
  tableBody.innerHTML = '';
  fretes.forEach(f => {
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td>${f.id}</td>
      <td>${f.bairro}</td>
      <td>${f.valor}</td>
      <td>${f.transportadora || ''}</td>
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

formFrete.addEventListener('submit', async e => {
  e.preventDefault();
  const formData = new FormData(formFrete);
  const data = Object.fromEntries(formData.entries());

  const res = await fetch('cadastro_frete.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(data)
  });
  const result = await res.json();
  if (result.success) {
    formFrete.reset();
    carregarFretes();
  } else {
    alert(result.message);
  }
});

async function editar(frete) {
  const bairro = prompt('Editar bairro:', frete.bairro);
  const valor = prompt('Editar valor:', frete.valor);
  const transportadora = prompt('Editar transportadora:', frete.transportadora || '');

  if (!bairro || !valor) return;

  const res = await fetch('editar_frete.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ id: frete.id, bairro, valor, transportadora })
  });
  const result = await res.json();
  if (result.success) carregarFretes();
  else alert(result.message);
}

async function excluir(id) {
  if (!confirm('Deseja realmente excluir este frete?')) return;

  const res = await fetch('excluir_frete.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ id })
  });
  const result = await res.json();
  if (result.success) carregarFretes();
  else alert(result.message);
}

// Inicializa
carregarFretes();
