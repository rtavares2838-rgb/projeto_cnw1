<?php
// menu.php — Header reutilizável
// Certifique-se de que a sessão é iniciada
if (session_status() === PHP_SESSION_NONE) session_start();

// Regra simples de autenticação
$isLoggedIn = isset($_SESSION['id']) && (int)$_SESSION['id'] > 0;
// Opcional: exibir nome se existir
$userName = isset($_SESSION['nome']) ? trim($_SESSION['nome']) : '';

// Inclua a configuração (BASE_URL)
require_once __DIR__ . '/config.php';

// --- CÓDIGO: BUSCAR CONTAGEM REAL DE NOTIFICAÇÕES NÃO LIDAS (MySQLi) ---
$notificationCount = 0;
if ($isLoggedIn && isset($_SESSION['id'])) {
    $idUsuario = (int)$_SESSION['id'];
    
    // LINHA FALTANTE INCLUÍDA AQUI
    require 'conexao/conecta.php'; 

    // Assume que $conn está definido e é um objeto mysqli válido
    if (isset($conn) && $conn instanceof mysqli && $conn->connect_error === null) {
        $sql = "SELECT COUNT(*) AS total FROM tb_notificacoes WHERE idUsuario = ? AND status = 'nao_lida'";
        $stmt = $conn->prepare($sql);
        
        if ($stmt) {
            $stmt->bind_param('i', $idUsuario);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $notificationCount = (int)$row['total'];
            $stmt->close();
        }
        // $conn permanece aberta para uso posterior pela página principal
    }
}
// --- FIM DO CÓDIGO PHP DE CONTAGEM ---

