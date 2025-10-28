document.addEventListener('DOMContentLoaded', () => {
  const form  = document.getElementById('form-login');
  const cpfEl = document.getElementById('cpf');
  const senEl = document.getElementById('senha');

  const showMsg = (msg) => alert(msg); // Você pode trocar por toast

  form.addEventListener('submit', async (e) => {
    e.preventDefault();

    const cpf = (cpfEl.value || '').replace(/\D+/g, '').trim();
    const senha = (senEl.value || '').trim();

    if (!cpf || !senha) {
      showMsg('Preencha CPF e senha.');
      return;
    }

    try {
      const resp = await fetch('../PHP/login.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ cpf, senha })
      });

      if (!resp.ok) {
        showMsg('Erro ao conectar com o servidor.');
        return;
      }

      const data = await resp.json();

      if (data.ok) {
        window.location.href = data.redirect;
      } else {
        showMsg(data.msg || 'Credenciais inválidas.');
      }

    } catch (err) {
      console.error(err);
      showMsg('Erro interno de conexão.');
    }
  });
});
