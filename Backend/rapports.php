<?php
declare(strict_types=1);

session_start();
require __DIR__ . '/connexion.php';

$pageTitle = 'Rapports — Univer Cosmetics';
$currentPage = 'reports';
$extraStyles = ['rapports.css'];
$error = '';

try {
    $pdo = getDatabaseConnection();

    $products = $pdo->query('SELECT p.*, c.nom AS categorie, m.nom AS marque,
                                    cl.nom AS client_nom, cl.prenom AS client_prenom
                             FROM produits p
                             LEFT JOIN categories c ON c.id_categorie = p.id_categorie
                             LEFT JOIN marques m ON m.id_marque = p.id_marque
                             LEFT JOIN clients cl ON cl.codcli = p.codcli
                             ORDER BY p.nom_produit, p.id_produits')->fetchAll();

    $categoryRows = $pdo->query('SELECT COALESCE(c.nom, "Sans catégorie") AS label,
                                        COUNT(p.id_produits) AS product_count,
                                        COALESCE(SUM(p.quantite), 0) AS units,
                                        COALESCE(SUM(p.quantite * p.prix_vente), 0) AS stock_value
                                 FROM produits p
                                 LEFT JOIN categories c ON c.id_categorie = p.id_categorie
                                 GROUP BY c.id_categorie, c.nom
                                 ORDER BY stock_value DESC, label')->fetchAll();

    $brandRows = $pdo->query('SELECT COALESCE(m.nom, "Sans marque") AS label,
                                     COUNT(p.id_produits) AS product_count,
                                     COALESCE(SUM(p.quantite), 0) AS units,
                                     COALESCE(SUM(p.quantite * p.prix_vente), 0) AS stock_value
                              FROM produits p
                              LEFT JOIN marques m ON m.id_marque = p.id_marque
                              GROUP BY m.id_marque, m.nom
                              ORDER BY stock_value DESC, label')->fetchAll();

    $clientCount = (int) $pdo->query('SELECT COUNT(*) FROM clients')->fetchColumn();
    $categoryCount = (int) $pdo->query('SELECT COUNT(*) FROM categories')->fetchColumn();
    $brandCount = (int) $pdo->query('SELECT COUNT(*) FROM marques')->fetchColumn();

    $productCount = count($products);
    $totalUnits = 0;
    $stockValue = 0;
    $emptyCount = 0;
    $lowCount = 0;
    $withoutCodeCount = 0;
    $withoutCategoryCount = 0;
    $withoutBrandCount = 0;

    $emptyProducts = [];
    $lowProducts = [];

    foreach ($products as $product) {
        $quantity = (int) $product['quantite'];
        $threshold = (int) $product['seuil_stock'];
        $totalUnits += $quantity;
        $stockValue += $quantity * (int) $product['prix_vente'];

        if ($quantity === 0) {
            $emptyCount++;
            $emptyProducts[] = $product;
        } elseif ($quantity <= $threshold) {
            $lowCount++;
            $lowProducts[] = $product;
        }
        if (trim((string) ($product['codprod'] ?? '')) === '') $withoutCodeCount++;
        if (empty($product['id_categorie'])) $withoutCategoryCount++;
        if (empty($product['id_marque'])) $withoutBrandCount++;
    }

    $topStockProducts = $products;
    usort($topStockProducts, static fn(array $a, array $b): int => (int) $b['quantite'] <=> (int) $a['quantite']);
    $topStockProducts = array_slice($topStockProducts, 0, 8);

    $topValueProducts = $products;
    usort($topValueProducts, static function(array $a, array $b): int {
        $aValue = (int) $a['quantite'] * (int) $a['prix_vente'];
        $bValue = (int) $b['quantite'] * (int) $b['prix_vente'];
        return $bValue <=> $aValue;
    });
    $topValueProducts = array_slice($topValueProducts, 0, 8);
} catch (Throwable $exception) {
    $error = $exception instanceof PDOException
        ? 'Impossible de charger les rapports depuis la base univers.'
        : $exception->getMessage();
    $products = [];
    $categoryRows = [];
    $brandRows = [];
    $emptyProducts = [];
    $lowProducts = [];
    $topStockProducts = [];
    $topValueProducts = [];
    $clientCount = 0;
    $categoryCount = 0;
    $brandCount = 0;
    $productCount = 0;
    $totalUnits = 0;
    $stockValue = 0;
    $emptyCount = 0;
    $lowCount = 0;
    $withoutCodeCount = 0;
    $withoutCategoryCount = 0;
    $withoutBrandCount = 0;
}

$formatPrice = static fn(int $value): string => number_format($value, 0, ',', ' ') . ' FCFA';

if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="rapport-stock.csv"');
    echo "Code;Produit;Categorie;Marque;Prix;Seuil;Quantite;Valeur stock\r\n";
    foreach ($products as $product) {
        $line = [
            $product['codprod'] ?? '',
            $product['nom_produit'] ?? '',
            $product['categorie'] ?? '',
            $product['marque'] ?? '',
            (int) $product['prix_vente'],
            (int) $product['seuil_stock'],
            (int) $product['quantite'],
            (int) $product['quantite'] * (int) $product['prix_vente'],
        ];
        foreach ($line as $index => $value) {
            if ($index > 0) echo ';';
            echo str_replace(["\r", "\n", ';'], [' ', ' ', ','], (string) $value);
        }
        echo "\r\n";
    }
    exit;
}

require __DIR__ . '/partials/head.php';
require __DIR__ . '/partials/sidebar.php';
?>

<main class="main">
    <header class="topbar">
        <button class="menu-button" id="menuButton" type="button" aria-label="Ouvrir le menu"><span></span><span></span><span></span></button>
        <label class="search reports-search">
            <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="M20 20l-4-4"/></svg>
            <input id="reportsSearch" type="search" placeholder="Rechercher dans les rapports..." aria-label="Rechercher dans les rapports">
        </label>
        <div class="topbar-actions"><a class="secondary-button reports-export" href="?export=csv">Exporter CSV</a><span class="sync-status"><span class="status-dot"></span><span>Données univers</span></span></div>
    </header>

    <section class="content reports-content">
        <div class="page-heading">
            <div><p class="eyebrow">ANALYSE DU CATALOGUE</p><h1>Rapports</h1></div>
            <button class="primary-button" type="button" onclick="window.print()">Imprimer</button>
        </div>

        <?php if ($error !== ''): ?>
            <div class="catalog-message error" role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <div class="stats-grid reports-stats" aria-label="Indicateurs des rapports">
            <article class="stat-card"><div class="stat-icon brown"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M21 8l-9-5-9 5 9 5 9-5z"/><path d="M3 8v8l9 5 9-5V8"/></svg></div><div class="stat-copy"><span>Références</span><strong><?= $productCount ?></strong><em><?= $totalUnits ?> unités au total</em></div></article>
            <article class="stat-card"><div class="stat-icon rose"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg></div><div class="stat-copy"><span>Valeur du stock</span><strong><?= $formatPrice($stockValue) ?></strong><em>Au prix de vente</em></div></article>
            <article class="stat-card"><div class="stat-icon beige"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 9v4M12 17h.01"/><path d="M10.3 3.7L2.7 17a2 2 0 0 0 1.7 3h15.2a2 2 0 0 0 1.7-3L13.7 3.7a2 2 0 0 0-3.4 0z"/></svg></div><div class="stat-copy"><span>Alertes stock</span><strong><?= $emptyCount + $lowCount ?></strong><em><?= $emptyCount ?> épuisé(s), <?= $lowCount ?> faible(s)</em></div></article>
            <article class="stat-card"><div class="stat-icon cream"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg></div><div class="stat-copy"><span>Clients</span><strong><?= $clientCount ?></strong><em><?= $categoryCount ?> catégories · <?= $brandCount ?> marques</em></div></article>
        </div>

        <div class="reports-grid">
            <section class="panel reports-panel reports-breakdown" aria-labelledby="categoryTitle">
                <div class="panel-header"><div><p class="panel-kicker">CATÉGORIES</p><h2 id="categoryTitle">Répartition du stock</h2></div></div>
                <div class="report-table-wrap"><table class="report-table"><thead><tr><th>Catégorie</th><th>Produits</th><th>Unités</th><th>Valeur</th></tr></thead><tbody>
                <?php foreach ($categoryRows as $row): ?><tr data-report-search="<?= htmlspecialchars(strtolower((string) $row['label']), ENT_QUOTES, 'UTF-8') ?>"><td><strong><?= htmlspecialchars((string) $row['label'], ENT_QUOTES, 'UTF-8') ?></strong></td><td><?= (int) $row['product_count'] ?></td><td><?= (int) $row['units'] ?></td><td><?= $formatPrice((int) $row['stock_value']) ?></td></tr><?php endforeach; ?>
                <?php if ($categoryRows === []): ?><tr><td colspan="4">Aucune catégorie disponible.</td></tr><?php endif; ?>
                </tbody></table></div>
            </section>

            <section class="panel reports-panel reports-attention" aria-labelledby="attentionTitle">
                <div class="panel-header"><div><p class="panel-kicker">ALERTES</p><h2 id="attentionTitle">Produits à surveiller</h2></div></div>
                <div class="report-alert-list">
                    <?php foreach (array_merge($emptyProducts, $lowProducts) as $product):
                        $quantity = (int) $product['quantite'];
                    ?>
                        <a class="report-alert-row" href="stock.php?edit=<?= (int) $product['id_produits'] ?>"><span class="report-alert-icon <?= $quantity === 0 ? 'danger' : 'warning' ?>"><?= $quantity === 0 ? '×' : '!' ?></span><span><strong><?= htmlspecialchars((string) $product['nom_produit'], ENT_QUOTES, 'UTF-8') ?></strong><small><?= $quantity === 0 ? 'Épuisé' : 'Stock faible' ?> · <?= $quantity ?> restant(s)</small></span><b><?= $formatPrice((int) $product['prix_vente']) ?></b></a>
                    <?php endforeach; ?>
                    <?php if ($emptyProducts === [] && $lowProducts === []): ?><div class="report-empty">Tous les stocks sont au-dessus du seuil.</div><?php endif; ?>
                </div>
            </section>

            <section class="panel reports-panel reports-quality" aria-labelledby="qualityTitle">
                <div class="panel-header"><div><p class="panel-kicker">QUALITÉ DU CATALOGUE</p><h2 id="qualityTitle">Informations à compléter</h2></div></div>
                <div class="report-quality-list">
                    <div><span>Sans code produit</span><strong><?= $withoutCodeCount ?></strong></div>
                    <div><span>Sans catégorie</span><strong><?= $withoutCategoryCount ?></strong></div>
                    <div><span>Sans marque</span><strong><?= $withoutBrandCount ?></strong></div>
                </div>
            </section>

            <section class="panel reports-panel reports-brand" aria-labelledby="brandTitle">
                <div class="panel-header"><div><p class="panel-kicker">MARQUES</p><h2 id="brandTitle">Répartition par marque</h2></div></div>
                <div class="report-table-wrap"><table class="report-table"><thead><tr><th>Marque</th><th>Produits</th><th>Unités</th><th>Valeur</th></tr></thead><tbody>
                <?php foreach ($brandRows as $row): ?><tr data-report-search="<?= htmlspecialchars(strtolower((string) $row['label']), ENT_QUOTES, 'UTF-8') ?>"><td><strong><?= htmlspecialchars((string) $row['label'], ENT_QUOTES, 'UTF-8') ?></strong></td><td><?= (int) $row['product_count'] ?></td><td><?= (int) $row['units'] ?></td><td><?= $formatPrice((int) $row['stock_value']) ?></td></tr><?php endforeach; ?>
                <?php if ($brandRows === []): ?><tr><td colspan="4">Aucune marque disponible.</td></tr><?php endif; ?>
                </tbody></table></div>
            </section>

            <section class="panel reports-panel reports-top-stock" aria-labelledby="topStockTitle">
                <div class="panel-header"><div><p class="panel-kicker">UNITÉS</p><h2 id="topStockTitle">Plus gros stocks</h2></div></div>
                <div class="report-ranking">
                    <?php foreach ($topStockProducts as $index => $product): ?><a href="stock.php?edit=<?= (int) $product['id_produits'] ?>" data-report-search="<?= htmlspecialchars(strtolower($product['nom_produit'] . ' ' . ($product['categorie'] ?? '') . ' ' . ($product['marque'] ?? '')), ENT_QUOTES, 'UTF-8') ?>"><span><?= $index + 1 ?></span><strong><?= htmlspecialchars((string) $product['nom_produit'], ENT_QUOTES, 'UTF-8') ?></strong><b><?= (int) $product['quantite'] ?> unités</b></a><?php endforeach; ?>
                    <?php if ($topStockProducts === []): ?><div class="report-empty">Aucun produit.</div><?php endif; ?>
                </div>
            </section>

            <section class="panel reports-panel reports-top-value" aria-labelledby="topValueTitle">
                <div class="panel-header"><div><p class="panel-kicker">VALEUR</p><h2 id="topValueTitle">Stocks les plus élevés</h2></div></div>
                <div class="report-ranking">
                    <?php foreach ($topValueProducts as $index => $product):
                        $value = (int) $product['quantite'] * (int) $product['prix_vente'];
                    ?><a href="stock.php?edit=<?= (int) $product['id_produits'] ?>" data-report-search="<?= htmlspecialchars(strtolower($product['nom_produit'] . ' ' . ($product['categorie'] ?? '') . ' ' . ($product['marque'] ?? '')), ENT_QUOTES, 'UTF-8') ?>"><span><?= $index + 1 ?></span><strong><?= htmlspecialchars((string) $product['nom_produit'], ENT_QUOTES, 'UTF-8') ?></strong><b><?= $formatPrice($value) ?></b></a><?php endforeach; ?>
                    <?php if ($topValueProducts === []): ?><div class="report-empty">Aucun produit.</div><?php endif; ?>
                </div>
            </section>
        </div>
    </section>
</main>

<script>
const searchInput = document.getElementById("reportsSearch");
searchInput.addEventListener("input", () => {
    const query = searchInput.value.trim().toLocaleLowerCase("fr");
    document.querySelectorAll("[data-report-search]").forEach(element => {
        element.hidden = query !== "" && !element.dataset.reportSearch.includes(query);
    });
});
document.getElementById("menuButton").addEventListener("click", () => {
    document.getElementById("sidebar").classList.toggle("open");
});
</script>
</body>
</html>
