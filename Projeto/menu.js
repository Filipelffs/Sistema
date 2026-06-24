document.addEventListener("DOMContentLoaded", function () {
  const body = document.body;

  if (body.classList.contains("login-body")) {
    return;
  }

  const appContainer = document.createElement("div");
  appContainer.className = "app-container";

  const mobileNavbar = document.createElement("div");
  mobileNavbar.className = "mobile-navbar";

  function getCookie(name) {
    const value = `; ${document.cookie}`;
    const parts = value.split(`; ${name}=`);
    if (parts.length === 2) return decodeURIComponent(parts.pop().split(';').shift());
    return null;
  }

  const userFoto = getCookie('usuario_foto') || window.USER_SESSION?.foto || '';
  const userNome = getCookie('usuario_nome') || window.USER_SESSION?.nome || 'Usuário';
  const userTipo = getCookie('usuario_tipo') || window.USER_SESSION?.tipo || 'veterinario';
  const isAdmin = userTipo === 'admin';

  const currentPath = decodeURIComponent(window.location.pathname.split("/").pop());
  const inVacinaFolder = window.location.pathname.includes('/vacina/');
  const prefix = inVacinaFolder ? "../" : "";

  let pageTitle = "Vacinação Animal";
  if (currentPath.includes("Dashboard")) pageTitle = "DASHBOARD";
  else if (currentPath.includes("cadastro_animal")) pageTitle = "CADASTRO DE ANIMAL";
  else if (currentPath.includes("lista_animal")) pageTitle = "LISTA DE ANIMAIS";
  else if (currentPath.includes("modulo_animal")) pageTitle = "MÓDULO ANIMAL";
  else if (currentPath.includes("ficha_animal")) pageTitle = "FICHA DO ANIMAL";
  else if (currentPath.includes("historico_de_vacinas")) pageTitle = "HISTÓRICO DE VACINAS";
  else if (currentPath.includes("lista_vacinas")) pageTitle = "ESTOQUE DE VACINAS E MEDICAÇÕES";
  else if (currentPath.includes("registro_aplicacao")) pageTitle = "REGISTRO DE APLICAÇÃO";
  else if (currentPath.includes("Lote")) pageTitle = "LOTES";
  else if (currentPath.includes("Relatorios")) pageTitle = "RELATÓRIOS";
  else if (currentPath.includes("Configuracoes")) pageTitle = "CONFIGURAÇÕES";
  else if (currentPath.includes("Usuarios")) pageTitle = "USUÁRIOS";
  else if (currentPath.includes("AcessoNegado")) pageTitle = "ACESSO RESTRITO";

  mobileNavbar.innerHTML = `
    <button class="btn-menu" id="toggleMobileMenu">
      <i class="bi bi-list"></i>
    </button>
    <h2 class="brand-title">${pageTitle}</h2>
    <div style="width: 24px;"></div>
  `;



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
        <li class="nav-item">
          <a class="nav-link ${currentPath.includes("Dashboard") ? "active" : ""}" href="${prefix}Dashboard.php">
            <i class="bi bi-grid-1x2-fill"></i> Dashboard
          </a>
        </li>
        
        <li class="nav-item">
          <a class="nav-link d-flex justify-content-between align-items-center ${currentPath.includes("animal") ? "active" : ""}" data-bs-toggle="collapse" href="#menuAnimais" role="button" aria-expanded="false">
            <span><i class="bi bi-plugin"></i> Animais</span>
            <i class="bi bi-chevron-down small"></i>
          </a>
          <div class="collapse ${currentPath.includes("animal") ? "show" : ""}" id="menuAnimais">
            <ul class="nav flex-column ms-3 mt-1">
              ${isAdmin ? `
              <li>
                <a class="nav-link py-1 px-3 ${currentPath.includes("cadastro_animal") ? "fw-bold text-white" : ""}" href="${prefix}cadastro_animal.php">
                  <i class="bi bi-plus-circle"></i> Cadastrar Animal
                </a>
              </li>
              ` : ''}
              <li>
                <a class="nav-link py-1 px-3 ${currentPath.includes("lista_animal") ? "fw-bold text-white" : ""}" href="${prefix}lista_animal.php">
                  <i class="bi bi-list-ul"></i> Lista de Animais
                </a>
              </li>
              <li>
                <a class="nav-link py-1 px-3 ${currentPath.includes("modulo_animal") ? "fw-bold text-white" : ""}" href="${prefix}modulo_animal.php">
                  <i class="bi bi-info-circle"></i> Módulo Animal
                </a>
              </li>
            </ul>
          </div>
        </li>

        <li class="nav-item">
          <a class="nav-link d-flex justify-content-between align-items-center ${currentPath.includes("Vacina") || currentPath.includes("vacina") || currentPath.includes("registro_aplicacao") ? "active" : ""}" data-bs-toggle="collapse" href="#menuSaude" role="button">
            <span><i class="bi bi-heart-pulse-fill"></i> Saúde</span>
            <i class="bi bi-chevron-down small"></i>
          </a>
          <div class="collapse ${currentPath.includes("Vacina") || currentPath.includes("vacina") || currentPath.includes("registro_aplicacao") || currentPath.includes("vacinacao") ? "show" : ""}" id="menuSaude">
            <ul class="nav flex-column ms-3 mt-1">
              <li>
                <a class="nav-link py-1 px-3 ${currentPath.includes("registro_aplicacao") ? "fw-bold text-white" : ""}" href="${prefix}registro_aplicacao.php">
                  <i class="bi bi-plus-circle"></i> Nova Aplicação
                </a>
              </li>
              <li>
                <a class="nav-link py-1 px-3 ${currentPath.includes("historico_de_vacinas") ? "fw-bold text-white" : ""}" href="${prefix}historico_de_vacinas.php">
                  <i class="bi bi-clock-history"></i> Histórico Vacinas
                </a>
              </li>
              <li>
                <a class="nav-link py-1 px-3 ${currentPath.includes("lista_vacinas") ? "fw-bold text-white" : ""}" href="${prefix}vacina/lista_vacinas.php">
                  <i class="bi bi-box"></i> Estoque (Vacina/Med)
                </a>
              </li>
            </ul>
          </div>
        </li>

        <li class="nav-item">
          <a class="nav-link ${currentPath.includes("Lote") ? "active" : ""}" href="${prefix}Lote.php">
            <i class="bi bi-tags-fill"></i> Lotes
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link ${currentPath.includes("Relatorios") ? "active" : ""}" href="${prefix}Relatorios.php">
            <i class="bi bi-file-earmark-bar-graph-fill"></i> Relatórios
          </a>
        </li>
        
        ${isAdmin ? `
        <li class="nav-item">
          <a class="nav-link ${currentPath.includes("Usuarios") ? "active" : ""}" href="${prefix}Usuarios.php">
            <i class="bi bi-people-fill"></i> Usuários
          </a>
        </li>
        ` : ''}

        <li class="nav-item">
          <a class="nav-link ${currentPath.includes("Configuracoes") ? "active" : ""}" href="${prefix}Configuracoes.php">
            <i class="bi bi-gear-fill"></i> Configurações
          </a>
        </li>
      </ul>
    </div>
    
    <div class="mt-auto">
      <div class="d-flex align-items-center gap-2 mb-3 p-2 bg-white bg-opacity-10 rounded">
        ${userFoto ? `
          <img src="${userFoto}" alt="Avatar" class="rounded-circle border" style="width: 38px; height: 38px; object-fit: cover; flex-shrink: 0;">
        ` : `
          <div class="avatar bg-white text-success rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 38px; height: 38px; font-size: 1.1rem; flex-shrink: 0;">
            ${userNome.charAt(0).toUpperCase()}
          </div>
        `}
        <div class="overflow-hidden" style="line-height: 1.2;">
          <div class="fw-bold text-truncate text-white" style="font-size: 0.85rem;" title="${userNome}">
            ${userNome}
          </div>
          <div class="text-white-50 text-truncate" style="font-size: 0.75rem;">
            ${isAdmin ? 'Administrador' : 'Veterinário'}
          </div>
        </div>
      </div>

      <a class="btn btn-danger w-100 rounded-pill d-flex align-items-center justify-content-center gap-2" href="${prefix}logout.php">
        <i class="bi bi-box-arrow-right"></i>
        <span>Sair</span>
      </a>
    </div>
  `;

  const contentWrapper = document.createElement("main");
  contentWrapper.className = "conteudo-principal";

  const children = Array.from(body.children);
  children.forEach(child => {
    if (child.tagName !== "SCRIPT" && child.tagName !== "LINK" && child.tagName !== "STYLE") {
      contentWrapper.appendChild(child);
    }
  });

  body.insertBefore(mobileNavbar, body.firstChild);
  appContainer.appendChild(sidebar);
  appContainer.appendChild(contentWrapper);
  body.appendChild(appContainer);

  const toggleBtn = document.getElementById("toggleMobileMenu");
  if (toggleBtn) {
    toggleBtn.addEventListener("click", function (e) {
      e.stopPropagation();
      sidebar.classList.toggle("active");
    });
  }

  document.addEventListener("click", function (e) {
    if (window.innerWidth <= 768) {
      if (!sidebar.contains(e.target) && e.target !== toggleBtn) {
        sidebar.classList.remove("active");
      }
    }
  });
});
