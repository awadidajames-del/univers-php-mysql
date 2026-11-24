<?php
declare(strict_types=1);

session_start();
require __DIR__ . '/connexion.php';

$pageTitle = 'Ajouter un stock — Univer Cosmetics';
$currentPage = 'stock';
$extraStyles = ['ajouterstock.css'];
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

        $statement = $pdo->prepare('INSERT INTO produits
            (id_categorie, codcli, id_marque, nom_produit, prix_vente, seuil_stock, quantite)
            VALUES (:category, :client, :brand, :name, :price, :threshold, :quantity)');
        $statement->execute([
            'category' => $category ?: null,
            'client' => $client ?: null,
            'brand' => $brand ?: null,
            'name' => $name,
            'price' => $price,
            'threshold' => $threshold,
            'quantity' => $quantity,
        ]);

        header('Location: stock.php?status=saved');
        exit;
    }

    $categories = $pdo->query('SELECT id_categorie, nom FROM categories WHERE actif = 1 ORDER BY nom')->fetchAll();
    $brands = $pdo->query('SELECT id_marque, nom FROM marques WHERE actif = 1 ORDER BY nom')->fetchAll();
    $clients = $pdo->query('SELECT codcli, nom, prenom FROM clients ORDER BY nom, prenom')->fetchAll();
} catch (Throwable $exception) {
    $error = $exception instanceof PDOException
        ? 'Impossible d’enregistrer le produit dans la base univers.'
        : $exception->getMessage();
    $categories = $categories ?? [];
    $brands = $brands ?? [];
    $clients = $clients ?? [];
}

require __DIR__ . '/partials/head.php';
require __DIR__ . '/partials/sidebar.php';
?>

<main class="main">
    <header class="topbar">
        <button class="menu-button" id="menuButton" type="button" aria-label="Ouvrir le menu"><span></span><span></span><span></span></button>
        <div class="stock-page-search"><span>STOCK</span><strong>Nouvelle réception</strong></div>
        <div class="topbar-actions"><span class="sync-status"><span class="status-dot"></span><span>Données univers</span></span></div>
    </header>

    <section class="content stock-entry-content">
        <div class="page-heading">
            <div><p class="eyebrow">GESTION DES ARTICLES</p><h1>Ajouter un stock</h1></div>
            <a class="secondary-button stock-back-button" href="stock.php">Retour au catalogue</a>
        </div>

        <?php if ($error !== ''): ?>
            <div class="catalog-message error" role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <form class="stock-entry-layout" id="stockForm" method="post">
            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">

            <div class="stock-entry-fields">
                <section class="panel stock-entry-card">
                    <div class="panel-header stock-entry-card-header"><div><p class="panel-kicker">ÉTAPE 1</p><h2>Identification du produit</h2></div></div>
                    <div class="stock-entry-grid">
                        <label>Nom du produit <input name="nom_produit" required maxlength="160" placeholder="Ex. Sérum éclat vitamine C"></label>
                        <label>Catégorie<select name="id_categorie"><option value="">Choisir une catégorie</option><?php foreach ($categories as $category): ?><option value="<?= (int) $category['id_categorie'] ?>"><?= htmlspecialchars($category['nom'], ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select></label>
                        <label>Marque<input name="marque" maxlength="120" list="brandSuggestions" placeholder="Saisir ou choisir une marque"><datalist id="brandSuggestions"><?php foreach ($brands as $brand): ?><option value="<?= htmlspecialchars($brand['nom'], ENT_QUOTES, 'UTF-8') ?>"><?php endforeach; ?></datalist></label>
                    </div>
                </section>

                <section class="panel stock-entry-card">
                    <div class="panel-header stock-entry-card-header"><div><p class="panel-kicker">ÉTAPE 2</p><h2>Informations sur le stock</h2></div></div>
                    <div class="stock-entry-grid">
                        <label>Quantité reçue *<input id="quantity" name="quantite" type="number" min="0" step="1" value="0" required><small>Nombre d’unités disponibles</small></label>
                        <label>Seuil d’alerte *<input name="seuil_stock" type="number" min="0" step="1" value="5" required><small>Alerte quand le stock atteint ce niveau</small></label>
                    </div>
                </section>

                <section class="panel stock-entry-card">
                    <div class="panel-header stock-entry-card-header"><div><p class="panel-kicker">ÉTAPE 3</p><h2>Informations financières</h2></div></div>
                    <div class="stock-entry-grid">
                        <label>Prix de vente unitaire (FCFA) *<input id="unitPrice" name="prix_vente" type="number" min="0" step="1" value="0" required></label>
                        <div class="stock-total-preview"><span>Valeur totale du stock</span><strong id="stockTotal">0 FCFA</strong></div>
                    </div>
                </section>
            </div>

            <aside class="panel stock-entry-summary">
                <div class="panel-header stock-entry-card-header"><div><p class="panel-kicker">RÉCAPITULATIF</p><h2>Avant enregistrement</h2></div></div>
                <dl class="stock-summary-list">
                    <div><dt>Produit</dt><dd id="summaryName">Non renseigné</dd></div>
                    <div><dt>Quantité</dt><dd id="summaryQuantity">0 unité(s)</dd></div>
                    <div><dt>Prix unitaire</dt><dd id="summaryPrice">0 FCFA</dd></div>
                </dl>
                <div class="stock-summary-total"><span>Valeur totale</span><strong id="summaryTotal">0 FCFA</strong></div>
                <button class="primary-button stock-submit-button" type="submit"><span>+</span> Enregistrer le stock</button>
                <a class="secondary-button stock-cancel-button" href="stock.php">Annuler</a>
            </aside>
        </form>
    </section>
</main>

<script>
const quantity = document.getElementById("quantity");
const unitPrice = document.getElementById("unitPrice");
const nameInput = document.querySelector('[name="nom_produit"]');

function formatPrice(value) {
    return new Intl.NumberFormat("fr-FR").format(value) + " FCFA";
}

function updateSummary() {
    const quantityValue = Number(quantity.value) || 0;
    const priceValue = Number(unitPrice.value) || 0;
    const total = quantityValue * priceValue;

    document.getElementById("summaryName").textContent = nameInput.value.trim() || "Non renseigné";
    document.getElementById("summaryQuantity").textContent = quantityValue + " unité(s)";
    document.getElementById("summaryPrice").textContent = formatPrice(priceValue);
    document.getElementById("summaryTotal").textContent = formatPrice(total);
    document.getElementById("stockTotal").textContent = formatPrice(total);
}

[quantity, unitPrice, nameInput].forEach(input => input.addEventListener("input", updateSummary));
updateSummary();

document.getElementById("menuButton").addEventListener("click", () => {
    document.getElementById("sidebar").classList.toggle("open");
});
</script>
</body>
</html>
