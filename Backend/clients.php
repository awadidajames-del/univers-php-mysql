<?php
declare(strict_types=1);
?>
<!doctype html>
<html lang="fr">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="theme-color" content="#302a28">
	<title>Clients — Univer Cosmetics</title>
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@600;700&family=Playfair+Display:wght@600&display=swap" rel="stylesheet">
	<link rel="stylesheet" href="styles.css">
</head>
<body>
	<aside class="sidebar" id="sidebar">
		<a class="brand" href="indet.php" aria-label="Univer Cosmetics, tableau de bord">
			<span class="brand-mark">U</span>
			<span><span class="brand-name">UNIVER</span><span class="brand-sub">COSMETICS</span></span>
		</a>
		<nav class="nav" aria-label="Navigation principale">
			<a class="nav-item" href="indet.php"><svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="3" width="7" height="7" rx="2"/><rect x="14" y="3" width="7" height="7" rx="2"/><rect x="3" y="14" width="7" height="7" rx="2"/><rect x="14" y="14" width="7" height="7" rx="2"/></svg><span>Tableau de bord</span></a>
			<a class="nav-item" href="indet.php"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 3h2l2.4 11.2a2 2 0 0 0 2 1.6h7.8a2 2 0 0 0 2-1.6L21 7H6"/><circle cx="10" cy="20" r="1"/><circle cx="18" cy="20" r="1"/></svg><span>Ventes</span></a>
			<a class="nav-item" href="stock.php"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M21 8l-9-5-9 5 9 5 9-5z"/><path d="M3 8v8l9 5 9-5V8"/><path d="M12 13v8"/></svg><span>Stock</span></a>
			<a class="nav-item active" href="clients.php" aria-current="page"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg><span>Clients</span></a>
			<a class="nav-item" href="indet.php"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 20V10"/><path d="M10 20V4"/><path d="M16 20v-7"/><path d="M22 20H2"/></svg><span>Rapports</span></a>
		</nav>
		<div class="sidebar-bottom">
			<a class="nav-item" href="indet.php"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.7 1.7 0 0 0 .34 1.88l.06.06-1.42 1.42-.06-.06a1.7 1.7 0 0 0-1.88-.34 1.7 1.7 0 0 0-1.04 1.56V20h-2v-.48A1.7 1.7 0 0 0 12.34 18a1.7 1.7 0 0 0-1.88.34l-.06.06-1.42-1.42.06-.06A1.7 1.7 0 0 0 9.38 15a1.7 1.7 0 0 0-1.56-1.04H7.4v-2h.42A1.7 1.7 0 0 0 9.38 10.9a1.7 1.7 0 0 0-.34-1.88l-.06-.06 1.42-1.42.06.06A1.7 1.7 0 0 0 12.34 7a1.7 1.7 0 0 0 1.04-1.56V5h2v.44A1.7 1.7 0 0 0 16.44 7a1.7 1.7 0 0 0 1.88-.34l.06-.06 1.42 1.42-.06.06A1.7 1.7 0 0 0 19.4 10a1.7 1.7 0 0 0 1.56 1.04H21v2h-.04A1.7 1.7 0 0 0 19.4 15z"/></svg><span>Paramètres</span></a>
			<div class="profile"><div class="avatar">MP</div><div class="profile-text"><strong>Mme. Princesse</strong><span>Administratrice</span></div><button class="icon-button" type="button" aria-label="Menu utilisateur">•••</button></div>
		</div>
	</aside>

	<main class="main">
		<header class="topbar">
			<button class="menu-button" id="menuButton" type="button" aria-label="Ouvrir le menu"><span></span><span></span><span></span></button>
			<label class="search clients-top-search">
				<svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="M20 20l-4-4"/></svg>
				<input id="clientSearch" type="search" placeholder="Rechercher un client ou produit..." aria-label="Rechercher un client ou produit">
			</label>
			<div class="topbar-actions"><span class="sync-status"><span class="status-dot"></span><span>Données locales</span></span></div>
		</header>

		<section class="content clients-content">
			<div class="page-heading">
				<div><p class="eyebrow">RELATION CLIENT</p><h1>Clients</h1></div>
				<button class="primary-button" id="newClientButton" type="button"><span>+</span> Nouveau client</button>
			</div>

			<div class="stats-grid clients-stats" aria-label="Aperçu des clients">
				<article class="stat-card"><div class="stat-icon brown"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/></svg></div><div class="stat-copy"><span>Clients enregistrés</span><strong id="clientCount">0</strong><em>Dans votre fichier client</em></div></article>
				<article class="stat-card"><div class="stat-icon rose"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 3h2l2.4 11.2a2 2 0 0 0 2 1.6h7.8a2 2 0 0 0 2-1.6L21 7H6"/><circle cx="10" cy="20" r="1"/><circle cx="18" cy="20" r="1"/></svg></div><div class="stat-copy"><span>Clients acheteurs</span><strong id="buyerCount">0</strong><em>Au moins une commande</em></div></article>
				<article class="stat-card"><div class="stat-icon beige"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h10"/><circle cx="19" cy="17" r="3"/></svg></div><div class="stat-copy"><span>Commandes passées</span><strong id="orderCount">0</strong><em>Historique enregistré</em></div></article>
				<article class="stat-card"><div class="stat-icon cream"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg></div><div class="stat-copy"><span>Chiffre d’affaires clients</span><strong id="customerRevenue">0 <small>FCFA</small></strong><em>Cumul des commandes</em></div></article>
			</div>

			<div class="clients-layout">
				<section class="panel client-list-panel" aria-labelledby="clientListTitle">
					<div class="panel-header clients-panel-header"><div><p class="panel-kicker">FICHIER CLIENT</p><h2 id="clientListTitle">Tous les clients</h2></div><div class="client-filters" role="group" aria-label="Filtrer les clients"><button class="filter-button active" type="button" data-filter="all" aria-pressed="true">Tous</button><button class="filter-button" type="button" data-filter="buyers" aria-pressed="false">Avec commandes</button></div></div>
					<div class="client-table-wrap">
						<table class="client-table">
							<thead><tr><th>Client</th><th>Contact</th><th>Commandes</th><th>Total dépensé</th><th>Dernière commande</th></tr></thead>
							<tbody id="clientRows"></tbody>
						</table>
						<div class="empty-state" id="emptyClients" hidden><span class="empty-mark">U</span><strong>Aucun client trouvé</strong><p>Modifiez votre recherche ou ajoutez un client.</p></div>
					</div>
				</section>

				<aside class="panel client-detail-panel" id="clientDetails" aria-live="polite" aria-label="Détail du client"></aside>
			</div>
		</section>
	</main>

	<div class="modal-backdrop" id="clientModal" hidden>
		<section class="modal client-modal" role="dialog" aria-modal="true" aria-labelledby="clientModalTitle">
			<header class="modal-header"><div><p class="panel-kicker">FICHIER CLIENT</p><h2 id="clientModalTitle">Nouveau client</h2></div><button class="icon-button" id="closeClientModal" type="button" aria-label="Fermer">×</button></header>
			<form id="clientForm">
				<div class="client-form-fields"><label>Nom complet<input name="name" required maxlength="100" autocomplete="name" placeholder="Ex. Aïcha Fall"></label><label>Téléphone<input name="phone" type="tel" maxlength="30" autocomplete="tel" placeholder="Ex. +221 77 000 00 00"></label><label>Adresse e-mail<input name="email" type="email" maxlength="150" autocomplete="email" placeholder="Ex. aicha@email.com"></label></div>
				<footer class="client-form-footer"><button class="secondary-button" id="cancelClientModal" type="button">Annuler</button><button class="primary-button" type="submit">Enregistrer</button></footer>
			</form>
		</section>
	</div>
	<div class="toast" id="clientToast" role="status" aria-live="polite"></div>
	<script src="clients.js" defer></script>
</body>
</html>
