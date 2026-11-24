<?php
declare(strict_types=1);

session_start();
require __DIR__ . '/connexion.php';

$pageTitle = 'Tableau de bord — Univer Cosmetics';
$currentPage = 'dashboard';
$extraStyles = ['dashboard.css'];
$error = '';

try {
    $pdo = getDatabaseConnection();

    $productCount = (int) $pdo->query('SELECT COUNT(*) FROM produits')->fetchColumn();
    $clientCount = (int) $pdo->query('SELECT COUNT(*) FROM clients')->fetchColumn();
    $categoryCount = (int) $pdo->query('SELECT COUNT(*) FROM categories')->fetchColumn();
    $brandCount = (int) $pdo->query('SELECT COUNT(*) FROM marques')->fetchColumn();

    $stockRows = $pdo->query('SELECT p.id_produits, p.codprod, p.nom_produit, p.detail,
                                     p.prix_vente, p.quantite, p.seuil_stock,
                                     c.nom AS categorie, m.nom AS marque
                              FROM produits p
                              LEFT JOIN categories c ON c.id_categorie = p.id_categorie
                              LEFT JOIN marques m ON m.id_marque = p.id_marque
                              ORDER BY p.id_produits DESC')->fetchAll();

    $totalUnits = 0;
    $stockValue = 0;
    $lowStockProducts = [];
    $emptyStockProducts = [];

    foreach ($stockRows as $product) {
        $quantity = (int) $product['quantite'];
        $threshold = (int) $product['seuil_stock'];
        $totalUnits += $quantity;
        $stockValue += $quantity * (int) $product['prix_vente'];

        if ($quantity === 0) {
            $emptyStockProducts[] = $product;
        } elseif ($quantity <= $threshold) {
            $lowStockProducts[] = $product;
        }
    }

    $attentionProducts = array_merge($emptyStockProducts, $lowStockProducts);
    $attentionProducts = array_slice($attentionProducts, 0, 6);
    $recentProducts = array_slice($stockRows, 0, 6);
} catch (Throwable $exception) {
    $error = $exception instanceof PDOException
        ? 'Impossible de charger les données du tableau de bord depuis univers.'
        : $exception->getMessage();
    $productCount = 0;
    $clientCount = 0;
    $categoryCount = 0;
    $brandCount = 0;
    $totalUnits = 0;
    $stockValue = 0;
    $attentionProducts = [];
    $recentProducts = [];
}

$formatPrice = static fn(int $value): string => number_format($value, 0, ',', ' ') . ' FCFA';
$days = ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'];
$months = ['', 'janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre'];
$now = new DateTime();
$today = $days[(int) $now->format('w')] . ' ' . $now->format('d') . ' ' . $months[(int) $now->format('n')] . ' ' . $now->format('Y');

require __DIR__ . '/partials/head.php';
require __DIR__ . '/partials/sidebar.php';
?>

<main class="main">
    <header class="topbar">
        <button class="menu-button" id="menuButton" type="button" aria-label="Ouvrir le menu"><span></span><span></span><span></span></button>
        <div class="dashboard-top-title"><span>Univer Cosmetics</span><strong>Tableau de bord</strong></div>
        <div class="topbar-actions">
            <a class="topbar-link" href="ventes.php">Caisse</a>
            <span class="sync-status"><span class="status-dot"></span><span>Données univers</span></span>
        </div>
    </header>

    <section class="content dashboard-content">
        <div class="page-heading dashboard-heading">
            <div><p class="eyebrow"><?= htmlspecialchars(ucfirst($today), ENT_QUOTES, 'UTF-8') ?></p><h1>Bonjour, Princesse</h1></div>
            <div class="dashboard-heading-actions">
                <a class="secondary-button" href="ajouterstock.php">Ajouter du stock</a>
                <a class="primary-button" href="ventes.php"><span>+</span> Nouvelle vente</a>
            </div>
        </div>

        <?php if ($error !== ''): ?>
            <div class="catalog-message error" role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <div class="dashboard-kpi-grid" aria-label="Indicateurs principaux">
            <article class="dashboard-kpi"><div class="dashboard-kpi-icon brown"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M21 8l-9-5-9 5 9 5 9-5z"/><path d="M3 8v8l9 5 9-5V8"/></svg></div><div><span>Références</span><strong><?= $productCount ?></strong><small><?= $totalUnits ?> unités au total</small></div></article>
            <article class="dashboard-kpi"><div class="dashboard-kpi-icon rose"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/></svg></div><div><span>Clients</span><strong><?= $clientCount ?></strong><small>Fichier client actif</small></div></article>
            <article class="dashboard-kpi"><div class="dashboard-kpi-icon beige"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16"/></svg></div><div><span>Catégories</span><strong><?= $categoryCount ?></strong><small><?= $brandCount ?> marques enregistrées</small></div></article>
            <article class="dashboard-kpi"><div class="dashboard-kpi-icon cream"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg></div><div><span>Valeur du stock</span><strong><?= $formatPrice($stockValue) ?></strong><small>Au prix de vente</small></div></article>
        </div>

        <div class="dashboard-grid">
            <section class="panel dashboard-recent-panel" aria-labelledby="recentTitle">
                <div class="panel-header"><div><p class="panel-kicker">CATALOGUE</p><h2 id="recentTitle">Derniers produits enregistrés</h2></div><a class="text-button" href="stock.php">Voir tout</a></div>
                <div class="dashboard-product-list">
                    <?php if ($recentProducts === []): ?>
                        <div class="dashboard-empty">Aucun produit disponible.</div>
                    <?php endif; ?>
                    <?php foreach ($recentProducts as $product):
                        $quantity = (int) $product['quantite'];
                        $threshold = (int) $product['seuil_stock'];
                        $stateClass = $quantity === 0 ? 'empty' : ($quantity <= $threshold ? 'low' : 'good');
                    ?>
                        <a class="dashboard-product-row" href="stock.php?edit=<?= (int) $product['id_produits'] ?>">
                            <span class="dashboard-product-mark"><?= htmlspecialchars(mb_strtoupper(mb_substr((string) $product['nom_produit'], 0, 1, 'UTF-8')), ENT_QUOTES, 'UTF-8') ?></span>
                            <span class="dashboard-product-copy"><strong><?= htmlspecialchars((string) $product['nom_produit'], ENT_QUOTES, 'UTF-8') ?></strong><small><?= htmlspecialchars((string) ($product['categorie'] ?? 'Sans catégorie'), ENT_QUOTES, 'UTF-8') ?> · <?= htmlspecialchars((string) ($product['marque'] ?? 'Sans marque'), ENT_QUOTES, 'UTF-8') ?></small></span>
                            <span class="dashboard-product-stock <?= $stateClass ?>"><?= $quantity ?></span>
                            <strong class="dashboard-product-price"><?= $formatPrice((int) $product['prix_vente']) ?></strong>
                        </a>
                    <?php endforeach; ?>
                </div>
            </section>

            <aside class="panel dashboard-attention-panel" aria-labelledby="attentionTitle">
                <div class="panel-header"><div><p class="panel-kicker">PRIORITÉS</p><h2 id="attentionTitle">Stock à surveiller</h2></div><a class="text-button" href="stock.php">Stock</a></div>
                <div class="dashboard-attention-list">
                    <?php if ($attentionProducts === []): ?>
                        <div class="dashboard-empty">Tous les stocks sont au-dessus du seuil.</div>
                    <?php endif; ?>
                    <?php foreach ($attentionProducts as $product):
                        $quantity = (int) $product['quantite'];
                        $stateLabel = $quantity === 0 ? 'Épuisé' : 'Stock faible';
                    ?>
                        <a class="dashboard-attention-row" href="stock.php?edit=<?= (int) $product['id_produits'] ?>">
                            <span class="dashboard-attention-icon <?= $quantity === 0 ? 'danger' : 'warning' ?>"><?= $quantity === 0 ? '×' : '!' ?></span>
                            <span><strong><?= htmlspecialchars((string) $product['nom_produit'], ENT_QUOTES, 'UTF-8') ?></strong><small><?= $stateLabel ?> · <?= $quantity ?> restant(s)</small></span>
                            <span class="dashboard-attention-arrow">›</span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </aside>

            <section class="panel dashboard-shortcuts-panel" aria-labelledby="shortcutsTitle">
                <div class="panel-header"><div><p class="panel-kicker">ACCÈS RAPIDE</p><h2 id="shortcutsTitle">Gestion</h2></div></div>
                <div class="dashboard-shortcuts">
                    <a href="stock.php"><span>+</span><strong>Catalogue stock</strong><small>Consulter les produits</small></a>
                    <a href="ajouterstock.php"><span>U</span><strong>Ajouter un produit</strong><small>Enregistrer une entrée</small></a>
                    <a href="categorie.php"><span>•</span><strong>Catégories</strong><small>Organiser le catalogue</small></a>
                    <a href="clients.php"><span>i</span><strong>Clients</strong><small>Gérer le fichier client</small></a>
                </div>
            </section>
        </div>
    </section>
</main>
<script>
document.getElementById("menuButton").addEventListener("click", () => {
    document.getElementById("sidebar").classList.toggle("open");
});
</script>
</body>
</html>
