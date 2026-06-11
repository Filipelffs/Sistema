<?php
require_once "sessao.php";
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
  <script>
    window.USER_SESSION = {
      id: <?php echo json_encode($_SESSION['usuario_id']); ?>,
      nome: <?php echo json_encode($_SESSION['usuario_nome']); ?>,
      email: <?php echo json_encode($_SESSION['usuario_email']); ?>,
      tipo: <?php echo json_encode($_SESSION['usuario_tipo']); ?>
    };
  </script>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Status de Vacinação - Vacinação Animal</title>
  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <!-- CSS -->
  <link rel="stylesheet" href="style.css">
</head>

<body>

  <div class="topo-pagina">
    <div>
      <h2 class="titulo-pagina">Status de Imunização</h2>
      <p class="subtitulo">Acompanhe as aplicações concluídas, pendentes ou atrasadas de todo o rebanho</p>
    </div>
    <button class="btn btn-outline-success rounded-pill px-4 py-2 d-flex align-items-center gap-2" onclick="atualizarStatus()" id="btnAtualizarStatus">
      <i class="bi bi-arrow-clockwise"></i> Atualizar Status
    </button>
  </div>

  <!-- Legend Header Panel -->
  <div class="card card-premium mb-4">
    <div class="card-body py-3">
      <div class="row text-center align-items-center g-2">
        <div class="col-4">
          <span class="badge-status success w-100 py-2 fs-7"><i class="bi bi-check-circle-fill"></i> CONCLUÍDO</span>
        </div>
        <div class="col-4">
          <span class="badge-status warning w-100 py-2 fs-7"><i class="bi bi-exclamation-circle-fill"></i> PENDENTE</span>
        </div>
        <div class="col-4">
          <span class="badge-status danger w-100 py-2 fs-7"><i class="bi bi-x-circle-fill"></i> ATRASADA</span>
        </div>
      </div>
    </div>
  </div>

  <!-- Applications list grouped or shown individually with status indicators -->
  <div class="row g-3" id="listaVacinasStatus">
    <div class="col-12 text-center py-5">
      <div class="spinner-border text-success" role="status"></div>
    </div>
  </div>

  <!-- Bottom Trigger -->
  <div class="d-grid mt-4">
    <a href="Registro de Aplicação.php" class="btn btn-success btn-lg rounded-pill py-3 shadow-sm d-flex align-items-center justify-content-center gap-2">
      <i class="bi bi-plus-circle-fill"></i> Adicionar Vacina / Aplicação
    </a>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <!-- Common Menu System -->
  <script src="menu.js"></script>

  <script>
    let aplicacoesList = [];

    function calcularStatusAutomatico(apl) {
      if (apl.status_aplicacao === "Concluído") {
        return "Concluído";
      }

      const hoje = new Date();
      hoje.setHours(0, 0, 0, 0);

      const dataAplicacao = new Date(apl.data_aplicacao + "T00:00:00");

      if (dataAplicacao < hoje) {
        return "Atrasada";
      } else {
        return "Pendente";
      }
    }

    async function carregarStatus() {
      try {
        const response = await fetch('aplicacao_action.php?action=list');
        const result = await response.json();
        if (result.success) {
          aplicacoesList = result.data;
          renderizarLista(aplicacoesList);
        } else {
          document.getElementById('listaVacinasStatus').innerHTML = `<div class="col-12 alert alert-danger">${result.message}</div>`;
        }
      } catch (error) {
        document.getElementById('listaVacinasStatus').innerHTML = `<div class="col-12 alert alert-danger">Erro de conexão ao carregar status.</div>`;
      }
    }

    async function atualizarStatus() {
      let alterados = 0;
      const btn = document.getElementById("btnAtualizarStatus");
      btn.disabled = true;

      for (const apl of aplicacoesList) {
        const novoStatus = calcularStatusAutomatico(apl);
        if (apl.status_aplicacao !== novoStatus) {
          try {
            const res = await fetch('aplicacao_action.php', {
              method: 'POST',
              headers: { 'Content-Type': 'application/json' },
              body: JSON.stringify({
                action: 'edit',
                id_aplicacao: apl.id_aplicacao,
                dose: apl.dose,
                data_aplicacao: apl.data_aplicacao,
                tecnico: apl.tecnico,
                status: novoStatus,
                observacoes: apl.observacoes
              })
            });
            const result = await res.json();
            if (result.success) {
              alterados++;
            }
          } catch (e) {
            console.error("Erro ao atualizar status do item " + apl.id_aplicacao);
          }
        }
      }

      await carregarStatus();

      const textoOriginal = btn.innerHTML;
      btn.innerHTML = `<i class="bi bi-check-circle-fill"></i> ${alterados} status atualizado(s)`;
      btn.classList.remove("btn-outline-success");
      btn.classList.add("btn-success", "text-white");
      btn.disabled = false;

      setTimeout(() => {
        btn.innerHTML = textoOriginal;
        btn.classList.remove("btn-success", "text-white");
        btn.classList.add("btn-outline-success");
      }, 2500);
    }

    function renderizarLista(lista) {
      const container = document.getElementById("listaVacinasStatus");

      if (lista.length === 0) {
        container.innerHTML = `
          <div class="col-12 text-center py-5">
            <i class="bi bi-clipboard-x fs-1 text-muted d-block mb-3"></i>
            <h6 class="text-muted">Nenhuma aplicação registrada</h6>
          </div>
        `;
        return;
      }

      container.innerHTML = "";
      lista.forEach(apl => {
        const statusAtual = calcularStatusAutomatico(apl);

        let statusClass = "success";
        let badgeText = "Concluído";
        let badgeIcon = "bi-check-circle-fill";
        if (statusAtual === "Atrasada") {
          statusClass = "danger";
          badgeText = "Atrasada";
          badgeIcon = "bi-x-circle-fill";
        } else if (statusAtual === "Pendente") {
          statusClass = "warning";
          badgeText = "Pendente";
          badgeIcon = "bi-exclamation-circle-fill";
        }

        let imgUrl = "https://images.unsplash.com/photo-1570042225831-d98fa7577f1e?q=80&w=150";
        if (apl.especie && apl.especie.toLowerCase().includes("capri")) {
          imgUrl = "https://images.unsplash.com/photo-1524388680868-377a2e6bbb1c?q=80&w=150";
        }

        let formattedDate = apl.data_aplicacao;
        const parts = apl.data_aplicacao.split("-");
        if (parts.length === 3) {
          formattedDate = `${parts[2]}/${parts[1]}/${parts[0]}`;
        }

        container.innerHTML += `
          <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4 mb-2">
              <div class="card-body p-3">
                <div class="d-flex align-items-center">
                  <img src="${imgUrl}" class="rounded-circle me-3 border" width="55" height="55" style="object-fit: cover;">
                  <div class="flex-grow-1">
                    <h6 class="fw-bold mb-1">${apl.nome_animal || 'Sem Animal'} <small class="text-muted">(${apl.numero_brinco || '---'})</small></h6>
                    <small class="d-block text-muted">Item: <strong class="text-dark">${apl.produto_nome || 'Produto Removido'}</strong> | ${apl.dose || '1ª dose'}</small>
                    <small class="text-muted">Data prevista: ${formattedDate}</small>
                  </div>
                  <span class="badge-status ${statusClass} px-3 py-2 fw-bold text-center" style="min-width: 100px;">
                    <i class="bi ${badgeIcon} me-1"></i>${badgeText}
                  </span>
                </div>
              </div>
            </div>
          </div>
        `;
      });
    }

    document.addEventListener("DOMContentLoaded", function () {
      carregarStatus();
    });
  </script>
</body>

</html>