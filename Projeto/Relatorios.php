<?php
require_once "sessao.php";
require_once "../Banco/conexao.php";
 
// ─── Fetch Lotes ─────────────────────────────────────────────────────────────
$sqlLotes = "SELECT id_lote, codigo_lote FROM lotes ORDER BY codigo_lote ASC";
$resLotes = $conn->query($sqlLotes);
$lotes = [];
if ($resLotes) { while ($r = $resLotes->fetch_assoc()) $lotes[] = $r; }
 
// ─── Fetch Espécies ───────────────────────────────────────────────────────────
$sqlEsp = "SELECT DISTINCT especie FROM animais WHERE especie IS NOT NULL ORDER BY especie";
$resEsp = $conn->query($sqlEsp);
$especies = [];
if ($resEsp) { while ($r = $resEsp->fetch_assoc()) $especies[] = $r['especie']; }
 
// ─── Fetch Veterinários ───────────────────────────────────────────────────────
$sqlVets = "SELECT DISTINCT tecnico FROM aplicacoes WHERE tecnico IS NOT NULL AND tecnico != '' ORDER BY tecnico";
$resVets = $conn->query($sqlVets);
$veterinarios = [];
if ($resVets) { while ($r = $resVets->fetch_assoc()) $veterinarios[] = $r['tecnico']; }
 
// ─── Fetch Vacinas ────────────────────────────────────────────────────────────
$sqlVacs = "SELECT id, nome FROM vacinas_medicamentos WHERE tipo='vacina' ORDER BY nome";
$resVacs = $conn->query($sqlVacs);
$vacinas = [];
if ($resVacs) { while ($r = $resVacs->fetch_assoc()) $vacinas[] = $r; }
 
// ─── KPI Summary ─────────────────────────────────────────────────────────────
$totalAnimais = $conn->query("SELECT COUNT(*) as c FROM animais")->fetch_assoc()['c'] ?? 0;
$emDia = $conn->query("SELECT COUNT(DISTINCT id_animal) as c FROM aplicacoes WHERE status_aplicacao='Concluído' AND (proxima_aplicacao IS NULL OR proxima_aplicacao >= CURDATE())")->fetch_assoc()['c'] ?? 0;
$pendentes = $conn->query("SELECT COUNT(DISTINCT id_animal) as c FROM aplicacoes WHERE status_aplicacao='Pendente'")->fetch_assoc()['c'] ?? 0;
$atrasadas = $conn->query("SELECT COUNT(DISTINCT id_animal) as c FROM aplicacoes WHERE status_aplicacao='Atrasada'")->fetch_assoc()['c'] ?? 0;
$vacinasMes = $conn->query("SELECT COUNT(*) as c FROM aplicacoes WHERE MONTH(data_aplicacao)=MONTH(CURDATE()) AND YEAR(data_aplicacao)=YEAR(CURDATE())")->fetch_assoc()['c'] ?? 0;
$cobertura = $totalAnimais > 0 ? round(($emDia / $totalAnimais) * 100) : 0;
 
// ─── Main Table Data ─────────────────────────────────────────────────────────
$sqlMain = "
 SELECT a.id_animal, a.nome_animal, a.numero_brinco, a.especie, a.raca,
 l.codigo_lote,
 v.nome AS ultima_vacina,
 ap.data_aplicacao, ap.proxima_aplicacao, ap.status_aplicacao, ap.tecnico
 FROM animais a
 LEFT JOIN lotes l ON a.id_lote = l.id_lote
 LEFT JOIN aplicacoes ap ON ap.id_aplicacao = (
 SELECT id_aplicacao FROM aplicacoes ap2
 WHERE ap2.id_animal = a.id_animal
 ORDER BY data_aplicacao DESC LIMIT 1
 )
 LEFT JOIN vacinas_medicamentos v ON ap.id_vacina_medicamento = v.id
 ORDER BY a.nome_animal ASC
 LIMIT 200";
$resMain = $conn->query($sqlMain);
$animaisData = [];
if ($resMain) { while ($r = $resMain->fetch_assoc()) $animaisData[] = $r; }
 
// ─── Sanidade por Lote ────────────────────────────────────────────────────────
$sqlLoteStats = "
 SELECT l.codigo_lote,
 COUNT(DISTINCT a.id_animal) AS total,
 COUNT(DISTINCT CASE WHEN ap.status_aplicacao='Concluído' THEN a.id_animal END) AS vacinados,
 COUNT(DISTINCT CASE WHEN ap.status_aplicacao IN ('Pendente','Atrasada') THEN a.id_animal END) AS pendentes
 FROM lotes l
 LEFT JOIN animais a ON a.id_lote = l.id_lote
 LEFT JOIN aplicacoes ap ON ap.id_animal = a.id_animal
 GROUP BY l.id_lote, l.codigo_lote
 ORDER BY l.codigo_lote";
$resLS = $conn->query($sqlLoteStats);
$loteStats = [];
if ($resLS) { while ($r = $resLS->fetch_assoc()) $loteStats[] = $r; }
 
// ─── Vacinas Mais Aplicadas ───────────────────────────────────────────────────
$sqlTop = "
 SELECT v.nome, COUNT(*) as qtd
 FROM aplicacoes ap
 JOIN vacinas_medicamentos v ON ap.id_vacina_medicamento = v.id
 WHERE v.tipo = 'vacina'
 GROUP BY v.id, v.nome ORDER BY qtd DESC LIMIT 8";
$resTop = $conn->query($sqlTop);
$topVacinas = [];
$totalAplicacoes = 0;
if ($resTop) { while ($r = $resTop->fetch_assoc()) { $topVacinas[] = $r; $totalAplicacoes += $r['qtd']; } }
 
// ─── Animais Atrasados ────────────────────────────────────────────────────────
$sqlAtrasados = "
 SELECT a.nome_animal, a.numero_brinco, v.nome AS vacina,
 ap.proxima_aplicacao, ap.tecnico,
 DATEDIFF(CURDATE(), ap.proxima_aplicacao) AS dias_atraso
 FROM aplicacoes ap
 JOIN animais a ON ap.id_animal = a.id_animal
 JOIN vacinas_medicamentos v ON ap.id_vacina_medicamento = v.id
 WHERE ap.status_aplicacao = 'Atrasada'
 ORDER BY dias_atraso DESC LIMIT 20";
$resAt = $conn->query($sqlAtrasados);
$atrasadosList = [];
if ($resAt) { while ($r = $resAt->fetch_assoc()) $atrasadosList[] = $r; }
 
// ─── Chart: Vacinas por Mês (últimos 6 meses) ──────────────────────────────
$sqlChart1 = "
 SELECT DATE_FORMAT(data_aplicacao,'%Y-%m') AS mes, COUNT(*) AS qtd
 FROM aplicacoes
 WHERE data_aplicacao >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
 GROUP BY mes ORDER BY mes";
$resC1 = $conn->query($sqlChart1);
$chartMeses = []; $chartQtds = [];
if ($resC1) { while ($r = $resC1->fetch_assoc()) { $chartMeses[] = $r['mes']; $chartQtds[] = (int)$r['qtd']; } }
 
