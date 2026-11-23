<?php
declare(strict_types=1);
?>
<!doctype html>
<html lang="fr">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="theme-color" content="#302a28">
	<title>Stock — Univer Cosmetics</title>
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
			<a class="nav-item active" href="stock.php" aria-current="page"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M21 8l-9-5-9 5 9 5 9-5z"/><path d="M3 8v8l9 5 9-5V8"/><path d="M12 13v8"/></svg><span>Stock</span></a>
			<a class="nav-item" href="clients.php"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg><span>Clients</span></a>
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
			<label class="search clients-top-search"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="M20 20l-4-4"/></svg><input id="stockSearch" type="search" placeholder="Rechercher un produit..." aria-label="Rechercher un produit"></label>
			<div class="topbar-actions"><span class="sync-status"><span class="status-dot"></span><span>Données locales</span></span></div>
		</header>

		<section class="content inventory-content">
			<div class="page-heading">
				<div><p class="eyebrow">INVENTAIRE</p><h1>Gestion du stock</h1></div>
				<button class="primary-button" id="newProductButton" type="button"><span>+</span> Nouveau produit</button>
			</div>

			<div class="stats-grid inventory-stats" aria-label="Aperçu de l’inventaire">
				<article class="stat-card"><div class="stat-icon brown"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M21 8l-9-5-9 5 9 5 9-5z"/><path d="M3 8v8l9 5 9-5V8"/><path d="M12 13v8"/></svg></div><div class="stat-copy"><span>Références</span><strong id="productCount">0</strong><em>Produits suivis</em></div></article>
				<article class="stat-card"><div class="stat-icon beige"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16"/><circle cx="8" cy="7" r="2"/><circle cx="15" cy="12" r="2"/><circle cx="10" cy="17" r="2"/></svg></div><div class="stat-copy"><span>Unités en stock</span><strong id="unitCount">0</strong><em>Toutes références</em></div></article>
				<article class="stat-card"><div class="stat-icon rose"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3 2.8 20h18.4L12 3z"/><path d="M12 9v5M12 17h.01"/></svg></div><div class="stat-copy"><span>À réapprovisionner</span><strong id="lowStockCount">0</strong><em>Au seuil ou en dessous</em></div></article>
				<article class="stat-card"><div class="stat-icon cream"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg></div><div class="stat-copy"><span>Valeur au prix de vente</span><strong id="stockValue">0 <small>FCFA</small></strong><em>Estimation de l’inventaire</em></div></article>
			</div>

			<div class="inventory-layout">
				<section class="panel inventory-list-panel" aria-labelledby="inventoryTitle">
					<div class="panel-header inventory-panel-header">
						<div><p class="panel-kicker">PRODUITS</p><h2 id="inventoryTitle">Inventaire</h2></div>
						<div class="inventory-controls">
							<div class="inventory-filters" role="group" aria-label="Filtrer les produits"><button class="inventory-filter active" data-filter="all" type="button" aria-pressed="true">Tous</button><button class="inventory-filter" data-filter="low" type="button" aria-pressed="false">Stock bas</button><button class="inventory-filter" data-filter="out" type="button" aria-pressed="false">Rupture</button></div>
							<select id="categoryFilter" aria-label="Filtrer par catégorie"><option value="all">Toutes catégories</option></select>
						</div>
					</div>
					<div class="inventory-table-wrap">
						<table class="inventory-table">
							<thead><tr><th>Produit</th><th>Catégorie</th><th>Prix de vente</th><th>Quantité</th><th>Seuil</th><th>État</th></tr></thead>
							<tbody id="productRows"></tbody>
						</table>
						<div class="empty-state" id="emptyProducts" hidden><span class="empty-mark">U</span><strong>Aucun produit trouvé</strong><p>Modifiez les filtres ou ajoutez un produit.</p></div>
					</div>
				</section>

				<aside class="panel inventory-detail-panel" id="productDetails" aria-live="polite" aria-label="Détail du produit"></aside>
			</div>
		</section>
	</main>

	<div class="modal-backdrop" id="productModal" hidden>
		<section class="modal inventory-modal" role="dialog" aria-modal="true" aria-labelledby="productModalTitle">
			<header class="modal-header"><div><p class="panel-kicker">CATALOGUE</p><h2 id="productModalTitle">Nouveau produit</h2></div><button class="icon-button" id="closeProductModal" type="button" aria-label="Fermer">×</button></header>
			<form id="productForm">
				<div class="inventory-form-fields">
					<label>Nom du produit<input name="name" required maxlength="100" placeholder="Ex. Sérum hydratant"></label>
					<div class="form-two-columns"><label>Catégorie<select name="category" required><option value="Beauté">Beauté</option><option value="Corps">Corps</option><option value="Maquillage">Maquillage</option><option value="Parfum">Parfum</option><option value="Autre">Autre</option></select></label><label>Variante / format<input name="detail" maxlength="80" placeholder="Ex. 30 ml"></label></div>
					<div class="form-two-columns"><label>Prix de vente (FCFA)<input name="price" type="number" min="0" step="1" required></label><label>Seuil d’alerte<input name="threshold" type="number" min="0" step="1" required></label></div>
					<label id="initialStockField">Stock initial<input name="stock" type="number" min="0" step="1" value="0" required><small>Les réapprovisionnements suivants se font par mouvement de stock.</small></label>
				</div>
				<footer class="inventory-form-footer"><button class="secondary-button" id="cancelProductModal" type="button">Annuler</button><button class="primary-button" type="submit">Enregistrer</button></footer>
			</form>
		</section>
	</div>

	<div class="modal-backdrop" id="movementModal" hidden>
		<section class="modal inventory-modal" role="dialog" aria-modal="true" aria-labelledby="movementModalTitle">
			<header class="modal-header"><div><p class="panel-kicker">INVENTAIRE</p><h2 id="movementModalTitle">Mouvement de stock</h2></div><button class="icon-button" id="closeMovementModal" type="button" aria-label="Fermer">×</button></header>
			<form id="movementForm">
				<div class="inventory-form-fields"><p class="movement-product" id="movementProductName"></p><label>Type de mouvement<select name="type"><option value="entry">Entrée</option><option value="exit">Sortie</option><option value="adjustment">Ajustement du stock réel</option></select></label><label id="movementQuantityLabel">Quantité<input name="quantity" type="number" min="1" step="1" required></label><label>Motif (facultatif)<input name="note" maxlength="120" placeholder="Ex. Réapprovisionnement fournisseur"></label><p class="form-error" id="movementError" role="alert" hidden></p></div>
				<footer class="inventory-form-footer"><button class="secondary-button" id="cancelMovementModal" type="button">Annuler</button><button class="primary-button" type="submit">Enregistrer le mouvement</button></footer>
			</form>
		</section>
	</div>

	<div class="toast" id="inventoryToast" role="status" aria-live="polite"></div>
	<script src="stock.js" defer></script>
</body>
</html>
