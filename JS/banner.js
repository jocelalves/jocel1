// ====================== FUNÇÃO AUXILIAR ======================
const esc = s => (s || '').replace(/[&<>"']/g, c => ({
  '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
}[c]));

// ====================== LISTAR BANNERS ======================
function listarBanners() {
  const tbody = document.getElementById('tabelaBanners');
  if (!tbody) return;

  fetch('../PHP/banners.php?listar=1&format=json', { cache: 'no-store' })
    .then(r => r.json())
    .then(d => {
      if (!d.ok) throw new Error(d.error || 'Erro ao listar banners');
      const banners = d.banners || [];
      tbody.innerHTML = banners.length
        ? banners.map(b => bannerRow(b)).join('')
        : `<tr><td colspan="6" class="text-center text-muted">Nenhum banner cadastrado.</td></tr>`;
    })
    .catch(err => {
      tbody.innerHTML = `<tr><td colspan="6" class="text-center text-danger">Falha ao carregar: ${esc(err.message)}</td></tr>`;
    });
}

// ====================== FUNÇÃO PARA GERAR LINHA DA TABELA ======================
function bannerRow(b) {
  return `
    <tr data-id="${b.idBanner}">
      <td>${b.imagem ? `<img src="${esc(b.imagem)}" alt="${esc(b.descricao)}" style="width:100px; height:auto;">` : 'Sem imagem'}</td>
      <td>${esc(b.descricao)}</td>
      <td>${b.link ? `<a href="${esc(b.link)}" target="_blank">Abrir link</a>` : ''}</td>
      <td>${esc(b.categoria)}</td>
      <td>${esc(b.validade)}</td>
      <td class="text-end">
        <button class="btn btn-sm btn-warning btn-editar-b" data-id="${b.idBanner}">Editar</button>
        <button class="btn btn-sm btn-danger btn-excluir-b" data-id="${b.idBanner}">Excluir</button>
      </td>
    </tr>`;
}



// ================= MODAL DE EDIÇÃO =================
const modalEditarBanner = new bootstrap.Modal(document.getElementById('modalEditarBanner'));
const editPreview = document.getElementById('edit-previewBanner');
const editPreviewText = document.getElementById('edit-previewText');
const editImgInput = document.getElementById('edit-imgbanner');

// Abrir modal e preencher dados
document.addEventListener('click', e => {
  const btn = e.target.closest('.btn-editar-b');
  if (!btn) return;

  const tr = btn.closest('tr');
  document.getElementById('edit-banner-id').value = btn.dataset.id;
  document.getElementById('edit-banner-descricao').value = tr.children[1].textContent;
  document.getElementById('edit-banner-link').value = tr.children[2].querySelector('a') ? tr.children[2].querySelector('a').textContent : '';
  document.getElementById('edit-banner-categoria').value = tr.children[3].textContent;
  document.getElementById('edit-banner-validade').value = tr.children[4].textContent;

  const imgTag = tr.children[0].querySelector('img');
  if (imgTag) {
    editPreview.src = imgTag.src;
    editPreview.style.display = 'block';
    editPreviewText.style.display = 'none';
  } else {
    editPreview.src = '';
    editPreview.style.display = 'none';
    editPreviewText.style.display = 'block';
  }

  editImgInput.value = '';
  modalEditarBanner.show();
});

// Preview da imagem no modal
editImgInput.addEventListener('change', () => {
  const file = editImgInput.files[0];
  if (file) {
    const reader = new FileReader();
    reader.onload = e => {
      editPreview.src = e.target.result;
      editPreview.style.display = 'block';
      editPreviewText.style.display = 'none';
    };
    reader.readAsDataURL(file);
  } else {
    editPreview.src = '';
    editPreview.style.display = 'none';
    editPreviewText.style.display = 'block';
  }
});

