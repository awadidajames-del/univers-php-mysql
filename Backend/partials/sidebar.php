<?php
$currentPage = $currentPage ?? 'dashboard';
$activeClass = static fn(string $page): string => $currentPage === $page ? ' active' : '';
?>
<aside class="sidebar" id="sidebar">
	<a class="brand" href="dashboard.php" aria-label="Univer Cosmetics, tableau de bord">
		<span class="brand-mark">U</span>
		<span><span class="brand-name">UNIVER</span><span class="brand-sub">COSMETICS</span></span>
	</a>
	<nav class="nav" aria-label="Navigation principale">
		<a class="nav-item<?= $activeClass('dashboard') ?>" href="dashboard.php"<?= $currentPage === 'dashboard' ? ' aria-current="page"' : '' ?>>
			<svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="3" width="7" height="7" rx="2"/><rect x="14" y="3" width="7" height="7" rx="2"/><rect x="3" y="14" width="7" height="7" rx="2"/><rect x="14" y="14" width="7" height="7" rx="2"/></svg><span>Tableau de bord</span>
		</a>
		<a class="nav-item<?= $activeClass('sales') ?>" href="ventes.php"<?= $currentPage === 'sales' ? ' aria-current="page"' : '' ?>>
			<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 3h2l2.4 11.2a2 2 0 0 0 2 1.6h7.8a2 2 0 0 0 2-1.6L21 7H6"/><circle cx="10" cy="20" r="1"/><circle cx="18" cy="20" r="1"/></svg><span>Ventes</span>
		</a>
		<a class="nav-item<?= $activeClass('stock') ?>" href="stock.php"<?= $currentPage === 'stock' ? ' aria-current="page"' : '' ?>>
			<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M21 8l-9-5-9 5 9 5 9-5z"/><path d="M3 8v8l9 5 9-5V8"/><path d="M12 13v8"/></svg><span>Stock</span>
		</a>
		<a class="nav-item<?= $activeClass('categories') ?>" href="categorie.php"<?= $currentPage === 'categories' ? ' aria-current="page"' : '' ?>>
			<svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg><span>Catégories</span>
		</a>
		<a class="nav-item<?= $activeClass('clients') ?>" href="clients.php"<?= $currentPage === 'clients' ? ' aria-current="page"' : '' ?>>
			<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg><span>Clients</span>
		</a>
		<a class="nav-item<?= $activeClass('reports') ?>" href="rapports.php"<?= $currentPage === 'reports' ? ' aria-current="page"' : '' ?>>
			<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 20V10"/><path d="M10 20V4"/><path d="M16 20v-7"/><path d="M22 20H2"/></svg><span>Rapports</span>
		</a>
	</nav>
	<div class="sidebar-bottom">
		<a class="nav-item<?= $activeClass('settings') ?>" href="#" data-page="settings">
			<svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.7 1.7 0 0 0 .34 1.88l.06.06-1.42 1.42-.06-.06a1.7 1.7 0 0 0-1.88-.34 1.7 1.7 0 0 0-1.04 1.56V20h-2v-.48A1.7 1.7 0 0 0 12.34 18a1.7 1.7 0 0 0-1.88.34l-.06.06-1.42-1.42.06-.06A1.7 1.7 0 0 0 9.38 15a1.7 1.7 0 0 0-1.56-1.04H7.4v-2h.42A1.7 1.7 0 0 0 9.38 10.9a1.7 1.7 0 0 0-.34-1.88l-.06-.06 1.42-1.42.06.06A1.7 1.7 0 0 0 12.34 7a1.7 1.7 0 0 0 1.04-1.56V5h2v.44A1.7 1.7 0 0 0 16.44 7a1.7 1.7 0 0 0 1.88-.34l-.06-.06 1.42 1.42-.06.06A1.7 1.7 0 0 0 19.4 10a1.7 1.7 0 0 0 1.56 1.04H21v2h-.04A1.7 1.7 0 0 0 19.4 15z"/></svg><span>Paramètres</span>
		</a>
		<div class="profile"><div class="avatar">MP</div><div class="profile-text"><strong>Mme. Princesse</strong><span>Administratrice</span></div><button class="icon-button" type="button" aria-label="Menu utilisateur">•••</button></div>
	</div>
</aside>