?>
<style>
 :root{
        --pri:#0d4b9e;--pri-d:#0a3a7a;--pri-l:#3a6cb5;
        --gold:#D4AF37;--gold-d:#996515;
        --ok:#16a34a;--warn:#f59e0b;--bad:#ef4444; /* Usado para o badge de notificação */
        --txt:#212529;--mut:#6b7280;--bg:#f5f7fa;--white:#fff;
        --rad:14px;--sh:0 10px 30px rgba(0,0,0,.08);
        --header-h: 120px;
        
        /* Variáveis do menu */
        --azul-primario: #0d4b9e;
        --azul-claro: rgba(13, 75, 158, 0.5);
        --azul-escuro: #0a3a7a;
        --gold-color: #D4AF37;
        --branco: #ffffff;
        --preto: #333333;
        --destaque: #1283c5;
        --sombra: 0 4px 12px rgba(0, 0, 0, 0.1);
        --transicao: all 0.3s ease;
        --borda-arredondada: 8px;
        --azul-claro-transparente: rgba(13,75,158,0.15); /* Para item hover e bordas */

        /* Estilos de notificação (para renderização dos itens dentro do modal) */
        .p-3 { padding: 1rem; } .mb-2 { margin-bottom: 0.5rem; } .rounded-lg { border-radius: 0.5rem; }
        .border-l-4 { border-left-width: 4px; border-style: solid; }
        .text-gray-600 { color: #4b5563; } .text-gray-700 { color: #374151; } .text-sm { font-size: 0.875rem; }
        .text-gray-500 { color: #6b7280; }
        .text-blue-600 { color: #2563eb; } .underline { text-decoration: underline; }
        .flex { display: flex; } .justify-between { justify-content: space-between; } .items-start { align-items: flex-start; }
        .border-green-500 { border-color: #10b981; } .bg-green-50 { background-color: #ecfdf5; }
        .border-yellow-500 { border-color: #f59e0b; } .bg-yellow-50 { background-color: #fffbeb; }
        .border-red-500 { border-color: #ef4444; } .bg-red-50 { background-color: #fef2f2; }
        .border-blue-500 { border-color: #3b82f6; } .bg-blue-50 { background-color: #eff6ff; }
        .text-green-600 { color: #059669; }

    }
    *{box-sizing:border-box;margin:0;padding:0}
    body{font-family:Poppins,system-ui,-apple-system,Segoe UI,Roboto,Ubuntu,'Helvetica Neue',Arial,sans-serif;background:var(--bg);color:var(--txt)}
    
    .container{max-width:1200px;margin:0 auto;padding:24px}

    /* Banner */
    .banner{
        background:linear-gradient(135deg,var(--pri),#152238);
        color:#fff;border-radius:var(--rad);padding:22px 22px 18px;box-shadow:var(--sh);
        display:flex;gap:18px;align-items:flex-start;flex-wrap:wrap
    }
    .banner i{font-size:1.4rem;color:var(--gold)}
    .banner h2{font-weight:600;margin-bottom:6px;font-size:1.25rem}
    .banner p{opacity:.95;line-height:1.6}

    /* Cards genéricos */
    .card{background:var(--white);border-radius:var(--rad);box-shadow:var(--sh);padding:22px}

    /* Form */
    .grid{display:grid;gap:24px;margin-top:24px}
    @media (min-width: 992px){.grid{grid-template-columns:2fr 1fr}}
    .card h3{font-size:1.15rem;color:var(--pri);margin-bottom:14px}
    .input,.textarea,select{
        width:100%;border:1px solid #e5e7eb;border-radius:12px;padding:12px 14px;font-size:0.98rem;transition:.2s;background:#fff
    }
    .input:focus,.textarea:focus,select:focus{outline:none;border-color:var(--gold);box-shadow:0 0 0 4px rgba(212,175,55,.15)}
    .textarea{min-height:260px;resize:vertical}
    .counter{font-size:.9rem;color:#6b7280;text-align:right;margin-top:6px}
    .btn{display:inline-flex;align-items:center;gap:8px;border:none;border-radius:12px;padding:12px 16px;font-weight:600;cursor:pointer;transition:.2s}
    .btn-primary{background:linear-gradient(90deg,var(--pri),var(--pri-d));color:#fff}
    .btn-primary:hover{filter:brightness(.95);transform:translateY(-1px)}
    .btn-ghost{background:#f3f4f6}
    .btn-danger{background:linear-gradient(90deg,#ef4444,#dc2626);color:#fff}
    .small{font-size:.85rem;color:#6b7280}

    /* KPIs */
    .kpis{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px;margin-top:24px}
    .kpi{background:#fff;border:1px solid #eef2f7;border-radius:12px;padding:12px 14px}
    .kpi b{font-size:.8rem;color:#6b7280;display:block}
    .kpi span{font-weight:800;font-size:1.2rem;color:#111827}

    /* Lista */
    .section-title{margin:34px 6px 12px;display:flex;align-items:center;gap:10px;color:#111827}
    .section-title i{color:var(--gold)}
    .cards{display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:18px}
    .r-card{
        background:var(--white);border-radius:16px;box-shadow:var(--sh);padding:18px;border-left:4px solid transparent;transition:.2s;cursor:pointer
    }
    .r-card:hover{transform:translateY(-3px);border-left-color:var(--gold)}
    .r-top{display:flex;align-items:flex-start;justify-content:space-between;gap:12px}
    .r-title{font-size:1rem;color:#111827;font-weight:700;line-height:1.3}
    .r-sub{font-size:.88rem;color:#374151;margin-top:2px}
    .r-meta{display:flex;gap:10px;flex-wrap:wrap;color:#6b7280;font-size:.88rem;margin-top:8px}
    .badge{padding:4px 10px;border-radius:999px;font-size:.78rem;font-weight:600;color:#fff}
    .nota-ouro{background:linear-gradient(90deg,#D4AF37,#f3c969);color:#111827}
    .nota-alta{background:linear-gradient(90deg,#10b981,#059669)}
    .nota-media{background:linear-gradient(90deg,#60a5fa,#2563eb)}
    .nota-baixa{background:linear-gradient(90deg,#f87171,#ef4444)}
    .aderencia-chip{border:1px solid #e5e7eb;background:#fff;padding:3px 8px;border-radius:999px;font-size:.78rem}
    .chips{display:flex;gap:8px;flex-wrap:wrap;margin-top:8px}
    .mini-competencias{display:grid;grid-template-columns:repeat(5,1fr);gap:6px;margin-top:10px}
    .mini-bar{height:8px;background:#f1f5f9;border-radius:999px;overflow:hidden;position:relative}
    .mini-fill{position:absolute;left:0;top:0;bottom:0;background:linear-gradient(90deg,#38bdf8,#0ea5e9)}
    .mini-labels{display:flex;justify-content:space-between;color:#6b7280;font-size:.72rem;margin-top:4px}

    /* === CSS DO MODAL === */
    .modal {
        display: none;
        position: fixed;
        inset: 0;
        top: 0; 
        background: rgba(0, 0, 0, .6);
        z-index: 10000; 
        justify-content: center;
        align-items: center; 
        padding: 20px;
    }

    .m-content {
        background: #fff;
        max-width: 980px;
        width: 100%;
        max-height: 90vh; 
        overflow: auto;
        border-radius: 18px;
        box-shadow: var(--sh);
        position: relative;
        padding-top: 10px; 
        margin: 0;
    }

    .m-head{
        padding: 18px 20px 10px;
        border-bottom: 1px solid #f0f0f0;
        padding-top: 20px;
    }
    
    .m-body{padding:18px 20px 22px}
    
    .m-title{
        font-size: 1.25rem;
        color: #111827;
        font-weight: 800;
        display: flex;
        gap: 10px;
        align-items: center;
    }
    
    .close {
        position: absolute;
        right: 14px;
        top: 14px; 
        border: none;
        background: transparent;
        font-size: 1.8rem; 
        color: #6b7280;
        cursor: pointer;
        z-index: 10001; 
    }

    .close:hover{color:#111827}
    .meta-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:12px;margin:10px 0 14px}
    .meta-item{background:#f9fafb;border:1px solid #eef2f7;border-radius:12px;padding:10px 12px}
    .meta-item b{display:block;font-size:.8rem;color:#6b7280;margin-bottom:4px}
    .meta-item span{font-weight:600}
    .redacao{background:#fbfbfd;border:1px solid #eef2f7;border-radius:12px;padding:14px;margin-top:6px;line-height:1.8;white-space:pre-wrap}

    .grid-competencias{display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:14px;margin-top:14px}
    .comp{border:1px solid #eef2f7;border-radius:12px;padding:12px;background:#fff}
    .comp h4{font-size:.95rem;color:#0f172a;margin-bottom:8px}
    .prog{position:relative;height:10px;background:#f1f5f9;border-radius:999px;overflow:hidden;margin-top:8px}
    .prog-fill{position:absolute;left:0;top:0;bottom:0;background:linear-gradient(90deg,#38bdf8,#0ea5e9)}
    .prog-label{font-size:.8rem;color:#0f172a;margin-top:6px;display:inline-block}
    .com-item{border-left:3px solid var(--gold);background:linear-gradient(180deg,#fff, #fcfcfe);border-radius:0 12px 12px 0;padding:10px 12px;margin-top:10px}

    /* Responsividade Modal */
    @media (max-width: 768px) {
        .banner{padding:16px}
        .cards{grid-template-columns:1fr}
        .kpis{grid-template-columns:repeat(2,1fr)}
        
        .modal {
            align-items: flex-start;
            padding-top: 10px;
        }

        .m-content {
            margin: 0 10px;
            max-height: 90vh; 
        }
    }


    /* === CSS DO MENU === */
    header.cs-header {
        position: fixed; 
        top: 0; left: 0; width: 100%;
        background: var(--azul-claro);
        padding: 15px 0;
        backdrop-filter: blur(8px);
        z-index: 9999; 
        box-shadow: var(--sombra);
        border-bottom: 4px solid var(--gold-color);
    }

    .cs-container {
        width: 90%;
        max-width: 1200px;
        margin: 0 auto;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .cs-logo img { height: 60px; transition: var(--transicao); }
    .cs-logo img:hover { transform: scale(1.05); }

    .cs-title {
        font-family: 'Merriweather', serif;
        font-weight: 700;
        font-size: 1.8rem;
        color: var(--branco);
        text-align: center;
        flex-grow: 1;
        margin: 0 20px;
        text-shadow: 1px 1px 3px rgba(0,0,0,0.3);
    }

    .cs-buttons { display: flex; gap: 15px; }

    .cs-btn {
        background-color: var(--branco);
        color: var(--azul-primario);
        padding: 10px 20px;
        border-radius: 30px;
        font-weight: 600;
        border: 2px solid transparent;
        transition: var(--transicao);
        cursor: pointer;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    .cs-btn:hover {
        background-color: transparent;
        border-color: var(--branco);
        color: var(--branco);
        transform: translateY(-2px);
    }

    .cs-header-right {
        display: flex;
        align-items: center;
        gap: 20px; /* Adicionado para espaçar os itens: nome, notificação, menu */
    }

    /* NOVO CSS: Botão de Notificações */
    .cs-notification-toggle {
        display: flex; align-items: center; justify-content: center;
        width: 44px; height: 44px; padding: 0;
        border-radius: 50%;
        background-color: var(--branco);
        color: var(--azul-primario);
        border: 2px solid transparent;
        transition: var(--transicao);
        cursor: pointer;
        position: relative; /* Essencial para posicionar o badge */
    }

    .cs-notification-toggle:hover {
        background-color: transparent;
        border-color: var(--branco);
        color: var(--branco);
        transform: translateY(-2px);
    }

    .cs-notification-toggle i { font-size: 1.2rem; }

    /* NOVO CSS: Badge de Notificações (o círculo vermelho com a contagem) */
    .cs-notification-badge {
        position: absolute;
        top: 0;
        right: 0;
        background-color: var(--bad); 
        color: var(--white);
        border-radius: 50%;
        padding: 3px 6px;
        font-size: 0.7rem;
        font-weight: 700;
        line-height: 1;
        min-width: 20px;
        text-align: center;
        border: 2px solid var(--azul-claro); 
    }

    .cs-user-menu { position: relative; margin-left: 0; }

    .cs-user-toggle {
        display: flex; align-items: center; justify-content: center;
        width: 44px; height: 44px; padding: 0;
        border-radius: 50%;
        background-color: var(--branco);
        color: var(--azul-primario);
        border: 2px solid transparent;
        transition: var(--transicao);
        cursor: pointer;
    }
    .cs-user-toggle:hover {
        background-color: transparent;
        border-color: var(--branco);
        color: var(--branco);
        transform: translateY(-2px);
    }
    .cs-user-toggle i { font-size: 1.2rem; }

    .cs-user-dropdown {
        display: none;
        position: absolute; right: 0; top: 50px;
        background-color: var(--branco);
        min-width: 220px;
        box-shadow: var(--sombra);
        border-radius: var(--borda-arredondada);
        z-index: 1000;
        overflow: hidden;
        border: 1px solid rgba(13,75,158,0.1);
        animation: csFadeIn .25s ease-out;
    }
    .cs-user-dropdown.cs-show { display: block; }

    .cs-user-dropdown a,
    .cs-user-dropdown button {
        display: flex; align-items: center; gap: 10px;
        padding: 12px 15px;
        color: var(--preto);
        text-decoration: none;
        transition: var(--transicao);
        font-size: 0.95rem;
        background: transparent;
        border: 0;
        width: 100%;
        text-align: left;
        cursor: pointer;
    }
    .cs-user-dropdown a:hover,
    .cs-user-dropdown button:hover {
        background-color: var(--azul-claro-transparente); /* Usando variável para o hover */
        color: var(--azul-primario);
    }
    .cs-user-dropdown i { width: 20px; text-align: center; color: var(--azul-primario); }

    .cs-user-name {
        color: var(--branco);
        font-size: 0.95rem;
        font-weight: 600;
        white-space: nowrap;
        max-width: 240px;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    @keyframes csFadeIn {
        from { opacity: 0; transform: translateY(6px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* Responsividade do Menu */
    @media (max-width: 768px) {
        .cs-container { flex-direction: column; text-align: center; }
        .cs-logo img { margin-bottom: 12px; }
        .cs-title { margin: 10px 0; font-size: 1.5rem; }
        .cs-header-right { flex-direction: row; justify-content: center; gap: 15px; } /* Ajustado para manter ícones na linha */
        .cs-user-name { display: none; } /* Oculta o nome do usuário em telas muito pequenas */
        .cs-user-dropdown { right: auto; left: 50%; transform: translateX(-50%); } /* Centraliza dropdown no mobile */
    }
</style>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Merriweather:ital,wght@0,400;0,700;1,400&family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">

<header class="cs-header" id="csHeader">
    <div class="cs-container">
        <div class="cs-logo">
            <a href="<?= BASE_URL ?>index.php" aria-label="Ir para a página inicial">
                <img src="<?= BASE_URL ?>imagem/logonova.png" alt="Logo Caminho do Saber">
            </a>
        </div>

        <h1 class="cs-title">CAMINHO DO SABER</h1>

        <div class="cs-header-right">
            <?php if (!$isLoggedIn): ?>
                <div class="cs-buttons" role="navigation" aria-label="Acesso">
                    <a href="<?= BASE_URL ?>login.php" class="cs-btn"><i class="fas fa-sign-in-alt"></i> Login</a>
                    <a href="<?= BASE_URL ?>cadastro.html" class="cs-btn"><i class="fas fa-user-plus"></i> Cadastrar-se</a>
                </div>
            <?php else: ?>
                <?php if ($userName !== ''): ?>
                    <div class="cs-user-name" title="<?php echo htmlspecialchars($userName, ENT_QUOTES, 'UTF-8'); ?>">
                        <i class="fas fa-user-circle" style="margin-right:6px;"></i>
                        <?php echo htmlspecialchars($userName, ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                <?php endif; ?>

                                <button class="cs-notification-toggle" aria-label="Notificações" id="csNotificationToggle">
                    <i class="fas fa-bell" aria-hidden="true"></i>
                    <?php if ($notificationCount > 0): ?>
                        <span class="cs-notification-badge" id="csNotificationCount"><?php echo $notificationCount; ?></span>
                    <?php endif; ?>
                </button>
                                <div class="cs-user-menu">
                    <button class="cs-user-toggle" id="csUserToggle" aria-haspopup="true" aria-expanded="false" aria-controls="csUserDropdown">
                        <i class="fas fa-bars" aria-hidden="true"></i>
                        <span class="sr-only">Abrir menu do usuário</span>
                    </button>
                    <div class="cs-user-dropdown" id="csUserDropdown" role="menu" aria-label="Menu do usuário">
                        <a href="<?= BASE_URL ?>home.php" role="menuitem"><i class="fas fa-home"></i> Home</a>
                        <a href="<?= BASE_URL ?>exibirProvas.php" role="menuitem"><i class="fas fa-clipboard-list"></i> Provas</a>
                        <a href="<?= BASE_URL ?>simulado.php" role="menuitem"><i class="fas fa-list-check"></i>Simulados</a>
                        <a href="<?= BASE_URL ?>corretor.php" role="menuitem"><i class="fas fa-pen-fancy"></i> Corretor</a>
                        <a href="<?= BASE_URL ?>progresso.php" role="menuitem"><i class="fas fa-chart-line"></i> Progresso</a>
                        <a href="<?= BASE_URL ?>configuracao/configuracoes.php" role="menuitem"><i class="fas fa-cog"></i> Configurações</a>
                        <hr style="margin:6px 0;border:0;border-top:1px solid rgba(13,75,158,0.15)">
                        <a href="<?= BASE_URL ?>sair.php" role="menuitem"><i class="fas fa-sign-out-alt"></i> Sair</a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</header>

<div class="modal" id="notificacoesModal">
    <div class="m-content">
        <button class="close" id="fecharModalNotificacoesBtn">&times;</button> 
        <div class="m-head">
            <h2 class="m-title"><i class="fas fa-bell"></i> Notificações</h2>
        </div>
        <div class="m-body" id="listaNotificacoes">
            <p class="text-gray-600">Carregando notificações...</p>
        </div>
    </div>
</div>
<script>
    // ==============================================
    // FUNÇÕES DE CONTROLE DE MODAL (Escopo Global)
    // As funções precisam estar no escopo global para serem acessíveis pelos botões criados
    // dinamicamente e pelo botão de fechar.
    // ==============================================

    /** Fecha o modal de notificações. */
    function fecharModalNotificacoes() {
        document.getElementById('notificacoesModal').style.display = 'none';
    }

    /** * Marca uma notificação específica como lida e recarrega o modal.
     * @param {number} id - O ID da notificação a ser marcada.
     */
    async function marcarLida(id) {
        try {
            await fetch('marcar_lida.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ id })
            });
            // Recarrega o modal para atualizar a lista e a contagem (que deve ser atualizada com um novo fetch)
            await abrirModalNotificacoes(); 
            
            // Opcional: Aqui você faria um novo AJAX para buscar a nova contagem para o badge
            // Ex: updateNotificationBadge();
            
        } catch (e) {
            console.error("Erro ao marcar notificação como lida:", e);
            alert("Erro ao marcar notificação como lida. Tente novamente.");
        }
    }

    /** * Abre o modal e carrega a lista de notificações via AJAX. 
     */
    async function abrirModalNotificacoes() {
        const modal = document.getElementById('notificacoesModal');
        const lista = document.getElementById('listaNotificacoes');
        
        modal.style.display = 'flex';
        lista.innerHTML = '<p class="text-gray-600">Carregando notificações...</p>';

        try {
            const res = await fetch('buscar_notificacoes.php'); 
            
            if (!res.ok) {
                const erro = await res.text();
                throw new Error(`Erro HTTP ${res.status}: ${erro.substring(0, 100)}...`);
            }
            
            const dados = await res.json();
            lista.innerHTML = '';

            if (!dados || !dados.length) {
                lista.innerHTML = '<p class="text-gray-600">Nenhuma notificação no momento.</p>';
                return;
            }

            // Renderiza as notificações
            dados.forEach(n => {
                const item = document.createElement('div');
                item.className = `p-3 mb-2 rounded-lg border-l-4 ${
                    n.tipo === 'sucesso' ? 'border-green-500 bg-green-50' :
                    n.tipo === 'alerta' ? 'border-yellow-500 bg-yellow-50' :
                    n.tipo === 'erro' ? 'border-red-500 bg-red-50' :
                    'border-blue-500 bg-blue-50'
                }`;

                item.innerHTML = `
                    <div class="flex justify-between items-start">
                        <div>
                            <strong class="block text-gray-800">${n.titulo}</strong>
                            <p class="text-gray-700 text-sm">${n.mensagem}</p>
                            <small class="text-gray-500">${n.dataEnvio}</small>
                        </div>
                        ${
                            // O botão 'Marcar como lida' chama a função global marcarLida
                            n.status === 'nao_lida'
                            ? `<button class="text-blue-600 text-sm underline" onclick="marcarLida(${n.id})" type="button">Marcar como lida</button>`
                            : `<span class="text-green-600 text-xs">✔ Lida</span>`
                        }
                    </div>
                `;
                lista.appendChild(item);
            });
            
            // Opcional: Atualizar o badge de contagem no header se o modal for aberto e a lista carregada
            const countBadge = document.getElementById('csNotificationCount');
            const unreadCount = dados.filter(n => n.status === 'nao_lida').length;
            
            if (countBadge) {
                if (unreadCount > 0) {
                    countBadge.textContent = unreadCount;
                } else {
                    countBadge.remove(); // Remove o badge se não houver notificações não lidas
                }
            }


        } catch (e) {
            console.error("Erro ao carregar notificações:", e);
            lista.innerHTML = `<p style="color:#ef4444;font-weight:600;">Erro ao carregar notificações.</p><p style="color:#6b7280;font-size:.9rem;">Detalhe: ${e.message}</p>`;
        }
    }


    // ==============================================
    // INICIALIZAÇÃO E LISTENERS (IIFE para isolamento de variáveis)
    // ==============================================
    (() => {
        const toggle = document.getElementById('csUserToggle');
        const dropdown = document.getElementById('csUserDropdown');
        const notificationToggle = document.getElementById('csNotificationToggle');
        const closeBtn = document.getElementById('fecharModalNotificacoesBtn'); 

        // --- Lógica do Dropdown do Usuário ---
        if (toggle && dropdown) {
            toggle.addEventListener('click', (e) => {
                e.stopPropagation();
                const isOpen = dropdown.classList.toggle('cs-show');
                toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            });
            document.addEventListener('click', () => {
                if (dropdown.classList.contains('cs-show')) {
                    dropdown.classList.remove('cs-show');
                    toggle.setAttribute('aria-expanded', 'false');
                }
            });
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && dropdown.classList.contains('cs-show')) {
                    dropdown.classList.remove('cs-show');
                    toggle.setAttribute('aria-expanded', 'false');
                    toggle.focus();
                }
            });
        }

        // --- Listeners do Modal de Notificação ---
        if (notificationToggle) {
            notificationToggle.addEventListener('click', abrirModalNotificacoes);
        }

        // Listener para o botão de fechar o modal
        if (closeBtn) {
            closeBtn.addEventListener('click', fecharModalNotificacoes);
        }

        // Fecha o modal clicando fora
        window.addEventListener('click', (event) => {
            const modal = document.getElementById('notificacoesModal');
            if (event.target === modal) {
                fecharModalNotificacoes();
            }
        });
    })();
</script>

<script>
    // Correção de espaçamento para o menu fixo
    (function() {
        const header = document.getElementById('csHeader');
        if (!header) return;

        function applyPadding() {
            const headerHeight = header.offsetHeight;
            document.body.style.paddingTop = `${headerHeight}px`;
        }

        applyPadding();
        window.addEventListener('resize', applyPadding);
    })();
</script>
