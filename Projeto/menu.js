document.addEventListener("DOMContentLoaded", function () {
  // Wrap existing body content in a layout structure if it has not been done already
  const body = document.body;

  // If the page is login or register, do not inject the menu
  if (body.classList.contains("login-body")) {
    return;
  }

  // Create layout wrapper elements
  const appContainer = document.createElement("div");
  appContainer.className = "app-container";

  // Mobile Top Navbar
  const mobileNavbar = document.createElement("div");
  mobileNavbar.className = "mobile-navbar";

  // Detect active page to show page title in header
  const currentPath = decodeURIComponent(window.location.pathname.split("/").pop());
  let pageTitle = "Vacinação Animal";
  if (currentPath.includes("Dashboard")) pageTitle = "DASHBOARD";
  else if (currentPath.includes("cadastro de animal")) pageTitle = "CADASTRO DE ANIMAL";
  else if (currentPath.includes("Lista de animal") || currentPath.includes("Module de animal")) pageTitle = "MÓDULO ANIMAL";
  else if (currentPath.includes("Ficha de animal")) pageTitle = "FICHA DE ANIMAL";
  else if (currentPath.includes("Lista de Vacinas")) pageTitle = "HISTÓRICO DE VACINAS";
  else if (currentPath.includes("Estoque_Vacinas")) pageTitle = "ESTOQUE VACINAS/MED.";
  else if (currentPath.includes("Cadastro de vacina")) pageTitle = "CADASTRO DE VACINA";
  else if (currentPath.includes("Cadastro de medicamento")) pageTitle = "CADASTRO DE MEDICAMENTOS";
  else if (currentPath.includes("Registro de Aplicação")) pageTitle = "REGISTRO DE APLICAÇÃO";
  else if (currentPath.includes("Vacinação")) pageTitle = "STATUS DE VACINAS";
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

  // Determine if user is admin
  const userTipo = (window.USER_SESSION && window.USER_SESSION.tipo) ? window.USER_SESSION.tipo : 'veterinario';
  const isAdmin = userTipo === 'admin';

  // Sidebar Menu Lateral
  const sidebar = document.createElement("nav");
  sidebar.className = "menu-lateral";
  sidebar.id = "sidebarMenu";

  sidebar.innerHTML = `
    <div>
      <a class="navbar-brand" href="Dashboard.php">
        <img src="img/logo2.png" alt="Logo" width="50" height="50" class="rounded-circle">
        <span>VACINAÇÃO ANIMAL</span>
      </a>
      <ul class="nav flex-column">
        <li class="nav-item">
          <a class="nav-link ${currentPath.includes("Dashboard") ? "active" : ""}" href="Dashboard.php">
            <i class="bi bi-grid-1x2-fill"></i> Dashboard
          </a>
        </li>
        
        <!-- Animais Accordion/Dropdown -->
        <li class="nav-item">
          <a class="nav-link d-flex justify-content-between align-items-center ${currentPath.includes("animal") ? "active" : ""}" data-bs-toggle="collapse" href="#menuAnimais" role="button" aria-expanded="false">
            <span><i class="bi bi-plugin"></i> Animais</span>
            <i class="bi bi-chevron-down small"></i>
          </a>
          <div class="collapse ${currentPath.includes("animal") ? "show" : ""}" id="menuAnimais">
            <ul class="nav flex-column ms-3 mt-1">
              ${isAdmin ? `
              <li>
                <a class="nav-link py-1 px-3 ${currentPath.includes("cadastro de animal") ? "fw-bold text-white" : ""}" href="cadastro de animal.php">
                  <i class="bi bi-plus-circle"></i> Cadastrar Animal
                </a>
              </li>
              ` : ''}
              <li>
                <a class="nav-link py-1 px-3 ${currentPath.includes("Lista de animal") ? "fw-bold text-white" : ""}" href="Lista de animal.php">
                  <i class="bi bi-list-ul"></i> Lista de Animais
                </a>
              </li>
              <li>
                <a class="nav-link py-1 px-3 ${currentPath.includes("Module de animal") ? "fw-bold text-white" : ""}" href="Module de animal.php">
                  <i class="bi bi-info-circle"></i> Módulo Animal
                </a>
              </li>
            </ul>
          </div>
        </li>

        <!-- Saúde Dropdown -->
        <li class="nav-item">
          <a class="nav-link d-flex justify-content-between align-items-center ${currentPath.includes("Vacina") || currentPath.includes("Aplicação") ? "active" : ""}" data-bs-toggle="collapse" href="#menuSaude" role="button">
            <span><i class="bi bi-heart-pulse-fill"></i> Saúde</span>
            <i class="bi bi-chevron-down small"></i>
          </a>
          <div class="collapse ${currentPath.includes("Vacina") || currentPath.includes("Aplicação") || currentPath.includes("Vacinação") ? "show" : ""}" id="menuSaude">
            <ul class="nav flex-column ms-3 mt-1">
              <li>
                <a class="nav-link py-1 px-3 ${currentPath.includes("Registro de Aplicação") ? "fw-bold text-white" : ""}" href="Registro de Aplicação.php">
                  <i class="bi bi-plus-circle"></i> Nova Aplicação
                </a>
              </li>
              <li>
                <a class="nav-link py-1 px-3 ${currentPath.includes("Estoque_Vacinas") ? "fw-bold text-white" : ""}" href="Estoque_Vacinas.php">
                  <i class="bi bi-box-seam"></i> Estoque Vacinas/Med.
                </a>
              </li>
              <li>
                <a class="nav-link py-1 px-3 ${currentPath.includes("Lista de Vacinas") ? "fw-bold text-white" : ""}" href="Lista de Vacinas.php">
                  <i class="bi bi-clock-history"></i> Histórico Vacinas
                </a>
              </li>
              <li>
                <a class="nav-link py-1 px-3 ${currentPath.includes("Vacinação") ? "fw-bold text-white" : ""}" href="Vacinação.php">
                  <i class="bi bi-clipboard2-check"></i> Status Vacinas
                </a>
              </li>
            </ul>
          </div>
        </li>

        <!-- Cadastros Dropdown -->
        <li class="nav-item">
          <a class="nav-link d-flex justify-content-between align-items-center ${currentPath.includes("Cadastro de") || currentPath.includes("medicamento") ? "active" : ""}" data-bs-toggle="collapse" href="#menuCadastros" role="button">
            <span><i class="bi bi-folder-fill"></i> Cadastros</span>
            <i class="bi bi-chevron-down small"></i>
          </a>
          <div class="collapse ${currentPath.includes("Cadastro de") || currentPath.includes("medicamento") ? "show" : ""}" id="menuCadastros">
            <ul class="nav flex-column ms-3 mt-1">
              <li>
                <a class="nav-link py-1 px-3 ${currentPath.includes("Cadastro de vacina") ? "fw-bold text-white" : ""}" href="Cadastro de vacina.php">
                  <i class="bi bi-virus"></i> Nova Vacina
                </a>
              </li>
              <li>
                <a class="nav-link py-1 px-3 ${currentPath.includes("Cadastro de medicamento") ? "fw-bold text-white" : ""}" href="Cadastro de medicamento.php">
                  <i class="bi bi-capsule"></i> Medicamentos
                </a>
              </li>
            </ul>
          </div>
        </li>

        <li class="nav-item">
          <a class="nav-link ${currentPath.includes("Lote") ? "active" : ""}" href="Lote.php">
            <i class="bi bi-tags-fill"></i> Lotes
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link ${currentPath.includes("Relatorios") ? "active" : ""}" href="Relatorios.php">
            <i class="bi bi-file-earmark-bar-graph-fill"></i> Relatórios
          </a>
        </li>
        
        <!-- Usuários CRUD (apenas para Admin) -->
        ${isAdmin ? `
        <li class="nav-item">
          <a class="nav-link ${currentPath.includes("Usuarios") ? "active" : ""}" href="Usuarios.php">
            <i class="bi bi-people-fill"></i> Usuários
          </a>
        </li>
        ` : ''}

        <li class="nav-item">
          <a class="nav-link ${currentPath.includes("Configuracoes") ? "active" : ""}" href="Configuracoes.php">
            <i class="bi bi-gear-fill"></i> Configurações
          </a>
        </li>
      </ul>
    </div>
    
    <div class="mt-auto">
      <!-- Exibição do Usuário Autenticado -->
      <div class="d-flex align-items-center gap-2 mb-3 p-2 bg-white bg-opacity-10 rounded">
        <div class="avatar bg-white text-success rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 38px; height: 38px; font-size: 1.1rem; flex-shrink: 0;">
          ${(window.USER_SESSION?.nome || 'U').charAt(0).toUpperCase()}
        </div>
        <div class="overflow-hidden" style="line-height: 1.2;">
          <div class="fw-bold text-truncate text-white" style="font-size: 0.85rem;" title="${window.USER_SESSION?.nome || 'Usuário'}">
            ${window.USER_SESSION?.nome || 'Usuário'}
          </div>
          <div class="text-white-50 text-truncate" style="font-size: 0.75rem;">
            ${isAdmin ? 'Administrador' : 'Veterinário'}
          </div>
        </div>
      </div>

      <a class="btn btn-danger w-100 rounded-pill d-flex align-items-center justify-content-center gap-2" href="logout.php">
        <i class="bi bi-box-arrow-right"></i>
        <span>Sair</span>
      </a>
    </div>
  `;

  // Create content area
  const contentWrapper = document.createElement("main");
  contentWrapper.className = "conteudo-principal";

  // Move existing elements (except scripts and link tags) to contentWrapper
  const children = Array.from(body.children);
  children.forEach(child => {
    if (child.tagName !== "SCRIPT" && child.tagName !== "LINK" && child.tagName !== "STYLE") {
      contentWrapper.appendChild(child);
    }
  });

  // Assemble the layout
  body.insertBefore(mobileNavbar, body.firstChild);
  appContainer.appendChild(sidebar);
  appContainer.appendChild(contentWrapper);
  body.appendChild(appContainer);

  // Responsive sidebar toggling behavior
  const toggleBtn = document.getElementById("toggleMobileMenu");
  if (toggleBtn) {
    toggleBtn.addEventListener("click", function (e) {
      e.stopPropagation();
      sidebar.classList.toggle("active");
    });
  }

  // Close sidebar clicking outside on mobile
  document.addEventListener("click", function (e) {
    if (window.innerWidth <= 768) {
      if (!sidebar.contains(e.target) && e.target !== toggleBtn) {
        sidebar.classList.remove("active");
      }
    }
  });
});

