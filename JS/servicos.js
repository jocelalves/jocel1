// ====================== LISTAR SERVIÇOS ======================
function listarServicos(tabelaId) {
  const tbody = document.getElementById(tabelaId);
  const url = '../PHP/cadastro_servicos.php?listar=1';
  let byId = new Map();

  const esc = s => (s || '').replace(/[&<>"']/g, c => ({
    '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'
  }[c]));

  const row = s => {
    const imgs = (s.imagens || []).map(img =>
      `<img src="data:image/*;base64,${img.foto}" class="img-fluid me-1 mb-1" style="max-height:60px;object-fit:cover;border-radius:4px;">`
    ).join('');

    byId.set(String(s.idServicos), s);

    return `
      <tr>
        <td>${esc(s.nome)}</td>
        <td>R$ ${s.preco.toFixed(2)}</td>
        <td>${s.desconto ?? '-'}</td>
        <td>${s.categoriaId ?? '-'}</td>
        <td>${esc(s.descricao)}</td>
        <td>${imgs || '-'}</td>
        <td class="text-end">
          <button class="btn btn-sm btn-warning btn-edit" data-id="${s.idServicos}">Editar</button>
          <button class="btn btn-sm btn-danger btn-delete" data-id="${s.idServicos}">Excluir</button>
        </td>
      </tr>
    `;
  };

  const carregar = () => {
    fetch(url, { cache: 'no-store' })
      .then(r => r.json())
      .then(data => {
        if (!data.length) {
          tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted">Nenhum serviço cadastrado.</td></tr>';
        } else {
          tbody.innerHTML = data.map(row).join('');
        }
      })
      .catch(err => {
        tbody.innerHTML = `<tr><td colspan="7" class="text-center text-danger">Erro: ${esc(err.message)}</td></tr>`;
      });
  };

  carregar(); // primeira carga

  // ====================== DELEGAÇÃO DE EVENTOS ======================
  tbody.addEventListener('click', ev => {
    const btn = ev.target.closest('button');
    if (!btn) return;
    const id = btn.getAttribute('data-id');
    const servico = byId.get(String(id));
    if (!servico) return alert('Serviço não encontrado');

    // --------- EDITAR ---------
    if (btn.classList.contains('btn-edit')) {
      preencherFormServico(servico);
    }

    // --------- EXCLUIR ---------
    if (btn.classList.contains('btn-delete')) {
      if (!confirm('Deseja realmente excluir este serviço?')) return;

      const fd = new FormData();
      fd.append('acao', 'excluir');
      fd.append('id', id);

      fetch('../PHP/cadastro_servicos.php', { method: 'POST', body: fd })
        .then(r => {
          if (!r.ok) throw new Error('Falha ao excluir');
          alert('Serviço excluído com sucesso!');
          carregar(); // recarregar tabela após exclusão
        })
        .catch(e => alert('Erro: ' + e.message));
    }
  });
}

// ====================== PREENCHER FORMULÁRIO ======================
// ====================== PREENCHER FORMULÁRIO PARA EDIÇÃO ======================
function preencherFormServico(s) {
  const form      = document.getElementById('formServico');
  const acaoInput = document.getElementById('acao');
  const idInput   = document.getElementById('idServico');

  // Inputs de formulário
  form.querySelector('input[name="nomeservico"]').value          = s.nome || '';
  form.querySelector('textarea[name="descricao_servico"]').value = s.descricao || '';
  form.querySelector('input[name="preco_servico"]').value        = s.preco || '';
  form.querySelector('input[name="desconto_servico"]').value     = s.desconto ?? '';
  const sel = form.querySelector('select[name="categoria_Servicos"]');
  if (sel) sel.value = s.categoriaId ?? '';

  idInput.value   = s.idServicos;
  acaoInput.value = 'atualizar';

  // ==================== Preview ====================
  const previewBox = document.getElementById('previewServico');
  previewBox.innerHTML = '';

  if (s.imagens && s.imagens.length) {
    s.imagens.forEach(imgObj => {
      const wrapper = document.createElement('div');
      wrapper.className = 'position-relative d-inline-block me-2 mb-2';
      wrapper.dataset.id = imgObj.id;

      // Imagem existente
      const img = document.createElement('img');
      img.src = `data:image/*;base64,${imgObj.foto}`;
      img.className = 'img-fluid';
      img.style.maxHeight = '120px';
      img.style.objectFit = 'contain';
      wrapper.appendChild(img);

      // Input para substituir a imagem
      const fileInput = document.createElement('input');
      fileInput.type = 'file';
      fileInput.accept = 'image/*';
      fileInput.className = 'form-control mt-1';
      fileInput.addEventListener('change', e => {
        const f = e.target.files[0];
        if (!f) return;
        const reader = new FileReader();
        reader.onload = ev => {
          img.src = ev.target.result;
          wrapper.dataset.nova = '1'; // marcar como alterada
        };
        reader.readAsDataURL(f);
      });
      wrapper.appendChild(fileInput);

      // Botão remover
      const btnRemove = document.createElement('button');
      btnRemove.type = 'button';
      btnRemove.innerHTML = '&times;';
      btnRemove.className = 'btn btn-sm btn-danger position-absolute top-0 end-0';
      btnRemove.style.padding = '0 6px';
      btnRemove.addEventListener('click', () => wrapper.remove());
      wrapper.appendChild(btnRemove);

      previewBox.appendChild(wrapper);
    });
  }

  // Limpar inputs de arquivos extras
  form.querySelectorAll('input[type="file"]').forEach(f => {
    if (!f.closest('#previewServico')) f.value = '';
  });

  // Botão de salvar altera o texto
  const btnSalvar = document.getElementById('btnSalvar');
  if (btnSalvar) {
    btnSalvar.textContent = 'Salvar alterações';
    btnSalvar.classList.remove('btn-primary');
    btnSalvar.classList.add('btn-success');
  }

  form.scrollIntoView({ behavior: 'smooth', block: 'start' });
}


// ====================== PREVIEW DE NOVAS IMAGENS ======================
document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('formServico');
  const previewBox = document.getElementById('previewServico');
  if (!form || !previewBox) return;

  form.addEventListener('change', e => {
    const input = e.target;
    if (input.type !== 'file' || !input.name.startsWith('imgproduto')) return;

    Array.from(input.files).forEach(file => {
      const reader = new FileReader();
      reader.onload = ev => {
        const wrapper = document.createElement('div');
        wrapper.className = 'position-relative d-inline-block me-2 mb-2';

        const img = document.createElement('img');
        img.src = ev.target.result;
        img.className = 'img-fluid';
        img.style.maxHeight = '120px';
        img.style.objectFit = 'contain';
        wrapper.appendChild(img);

        const btnRemove = document.createElement('button');
        btnRemove.type = 'button';
        btnRemove.innerHTML = '&times;';
        btnRemove.className = 'btn btn-sm btn-danger position-absolute top-0 end-0';
        btnRemove.style.padding = '0 6px';
        btnRemove.addEventListener('click', () => {
          wrapper.remove();
          input.value = ''; // limpar input se remover a imagem
        });
        wrapper.appendChild(btnRemove);

        previewBox.appendChild(wrapper);
      };
      reader.readAsDataURL(file);
    });
  });
});

// ====================== INICIALIZAÇÃO ======================
listarServicos('tbServicos');
