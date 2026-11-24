<?php
declare(strict_types=1);

session_start();
require __DIR__ . '/connexion.php';

$pageTitle = 'Ventes — Univer Cosmetics';
$currentPage = 'sales';
$extraStyles = ['ventes.css'];
$error = '';

try {
    $pdo = getDatabaseConnection();
    $products = $pdo->query('SELECT p.id_produits, p.codprod, p.nom_produit, p.detail,
                                    p.prix_vente, p.quantite, p.seuil_stock,
                                    c.nom AS categorie
                             FROM produits p
                             LEFT JOIN categories c ON c.id_categorie = p.id_categorie
                             ORDER BY p.nom_produit')->fetchAll();
    $clients = $pdo->query('SELECT codcli, nom, prenom, telephone
                            FROM clients
                            ORDER BY nom, prenom')->fetchAll();

    $saleTableAvailable = false;
    $sales = [];

    $productCount = count($products);
    $availableUnits = array_sum(array_map(static fn(array $product): int => (int) $product['quantite'], $products));
    $catalogValue = 0;
    $lowStockCount = 0;
    foreach ($products as $product) {
        $quantity = (int) $product['quantite'];
        $catalogValue += $quantity * (int) $product['prix_vente'];
        if ($quantity <= (int) $product['seuil_stock']) {
            $lowStockCount++;
        }
    }
} catch (Throwable $exception) {
    $error = $exception instanceof PDOException
        ? 'Impossible d’accéder aux données de vente dans la base univers.'
        : $exception->getMessage();
    $products = [];
    $clients = [];
    $saleTableAvailable = false;
    $sales = [];
    $productCount = 0;
    $availableUnits = 0;
    $catalogValue = 0;
    $lowStockCount = 0;
}

$formatPrice = static fn(int $value): string => number_format($value, 0, ',', ' ') . ' FCFA';

require __DIR__ . '/partials/head.php';
require __DIR__ . '/partials/sidebar.php';
?>

<main class="main">
    <header class="topbar">
        <button class="menu-button" id="menuButton" type="button" aria-label="Ouvrir le menu"><span></span><span></span><span></span></button>
        <label class="search sales-search">
            <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="M20 20l-4-4"/></svg>
            <input id="salesProductSearch" type="search" placeholder="Rechercher un produit à vendre..." aria-label="Rechercher un produit">
        </label>
        <div class="topbar-actions"><span class="sync-status"><span class="status-dot"></span><span>Données univers</span></span></div>
    </header>

    <section class="content sales-content">
        <div class="page-heading">
            <div><h1>Ventes</h1></div>
            <button class="primary-button" id="newSaleButton" type="button"<?= $saleTableAvailable ? '' : ' disabled' ?>><span>+</span> Nouvelle vente</button>
        </div>

        <?php if ($error !== ''): ?>
            <div class="catalog-message error" role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <?php if (!$saleTableAvailable): ?>
            <div class="sales-data-notice" role="status">
                <strong>Historique des ventes non disponible</strong>
                <span>Le fichier univers.sql ne contient aucune table dédiée aux ventes. Les opérations ne peuvent pas être enregistrées sans modifier le modèle SQL.</span>
            </div>
        <?php endif; ?>

        <div class="stats-grid sales-stats" aria-label="Aperçu des ventes">
            <article class="stat-card"><div class="stat-icon brown"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 3h18v18H3z"/><path d="M7 16V9M12 16V6M17 16v-4"/></svg></div><div class="stat-copy"><span>Ventes enregistrées</span><strong>0</strong><em>Aucune table SQL disponible</em></div></article>
            <article class="stat-card"><div class="stat-icon rose"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 3h2l2.4 11.2a2 2 0 0 0 2 1.6h7.8a2 2 0 0 0 2-1.6L21 7H6"/><circle cx="10" cy="20" r="1"/><circle cx="18" cy="20" r="1"/></svg></div><div class="stat-copy"><span>Produits vendables</span><strong><?= $productCount ?></strong><em><?= $availableUnits ?> unité(s) disponibles</em></div></article>
            <article class="stat-card"><div class="stat-icon beige"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg></div><div class="stat-copy"><span>Valeur disponible</span><strong><?= $formatPrice($catalogValue) ?></strong><em>Au prix de vente</em></div></article>
            <article class="stat-card"><div class="stat-icon cream"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 9v4M12 17h.01"/><path d="M10.3 3.7L2.7 17a2 2 0 0 0 1.7 3h15.2a2 2 0 0 0 1.7-3L13.7 3.7a2 2 0 0 0-3.4 0z"/></svg></div><div class="stat-copy"><span>Stock à surveiller</span><strong><?= $lowStockCount ?></strong><em>Références sous alerte</em></div></article>
        </div>

        <div class="sales-layout">
            <section class="panel sales-history-panel" aria-labelledby="salesHistoryTitle">
                <div class="panel-header sales-panel-header">
                    <div><p class="panel-kicker">HISTORIQUE</p><h2 id="salesHistoryTitle">Dernières ventes</h2></div>
                </div>
                <div class="sales-history-empty">
                    <span class="empty-mark">V</span>
                    <strong>Aucune vente enregistrée</strong>
                    <p>Les ventes apparaîtront ici après ajout d’une structure SQL adaptée.</p>
                </div>
            </section>

            <aside class="panel sales-catalog-panel">
                <div class="panel-header sales-panel-header">
                    <div><p class="panel-kicker">PRODUITS</p><h2>Catalogue vendable</h2></div>
                    <span class="sales-result-count" id="salesResultCount"><?= $productCount ?> produit(s)</span>
                </div>
                <div class="sales-product-list" id="salesProductList">
                    <?php foreach ($products as $product):
                        $quantity = (int) $product['quantite'];
                        $stockClass = $quantity === 0 ? 'empty' : ($quantity <= (int) $product['seuil_stock'] ? 'low' : '');
                        $search = strtolower(trim(($product['nom_produit'] ?? '') . ' ' . ($product['codprod'] ?? '') . ' ' . ($product['categorie'] ?? '')));
                    ?>
                        <article class="sales-product-row" data-search="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>">
                            <div class="sales-product-info">
                                <strong><?= htmlspecialchars($product['nom_produit'], ENT_QUOTES, 'UTF-8') ?></strong>
                                <span><?= htmlspecialchars((string) ($product['categorie'] ?? 'Sans catégorie'), ENT_QUOTES, 'UTF-8') ?> · <?= htmlspecialchars((string) ($product['codprod'] ?? 'Sans code'), ENT_QUOTES, 'UTF-8') ?></span>
                            </div>
                            <strong class="sales-product-price"><?= $formatPrice((int) $product['prix_vente']) ?></strong>
                            <span class="sales-product-stock <?= $stockClass ?>"><?= $quantity ?> en stock</span>
                        </article>
                    <?php endforeach; ?>
                    <?php if ($products === []): ?>
                        <div class="sales-no-products">Aucun produit disponible dans le catalogue.</div>
                    <?php endif; ?>
                </div>
            </aside>
        </div>
    </section>
</main>

<script>
const searchInput = document.getElementById("salesProductSearch");
const productRows = [...document.querySelectorAll(".sales-product-row")];
const resultCount = document.getElementById("salesResultCount");

function filterProducts() {
    const query = searchInput.value.trim().toLocaleLowerCase("fr");
    let visible = 0;
    productRows.forEach(row => {
        const matches = row.dataset.search.includes(query);
        row.hidden = !matches;
        if (matches) visible++;
    });
    resultCount.textContent = visible + " produit(s)";
}

searchInput.addEventListener("input", filterProducts);
document.getElementById("menuButton").addEventListener("click", () => {
    document.getElementById("sidebar").classList.toggle("open");
});
</script>
</body>
</html>
