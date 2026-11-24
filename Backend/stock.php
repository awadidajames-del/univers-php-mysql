<?php
declare(strict_types=1);

session_start();
require __DIR__ . '/connexion.php';

$pageTitle = 'Catalogue produits — Univer Cosmetics';
$currentPage = 'stock';

$message = '';
$error = '';

if (empty($_SESSION['stock_csrf'])) {
    $_SESSION['stock_csrf'] = bin2hex(random_bytes(32));
}
$csrfToken = $_SESSION['stock_csrf'];

try {
    $pdo = getDatabaseConnection();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!hash_equals($csrfToken, (string) ($_POST['csrf_token'] ?? ''))) {
            throw new RuntimeException('Session expirée. Rechargez la page puis réessayez.');
        }

        $action = (string) ($_POST['action'] ?? '');

        if ($action === 'save_product') {
            $id = filter_input(INPUT_POST, 'id_produits', FILTER_VALIDATE_INT);
            $name = trim((string) ($_POST['nom_produit'] ?? ''));
            $category = filter_input(INPUT_POST, 'id_categorie', FILTER_VALIDATE_INT);
            $brandName = trim((string) ($_POST['marque'] ?? ''));
            $client = filter_input(INPUT_POST, 'codcli', FILTER_VALIDATE_INT);
            $price = filter_input(INPUT_POST, 'prix_vente', FILTER_VALIDATE_INT);
            $threshold = filter_input(INPUT_POST, 'seuil_stock', FILTER_VALIDATE_INT);
            $quantity = filter_input(INPUT_POST, 'quantite', FILTER_VALIDATE_INT);

            if ($name === '') {
                throw new RuntimeException('Le nom du produit est obligatoire.');
            }
            if ($price === false || $price < 0 || $threshold === false || $threshold < 0 || $quantity === false || $quantity < 0) {
                throw new RuntimeException('Le prix, le seuil et la quantité doivent être des nombres positifs.');
            }

            $category = $category ?: null;
            $brand = null;
            if ($brandName !== '') {
                $statement = $pdo->prepare('SELECT id_marque FROM marques WHERE nom = :name LIMIT 1');
                $statement->execute(['name' => $brandName]);
                $brand = $statement->fetchColumn();

                if ($brand === false) {
                    $statement = $pdo->prepare('INSERT INTO marques (nom, actif) VALUES (:name, 1)');
                    $statement->execute(['name' => $brandName]);
                    $brand = $pdo->lastInsertId();
                }
            }
            $client = $client ?: null;

            if ($id) {
                $sql = 'UPDATE produits
                        SET id_categorie = :category, codcli = :client, id_marque = :brand,
                            nom_produit = :name,
                            prix_vente = :price, seuil_stock = :threshold, quantite = :quantity
                        WHERE id_produits = :id';
                $statement = $pdo->prepare($sql);
                $statement->execute([
                    'category' => $category, 'client' => $client, 'brand' => $brand,
                    'name' => $name,
                    'price' => $price,
                    'threshold' => $threshold, 'quantity' => $quantity, 'id' => $id,
                ]);
                $message = 'Produit modifié avec succès.';
            } else {
                $sql = 'INSERT INTO produits
                        (id_categorie, codcli, id_marque, nom_produit, prix_vente, seuil_stock, quantite)
                        VALUES (:category, :client, :brand, :name, :price, :threshold, :quantity)';
                $statement = $pdo->prepare($sql);
                $statement->execute([
                    'category' => $category, 'client' => $client, 'brand' => $brand,
                    'name' => $name,
                    'price' => $price,
                    'threshold' => $threshold, 'quantity' => $quantity,
                ]);
                $message = 'Produit enregistré dans le stock.';
            }

            header('Location: stock.php?status=saved');
            exit;
        }

        if ($action === 'delete_product') {
            $id = filter_input(INPUT_POST, 'id_produits', FILTER_VALIDATE_INT);
            if (!$id) {
                throw new RuntimeException('Produit introuvable.');
            }
            $statement = $pdo->prepare('DELETE FROM produits WHERE id_produits = :id');
            $statement->execute(['id' => $id]);
            header('Location: stock.php?status=deleted');
            exit;
        }

        if ($action === 'move_stock') {
            $id = filter_input(INPUT_POST, 'id_produits', FILTER_VALIDATE_INT);
            $movementType = (string) ($_POST['type_mouvement'] ?? '');
            $movementQuantity = filter_input(INPUT_POST, 'quantite_mouvement', FILTER_VALIDATE_INT);

            if (!$id || $movementQuantity === false || $movementQuantity < 0) {
                throw new RuntimeException('Le produit et la quantité sont obligatoires.');
            }

            $pdo->beginTransaction();
            $statement = $pdo->prepare('SELECT quantite FROM produits WHERE id_produits = :id FOR UPDATE');
            $statement->execute(['id' => $id]);
            $current = $statement->fetchColumn();

            if ($current === false) {
                $pdo->rollBack();
                throw new RuntimeException('Produit introuvable.');
            }

            $currentQuantity = (int) $current;
            if ($movementType === 'entree') {
                $newQuantity = $currentQuantity + $movementQuantity;
            } elseif ($movementType === 'sortie') {
                if ($movementQuantity <= 0) {
                    $pdo->rollBack();
                    throw new RuntimeException('La quantité sortie doit être supérieure à zéro.');
                }
                if ($movementQuantity > $currentQuantity) {
                    $pdo->rollBack();
                    throw new RuntimeException('Stock insuffisant pour cette sortie.');
                }
                $newQuantity = $currentQuantity - $movementQuantity;
            } elseif ($movementType === 'ajustement') {
                $newQuantity = $movementQuantity;
            } else {
                $pdo->rollBack();
                throw new RuntimeException('Type de mouvement invalide.');
            }

            $statement = $pdo->prepare('UPDATE produits SET quantite = :quantity WHERE id_produits = :id');
            $statement->execute(['quantity' => $newQuantity, 'id' => $id]);
            $pdo->commit();

            header('Location: stock.php?status=movement&check=' . $newQuantity);
            exit;
        }
    }

    $categories = $pdo->query('SELECT id_categorie, nom FROM categories WHERE actif = 1 ORDER BY nom')->fetchAll();
    $brands = $pdo->query('SELECT id_marque, nom FROM marques WHERE actif = 1 ORDER BY nom')->fetchAll();
    $clients = $pdo->query('SELECT codcli, nom, prenom FROM clients ORDER BY nom, prenom')->fetchAll();
    $products = $pdo->query('SELECT p.*, c.nom AS categorie, m.nom AS marque,
                                    cl.nom AS client_nom, cl.prenom AS client_prenom
                             FROM produits p
                             LEFT JOIN categories c ON c.id_categorie = p.id_categorie
                             LEFT JOIN marques m ON m.id_marque = p.id_marque
                             LEFT JOIN clients cl ON cl.codcli = p.codcli
                             ORDER BY p.nom_produit, p.id_produits')->fetchAll();

    $editProduct = null;
    $editId = filter_input(INPUT_GET, 'edit', FILTER_VALIDATE_INT);
    if ($editId) {
        $statement = $pdo->prepare('SELECT * FROM produits WHERE id_produits = :id');
        $statement->execute(['id' => $editId]);
        $editProduct = $statement->fetch() ?: null;
    }

    $status = (string) ($_GET['status'] ?? '');
    if ($status === 'saved') {
        $message = 'Produit enregistré avec succès.';
    } elseif ($status === 'deleted') {
        $message = 'Produit supprimé.';
    } elseif ($status === 'movement') {
        $message = 'Le stock a été mis à jour.';
    }

    $totalQuantity = 0;
    $stockValue = 0;
    $lowStockCount = 0;
    foreach ($products as $product) {
        $quantity = (int) $product['quantite'];
        $totalQuantity += $quantity;
        $stockValue += $quantity * (int) $product['prix_vente'];
        if ($quantity <= (int) $product['seuil_stock']) {
            $lowStockCount++;
        }
    }
} catch (Throwable $exception) {
    $error = $exception instanceof PDOException
        ? 'Impossible d’enregistrer les données dans la base univers. Vérifiez la connexion et les champs du SQL.'
        : $exception->getMessage();
    $categories = $categories ?? [];
    $brands = $brands ?? [];
    $clients = $clients ?? [];
    $products = $products ?? [];
    $editProduct = null;
    $totalQuantity = 0;
    $stockValue = 0;
    $lowStockCount = 0;
}

