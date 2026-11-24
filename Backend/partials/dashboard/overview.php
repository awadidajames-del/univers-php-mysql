<section class="content" id="dashboardView">
	<div class="page-heading"><div><p class="eyebrow">Lundi 23 novembre</p><h1>Bonjour, Princesse</h1></div><button class="primary-button" id="newSaleButton" type="button"><span>+</span> Nouvelle vente</button></div>
	<?php require __DIR__ . '/stats.php'; ?>
	<div class="dashboard-grid">
		<?php require __DIR__ . '/sales-chart.php'; ?>
		<?php require __DIR__ . '/tasks.php'; ?>
		<?php require __DIR__ . '/top-products.php'; ?>
		<?php require __DIR__ . '/activity.php'; ?>
	</div>
</section>