// ─── Chart: Aplicações por Veterinário ───────────────────────────────────────
$sqlChartVet = "
 SELECT tecnico, COUNT(*) as qtd FROM aplicacoes
 WHERE tecnico IS NOT NULL AND tecnico != ''
 GROUP BY tecnico ORDER BY qtd DESC LIMIT 6";
$resCV = $conn->query($sqlChartVet);
$chartVetNomes = []; $chartVetQtds = [];
if ($resCV) { while ($r = $resCV->fetch_assoc()) { $chartVetNomes[] = $r['tecnico']; $chartVetQtds[] = (int)$r['qtd']; } }
?>
<!DOCTYPE html>
<html lang="pt-br" <?= $TEMA_ESCURO ? 'data-theme="dark"' : '' ?>>
<head>
 <script>
 window.USER_SESSION = {
 id: <?= json_encode($_SESSION['usuario_id'] ?? null) ?>,
 nome: <?= json_encode($_SESSION['usuario_nome'] ?? 'Usuário') ?>,
 email: <?= json_encode($_SESSION['usuario_email'] ?? '') ?>,
 tipo: <?= json_encode($_SESSION['usuario_tipo'] ?? 'veterinario') ?>
 };
 </script>
 <meta charset="UTF-8">
 <meta name="viewport" content="width=device-width, initial-scale=1.0">
 <title>Relatório de Sanidade Animal – Escola Fazenda</title>
 <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
 <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
 <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
 <link rel="stylesheet" href="style.css">
 
 <style>
 /* ── Relatório-specific tokens ──────────────────────────────────────────── */
 :root {
 --green-1: #1FAF7A;
 --green-2: #178B60;
 --green-3: #0d6e4c;
 --amber: #F59E0B;
 --red: #EF4444;
 --blue: #3B82F6;
 --purple: #8B5CF6;
 --surface: #ffffff;
 --bg: #F0F4F3;
 --border: rgba(0,0,0,.06);
 --shadow: 0 2px 16px rgba(0,0,0,.06);
 --shadow-hover: 0 6px 28px rgba(0,0,0,.12);
 --radius: 14px;
 --radius-sm: 8px;
 }
 
 /* ── Page header ────────────────────────────────────────────────────────── */
 .rel-hero {
 background: linear-gradient(135deg, var(--green-1) 0%, var(--green-3) 100%);
 border-radius: var(--radius);
 padding: 28px 32px;
 color: #fff;
 margin-bottom: 28px;
 position: relative;
 overflow: hidden;
 }
 .rel-hero::after {
 content: '\f489';
 font-family: 'Bootstrap Icons';
 position: absolute;
 right: 24px; top: 50%;
 transform: translateY(-50%);
 font-size: 7rem;
 opacity: .08;
 pointer-events: none;
 }
 .rel-hero h1 { font-size: 1.7rem; font-weight: 700; margin: 0 0 4px; }
 .rel-hero p { opacity: .88; margin: 0; font-size: .95rem; }
 
 /* ── Section heading ────────────────────────────────────────────────────── */
 .sec-heading {
 display: flex; align-items: center; gap: 10px;
 font-size: 1.05rem; font-weight: 700;
 color: var(--text-dark, #2C3E50);
 margin-bottom: 16px;
 }
 .sec-heading .dot {
 width: 6px; height: 32px;
 background: var(--green-1);
 border-radius: 3px;
 }
 
 /* ── Filter card ────────────────────────────────────────────────────────── */
 .filter-card {
 background: var(--surface);
 border-radius: var(--radius);
 box-shadow: var(--shadow);
 padding: 24px;
 margin-bottom: 28px;
 border: 1px solid var(--border);
 }
 .filter-card .form-label { font-size: .82rem; font-weight: 600; color: #555; margin-bottom: 6px; }
 .filter-card .form-control,
 .filter-card .form-select { border-radius: 9px; border: 1px solid #e0e0e0; font-size: .9rem; background: #fafafa; }
 .filter-card .form-control:focus,
 .filter-card .form-select:focus {
 border-color: var(--green-1);
 box-shadow: 0 0 0 3px rgba(31,175,122,.15);
 background: #fff;
 }
 
 /* ── KPI cards ──────────────────────────────────────────────────────────── */
 .kpi-grid { display: grid; grid-template-columns: repeat(6,1fr); gap: 16px; margin-bottom: 28px; }
 @media(max-width:1200px){ .kpi-grid{ grid-template-columns: repeat(3,1fr); } }
 @media(max-width:640px) { .kpi-grid{ grid-template-columns: repeat(2,1fr); } }
 
 .kpi-box {
 background: var(--surface);
 border-radius: var(--radius);
 padding: 20px 18px;
 box-shadow: var(--shadow);
 border: 1px solid var(--border);
 transition: transform .2s, box-shadow .2s;
 position: relative;
 overflow: hidden;
 }
 .kpi-box:hover { transform: translateY(-3px); box-shadow: var(--shadow-hover); }
 .kpi-box .kpi-icon {
 width: 46px; height: 46px; border-radius: 12px;
 display: flex; align-items: center; justify-content: center;
 font-size: 1.35rem; margin-bottom: 14px;
 }
 .kpi-box .kpi-val { font-size: 2.1rem; font-weight: 700; line-height: 1; margin-bottom: 4px; }
 .kpi-box .kpi-lbl { font-size: .8rem; color: #777; font-weight: 500; }
 .kpi-box .kpi-stripe {
 position: absolute; left: 0; top: 0; bottom: 0;
 width: 4px; border-radius: 14px 0 0 14px;
 }
 /* colour variants */
 .kpi-total .kpi-stripe { background: var(--blue); } .kpi-total .kpi-icon { background: #EFF6FF; color: var(--blue); } .kpi-total .kpi-val { color: var(--blue); }
 .kpi-emdia .kpi-stripe { background: var(--green-1);} .kpi-emdia .kpi-icon { background: #ECFDF5; color: var(--green-1);} .kpi-emdia .kpi-val { color: var(--green-1);}
 .kpi-pend .kpi-stripe { background: var(--amber); } .kpi-pend .kpi-icon { background: #FFFBEB; color: var(--amber); } .kpi-pend .kpi-val { color: var(--amber); }
 .kpi-atras .kpi-stripe { background: var(--red); } .kpi-atras .kpi-icon { background: #FEF2F2; color: var(--red); } .kpi-atras .kpi-val { color: var(--red); }
 .kpi-mes .kpi-stripe { background: var(--purple); } .kpi-mes .kpi-icon { background: #F5F3FF; color: var(--purple); } .kpi-mes .kpi-val { color: var(--purple); }
 .kpi-cob .kpi-stripe { background: var(--green-2);} .kpi-cob .kpi-icon { background: #ECFDF5; color: var(--green-2);} .kpi-cob .kpi-val { color: var(--green-2);}
 
 /* ── Generic panel card ─────────────────────────────────────────────────── */
 .panel {
 background: var(--surface);
 border-radius: var(--radius);
 box-shadow: var(--shadow);
 border: 1px solid var(--border);
 margin-bottom: 28px;
 overflow: hidden;
 }
 .panel-header {
 padding: 16px 22px;
 border-bottom: 1px solid #f0f0f0;
 display: flex; align-items: center; justify-content: space-between;
 flex-wrap: wrap; gap: 10px;
 }
 .panel-header h5 { margin: 0; font-size: .98rem; font-weight: 700; display: flex; align-items: center; gap: 8px; }
 .panel-body { padding: 20px 22px; }
 .panel-body.p0 { padding: 0; }
 
 /* ── Fancy table ────────────────────────────────────────────────────────── */
 .fancy-table { width: 100%; border-collapse: separate; border-spacing: 0; font-size: .875rem; }
 .fancy-table thead th {
 background: #F8FAFB; color: #555;
 font-weight: 600; font-size: .78rem; text-transform: uppercase;
 letter-spacing: .04em; padding: 12px 16px;
 border-bottom: 2px solid #eee; white-space: nowrap;
 }
 .fancy-table tbody tr { transition: background .15s; }
 .fancy-table tbody tr:hover { background: #F0FAF6; }
 .fancy-table tbody td { padding: 12px 16px; border-bottom: 1px solid #f4f4f4; vertical-align: middle; }
 .fancy-table tbody tr:last-child td { border-bottom: none; }
 
 /* ── Status badges ──────────────────────────────────────────────────────── */
 .stbadge {
 display: inline-flex; align-items: center; gap: 5px;
 padding: 4px 12px; border-radius: 20px;
 font-size: .78rem; font-weight: 600;
 }
 .stbadge-ok { background: #ECFDF5; color: #065F46; }
 .stbadge-pend { background: #FFFBEB; color: #92400E; }
 .stbadge-atras{ background: #FEF2F2; color: #991B1B; }
 .stbadge::before { content: ''; width: 7px; height: 7px; border-radius: 50%; background: currentColor; opacity: .7; }
 
 /* ── Action buttons ─────────────────────────────────────────────────────── */
 .btn-act { border: none; background: none; padding: 5px 9px; border-radius: 8px; cursor: pointer; font-size: .9rem; transition: background .15s; }
 .btn-act.view { color: var(--green-1); } .btn-act.view:hover { background: #ECFDF5; }
 .btn-act.print { color: #6B7280; } .btn-act.print:hover { background: #F3F4F6; }
 
 /* ── Lote cards ─────────────────────────────────────────────────────────── */
 .lote-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(230px,1fr)); gap: 16px; }
 .lote-card {
 background: var(--surface); border-radius: var(--radius);
 padding: 20px; box-shadow: var(--shadow);
 border: 1px solid var(--border); transition: transform .2s, box-shadow .2s;
 }
 .lote-card:hover { transform: translateY(-2px); box-shadow: var(--shadow-hover); }
 .lote-card .lote-name { font-weight: 700; font-size: 1rem; color: var(--green-2); margin-bottom: 14px; }
 .lote-stat { display: flex; justify-content: space-between; font-size: .84rem; margin-bottom: 5px; }
 .lote-stat span:first-child { color: #666; }
 .lote-stat span:last-child { font-weight: 600; }
 .lote-bar { height: 7px; border-radius: 10px; background: #E5E7EB; margin-top: 12px; overflow: hidden; }
 .lote-bar-fill { height: 100%; border-radius: 10px; background: linear-gradient(90deg, var(--green-1), var(--green-2)); transition: width .6s ease; }
 
 /* ── Top-vacinas table ──────────────────────────────────────────────────── */
 .rank-bar { height: 8px; border-radius: 6px; background: #E5E7EB; min-width: 80px; }
 .rank-bar-fill { height: 100%; border-radius: 6px; background: linear-gradient(90deg, var(--green-1), var(--blue)); }
 
 /* ── Atrasados cards ────────────────────────────────────────────────────── */
 .atras-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px,1fr)); gap: 16px; }
 .atras-card {
 background: #FEF2F2; border: 1px solid #FEE2E2;
 border-radius: var(--radius); padding: 18px;
 position: relative; overflow: hidden;
 }
 .atras-card::before {
 content: ''; position: absolute; left: 0; top: 0; bottom: 0;
 width: 4px; background: var(--red); border-radius: 14px 0 0 14px;
 }
 .atras-card .atras-animal { font-weight: 700; color: #991B1B; font-size: .97rem; }
 .atras-card .atras-detail { font-size: .83rem; color: #7F1D1D; margin-top: 4px; }
 .atras-card .atras-badge {
 display: inline-block; background: var(--red); color: #fff;
 border-radius: 20px; padding: 3px 10px; font-size: .75rem; font-weight: 700; margin-top: 8px;
 }
 
 /* ── Dashboard charts ───────────────────────────────────────────────────── */
 .chart-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 28px; }
 @media(max-width:900px){ .chart-grid{ grid-template-columns: 1fr; } }
 .chart-box { background: var(--surface); border-radius: var(--radius); box-shadow: var(--shadow); border: 1px solid var(--border); padding: 22px; }
 .chart-box canvas { max-height: 260px; }
 
 /* ── Individual animal modal ────────────────────────────────────────────── */
 .animal-modal-header {
 background: linear-gradient(135deg, var(--green-1), var(--green-3));
 color: #fff; border-radius: var(--radius) var(--radius) 0 0;
 padding: 24px; position: relative;
 }
 .animal-modal-header h4 { font-weight: 700; margin: 0 0 4px; }
 .animal-bio { display: grid; grid-template-columns: 1fr 1fr; gap: 6px 20px; margin-top: 14px; }
 .bio-item { font-size: .85rem; opacity: .92; }
 .bio-item strong { display: block; opacity: .7; font-size: .75rem; font-weight: 600; text-transform: uppercase; }
 
 .resumo-sanitario { display: grid; grid-template-columns: repeat(2,1fr); gap: 12px; margin-top: 16px; }
 @media(max-width:540px){ .resumo-sanitario{ grid-template-columns: 1fr; } }
 .resumo-item { background: #F8FAFB; border-radius: 10px; padding: 14px 16px; border: 1px solid #eee; }
 .resumo-item .r-label { font-size: .75rem; color: #888; font-weight: 600; text-transform: uppercase; }
 .resumo-item .r-val { font-size: .97rem; font-weight: 700; color: var(--text-dark,#2C3E50); margin-top: 3px; }
 
 /* ── Print styles ───────────────────────────────────────────────────────── */
 @media print {
 .menu-lateral, .mobile-navbar, .filter-card .btn, .no-print,
 .panel-header button, .btn-act, .btn, .chart-grid { display: none !important; }
 body, .conteudo-principal { background: #fff !important; }
 .panel, .kpi-box, .lote-card, .atras-card { box-shadow: none !important; break-inside: avoid; }
 .print-header { display: block !important; }
 .fancy-table tbody tr:hover { background: none !important; }
 }
 .print-header {
 display: none;
 text-align: center; margin-bottom: 24px;
 padding-bottom: 16px; border-bottom: 2px solid #1FAF7A;
 }
 .print-header h2 { color: #1FAF7A; font-weight: 700; }
 .print-header p { color: #555; font-size: .9rem; }
 
 /* ── Misc ───────────────────────────────────────────────────────────────── */
 .search-pill { border-radius: 30px; padding-left: 16px; padding-right: 16px; }
 .empty-state { text-align: center; padding: 40px 20px; color: #aaa; }
 .empty-state i { font-size: 2.5rem; display: block; margin-bottom: 10px; }
 .tag-especie { background: #EFF6FF; color: #1D4ED8; border-radius: 6px; padding: 2px 8px; font-size: .75rem; font-weight: 600; }
 .tag-lote { background: #F5F3FF; color: #5B21B6; border-radius: 6px; padding: 2px 8px; font-size: .75rem; font-weight: 600; }
 
 /* Tabs */
 .nav-tabs-custom { border-bottom: 2px solid #eee; margin-bottom: 24px; flex-wrap: nowrap; overflow-x: auto; }
 .nav-tabs-custom .nav-link { border: none; color: #777; font-weight: 600; padding: 10px 20px; border-bottom: 3px solid transparent; white-space: nowrap; }
 .nav-tabs-custom .nav-link.active { color: var(--green-1); border-bottom-color: var(--green-1); background: none; }
 .nav-tabs-custom .nav-link:hover { color: var(--green-1); background: none; }
 </style>
</head>
<body>
 
<!-- Print Header (visible only on print) -->
<div class="print-header">
 <h2>🐄 Escola Fazenda – Relatório de Sanidade Animal</h2>
 <p>
 Emitido em: <?= date('d/m/Y H:i') ?> &nbsp;|&nbsp;
 Responsável: <?= htmlspecialchars($_SESSION['usuario_nome'] ?? 'Usuário') ?>
 </p>
</div>
 
<!-- ══════════════════════════════════════════════════════
 HERO HEADER
══════════════════════════════════════════════════════ -->
<div class="rel-hero">
 <div class="d-flex align-items-start gap-3">
 <div>
 <h1><i class="bi bi-clipboard2-pulse-fill me-2"></i>Relatório de Sanidade Animal</h1>
 <p>Visualização completa da situação vacinal do rebanho e histórico sanitário da Escola Fazenda.</p>
 </div>
 </div>
 <div class="mt-3 d-flex flex-wrap gap-2 no-print">
 <button class="btn btn-light btn-sm rounded-pill px-3 fw-600" onclick="gerarRelatorio()">
 <i class="bi bi-search me-1"></i> Gerar Relatório
 </button>
 <button class="btn btn-outline-light btn-sm rounded-pill px-3" onclick="limparFiltros()">
 <i class="bi bi-x-circle me-1"></i> Limpar Filtros
 </button>
 <button class="btn btn-outline-light btn-sm rounded-pill px-3" onclick="imprimirPDF()">
 <i class="bi bi-printer-fill me-1"></i> Imprimir PDF
 </button>
 <button class="btn btn-outline-light btn-sm rounded-pill px-3" onclick="exportarExcel()">
 <i class="bi bi-file-earmark-excel-fill me-1"></i> Exportar Excel
 </button>
 </div>
</div>
 
<!-- ══════════════════════════════════════════════════════
 FILTROS
══════════════════════════════════════════════════════ -->
<div class="filter-card no-print">
 <div class="sec-heading mb-3">
 <div class="dot"></div>
 <span>Filtros de Pesquisa</span>
 </div>
 <div class="row g-3">
 <div class="col-6 col-md-3 col-lg-2">
 <label class="form-label">Data Inicial</label>
 <input type="date" id="filtroInicio" class="form-control">
 </div>
 <div class="col-6 col-md-3 col-lg-2">
 <label class="form-label">Data Final</label>
 <input type="date" id="filtroFim" class="form-control">
 </div>
 <div class="col-6 col-md-3 col-lg-2">
 <label class="form-label">Lote</label>
 <select id="filtroLote" class="form-select">
 <option value="">Todos os Lotes</option>
 <?php foreach($lotes as $l): ?>
 <option value="<?= htmlspecialchars($l['codigo_lote']) ?>"><?= htmlspecialchars($l['codigo_lote']) ?></option>
 <?php endforeach; ?>
 </select>
 </div>
 <div class="col-6 col-md-3 col-lg-2">
 <label class="form-label">Espécie</label>
 <select id="filtroEspecie" class="form-select">
 <option value="">Todas</option>
 <?php foreach($especies as $e): ?>
 <option value="<?= htmlspecialchars($e) ?>"><?= htmlspecialchars($e) ?></option>
 <?php endforeach; ?>
 </select>
 </div>
 <div class="col-6 col-md-3 col-lg-2">
 <label class="form-label">Vacina</label>
 <select id="filtroVacina" class="form-select">
 <option value="">Todas</option>
 <?php foreach($vacinas as $v): ?>
 <option value="<?= htmlspecialchars($v['nome']) ?>"><?= htmlspecialchars($v['nome']) ?></option>
 <?php endforeach; ?>
 </select>
 </div>
 <div class="col-6 col-md-3 col-lg-2">
 <label class="form-label">Veterinário</label>
 <select id="filtroVet" class="form-select">
 <option value="">Todos</option>
 <?php foreach($veterinarios as $vet): ?>
 <option value="<?= htmlspecialchars($vet) ?>"><?= htmlspecialchars($vet) ?></option>
 <?php endforeach; ?>
 </select>
 </div>
 <div class="col-6 col-md-3 col-lg-2">
 <label class="form-label">Situação</label>
 <select id="filtroStatus" class="form-select">
 <option value="">Todas</option>
 <option value="Concluído">Em Dia</option>
 <option value="Pendente">Pendente</option>
 <option value="Atrasada">Atrasada</option>
 </select>
 </div>
 <div class="col-12 col-md-6 col-lg-4">
 <label class="form-label">Busca por Animal</label>
 <div class="input-group">
 <span class="input-group-text bg-white border-end-0" style="border-radius:9px 0 0 9px; border-color:#e0e0e0;">
 <i class="bi bi-search text-muted"></i>
 </span>
 <input type="text" id="buscaAnimal" class="form-control border-start-0" placeholder="Nome ou Nº Brinco…" style="border-radius:0 9px 9px 0; border-color:#e0e0e0;">
 </div>
 </div>
 </div>
</div>
 
<!-- ══════════════════════════════════════════════════════
 KPI CARDS
══════════════════════════════════════════════════════ -->
<div class="kpi-grid">
 <div class="kpi-box kpi-total">
 <div class="kpi-stripe"></div>
 <div class="kpi-icon"><i class="bi bi-tags-fill"></i></div>
 <div class="kpi-val" id="kpiTotal"><?= $totalAnimais ?></div>
 <div class="kpi-lbl">Total de Animais</div>
 </div>
 <div class="kpi-box kpi-emdia">
 <div class="kpi-stripe"></div>
 <div class="kpi-icon"><i class="bi bi-check-circle-fill"></i></div>
 <div class="kpi-val" id="kpiEmDia"><?= $emDia ?></div>
 <div class="kpi-lbl">Vacinação em Dia</div>
 </div>
 <div class="kpi-box kpi-pend">
 <div class="kpi-stripe"></div>
 <div class="kpi-icon"><i class="bi bi-clock-fill"></i></div>
 <div class="kpi-val" id="kpiPend"><?= $pendentes ?></div>
 <div class="kpi-lbl">Vacinação Pendente</div>
 </div>
 <div class="kpi-box kpi-atras">
 <div class="kpi-stripe"></div>
 <div class="kpi-icon"><i class="bi bi-exclamation-triangle-fill"></i></div>
 <div class="kpi-val" id="kpiAtras"><?= $atrasadas ?></div>
 <div class="kpi-lbl">Vacinação Atrasada</div>
 </div>
 <div class="kpi-box kpi-mes">
 <div class="kpi-stripe"></div>
 <div class="kpi-icon"><i class="bi bi-syringe"></i></div>
 <div class="kpi-val" id="kpiMes"><?= $vacinasMes ?></div>
 <div class="kpi-lbl">Vacinas no Mês</div>
 </div>
 <div class="kpi-box kpi-cob">
 <div class="kpi-stripe"></div>
 <div class="kpi-icon"><i class="bi bi-shield-check"></i></div>
 <div class="kpi-val" id="kpiCob"><?= $cobertura ?>%</div>
 <div class="kpi-lbl">Cobertura Vacinal</div>
 </div>
</div>
 
<!-- ══════════════════════════════════════════════════════
 TABS
══════════════════════════════════════════════════════ -->
<ul class="nav nav-tabs-custom" id="relTabs">
 <li class="nav-item"><button class="nav-link active" data-tab="geral"><i class="bi bi-table me-1"></i>Relatório Geral</button></li>
 <li class="nav-item"><button class="nav-link" data-tab="lotes"><i class="bi bi-grid me-1"></i>Sanidade por Lote</button></li>
 <li class="nav-item"><button class="nav-link" data-tab="topvac"><i class="bi bi-bar-chart me-1"></i>Vacinas Aplicadas</button></li>
 <li class="nav-item"><button class="nav-link" data-tab="atrasados"><i class="bi bi-exclamation-circle me-1"></i>Atrasados</button></li>
 <li class="nav-item"><button class="nav-link" data-tab="dashboard"><i class="bi bi-graph-up me-1"></i>Dashboard</button></li>
</ul>
 
<!-- ═══════ TAB: GERAL ═══════ -->
<div id="tab-geral" class="tab-pane-content">
 <div class="panel">
 <div class="panel-header">
 <h5><i class="bi bi-table text-success me-2"></i> Relatório Geral de Sanidade</h5>
 <div class="d-flex align-items-center gap-2">
 <span class="badge bg-secondary rounded-pill" id="countRows"><?= count($animaisData) ?> registros</span>
 <button class="btn btn-outline-success btn-sm rounded-pill px-3" onclick="imprimirPDF()">
 <i class="bi bi-printer me-1"></i>Imprimir
 </button>
 </div>
 </div>
 <div class="panel-body p0">
 <div class="table-responsive">
 <table class="fancy-table" id="tabelaGeral">
 <thead>
 <tr>
 <th>Brinco</th>
 <th>Animal</th>
 <th>Espécie</th>
 <th>Raça</th>
 <th>Lote</th>
 <th>Última Vacina</th>
 <th>Dt. Aplicação</th>
 <th>Próx. Reforço</th>
 <th>Situação</th>
 <th>Responsável</th>
 <th class="no-print">Ações</th>
 </tr>
 </thead>
 <tbody id="tbodyGeral">
 <?php if (empty($animaisData)): ?>
 <tr><td colspan="11" class="empty-state"><i class="bi bi-inbox"></i>Nenhum animal encontrado.</td></tr>
 <?php else: ?>
 <?php foreach($animaisData as $a): ?>
 <?php
 $st = $a['status_aplicacao'] ?? 'Concluído';
 $cls = ($st === 'Concluído') ? 'stbadge-ok' : (($st === 'Atrasada') ? 'stbadge-atras' : 'stbadge-pend');
 $lbl = ($st === 'Concluído') ? 'Em Dia' : $st;
 ?>
 <tr
 data-brinco="<?= htmlspecialchars($a['numero_brinco'] ?? '') ?>"
 data-nome="<?= htmlspecialchars($a['nome_animal']) ?>"
 data-especie="<?= htmlspecialchars($a['especie'] ?? '') ?>"
 data-lote="<?= htmlspecialchars($a['codigo_lote'] ?? '') ?>"
 data-vacina="<?= htmlspecialchars($a['ultima_vacina'] ?? '') ?>"
 data-status="<?= htmlspecialchars($st) ?>"
 data-vet="<?= htmlspecialchars($a['tecnico'] ?? '') ?>"
 data-dt="<?= $a['data_aplicacao'] ?? '' ?>"
 data-id="<?= $a['id_animal'] ?>"
 >
 <td><span class="fw-600 text-secondary"><?= htmlspecialchars($a['numero_brinco'] ?? '—') ?></span></td>
 <td><span class="fw-600"><?= htmlspecialchars($a['nome_animal']) ?></span></td>
 <td><span class="tag-especie"><?= htmlspecialchars($a['especie'] ?? '—') ?></span></td>
 <td><?= htmlspecialchars($a['raca'] ?? '—') ?></td>
 <td><span class="tag-lote"><?= htmlspecialchars($a['codigo_lote'] ?? 'Sem Lote') ?></span></td>
 <td><?= htmlspecialchars($a['ultima_vacina'] ?? '—') ?></td>
 <td><?= $a['data_aplicacao'] ? date('d/m/Y', strtotime($a['data_aplicacao'])) : '—' ?></td>
 <td><?= $a['proxima_aplicacao'] ? date('d/m/Y', strtotime($a['proxima_aplicacao'])) : '—' ?></td>
 <td><span class="stbadge <?= $cls ?>"><?= $lbl ?></span></td>
 <td><?= htmlspecialchars($a['tecnico'] ?? '—') ?></td>
 <td class="no-print">
 <button class="btn-act view" title="Visualizar" onclick="abrirFichaAnimal(<?= $a['id_animal'] ?>)">
 <i class="bi bi-eye-fill"></i>
 </button>
 <button class="btn-act print" title="Imprimir Individual" onclick="imprimirIndividual(<?= $a['id_animal'] ?>)">
 <i class="bi bi-printer"></i>
 </button>
 </td>
 </tr>
 <?php endforeach; ?>
 <?php endif; ?>
 </tbody>
 </table>
 </div>
 </div>
 </div>
</div>
 
<!-- ═══════ TAB: LOTES ═══════ -->
<div id="tab-lotes" class="tab-pane-content d-none">
 <div class="sec-heading"><div class="dot"></div><span>Sanidade por Lote</span></div>
 <div class="lote-grid">
 <?php if (empty($loteStats)): ?>
 <div class="empty-state"><i class="bi bi-inbox"></i><br>Nenhum lote encontrado.</div>
 <?php else: ?>
 <?php foreach($loteStats as $ls):
 $cob = ($ls['total'] > 0) ? round(($ls['vacinados'] / $ls['total']) * 100) : 0;
 $cobCls = $cob >= 80 ? 'text-success' : ($cob >= 50 ? 'text-warning' : 'text-danger');
 ?>
 <div class="lote-card">
 <div class="lote-name"><i class="bi bi-grid-1x2 me-2 text-success"></i><?= htmlspecialchars($ls['codigo_lote']) ?></div>
 <div class="lote-stat"><span>Total de Animais</span><span><?= $ls['total'] ?></span></div>
 <div class="lote-stat"><span>Vacinados</span><span class="text-success fw-bold"><?= $ls['vacinados'] ?></span></div>
 <div class="lote-stat"><span>Pendentes</span><span class="text-warning fw-bold"><?= $ls['pendentes'] ?></span></div>
 <div class="lote-stat"><span>Cobertura</span><span class="fw-bold <?= $cobCls ?>"><?= $cob ?>%</span></div>
 <div class="lote-bar"><div class="lote-bar-fill" style="width:<?= $cob ?>%"></div></div>
 </div>
 <?php endforeach; ?>
 <?php endif; ?>
 </div>
</div>
 
<!-- ═══════ TAB: TOP VACINAS ═══════ -->
<div id="tab-topvac" class="tab-pane-content d-none">
 <div class="panel">
 <div class="panel-header">
 <h5><i class="bi bi-bar-chart-fill text-success me-2"></i> Vacinas Mais Aplicadas</h5>
 </div>
 <div class="panel-body p0">
 <table class="fancy-table">
 <thead>
 <tr>
 <th>#</th>
 <th>Vacina</th>
 <th>Quantidade</th>
 <th>Percentual</th>
 <th>Distribuição</th>
 </tr>
 </thead>
 <tbody>
 <?php if (empty($topVacinas)): ?>
 <tr><td colspan="5" class="empty-state"><i class="bi bi-inbox"></i>Sem dados.</td></tr>
 <?php else: ?>
 <?php foreach($topVacinas as $i => $tv):
 $pct = $totalAplicacoes > 0 ? round(($tv['qtd'] / $totalAplicacoes) * 100) : 0;
 ?>
 <tr>
 <td><span class="badge bg-secondary rounded-pill"><?= $i+1 ?></span></td>
 <td class="fw-600"><?= htmlspecialchars($tv['nome']) ?></td>
 <td><span class="fw-700 text-success"><?= $tv['qtd'] ?></span> aplicações</td>
 <td><?= $pct ?>%</td>
 <td style="min-width:140px;">
 <div class="rank-bar"><div class="rank-bar-fill" style="width:<?= $pct ?>%"></div></div>
 </td>
 </tr>
 <?php endforeach; ?>
 <?php endif; ?>
 </tbody>
 </table>
 </div>
 </div>
</div>
 
<!-- ═══════ TAB: ATRASADOS ═══════ -->
<div id="tab-atrasados" class="tab-pane-content d-none">
 <div class="sec-heading mb-3"><div class="dot"></div><span>Animais com Vacinação Atrasada</span></div>
 <?php if (empty($atrasadosList)): ?>
 <div class="panel"><div class="panel-body empty-state"><i class="bi bi-check-circle-fill text-success" style="font-size:2.5rem;"></i><br><strong class="d-block mt-2 text-success">Nenhum animal com atraso!</strong><span class="text-muted">Rebanho com vacinação em dia.</span></div></div>
 <?php else: ?>
 <div class="atras-grid">
 <?php foreach($atrasadosList as $at): ?>
 <div class="atras-card">
 <div class="atras-animal"><i class="bi bi-exclamation-triangle-fill me-2"></i><?= htmlspecialchars($at['nome_animal']) ?></div>
 <div class="atras-detail">
 <strong>Brinco:</strong> <?= htmlspecialchars($at['numero_brinco'] ?? '—') ?><br>
 <strong>Vacina:</strong> <?= htmlspecialchars($at['vacina']) ?><br>
 <strong>Data Prevista:</strong> <?= $at['proxima_aplicacao'] ? date('d/m/Y', strtotime($at['proxima_aplicacao'])) : '—' ?><br>
 <strong>Responsável:</strong> <?= htmlspecialchars($at['tecnico'] ?? '—') ?>
 </div>
 <div class="atras-badge"><i class="bi bi-clock me-1"></i><?= $at['dias_atraso'] ?> dias de atraso</div>
 </div>
 <?php endforeach; ?>
 </div>
 <?php endif; ?>
</div>
 
<!-- ═══════ TAB: DASHBOARD ═══════ -->
<div id="tab-dashboard" class="tab-pane-content d-none">
 <div class="chart-grid">
 <div class="chart-box">
 <div class="sec-heading mb-3"><div class="dot"></div><span>Vacinas Aplicadas por Mês</span></div>
 <canvas id="chartMes"></canvas>
 </div>
 <div class="chart-box">
 <div class="sec-heading mb-3"><div class="dot"></div><span>Status Vacinal do Rebanho</span></div>
 <canvas id="chartStatus"></canvas>
 </div>
 <div class="chart-box">
 <div class="sec-heading mb-3"><div class="dot"></div><span>Cobertura por Lote (%)</span></div>
 <canvas id="chartLote"></canvas>
 </div>
 <div class="chart-box">
 <div class="sec-heading mb-3"><div class="dot"></div><span>Aplicações por Veterinário</span></div>
 <canvas id="chartVet"></canvas>
 </div>
 </div>
</div>
 
<!-- ══════════════════════════════════════════════════════
 MODAL – FICHA INDIVIDUAL
══════════════════════════════════════════════════════ -->
<div class="modal fade" id="modalFicha" tabindex="-1" aria-hidden="true">
 <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
 <div class="modal-content border-0 shadow-lg" style="border-radius:var(--radius);">
 <div id="fichaContent">
 <div class="animal-modal-header">
 <div class="d-flex justify-content-between align-items-start">
 <div>
 <div style="font-size:.8rem;opacity:.75;text-transform:uppercase;font-weight:600;">Relatório Individual</div>
 <h4 id="fichaTitle">–</h4>
 <div style="font-size:.85rem;opacity:.8;" id="fichaBrinco">Brinco: –</div>
 </div>
 <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
 </div>
 <div class="animal-bio" id="fichaBio"></div>
 </div>
 <div class="modal-body p-4">
 <div class="sec-heading mb-3"><div class="dot"></div><span>Histórico de Vacinação</span></div>
 <div class="table-responsive mb-4">
 <table class="fancy-table" id="fichaTabela">
 <thead>
 <tr>
 <th>Vacina / Medicamento</th>
 <th>Aplicação</th>
 <th>Próx. Reforço</th>
 <th>Situação</th>
 <th>Responsável</th>
 </tr>
 </thead>
 <tbody id="fichaTbody">
 <tr><td colspan="5" class="empty-state"><i class="bi bi-hourglass-split"></i>Carregando…</td></tr>
 </tbody>
 </table>
 </div>
 <div class="sec-heading mb-3"><div class="dot"></div><span>Resumo Sanitário</span></div>
 <div class="resumo-sanitario" id="fichaResumo"></div>
 </div>
 <div class="modal-footer border-0">
 <button class="btn btn-success rounded-pill px-4" onclick="imprimirFichaAtual()">
 <i class="bi bi-printer-fill me-2"></i>Imprimir Relatório Individual
 </button>
 <button class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Fechar</button>
 </div>
 </div>
 </div>
 </div>
</div>
 
<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script src="menu.js"></script>
 
<script>
// ── PHP → JS data ──────────────────────────────────────────────────────────
const PHP_DATA = {
 chartMeses: <?= json_encode($chartMeses) ?>,
 chartQtds: <?= json_encode($chartQtds) ?>,
 chartVetNomes: <?= json_encode($chartVetNomes) ?>,
 chartVetQtds: <?= json_encode($chartVetQtds) ?>,
 loteStats: <?= json_encode($loteStats) ?>,
 kpi: {
 emDia: <?= $emDia ?>,
 pend: <?= $pendentes ?>,
 atras: <?= $atrasadas ?>
 }
};
 
// ── Tabs ──────────────────────────────────────────────────────────────────
document.querySelectorAll('[data-tab]').forEach(btn => {
 btn.addEventListener('click', () => {
 document.querySelectorAll('[data-tab]').forEach(b => b.classList.remove('active'));
 document.querySelectorAll('.tab-pane-content').forEach(p => p.classList.add('d-none'));
 btn.classList.add('active');
 document.getElementById('tab-' + btn.dataset.tab).classList.remove('d-none');
 if (btn.dataset.tab === 'dashboard') initCharts();
 });
});
 
// ── Filtros ───────────────────────────────────────────────────────────────
function gerarRelatorio() {
 const ini = document.getElementById('filtroInicio').value;
 const fim = document.getElementById('filtroFim').value;
 const lote = document.getElementById('filtroLote').value.toLowerCase();
 const esp = document.getElementById('filtroEspecie').value.toLowerCase();
 const vac = document.getElementById('filtroVacina').value.toLowerCase();
 const vet = document.getElementById('filtroVet').value.toLowerCase();
 const st = document.getElementById('filtroStatus').value.toLowerCase();
 const busca = document.getElementById('buscaAnimal').value.toLowerCase();
 
 const rows = document.querySelectorAll('#tbodyGeral tr[data-nome]');
 let count = 0;
 
 rows.forEach(tr => {
 const rNome = (tr.dataset.nome || '').toLowerCase();
 const rBrinco = (tr.dataset.brinco || '').toLowerCase();
 const rLote = (tr.dataset.lote || '').toLowerCase();
 const rEsp = (tr.dataset.especie|| '').toLowerCase();
 const rVac = (tr.dataset.vacina || '').toLowerCase();
 const rVet = (tr.dataset.vet || '').toLowerCase();
 const rSt = (tr.dataset.status || '').toLowerCase();
 const rDt = tr.dataset.dt || '';
 
 let show = true;
 if (lote && rLote !== lote) show = false;
 if (esp && rEsp !== esp) show = false;
 if (vac && !rVac.includes(vac)) show = false;
 if (vet && !rVet.includes(vet)) show = false;
 if (st && rSt !== st) show = false;
 if (busca && !rNome.includes(busca) && !rBrinco.includes(busca)) show = false;
 if (show && ini && rDt && rDt < ini) show = false;
 if (show && fim && rDt && rDt > fim) show = false;
 
 tr.style.display = show ? '' : 'none';
 if (show) count++;
 });
 
 document.getElementById('countRows').textContent = count + ' registros';
}
 
function limparFiltros() {
 ['filtroInicio','filtroFim','filtroLote','filtroEspecie',
 'filtroVacina','filtroVet','filtroStatus','buscaAnimal'].forEach(id => {
 const el = document.getElementById(id);
 if (el) el.value = '';
 });
 gerarRelatorio();
}
 
// Live search
document.getElementById('buscaAnimal').addEventListener('input', gerarRelatorio);
 
// ── PDF / Print ───────────────────────────────────────────────────────────
function imprimirPDF() {
 window.print();
}
 
function exportarExcel() {
 const rows = [...document.querySelectorAll('#tbodyGeral tr[data-nome]')]
 .filter(r => r.style.display !== 'none')
 .map(r => [
 r.dataset.brinco, r.dataset.nome, r.dataset.especie,
 r.dataset.lote, r.dataset.vacina, r.dataset.dt,
 r.dataset.status, r.dataset.vet
 ]);
 
 const header = ['Brinco','Animal','Espécie','Lote','Última Vacina','Dt. Aplicação','Situação','Responsável'];
 let csv = [header, ...rows].map(r => r.map(c => `"${(c||'').replace(/"/g,'""')}"`).join(',')).join('\n');
 const a = document.createElement('a');
 a.href = 'data:text/csv;charset=utf-8,' + encodeURIComponent('\uFEFF' + csv);
 a.download = 'relatorio_sanidade_' + new Date().toISOString().slice(0,10) + '.csv';
 a.click();
}
 
// ── Ficha Individual ──────────────────────────────────────────────────────
let fichaAnimalId = null;
 
function abrirFichaAnimal(id) {
 fichaAnimalId = id;
 
 // Preenche loading
 document.getElementById('fichaTitle').textContent = 'Carregando…';
 document.getElementById('fichaBrinco').textContent = '';
 document.getElementById('fichaBio').innerHTML = '';
 document.getElementById('fichaTbody').innerHTML = '<tr><td colspan="5" class="empty-state"><i class="bi bi-hourglass-split"></i> Carregando…</td></tr>';
 document.getElementById('fichaResumo').innerHTML = '';
 
 const modal = new bootstrap.Modal(document.getElementById('modalFicha'));
 modal.show();
 
 fetch('Ficha de animal.php?id=' + id + '&json=1')
 .then(r => r.ok ? r.json() : null)
 .then(data => {
 if (!data) { preencherFichaMock(id); return; }
 preencherFicha(data);
 })
 .catch(() => preencherFichaMock(id));
}
 
function preencherFichaMock(id) {
 // Busca dados já presentes na linha da tabela
 const tr = document.querySelector(`#tbodyGeral tr[data-id="${id}"]`);
 if (!tr) return;
 
 document.getElementById('fichaTitle').textContent = tr.dataset.nome || '—';
 document.getElementById('fichaBrinco').textContent = 'Brinco: ' + (tr.dataset.brinco || '—');
 document.getElementById('fichaBio').innerHTML = `
 <div class="bio-item"><strong>Espécie</strong>${tr.dataset.especie || '—'}</div>
 <div class="bio-item"><strong>Lote</strong>${tr.dataset.lote || '—'}</div>
 <div class="bio-item"><strong>Veterinário</strong>${tr.dataset.vet || '—'}</div>
 <div class="bio-item"><strong>Situação</strong>${tr.dataset.status || '—'}</div>
 `;
 
 const st = tr.dataset.status || '—';
 const cls = st === 'Concluído' ? 'stbadge-ok' : (st === 'Atrasada' ? 'stbadge-atras' : 'stbadge-pend');
 const lbl = st === 'Concluído' ? 'Em Dia' : st;
 
 document.getElementById('fichaTbody').innerHTML = `
 <tr>
 <td class="fw-600">${tr.dataset.vacina || '—'}</td>
 <td>${tr.dataset.dt ? formatDate(tr.dataset.dt) : '—'}</td>
 <td>—</td>
 <td><span class="stbadge ${cls}">${lbl}</span></td>
 <td>${tr.dataset.vet || '—'}</td>
 </tr>
 `;
 
 document.getElementById('fichaResumo').innerHTML = `
 <div class="resumo-item"><div class="r-label">Total de Vacinas</div><div class="r-val">1</div></div>
 <div class="resumo-item"><div class="r-label">Última Vacinação</div><div class="r-val">${tr.dataset.dt ? formatDate(tr.dataset.dt) : '—'}</div></div>
 <div class="resumo-item"><div class="r-label">Status Sanitário</div><div class="r-val"><span class="stbadge ${cls}">${lbl}</span></div></div>
 <div class="resumo-item"><div class="r-label">Vacina Registrada</div><div class="r-val">${tr.dataset.vacina || '—'}</div></div>
 `;
}
 
function preencherFicha(d) {
 const a = d.animal || {};
 document.getElementById('fichaTitle').textContent = a.nome_animal || '—';
 document.getElementById('fichaBrinco').textContent = 'Brinco: ' + (a.numero_brinco || '—');
 document.getElementById('fichaBio').innerHTML = `
 <div class="bio-item"><strong>Espécie</strong>${a.especie||'—'}</div>
 <div class="bio-item"><strong>Raça</strong>${a.raca||'—'}</div>
 <div class="bio-item"><strong>Sexo</strong>${a.sexo||'—'}</div>
 <div class="bio-item"><strong>Nascimento</strong>${a.data_nascimento ? formatDate(a.data_nascimento) : '—'}</div>
 <div class="bio-item"><strong>Lote</strong>${a.codigo_lote||'—'}</div>
 <div class="bio-item"><strong>Status</strong>${a.status_animal||'—'}</div>
 `;
 
 const hist = d.historico || [];
 if (!hist.length) {
 document.getElementById('fichaTbody').innerHTML = '<tr><td colspan="5" class="empty-state"><i class="bi bi-inbox"></i> Sem histórico.</td></tr>';
 } else {
 document.getElementById('fichaTbody').innerHTML = hist.map(h => {
 const st = h.status_aplicacao || '—';
 const cls = st === 'Concluído' ? 'stbadge-ok' : (st === 'Atrasada' ? 'stbadge-atras' : 'stbadge-pend');
 const lbl = st === 'Concluído' ? 'Em Dia' : st;
 return `<tr>
 <td class="fw-600">${h.nome||'—'}</td>
 <td>${h.data_aplicacao ? formatDate(h.data_aplicacao) : '—'}</td>
 <td>${h.proxima_aplicacao ? formatDate(h.proxima_aplicacao) : '—'}</td>
 <td><span class="stbadge ${cls}">${lbl}</span></td>
 <td>${h.tecnico||'—'}</td>
 </tr>`;
 }).join('');
 }
 
 const total = hist.length;
 const ultima = hist[0]?.data_aplicacao ? formatDate(hist[0].data_aplicacao) : '—';
 const prox = hist[0]?.proxima_aplicacao ? formatDate(hist[0].proxima_aplicacao) : '—';
 const stGeral = hist[0]?.status_aplicacao || '—';
 const stCls = stGeral === 'Concluído' ? 'stbadge-ok' : (stGeral === 'Atrasada' ? 'stbadge-atras' : 'stbadge-pend');
 
 document.getElementById('fichaResumo').innerHTML = `
 <div class="resumo-item"><div class="r-label">Total de Vacinas Aplicadas</div><div class="r-val">${total}</div></div>
 <div class="resumo-item"><div class="r-label">Última Vacinação</div><div class="r-val">${ultima}</div></div>
 <div class="resumo-item"><div class="r-label">Próxima Vacinação</div><div class="r-val">${prox}</div></div>
 <div class="resumo-item"><div class="r-label">Status Sanitário</div><div class="r-val"><span class="stbadge ${stCls}">${stGeral === 'Concluído' ? 'Em Dia' : stGeral}</span></div></div>
 `;
}
 
function imprimirFichaAtual() {
 window.print();
}
 
function imprimirIndividual(id) {
 abrirFichaAnimal(id);
}
 
function formatDate(str) {
 if (!str) return '—';
 const parts = str.split('-');
 if (parts.length === 3) return `${parts[2]}/${parts[1]}/${parts[0]}`;
 return str;
}
 
// ── Charts ────────────────────────────────────────────────────────────────
let chartsInit = false;
 
function initCharts() {
 if (chartsInit) return;
 chartsInit = true;
 
 const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
 if (isDark) {
   Chart.defaults.color = '#ffffff';
   Chart.defaults.borderColor = 'rgba(255, 255, 255, 0.15)';
 }
 const gridColor = isDark ? 'rgba(255, 255, 255, 0.1)' : '#f0f0f0';
 
 const palette = {
 green: isDark ? '#158C5D' : '#1FAF7A', amber: '#F59E0B', red: '#EF4444',
 blue: '#3B82F6', purple: '#8B5CF6', teal: '#14B8A6'
 };
 
 // Chart 1 – Vacinas por mês
 new Chart(document.getElementById('chartMes'), {
 type: 'bar',
 data: {
 labels: PHP_DATA.chartMeses.length ? PHP_DATA.chartMeses : ['Jan','Fev','Mar','Abr','Mai','Jun'],
 datasets: [{
 label: 'Vacinas Aplicadas',
 data: PHP_DATA.chartQtds.length ? PHP_DATA.chartQtds : [12,18,9,24,16,21],
 backgroundColor: palette.green + 'CC',
 borderColor: palette.green,
 borderWidth: 2,
 borderRadius: 8
 }]
 },
 options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, grid: { color: gridColor } }, x: { grid: { display: false } } } }
 });
 
 // Chart 2 – Status (doughnut)
 new Chart(document.getElementById('chartStatus'), {
 type: 'doughnut',
 data: {
 labels: ['Em Dia', 'Pendente', 'Atrasada'],
 datasets: [{
 data: [PHP_DATA.kpi.emDia || 45, PHP_DATA.kpi.pend || 12, PHP_DATA.kpi.atras || 5],
 backgroundColor: [palette.green, palette.amber, palette.red],
 borderWidth: 0,
 hoverOffset: 8
 }]
 },
 options: { responsive: true, cutout: '65%', plugins: { legend: { position: 'bottom' } } }
 });
 
 // Chart 3 – Cobertura por lote (horizontal bar)
 const lNames = PHP_DATA.loteStats.map(l => l.codigo_lote) || ['L01','L02','L03'];
 const lCob = PHP_DATA.loteStats.map(l => l.total > 0 ? Math.round((l.vacinados/l.total)*100) : 0) || [80,60,95];
 new Chart(document.getElementById('chartLote'), {
 type: 'bar',
 data: {
 labels: lNames.length ? lNames : ['Lote 01','Lote 02','Lote 03'],
 datasets: [{
 label: 'Cobertura (%)',
 data: lCob.length ? lCob : [80,60,95],
 backgroundColor: lCob.map(v => v >= 80 ? palette.green+'CC' : (v >= 50 ? palette.amber+'CC' : palette.red+'CC')),
 borderRadius: 8
 }]
 },
 options: { indexAxis: 'y', responsive: true, plugins: { legend: { display: false } }, scales: { x: { min: 0, max: 100, grid: { color: gridColor } }, y: { grid: { display: false } } } }
 });
 
 // Chart 4 – Aplicações por veterinário
 new Chart(document.getElementById('chartVet'), {
 type: 'polarArea',
 data: {
 labels: PHP_DATA.chartVetNomes.length ? PHP_DATA.chartVetNomes : ['Dr. Silva','Dra. Lima','Dr. Costa'],
 datasets: [{
 data: PHP_DATA.chartVetQtds.length ? PHP_DATA.chartVetQtds : [34,28,19],
 backgroundColor: [palette.green+'99', palette.blue+'99', palette.purple+'99', palette.amber+'99', palette.teal+'99', palette.red+'99']
 }]
 },
 options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
 });
}
</script>
</body>
</html>