$formatPrice = static fn(int $value): string => number_format($value, 0, ',', ' ') . ' FCFA';

require __DIR__ . '/partials/head.php';
require __DIR__ . '/partials/sidebar.php';
?>

<main class="main">
    <header class="topbar">
        <button class="menu-button" id="menuButton" type="button" aria-label="Ouvrir le menu"><span></span><span></span><span></span></button>
        <label class="search">
            <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="M20 20l-4-4"/></svg>
            <input id="catalogSearch" type="search" placeholder="Rechercher un produit..." aria-label="Rechercher un produit">
            <kbd>Ctrl K</kbd>
        </label>
        <div class="topbar-actions"><span class="sync-status"><span class="status-dot"></span><span>Données univers</span></span></div>
    </header>

    <section class="content catalog-content">
        <div class="page-heading">
            <div><h1>Catalogue produits</h1></div>
            <div class="catalog-heading-actions">
                <a class="secondary-button" href="ajouterstock.php">Nouveau Stock</a>
            </div>
        </div>

        <?php if ($message !== ''): ?>
            <div class="catalog-message success" role="status"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>
        <?php if ($error !== ''): ?>
            <div class="catalog-message error" role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <div class="stats-grid catalog-stats" aria-label="Aperçu du stock">
            <article class="stat-card"><div class="stat-icon brown"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M21 8l-9-5-9 5 9 5 9-5z"/><path d="M3 8v8l9 5 9-5V8"/></svg></div><div class="stat-copy"><span>Références</span><strong><?= count($products) ?></strong><em>Produits enregistrés</em></div></article>
            <article class="stat-card"><div class="stat-icon rose"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h10"/></svg></div><div class="stat-copy"><span>Unités disponibles</span><strong><?= number_format($totalQuantity, 0, ',', ' ') ?></strong><em>Stock total</em></div></article>
            <article class="stat-card"><div class="stat-icon beige"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg></div><div class="stat-copy"><span>Valeur du stock</span><strong><?= $formatPrice($stockValue) ?></strong><em>Prix de vente</em></div></article>
            <article class="stat-card"><div class="stat-icon cream"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 9v4M12 17h.01"/><path d="M10.3 3.7L2.7 17a2 2 0 0 0 1.7 3h15.2a2 2 0 0 0 1.7-3L13.7 3.7a2 2 0 0 0-3.4 0z"/></svg></div><div class="stat-copy"><span>Stock faible</span><strong><?= $lowStockCount ?></strong><em>À réapprovisionner</em></div></article>
        </div>

        <div class="catalog-layout">
            <section class="panel catalog-list-panel" aria-labelledby="catalogListTitle">
                <div class="panel-header catalog-panel-header">
                    <div><h2 id="catalogListTitle">Tous les produits</h2></div>
                    <span class="catalog-result-count" id="catalogCount"><?= count($products) ?> produit(s)</span>
                </div>
                <div class="catalog-filters">
                    <select id="categoryFilter" aria-label="Filtrer par catégorie"><option value="">Toutes les catégories</option><?php foreach ($categories as $category): ?><option value="<?= (int) $category['id_categorie'] ?>"><?= htmlspecialchars($category['nom'], ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select>
                    <select id="brandFilter" aria-label="Filtrer par marque"><option value="">Toutes les marques</option><?php foreach ($brands as $brand): ?><option value="<?= (int) $brand['id_marque'] ?>"><?= htmlspecialchars($brand['nom'], ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select>
                </div>
                <div class="catalog-table-wrap">
                    <table class="catalog-table">
                        <thead><tr><th>Produit</th><th>Catégorie</th><th>Marque</th><th>Prix</th><th>Quantité</th><th>État</th><th>Actions</th></tr></thead>
                        <tbody id="catalogRows">
                        <?php foreach ($products as $product):
                            $quantity = (int) $product['quantite'];
                            $threshold = (int) $product['seuil_stock'];
                            $stockClass = $quantity === 0 ? 'empty' : ($quantity <= $threshold ? 'low' : '');
                            $stockLabel = $quantity === 0 ? 'Épuisé' : ($quantity <= $threshold ? 'Stock faible' : 'Disponible');
                            $search = strtolower(trim(($product['nom_produit'] ?? '') . ' ' . ($product['categorie'] ?? '') . ' ' . ($product['marque'] ?? '')));
                        ?>
                            <tr data-search="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>" data-category="<?= (int) ($product['id_categorie'] ?? 0) ?>" data-brand="<?= (int) ($product['id_marque'] ?? 0) ?>">
                                <td><strong><?= htmlspecialchars($product['nom_produit'], ENT_QUOTES, 'UTF-8') ?></strong></td>
                                <td><?= htmlspecialchars((string) ($product['categorie'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars((string) ($product['marque'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= $formatPrice((int) $product['prix_vente']) ?></td>
                                <td><span class="stock-quantity <?= $stockClass ?>"><?= $quantity ?></span></td>
                                <td><span class="stock-badge <?= $stockClass === '' ? 'good' : ($quantity === 0 ? 'out' : 'low') ?>"><?= $stockLabel ?></span></td>
                                <td><div class="catalog-actions"><a class="icon-button stock-action" href="stock.php?edit=<?= (int) $product['id_produits'] ?>" aria-label="Modifier <?= htmlspecialchars($product['nom_produit'], ENT_QUOTES, 'UTF-8') ?>">✎</a><form method="post" data-confirm="Supprimer ce produit ?"><input type="hidden" name="csrf_token" value="<?= $csrfToken ?>"><input type="hidden" name="action" value="delete_product"><input type="hidden" name="id_produits" value="<?= (int) $product['id_produits'] ?>"><button class="icon-button stock-action" type="submit" aria-label="Supprimer">×</button></form></div></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                    <div class="empty-state" id="emptyCatalog" hidden><span class="empty-mark">U</span><strong>Aucun produit trouvé</strong><p>Modifiez les filtres ou ajoutez un produit.</p></div>
                </div>
            </section>

           
        </div>
    </section>
</main>
<script src="stock-catalog.js?v=3" defer></script>
</body>
</html>