// Enviar edição via AJAX
document.getElementById('formEditarBanner').addEventListener('submit', e => {
  e.preventDefault();
  const formData = new FormData(e.target);
  formData.append('acao', 'atualizar');

  fetch('../PHP/banners.php', { method: 'POST', body: formData })
    .then(r => r.json())
    .then(d => {
      if (!d.ok) throw new Error(d.error || 'Erro ao atualizar banner');

      // Atualiza a linha da tabela
      const tr = document.querySelector(`tr[data-id="${d.banner.idBanner}"]`);
      if (tr) tr.outerHTML = bannerRow(d.banner);

      modalEditarBanner.hide();
      alert('Banner atualizado com sucesso!');
    })
    .catch(err => alert('Erro: ' + err.message));
});











// ====================== EXCLUIR BANNER ======================
function excluirBanner(id) {
  if (!confirm('Tem certeza que deseja excluir este banner?')) return;

  const formData = new FormData();
  formData.append('acao', 'excluir');
  formData.append('id', id);

  fetch('../PHP/banners.php', { method: 'POST', body: formData })
    .then(r => r.text())
    .then(() => {
      alert('Banner excluído com sucesso!');
      listarBanners();
    })
    .catch(err => alert('Erro ao excluir: ' + err.message));
}

// ====================== EDITAR BANNER ======================
function editarBanner(banner) {
  const form = document.getElementById('formBanner');
  form.querySelector('[name="descricao"]').value = banner.descricao;
  form.querySelector('[name="validade"]').value = banner.validade;
  form.querySelector('[name="link"]').value = banner.link || '';
  form.querySelector('[name="categoria"]').value = banner.categoria || '';
  form.dataset.editId = banner.idBanner;

  const preview = document.getElementById('previewBanner');
  const previewText = document.getElementById('previewText');
  if (banner.imagem) {
    preview.src = banner.imagem;
    preview.style.display = 'block';
    previewText.style.display = 'none';
  } else {
    preview.src = '';
    preview.style.display = 'none';
    previewText.style.display = 'block';
  }
}

// ====================== SUBMIT DO FORM ======================
document.getElementById('formBanner').addEventListener('submit', function(e) {
  e.preventDefault();
  const form = e.target;
  const formData = new FormData(form);

  // Se estiver editando
  if (form.dataset.editId) {
    formData.append('acao', 'atualizar');
    formData.append('id', form.dataset.editId);
  }

  fetch('../PHP/banners.php', { method: 'POST', body: formData })
    .then(r => r.json())
    .then(d => {
      if (!d.ok) throw new Error(d.error || 'Erro no cadastro/edição');
      
      alert(form.dataset.editId ? 'Banner atualizado!' : 'Banner cadastrado!');

      // Atualiza tabela dinamicamente
      if (form.dataset.editId) {
        // Atualiza linha existente
        const tr = document.querySelector(`tr[data-id="${form.dataset.editId}"]`);
        if (tr) {
          tr.outerHTML = bannerRow(d.banner);
        }
      } else {
        // Adiciona nova linha
        const tbody = document.getElementById('tabelaBanners');
        tbody.insertAdjacentHTML('beforeend', bannerRow(d.banner));
      }

      form.reset();
      delete form.dataset.editId;
      document.getElementById('previewBanner').style.display = 'none';
      document.getElementById('previewText').style.display = 'block';
    })
    .catch(err => alert('Erro: ' + err.message));
});

// ====================== DELEGAR CLICK NOS BOTÕES ======================
document.addEventListener('click', function(e) {
  if (e.target.matches('.btn-excluir-b') || e.target.closest('.btn-excluir-b')) {
    const btn = e.target.closest('.btn-excluir-b');
    excluirBanner(btn.dataset.id);
  }

  if (e.target.matches('.btn-editar-b') || e.target.closest('.btn-editar-b')) {
    const btn = e.target.closest('.btn-editar-b');
    const tr = btn.closest('tr');
    const banner = {
      idBanner: btn.dataset.id,
      descricao: tr.children[1].textContent,
      link: tr.children[2].querySelector('a') ? tr.children[2].querySelector('a').href : '',
      categoria: tr.children[3].textContent,
      validade: tr.children[4].textContent,
      imagem: tr.children[0].querySelector('img') ? tr.children[0].querySelector('img').src : null
    };
    editarBanner(banner);
  }
});

// ====================== INICIALIZAÇÃO ======================
document.addEventListener('DOMContentLoaded', () => {
  listarBanners("#tabelaBanners");
});
