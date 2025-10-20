function listarCupons(tbcupom) {
  document.addEventListener('DOMContentLoaded', () => {
    const tbody = document.getElementById(tbcupom);
    const url   = '../PHP/cupom.php?listar=1';

    // Escapa texto (evita injeção de HTML)
    const esc = s => (s || '').replace(/[&<>"']/g, c => ({
      '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'
    }[c]));

    // Converte data YYYY-MM-DD → DD/MM/YYYY
    const dtbr = iso => {
      if (!iso) return '-';
      const [y,m,d] = String(iso).split('-');
      return (y && m && d) ? `${d}/${m}/${y}` : '-';
    };

    // Monta a <tr> de cada cupom
    const row = c => `
      <tr>
        <td>${c.id}</td>
        <td>${esc(c.nome)}</td>
        <td>R$ ${parseFloat(c.valor).toFixed(2).replace('.', ',')}</td>
        <td>${dtbr(c.data_validade)}</td>
        <td>${c.quantidade}</td>
        <td class="text-end">
          <button class="btn btn-sm btn-warning" data-id="${c.id}">Editar</button>
          <button class="btn btn-sm btn-danger"  data-id="${c.id}">Excluir</button>
        </td>
      </tr>`;

    // Busca os dados e preenche a tabela
    fetch(url, { cache: 'no-store' })
      .then(r => r.json())
      .then(d => {
        if (!d.ok) throw new Error(d.error || 'Erro ao listar cupons');
        const arr = d.cupons || [];
        tbody.innerHTML = arr.length
          ? arr.map(row).join('')
          : `<tr><td colspan="6" class="text-center text-muted">Nenhum cupom cadastrado.</td></tr>`;
      })
      .catch(err => {
        tbody.innerHTML = `<tr><td colspan="6" class="text-center text-danger">Falha ao carregar: ${esc(err.message)}</td></tr>`;
      });
  });
}



listarCupons ("tabelacupom");