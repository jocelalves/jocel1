// ===================== FUNÇÃO AUXILIAR =====================
// Escapa caracteres especiais para evitar problemas de HTML
// Ex: '<' vira '&lt;', '>' vira '&gt;'
const esc = s => (s || '').replace(/[&<>"']/g, c => ({
  '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
}[c]));

// ===================== LISTAR CUPONS =====================
// Busca os cupons do PHP via GET ?listar=1 e preenche a tabela
async function listarCupons() {
  const tbody = document.getElementById('tbcupom');
  if (!tbody) return;

  try {
    const res = await fetch('../PHP/cupom.php?listar=1'); // Chama o PHP
    const data = await res.json(); // Converte a resposta para JSON
    tbody.innerHTML = ''; // Limpa tabela antes de preencher

    if (data.ok && data.cupons.length > 0) {
      data.cupons.forEach(c => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
          <td>${esc(c.idCupom)}</td>
          <td>${esc(c.nome)}</td>
          <td>${esc(c.valor)}</td>
          <td>${esc(c.data_validade)}</td>
          <td>${esc(c.quantidade)}</td>
          <td class="text-end">
            <button class="btn btn-sm btn-warning btn-editar" data-id="${c.idCupom}">Editar</button>
            <button class="btn btn-sm btn-danger btn-excluir" data-id="${c.idCupom}">Excluir</button>
          </td>
        `;
        tbody.appendChild(tr); // Adiciona a linha na tabela
      });
    }
  } catch (err) {
    console.error('Erro ao listar cupons:', err);
  }
}

// ===================== PREENCHER FORMULÁRIO PARA EDITAR =====================
// Preenche o formulário com os dados do cupom selecionado para edição
function preencherFormulario(cupom) {
  document.getElementById('nome-cupom').value = cupom.nome;
  document.getElementById('valor-cupom').value = cupom.valor;
  document.getElementById('validade-cupom').value = cupom.data_validade;
  document.getElementById('quantidade-cupom').value = cupom.quantidade;

  const form = document.querySelector('form');
  form.setAttribute('data-acao', 'atualizar'); // Define que é edição
  form.setAttribute('data-id', cupom.idCupom); // Salva o id do cupom
  form.querySelector('button[type="submit"]').textContent = 'Atualizar'; // Altera texto do botão
}

// ===================== CANCELAR EDIÇÃO =====================
// Reseta o formulário para o modo cadastrar
document.getElementById('cancelar-edicao').addEventListener('click', () => {
  const form = document.querySelector('form');
  form.removeAttribute('data-acao'); // Remove flag de atualizar
  form.removeAttribute('data-id');   // Remove id armazenado
  form.querySelector('button[type="submit"]').textContent = 'Cadastrar';
  form.reset(); // Limpa os campos
});

// ===================== EXCLUIR CUPOM =====================
// Envia POST para o PHP com acao=excluir e id do cupom
function excluirCupom(id) {
  if (!confirm('Tem certeza que deseja excluir este cupom?')) return;

  const formData = new FormData();
  formData.append('acao', 'excluir');
  formData.append('id', id);

  fetch('../PHP/cupom.php', { method: 'POST', body: formData })
    .then(res => {
      if (res.redirected) {
        window.location.href = res.url; // Segue o redirect do PHP
      }
    });
}

// ===================== EVENT DELEGATION =====================
// Captura cliques nos botões de editar ou excluir da tabela
document.getElementById('tbcupom').addEventListener('click', e => {
  const btn = e.target;
  const id = btn.dataset.id;
  if (!id) return;

  // Botão Editar
  if (btn.classList.contains('btn-editar')) {
    fetch('../PHP/cupom.php?listar=1') // Busca todos os cupons
      .then(res => res.json())
      .then(data => {
        if (data.ok) {
          const cupom = data.cupons.find(c => c.idCupom == id); // Procura o cupom clicado
          if (cupom) preencherFormulario(cupom); // Preenche o formulário
        }
      });
  }

  // Botão Excluir
  if (btn.classList.contains('btn-excluir')) {
    excluirCupom(id);
  }
});

// ===================== SUBMIT FORMULÁRIO =====================
// Envia o formulário para cadastrar ou atualizar
document.querySelector('form').addEventListener('submit', function(e) {
  e.preventDefault();

  const form = e.target;
  const acao = form.getAttribute('data-acao') || 'cadastrar'; // Define se é cadastro ou edição

  const formData = new FormData(form);

  if (acao === 'atualizar') {
    // Mapear campos do formulário para o que o PHP de atualizar espera
    formData.append('acao', 'atualizar');
    formData.append('id', form.getAttribute('data-id'));
    formData.append('nome', formData.get('CupomNome'));
    formData.append('valor', formData.get('valor_cupom'));
    formData.append('data_validade', formData.get('validade_cupom'));
    formData.append('quantidade', formData.get('quantidade_cupom'));
  } else {
    // Cadastro mantém os nomes originais
    formData.append('acao', 'cadastrar');
  }

  // Envia os dados para o PHP
  fetch('../PHP/cupom.php', { method: 'POST', body: formData })
    .then(res => {
      if (res.redirected) {
        window.location.href = res.url; // Segue o redirect do PHP
      }
    });
});

// ===================== INICIALIZAÇÃO =====================
// Executa a listagem de cupons assim que a página carrega
document.addEventListener('DOMContentLoaded', listarCupons);
