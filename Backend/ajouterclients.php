<?php
declare(strict_types=1);

session_start();
require __DIR__ . '/connexion.php';

$pageTitle = 'Ajouter un client — Univer Cosmetics';
$currentPage = 'clients';
$extraStyles = ['ajouterclients.css'];
$error = '';

if (empty($_SESSION['client_csrf'])) {
    $_SESSION['client_csrf'] = bin2hex(random_bytes(32));
}
$csrfToken = $_SESSION['client_csrf'];

try {
    $pdo = getDatabaseConnection();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!hash_equals($csrfToken, (string) ($_POST['csrf_token'] ?? ''))) {
            throw new RuntimeException('Session expirée. Rechargez la page puis réessayez.');
        }

        $nom = trim((string) ($_POST['nom'] ?? ''));
        $prenom = trim((string) ($_POST['prenom'] ?? ''));
        $telephone = trim((string) ($_POST['telephone'] ?? ''));
        $productId = filter_input(INPUT_POST, 'id_produits', FILTER_VALIDATE_INT);

        if ($nom === '' || $prenom === '' || $telephone === '') {
            throw new RuntimeException('Le nom, le prénom et le numéro de téléphone sont obligatoires.');
        }

        $pdo->beginTransaction();

        $statement = $pdo->prepare('INSERT INTO clients (nom, prenom, telephone)
            VALUES (:nom, :prenom, :telephone)');
        $statement->execute([
            'nom' => $nom,
            'prenom' => $prenom,
            'telephone' => $telephone,
        ]);
        $codcli = (int) $pdo->lastInsertId();

        if ($productId) {
            $statement = $pdo->prepare('UPDATE produits SET codcli = :codcli WHERE id_produits = :id');
            $statement->execute(['codcli' => $codcli, 'id' => $productId]);
        }

        $pdo->commit();
        header('Location: clients.php?status=created');
        exit;
    }

    $products = $pdo->query('SELECT p.id_produits, p.nom_produit, p.prix_vente, p.quantite,
                                    c.nom AS categorie
                             FROM produits p
                             LEFT JOIN categories c ON c.id_categorie = p.id_categorie
                             ORDER BY p.nom_produit')->fetchAll();
} catch (Throwable $exception) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    $error = $exception instanceof PDOException
        ? 'Impossible d’enregistrer le client dans la base univers.'
        : $exception->getMessage();
    $products = $products ?? [];
}

$formatPrice = static fn(int $value): string => number_format($value, 0, ',', ' ') . ' FCFA';

require __DIR__ . '/partials/head.php';
require __DIR__ . '/partials/sidebar.php';
?>

<main class="main">
    <header class="topbar">
        <button class="menu-button" id="menuButton" type="button" aria-label="Ouvrir le menu"><span></span><span></span><span></span></button>
        <div class="client-entry-page-title"><span>CLIENTS</span><strong>Nouvelle fiche</strong></div>
        <div class="topbar-actions"><span class="sync-status"><span class="status-dot"></span><span>Données univers</span></span></div>
    </header>

    <section class="content client-entry-content">
        <div class="page-heading">
            <div><p class="eyebrow">RELATION CLIENT</p><h1>Ajouter un client</h1></div>
            <a class="secondary-button client-entry-back" href="clients.php">Retour aux clients</a>
        </div>

        <?php if ($error !== ''): ?>
            <div class="catalog-message error" role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <form class="client-entry-layout" method="post">
            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">

            <div class="client-entry-fields">
                <section class="panel client-entry-card">
                    <div class="panel-header client-entry-card-header">
                        <div><p class="panel-kicker">ÉTAPE 1</p><h2>Identité du client</h2></div>
                    </div>
                    <div class="client-entry-grid">
                        <label>Nom *<input id="clientLastName" name="nom" required maxlength="100" placeholder="Ex. Diallo" autocomplete="family-name"></label>
                        <label>Prénom *<input id="clientFirstName" name="prenom" required maxlength="100" placeholder="Ex. Aïcha" autocomplete="given-name"></label>
                        <label class="client-entry-full">Téléphone *<input id="clientPhone" name="telephone" type="tel" required maxlength="30" placeholder="Ex. +221 77 000 00 00" autocomplete="tel"></label>
                    </div>
                </section>

                <section class="panel client-entry-card">
                    <div class="panel-header client-entry-card-header">
                        <div><p class="panel-kicker">ÉTAPE 2</p><h2>Commande effectuée</h2></div>
                    </div>
                    <div class="client-entry-grid">
                        <label class="client-entry-full">Produit commandé<select id="clientProduct" name="id_produits"><option value="">Aucun produit associé</option><?php foreach ($products as $product): ?><option value="<?= (int) $product['id_produits'] ?>" data-price="<?= (int) $product['prix_vente'] ?>" data-category="<?= htmlspecialchars((string) ($product['categorie'] ?? 'Sans catégorie'), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($product['nom_produit'], ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select></label>
                    </div>
                </section>
            </div>

            <aside class="panel client-entry-summary">
                <div class="panel-header client-entry-card-header"><div><p class="panel-kicker">RÉCAPITULATIF</p><h2>Avant enregistrement</h2></div></div>
                <dl class="client-summary-list">
                    <div><dt>Nom complet</dt><dd id="summaryClientName">Non renseigné</dd></div>
                    <div><dt>Téléphone</dt><dd id="summaryClientPhone">Non renseigné</dd></div>
                    <div><dt>Produit</dt><dd id="summaryClientProduct">Aucun produit</dd></div>
                    <div><dt>Catégorie</dt><dd id="summaryClientCategory">—</dd></div>
                </dl>
                <div class="client-summary-total"><span>Prix du produit</span><strong id="summaryClientPrice">0 FCFA</strong></div>
                <button class="primary-button client-entry-submit" type="submit"><span>+</span> Enregistrer le client</button>
                <a class="secondary-button client-entry-cancel" href="clients.php">Annuler</a>
            </aside>
        </form>
    </section>
</main>

<script>
const lastName = document.getElementById("clientLastName");
const firstName = document.getElementById("clientFirstName");
const phone = document.getElementById("clientPhone");
const product = document.getElementById("clientProduct");

function formatPrice(value) {
    return new Intl.NumberFormat("fr-FR").format(value) + " FCFA";
}

function updateSummary() {
    const selected = product.options[product.selectedIndex];
    const fullName = [firstName.value.trim(), lastName.value.trim()].filter(Boolean).join(" ");

    document.getElementById("summaryClientName").textContent = fullName || "Non renseigné";
    document.getElementById("summaryClientPhone").textContent = phone.value.trim() || "Non renseigné";
    document.getElementById("summaryClientProduct").textContent = product.value ? selected.textContent : "Aucun produit";
    document.getElementById("summaryClientCategory").textContent = product.value ? selected.dataset.category : "—";
    document.getElementById("summaryClientPrice").textContent = product.value ? formatPrice(Number(selected.dataset.price) || 0) : "0 FCFA";
}

[lastName, firstName, phone, product].forEach(input => input.addEventListener("input", updateSummary));
updateSummary();

document.getElementById("menuButton").addEventListener("click", () => {
    document.getElementById("sidebar").classList.toggle("open");
});
</script>
</body>
</html>