// Helper function to initialize seed data in LocalStorage if empty
(function initLocalStorageData() {
  if (!localStorage.getItem("usuario")) {
    localStorage.setItem("usuario", JSON.stringify({
      nome: "Jefferson Rayldo",
      email: "jeffersonsantos@gmail.com",
      foto: "https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?q=80&w=200",
      notificacoes: true,
      lembretes: true,
      validacao: false
    }));
  }

  if (!localStorage.getItem("animais")) {
    const defaultAnimais = [
      { id: 1, nome: "Mimosa", numero: "001", especie: "Bovino", raca: "Holandesa", sexo: "Fêmea", dataNascimento: "2024-03-12", pai: "Touro Nelore", mae: "Vaca Holandesa", lote: "LOTE 04C-01" },
      { id: 2, nome: "Paula", numero: "002", especie: "Bovino", raca: "Gir", sexo: "Fêmea", dataNascimento: "2024-06-20", pai: "Touro Holandês", mae: "Vaca Gir", lote: "LOTE 04C-01" },
      { id: 3, nome: "Aninha", numero: "003", especie: "Caprino", raca: "Anglonubiana", sexo: "Fêmea", dataNascimento: "2025-01-10", pai: "Bode Soberano", mae: "Cabra Mimosa", lote: "LOTE 05V-01" },
      { id: 4, nome: "Rajado", numero: "004", especie: "Bovino", raca: "Angus", sexo: "Macho", dataNascimento: "2024-02-15", pai: "Boi Supremo", mae: "Vaca Crioula", lote: "LOTE 02" }
    ];
    localStorage.setItem("animais", JSON.stringify(defaultAnimais));
  }

  if (!localStorage.getItem("vacinas")) {
    const defaultVacinas = [
      { id: 1, lote: "V-2026A", nome: "Raiva", tipo: "Vacina", intervalo: "1 ano", obs: "Dose anual obrigatória para bovinos." },
      { id: 2, lote: "V-2026B", nome: "Clostridiose", tipo: "Vacina", intervalo: "Única", obs: "Recomendada para bezerras." },
      { id: 3, lote: "V-2026C", nome: "Leptospirose", tipo: "Vacina", intervalo: "6 meses", obs: "Prevenção de abortos em vacas." },
      { id: 4, lote: "M-2026D", nome: "Febre Aftosa", tipo: "Vacina", intervalo: "6 meses", obs: "Campanha nacional de vacinação." }
    ];
    localStorage.setItem("vacinas", JSON.stringify(defaultVacinas));
  }

  if (!localStorage.getItem("medicamentos")) {
    const defaultMedicamentos = [
      { id: 1, nome: "Ivermectina", tipo: "Vermífugo", dose: "1ml para 50kg", via: "Subcutânea", intervalo: "3 meses", lote: "LOTE 28" },
      { id: 2, nome: "Terramicina", tipo: "Antibiótico", dose: "1ml para 10kg", via: "Intramuscular", intervalo: "Única", lote: "LOTE 53" }
    ];
    localStorage.setItem("medicamentos", JSON.stringify(defaultMedicamentos));
  }

  if (!localStorage.getItem("aplicacoes")) {
    const defaultAplicacoes = [
      { id: 1, animalId: 1, itemNome: "Raiva", tipo: "Vacina", dose: "2ml", data: "2026-03-12", status: "Concluído", tecnico: "Julia Silva", lote: "V-2026A", obs: "Aplicação de rotina." },
      { id: 2, animalId: 2, itemNome: "Febre Aftosa", tipo: "Vacina", dose: "5ml", data: "2026-05-19", status: "Pendente", tecnico: "Julia Silva", lote: "V-2026C", obs: "Aguardando agendamento." },
      { id: 3, animalId: 1, itemNome: "Clostridiose", tipo: "Vacina", dose: "5ml", data: "2026-05-10", status: "Atrasada", tecnico: "Julia Silva", lote: "V-2026B", obs: "Precisa aplicar urgente." },
      { id: 4, animalId: 3, itemNome: "Leptospirose", tipo: "Vacina", dose: "2ml", data: "2026-05-20", status: "Concluído", tecnico: "Julia Silva", lote: "V-2026C", obs: "Sem intercorrências." }
    ];
    localStorage.setItem("aplicacoes", JSON.stringify(defaultAplicacoes));
  }
})();
