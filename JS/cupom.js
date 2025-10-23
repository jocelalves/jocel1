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
          <button class="btn btn-sm btn-warning btn-editar" data-id="${c.id}">Editar</button>
          <button class="btn btn-sm btn-danger btn-excluir" data-id="${c.id}">Excluir</button>
        </td>
      </tr>`;

    // Atualiza a tabela
    const atualizarTabela = () => {
      fetch(url, { cache: 'no-store' })
        .then(r => r.json())
        .then(d => {
          if (!d.ok) throw new Error(d.error || 'Erro ao listar cupons');
          const arr = d.cupons || [];
          tbody.innerHTML = arr.length
            ? arr.map(row).join('')
            : `<tr><td colspan="6" class="text-center text-muted">Nenhum cupom cadastrado.</td></tr>`;
          ativarBotoes();
        })
        .catch(err => {
          tbody.innerHTML = `<tr><td colspan="6" class="text-center text-danger">Falha ao carregar: ${esc(err.message)}</td></tr>`;
        });
    };

    // Ativa eventos de editar/excluir
    const ativarBotoes = () => {
      tbody.querySelectorAll('.btn-excluir').forEach(btn => {
        btn.onclick = () => {
          const id = btn.dataset.id;
          if (!confirm('Deseja realmente excluir este cupom?')) return;
          fetch('../PHP/cupom.php', {
            method: 'POST',
            headers: {'Content-Type':'application/x-www-form-urlencoded'},
            body: `acao=excluir&id=${id}`
          })
          .then(r => r.json())
          .then(d => {
            if (d.ok || d.excluir_cupom === 'ok') atualizarTabela();
            else alert('Erro ao excluir cupom: ' + (d.error||''));
          })
          .catch(err => alert('Erro ao excluir cupom: ' + err.message));
        };
      });

      tbody.querySelectorAll('.btn-editar').forEach(btn => {
        btn.onclick = () => {
          const id = btn.dataset.id;
          // Aqui você pode preencher o formulário com os dados do cupom para edição
          alert('Função de edição para cupom ID ' + id + ' pode ser implementada.');
        };
      });
    };

    // Primeira chamada para preencher a tabela
    atualizarTabela();
  });
}

listarCupons("tabelacupom");
