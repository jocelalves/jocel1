// função de listar banners em tabela
function listarBanners(tabelaBn) {
  document.addEventListener('DOMContentLoaded', () => {
    const tbody = document.getElementById(tabelaBn);
    const url = '../PHP/banners.php?listar=1&format=json'; // ajuste o caminho do seu PHP

    // Função para escapar caracteres especiais
    const esc = s => (s||'').replace(/[&<>"']/g, c => ({
      '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'
    }[c]));

    // Função que monta cada linha (<tr>) da tabela
    const row = b => `
      <tr>
        <td>
          ${b.imagem ? `<img src="${b.imagem}" alt="${esc(b.descricao)}" style="width:100px; height:auto;">` : 'Sem imagem'}
        </td>
        <td>${esc(b.descricao)}</td>
        <td>${b.link ? `<a href="${esc(b.link)}" target="_blank">Abrir link</a>` : ''}</td>
        <td>${esc(b.categoria)}</td>
        <td>${esc(b.validade)}</td>
        <td class="text-end">
          <button class="btn btn-sm btn-warning" data-id="${b.idBanner}">
            <i class="bi bg-primary "></i> Editar
          </button>
          <button class="btn btn-sm btn-danger" data-id="${b.idBanner}">
            <i class="bi bi-trash"></i> Excluir
          </button>
        </td>
      </tr>`;

    // Faz a requisição e preenche a tabela
    fetch(url, { cache: 'no-store' })
      .then(r => r.json())
      .then(d => {
        if (!d.ok) throw new Error(d.error || 'Erro ao listar banners');
        const banners = d.banners || [];
        tbody.innerHTML = banners.length
          ? banners.map(row).join('')
          : `<tr><td colspan="6" class="text-center text-muted">Nenhum banner cadastrado.</td></tr>`;
      })
      .catch(err => {
        tbody.innerHTML = `<tr><td colspan="6" class="text-center text-danger">Falha ao carregar: ${esc(err.message)}</td></tr>`;
      });
  });
}

// Chama a função passando o ID do <tbody>
listarBanners("tabelaBanners");
