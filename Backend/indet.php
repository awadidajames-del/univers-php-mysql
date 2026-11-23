<!doctype html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="theme-color" content="#302a28">
  <title>Univer Cosmetics — Tableau de bord</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@600;700&family=Playfair+Display:wght@600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="styles.css">
</head>
<body>
  <aside class="sidebar" id="sidebar">
    <div class="brand">
      <div class="brand-mark">U</div>
      <div>
        <span class="brand-name">UNIVER</span>
        <span class="brand-sub">COSMETICS</span>
      </div>
    </div>

    <nav class="nav" aria-label="Navigation principale">
      <button class="nav-item active" data-view="dashboard">
        <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="3" width="7" height="7" rx="2"/><rect x="14" y="3" width="7" height="7" rx="2"/><rect x="3" y="14" width="7" height="7" rx="2"/><rect x="14" y="14" width="7" height="7" rx="2"/></svg>
        <span>Tableau de bord</span>
      </button>
      <button class="nav-item" data-view="sales">
        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 3h2l2.4 11.2a2 2 0 0 0 2 1.6h7.8a2 2 0 0 0 2-1.6L21 7H6"/><circle cx="10" cy="20" r="1"/><circle cx="18" cy="20" r="1"/></svg>
        <span>Ventes</span>
      </button>
      <a class="nav-item" href="stock.php">
        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M21 8l-9-5-9 5 9 5 9-5z"/><path d="M3 8v8l9 5 9-5V8"/><path d="M12 13v8"/></svg>
        <span>Stock</span>
      </a>
      <a class="nav-item" href="clients.php">
        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        <span>Clients</span>
      </a>
      <button class="nav-item" data-view="reports">
        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 20V10"/><path d="M10 20V4"/><path d="M16 20v-7"/><path d="M22 20H2"/></svg>
        <span>Rapports</span>
      </button>
    </nav>

    <div class="sidebar-bottom">
      <button class="nav-item" data-view="settings">
        <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.7 1.7 0 0 0 .34 1.88l.06.06-1.42 1.42-.06-.06a1.7 1.7 0 0 0-1.88-.34 1.7 1.7 0 0 0-1.04 1.56V20h-2v-.48A1.7 1.7 0 0 0 12.34 18a1.7 1.7 0 0 0-1.88.34l-.06.06-1.42-1.42.06-.06A1.7 1.7 0 0 0 9.38 15a1.7 1.7 0 0 0-1.56-1.04H7.4v-2h.42A1.7 1.7 0 0 0 9.38 10.9a1.7 1.7 0 0 0-.34-1.88l-.06-.06 1.42-1.42.06.06A1.7 1.7 0 0 0 12.34 7a1.7 1.7 0 0 0 1.04-1.56V5h2v.44A1.7 1.7 0 0 0 16.44 7a1.7 1.7 0 0 0 1.88-.34l.06-.06 1.42 1.42-.06.06A1.7 1.7 0 0 0 19.4 10a1.7 1.7 0 0 0 1.56 1.04H21v2h-.04A1.7 1.7 0 0 0 19.4 15z"/></svg>
        <span>Paramètres</span>
      </button>
      <div class="profile">
        <div class="avatar">MP</div>
        <div class="profile-text">
          <strong>Mme. Princesse</strong>
          <span>Administratrice</span>
        </div>
        <button class="icon-button" aria-label="Menu utilisateur">•••</button>
      </div>
    </div>
  </aside>

  <main class="main">
    <header class="topbar">
      <button class="menu-button" id="menuButton" aria-label="Ouvrir le menu">
        <span></span><span></span><span></span>
      </button>
      <div class="search">
        <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="M20 20l-4-4"/></svg>
        <input type="search" placeholder="Rechercher un produit, une vente..." aria-label="Rechercher">
        <kbd>⌘ K</kbd>
      </div>
      <div class="topbar-actions">
        <button class="sync-status"><span class="status-dot"></span><span>Synchronisé</span></button>
        <button class="icon-button notification" aria-label="Notifications">
          <svg viewBox="0 0 24 24"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"/><path d="M10 21h4"/></svg>
          <span class="badge">3</span>
        </button>
      </div>
    </header>

    <section class="content" id="dashboardView">
      <div class="page-heading">
        <div>
          <p class="eyebrow">Lundi 23 novembre</p>
          <h1>Bonjour, Princesse</h1>
        </div>
        <button class="primary-button" id="newSaleButton">
          <span>+</span> Nouvelle vente
        </button>
      </div>

      <div class="stats-grid">
        <article class="stat-card">
          <div class="stat-icon brown">
            <svg viewBox="0 0 24 24"><path d="M3 3h18v18H3z"/><path d="M7 16V9"/><path d="M12 16V6"/><path d="M17 16v-4"/></svg>
          </div>
          <div class="stat-copy"><span>Ventes du jour</span><strong>248 500 <small>FCFA</small></strong><em class="positive">↗ 12,4%</em></div>
        </article>
        <article class="stat-card">
          <div class="stat-icon rose">
            <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
          </div>
          <div class="stat-copy"><span>À traiter</span><strong>8 <small>tâches</small></strong><em>2 urgentes</em></div>
        </article>
        <article class="stat-card">
          <div class="stat-icon beige">
            <svg viewBox="0 0 24 24"><path d="M21 8l-9-5-9 5 9 5 9-5z"/><path d="M3 8v8l9 5 9-5V8"/></svg>
          </div>
          <div class="stat-copy"><span>Valeur du stock</span><strong>12,8 <small>M FCFA</small></strong><em>1 284 variantes</em></div>
        </article>
        <article class="stat-card">
          <div class="stat-icon cream">
            <svg viewBox="0 0 24 24"><path d="M12 9v4"/><path d="M12 17h.01"/><circle cx="12" cy="12" r="9"/></svg>
          </div>
          <div class="stat-copy"><span>Alertes stock</span><strong>23 <small>produits</small></strong><em class="warning">6 en rupture</em></div>
        </article>
      </div>

      <div class="dashboard-grid">
        <article class="panel sales-panel">
          <div class="panel-header">
            <div><p class="panel-kicker">APERÇU</p><h2>Évolution des ventes</h2></div>
            <select aria-label="Période">
              <option>7 derniers jours</option>
              <option>30 derniers jours</option>
            </select>
          </div>
          <div class="chart-summary">
            <strong>1 482 300 <small>FCFA</small></strong>
            <span class="positive">↗ 8,2% vs semaine précédente</span>
          </div>
          <div class="chart" aria-label="Graphique des ventes sur sept jours">
            <div class="grid-line line-1"></div><div class="grid-line line-2"></div><div class="grid-line line-3"></div>
            <svg viewBox="0 0 700 220" preserveAspectRatio="none" role="img">
              <defs>
                <linearGradient id="area" x1="0" y1="0" x2="0" y2="1">
                  <stop offset="0%" stop-color="#a98261" stop-opacity=".24"/>
                  <stop offset="100%" stop-color="#a98261" stop-opacity="0"/>
                </linearGradient>
              </defs>
              <path class="chart-area" d="M0 174 C55 160 72 146 116 152 S195 118 233 126 S306 82 350 96 S430 150 466 128 S538 66 583 82 S650 44 700 54 L700 220 L0 220 Z"/>
              <path class="chart-line" d="M0 174 C55 160 72 146 116 152 S195 118 233 126 S306 82 350 96 S430 150 466 128 S538 66 583 82 S650 44 700 54"/>
              <circle cx="583" cy="82" r="5"/>
            </svg>
            <div class="chart-labels"><span>17 Nov</span><span>18 Nov</span><span>19 Nov</span><span>20 Nov</span><span>21 Nov</span><span>22 Nov</span><span>Aujourd'hui</span></div>
          </div>
        </article>

        <article class="panel tasks-panel">
          <div class="panel-header">
            <div><p class="panel-kicker">PRIORITÉS</p><h2>À traiter</h2></div>
            <button class="text-button">Tout voir</button>
          </div>
          <div class="task-list">
            <button class="task-row">
              <span class="task-icon warning">!</span>
              <span class="task-copy"><strong>Sortie à approuver</strong><small>Perte · 12 500 FCFA · 8 min</small></span>
              <span class="task-arrow">›</span>
            </button>
            <button class="task-row">
              <span class="task-icon danger">−</span>
              <span class="task-copy"><strong>Écart de stock détecté</strong><small>Crème Nivea · Écart de 3</small></span>
              <span class="task-arrow">›</span>
            </button>
            <button class="task-row">
              <span class="task-icon cash">F</span>
              <span class="task-copy"><strong>Clôture de caisse</strong><small>Écart estimé · 2 000 FCFA</small></span>
              <span class="task-arrow">›</span>
            </button>
            <button class="task-row">
              <span class="task-icon info">%</span>
              <span class="task-copy"><strong>Prix à compléter</strong><small>5 produits sans prix</small></span>
              <span class="task-arrow">›</span>
            </button>
          </div>
        </article>

        <article class="panel products-panel">
          <div class="panel-header">
            <div><p class="panel-kicker">STOCK</p><h2>Produits les plus vendus</h2></div>
            <button class="text-button">Voir le stock</button>
          </div>
          <div class="product-table">
            <div class="table-head"><span>Produit</span><span>Vendu</span><span>Stock</span><span>Montant</span></div>
            <div class="product-row">
              <div class="product-info"><div class="product-thumb blush"></div><div><strong>Glossier Lip Oil</strong><small>Beauté · 4 ml</small></div></div>
              <span>42</span><span><b class="stock-good">68</b></span><span>63 000 FCFA</span>
            </div>
            <div class="product-row">
              <div class="product-info"><div class="product-thumb ivory"></div><div><strong>Nivea Soft Crème</strong><small>Corps · 200 ml</small></div></div>
              <span>38</span><span><b class="stock-low">7</b></span><span>34 200 FCFA</span>
            </div>
            <div class="product-row">
              <div class="product-info"><div class="product-thumb brown-tube"></div><div><strong>Maybelline Mascara</strong><small>Maquillage · Noir</strong></small></div></div>
              <span>31</span><span><b class="stock-good">24</b></span><span>46 500 FCFA</span>
            </div>
            <div class="product-row">
              <div class="product-info"><div class="product-thumb gold"></div><div><strong>Bodycology Mist</strong><small>Corps · 236 ml</small></div></div>
              <span>29</span><span><b class="stock-low">5</b></span><span>40 600 FCFA</span>
            </div>
          </div>
        </article>

        <article class="panel activity-panel">
          <div class="panel-header">
            <div><p class="panel-kicker">ACTIVITÉ</p><h2>Dernières opérations</h2></div>
          </div>
          <div class="activity-list">
            <div class="activity-row"><span class="activity-dot sale"></span><div><strong>Vente enregistrée</strong><small>#VT-2841 · Fatou</small></div><b>+18 500 FCFA</b></div>
            <div class="activity-row"><span class="activity-dot entry"></span><div><strong>Entrée de marchandises</strong><small>8 produits · Admin</small></div><b>+42 unités</b></div>
            <div class="activity-row"><span class="activity-dot count"></span><div><strong>Comptage validé</strong><small>Vitrine A · Mouna</small></div><b class="negative">-2 écarts</b></div>
          </div>
        </article>
      </div>
    </section>

    <section class="content placeholder-view" id="genericView" hidden>
      <div class="placeholder-card">
        <div class="placeholder-icon" id="placeholderIcon">U</div>
        <p class="eyebrow">UNIVER COSMETICS</p>
        <h1 id="placeholderTitle">Module</h1>
        <p id="placeholderText">Ce module sera disponible dans la version suivante.</p>
        <button class="primary-button" id="backButton">Retour au tableau de bord</button>
      </div>
    </section>
  </main>

  <div class="modal-backdrop" id="saleModal" hidden>
    <section class="modal" role="dialog" aria-modal="true" aria-labelledby="saleTitle">
      <header class="modal-header">
        <div><p class="panel-kicker">CAISSE</p><h2 id="saleTitle">Nouvelle vente</h2></div>
        <button class="icon-button" id="closeModal" aria-label="Fermer">×</button>
      </header>
      <div class="sale-search">
        <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="M20 20l-4-4"/></svg>
        <input id="productSearch" placeholder="Rechercher un produit ou variante">
      </div>
      <div class="sale-products" id="saleProducts"></div>
      <footer class="sale-footer">
        <div><span>Total</span><strong id="saleTotal">0 FCFA</strong></div>
        <button class="primary-button" id="validateSale">Valider la vente</button>
      </footer>
    </section>
  </div>

  <div class="toast" id="toast" role="status" aria-live="polite">Vente enregistrée</div>
  <script src="script.js"></script>
</body>
</html>
