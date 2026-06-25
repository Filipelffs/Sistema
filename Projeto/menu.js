document.addEventListener("DOMContentLoaded", function () {
  const body = document.body;

  if (body.classList.contains("login-body")) return;

  // ── Dados da sessão ───────────────────────────────────────
  function getCookie(name) {
    const value = `; ${document.cookie}`;
    const parts = value.split(`; ${name}=`);
    if (parts.length === 2) return decodeURIComponent(parts.pop().split(";").shift());
    return null;
  }

  const userFoto = getCookie("usuario_foto") || window.USER_SESSION?.foto || "";
  const userNome = getCookie("usuario_nome") || window.USER_SESSION?.nome || "Usuário";
  const userTipo = getCookie("usuario_tipo") || window.USER_SESSION?.tipo || "veterinario";
  const isAdmin  = userTipo === "admin";

  // ── Detecção de página ────────────────────────────────────
  const currentPath    = decodeURIComponent(window.location.pathname.split("/").pop());
  const inVacinaFolder = window.location.pathname.includes("/vacina/");
  const prefix         = inVacinaFolder ? "../" : "";

  // Helper: verifica se a página atual corresponde a alguma das chaves
  const isOn = (...keys) => keys.some(k => currentPath.includes(k));

  // Título da navbar mobile — mapa em vez de cadeia de else-if
  const PAGE_TITLES = {
    Dashboard:            "DASHBOARD",
    cadastro_animal:      "CADASTRO DE ANIMAL",
    lista_animal:         "LISTA DE ANIMAIS",
    modulo_animal:        "MÓDULO ANIMAL",
    ficha_animal:         "FICHA DO ANIMAL",
    historico_de_vacinas: "HISTÓRICO DE VACINAS",
    lista_vacinas:        "ESTOQUE DE VACINAS E MEDICAÇÕES",
    registro_aplicacao:   "REGISTRO DE APLICAÇÃO",
    cronograma:           "CRONOGRAMA DE VACINAÇÃO",
    Lote:                 "LOTES",
    Relatorios:           "RELATÓRIOS",
    Configuracoes:        "CONFIGURAÇÕES",
    Usuarios:             "USUÁRIOS",
    AcessoNegado:         "ACESSO RESTRITO",
  };
  const pageTitle = Object.entries(PAGE_TITLES).find(([key]) => currentPath.includes(key))?.[1] ?? "Vacinação Animal";

  // Estado dos submenus (calculado uma vez, reutilizado em vários lugares)
  const submenu = {
    animal: isOn("animal"),
    saude:  isOn("vacina", "Vacina", "registro_aplicacao", "cronograma", "historico_de_vacinas", "lista_vacinas"),
  };

  // ── Navbar Mobile ─────────────────────────────────────────
  const mobileNavbar = document.createElement("div");
  mobileNavbar.className = "mobile-navbar";
  mobileNavbar.innerHTML = `
    <button class="btn-menu" id="toggleMobileMenu">
      <i class="bi bi-list"></i>
    </button>
    <h2 class="brand-title">${pageTitle}</h2>
    <div style="width: 24px;"></div>
  `;

  // ── Sidebar ───────────────────────────────────────────────
  const sidebar = document.createElement("nav");
  sidebar.className = "menu-lateral";
  sidebar.id = "sidebarMenu";

  sidebar.innerHTML = `
    <div>
      <a class="navbar-brand" href="${prefix}Dashboard.php">
        <img src="${prefix}img/logo2.png" alt="Logo" width="40" height="40" class="rounded-circle">
        <span>VACINAÇÃO ANIMAL</span>
      </a>

      <ul class="nav flex-column">

        <!-- Dashboard -->
        <li class="nav-item">
          <a class="nav-link ${isOn("Dashboard") ? "active" : ""}" href="${prefix}Dashboard.php">
            <i class="bi bi-grid-1x2-fill"></i> Dashboard
          </a>
        </li>

        <!-- Animais -->
        <li class="nav-item">
          <a class="nav-link d-flex justify-content-between align-items-center ${submenu.animal ? "active" : ""}"
             data-bs-toggle="collapse" href="#menuAnimais" role="button"
             aria-expanded="${submenu.animal}" aria-controls="menuAnimais">
            <span><i class="bi bi-plugin"></i> Animais</span>
            <i class="bi bi-chevron-down small"></i>
          </a>
          <div class="collapse ${submenu.animal ? "show" : ""}" id="menuAnimais">
            <ul class="nav flex-column ms-3 mt-1">
              ${isAdmin ? `
              <li>
                <a class="nav-link py-1 px-3 ${isOn("cadastro_animal") ? "fw-bold text-white" : ""}" href="${prefix}cadastro_animal.php">
                  <i class="bi bi-plus-circle"></i> Cadastrar Animal
                </a>
              </li>` : ""}
              <li>
                <a class="nav-link py-1 px-3 ${isOn("lista_animal") ? "fw-bold text-white" : ""}" href="${prefix}lista_animal.php">
                  <i class="bi bi-list-ul"></i> Lista de Animais
                </a>
              </li>
              <li>
                <a class="nav-link py-1 px-3 ${isOn("modulo_animal") ? "fw-bold text-white" : ""}" href="${prefix}modulo_animal.php">
                  <i class="bi bi-info-circle"></i> Módulo Animal
                </a>
              </li>
            </ul>
          </div>
        </li>

        <!-- Saúde -->
        <li class="nav-item">
          <a class="nav-link d-flex justify-content-between align-items-center ${submenu.saude ? "active" : ""}"
             data-bs-toggle="collapse" href="#menuSaude" role="button"
             aria-expanded="${submenu.saude}" aria-controls="menuSaude">
            <span><i class="bi bi-heart-pulse-fill"></i> Saúde</span>
            <i class="bi bi-chevron-down small"></i>
          </a>
          <div class="collapse ${submenu.saude ? "show" : ""}" id="menuSaude">
            <ul class="nav flex-column ms-3 mt-1">
              <li>
                <a class="nav-link py-1 px-3 ${isOn("cronograma") ? "fw-bold text-white" : ""}" href="${prefix}cronograma.php">
                  <i class="bi bi-calendar-event"></i> Cronograma
                </a>
              </li>
              <li>
                <a class="nav-link py-1 px-3 ${isOn("registro_aplicacao") ? "fw-bold text-white" : ""}" href="${prefix}registro_aplicacao.php">
                  <i class="bi bi-plus-circle"></i> Nova Aplicação
                </a>
              </li>
              <li>
                <a class="nav-link py-1 px-3 ${isOn("historico_de_vacinas") ? "fw-bold text-white" : ""}" href="${prefix}historico_de_vacinas.php">
                  <i class="bi bi-clock-history"></i> Histórico Vacinas
                </a>
              </li>
              <li>
                <a class="nav-link py-1 px-3 ${isOn("lista_vacinas") ? "fw-bold text-white" : ""}" href="${prefix}vacina/lista_vacinas.php">
                  <i class="bi bi-box"></i> Estoque (Vacina/Med)
                </a>
              </li>
            </ul>
          </div>
        </li>

        <!-- Lotes -->
        <li class="nav-item">
          <a class="nav-link ${isOn("Lote") ? "active" : ""}" href="${prefix}Lote.php">
            <i class="bi bi-tags-fill"></i> Lotes
          </a>
        </li>

        <!-- Relatórios -->
        <li class="nav-item">
          <a class="nav-link ${isOn("Relatorios") ? "active" : ""}" href="${prefix}Relatorios.php">
            <i class="bi bi-file-earmark-bar-graph-fill"></i> Relatórios
          </a>
        </li>

        <!-- Usuários (admin only) -->
        ${isAdmin ? `
        <li class="nav-item">
          <a class="nav-link ${isOn("Usuarios") ? "active" : ""}" href="${prefix}Usuarios.php">
            <i class="bi bi-people-fill"></i> Usuários
          </a>
        </li>` : ""}

        <!-- Configurações -->
        <li class="nav-item">
          <a class="nav-link ${isOn("Configuracoes") ? "active" : ""}" href="${prefix}Configuracoes.php">
            <i class="bi bi-gear-fill"></i> Configurações
          </a>
        </li>

      </ul>
    </div>

    <!-- Perfil + Sair -->
    <div class="mt-auto">
      <div class="d-flex align-items-center gap-2 mb-3 p-2 bg-white bg-opacity-10 rounded">
        ${userFoto
          ? `<img src="${userFoto}" alt="Avatar" class="rounded-circle border" style="width:38px;height:38px;object-fit:cover;flex-shrink:0;">`
          : `<div class="avatar bg-white text-success rounded-circle d-flex align-items-center justify-content-center fw-bold"
                  style="width:38px;height:38px;font-size:1.1rem;flex-shrink:0;">
               ${userNome.charAt(0).toUpperCase()}
             </div>`
        }
        <div class="overflow-hidden" style="line-height:1.2;">
          <div class="fw-bold text-truncate text-white" style="font-size:0.85rem;" title="${userNome}">
            ${userNome}
          </div>
          <div class="text-white-50 text-truncate" style="font-size:0.75rem;">
            ${isAdmin ? "Administrador" : "Veterinário"}
          </div>
        </div>
      </div>

      <a class="btn btn-danger w-100 rounded-pill d-flex align-items-center justify-content-center gap-2"
         href="${prefix}logout.php">
        <i class="bi bi-box-arrow-right"></i>
        <span>Sair</span>
      </a>
    </div>
  `;

  // ── Monta a estrutura na página ───────────────────────────
  const appContainer = document.createElement("div");
  appContainer.className = "app-container";

  const contentWrapper = document.createElement("main");
  contentWrapper.className = "conteudo-principal";

  // Move os filhos existentes do body para o contentWrapper
  Array.from(body.children).forEach(child => {
    if (!["SCRIPT", "LINK", "STYLE"].includes(child.tagName)) {
      contentWrapper.appendChild(child);
    }
  });

  body.insertBefore(mobileNavbar, body.firstChild);
  appContainer.appendChild(sidebar);
  appContainer.appendChild(contentWrapper);
  body.appendChild(appContainer);

  // ── Toggle mobile ─────────────────────────────────────────
  const toggleBtn = document.getElementById("toggleMobileMenu");

  toggleBtn?.addEventListener("click", function (e) {
    e.stopPropagation();
    sidebar.classList.toggle("active");
  });

  document.addEventListener("click", function (e) {
    if (window.innerWidth <= 768 && !sidebar.contains(e.target) && e.target !== toggleBtn) {
      sidebar.classList.remove("active");
    }
  });
});
