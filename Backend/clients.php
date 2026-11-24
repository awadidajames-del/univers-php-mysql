<?php
declare(strict_types=1);

session_start();
require __DIR__ . '/connexion.php';

$pageTitle = 'Clients — Univer Cosmetics';
$currentPage = 'clients';
$message = '';
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

        $action = (string) ($_POST['action'] ?? 'save');

        if ($action === 'save') {
            $id = filter_input(INPUT_POST, 'codcli', FILTER_VALIDATE_INT);
            $nom = trim((string) ($_POST['nom'] ?? ''));
            $prenom = trim((string) ($_POST['prenom'] ?? ''));
            $telephone = trim((string) ($_POST['telephone'] ?? ''));
            $productId = filter_input(INPUT_POST, 'id_produits', FILTER_VALIDATE_INT);

            if ($nom === '' || $prenom === '' || $telephone === '') {
                throw new RuntimeException('Le nom, le prénom et le numéro sont obligatoires.');
            }

            $pdo->beginTransaction();
            if ($id) {
                $statement = $pdo->prepare('UPDATE clients SET nom = :nom, prenom = :prenom, telephone = :telephone WHERE codcli = :id');
                $statement->execute(['nom' => $nom, 'prenom' => $prenom, 'telephone' => $telephone, 'id' => $id]);
                $codcli = $id;
                $messageText = 'Client modifié avec succès.';
            } else {
                $statement = $pdo->prepare('INSERT INTO clients (nom, prenom, telephone) VALUES (:nom, :prenom, :telephone)');
                $statement->execute(['nom' => $nom, 'prenom' => $prenom, 'telephone' => $telephone]);
                $codcli = (int) $pdo->lastInsertId();
                $messageText = 'Client enregistré avec succès.';
            }

            if ($productId) {
                $statement = $pdo->prepare('UPDATE produits SET codcli = :codcli WHERE id_produits = :id');
                $statement->execute(['codcli' => $codcli, 'id' => $productId]);
            }
            $pdo->commit();

            header('Location: clients.php?status=' . ($id ? 'updated' : 'created'));
            exit;
        }

        if ($action === 'delete') {
            $id = filter_input(INPUT_POST, 'codcli', FILTER_VALIDATE_INT);
            if (!$id) throw new RuntimeException('Client introuvable.');

            $pdo->beginTransaction();
            $pdo->prepare('UPDATE produits SET codcli = NULL WHERE codcli = :id')->execute(['id' => $id]);
            $pdo->prepare('DELETE FROM clients WHERE codcli = :id')->execute(['id' => $id]);
            $pdo->commit();
            header('Location: clients.php?status=deleted');
            exit;
        }
    }

    $clients = $pdo->query('SELECT cl.*,
                                   COUNT(p.id_produits) AS product_count,
                                   COALESCE(SUM(p.quantite * p.prix_vente), 0) AS associated_value
                            FROM clients cl
                            LEFT JOIN produits p ON p.codcli = cl.codcli
                            GROUP BY cl.codcli
                            ORDER BY cl.nom, cl.prenom')->fetchAll();

    $products = $pdo->query('SELECT p.id_produits, p.nom_produit, p.quantite, p.prix_vente, p.codcli,
                                    c.nom AS categorie
                             FROM produits p
                             LEFT JOIN categories c ON c.id_categorie = p.id_categorie
                             ORDER BY p.nom_produit')->fetchAll();

    $status = (string) ($_GET['status'] ?? '');
    if ($status === 'created') $message = 'Client enregistré avec succès.';
    if ($status === 'updated') $message = 'Client modifié avec succès.';
    if ($status === 'deleted') $message = 'Client supprimé.';
} catch (Throwable $exception) {
    if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
    $error = $exception instanceof PDOException
        ? 'Impossible d’enregistrer le client dans la base univers.'
        : $exception->getMessage();
    $clients = $clients ?? [];
    $products = $products ?? [];
}

$editClient = null;
$isNewClient = (string) ($_GET['edit'] ?? '') === 'new';
$editId = filter_input(INPUT_GET, 'edit', FILTER_VALIDATE_INT);
foreach ($clients as $client) {
    if ($editId && (int) $client['codcli'] === $editId) {
        $editClient = $client;
        break;
    }
}

$formatPrice = static fn(int $value): string => number_format($value, 0, ',', ' ') . ' FCFA';

require __DIR__ . '/partials/head.php';
require __DIR__ . '/partials/sidebar.php';
?>

<main class="main">
    <header class="topbar">
        <button class="menu-button" id="menuButton" type="button" aria-label="Ouvrir le menu"><span></span><span></span><span></span></button>
        <label class="search clients-top-search"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="M20 20l-4-4"/></svg><input id="clientSearch" type="search" placeholder="Rechercher un client ou produit..." aria-label="Rechercher un client ou produit"></label>
        <div class="topbar-actions"><span class="sync-status"><span class="status-dot"></span><span>Données univers</span></span></div>
    </header>

    <section class="content clients-content">
        <div class="page-heading"><div><p class="eyebrow">RELATION CLIENT</p><h1>Clients</h1></div><button class="primary-button" id="newClientButton" type="button"><span>+</span> Nouveau client</button></div>

        <?php if ($message !== ''): ?><div class="catalog-message success" role="status"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
        <?php if ($error !== ''): ?><div class="catalog-message error" role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>

        <div class="stats-grid clients-stats" aria-label="Aperçu des clients">
            <article class="stat-card"><div class="stat-icon brown"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg></div><div class="stat-copy"><span>Clients enregistrés</span><strong><?= count($clients) ?></strong><em>Dans votre fichier client</em></div></article>
            <article class="stat-card"><div class="stat-icon rose"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 3h2l2.4 11.2a2 2 0 0 0 2 1.6h7.8a2 2 0 0 0 2-1.6L21 7H6"/></svg></div><div class="stat-copy"><span>Clients acheteurs</span><strong><?= count(array_filter($clients, static fn(array $client): bool => (int) $client['product_count'] > 0)) ?></strong><em>Avec produit associé</em></div></article>
            <article class="stat-card"><div class="stat-icon beige"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h10"/></svg></div><div class="stat-copy"><span>Produits associés</span><strong><?= count(array_filter($products, static fn(array $product): bool => $product['codcli'] !== null)) ?></strong><em>Liés à une commande client</em></div></article>
            <article class="stat-card"><div class="stat-icon cream"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg></div><div class="stat-copy"><span>Valeur associée</span><strong><?= $formatPrice((int) array_sum(array_column($clients, 'associated_value'))) ?></strong><em>Produits liés aux clients</em></div></article>
        </div>

        <div class="clients-layout">
            <section class="panel client-list-panel" aria-labelledby="clientListTitle">
                <div class="panel-header clients-panel-header"><div><p class="panel-kicker">FICHIER CLIENT</p><h2 id="clientListTitle">Tous les clients</h2></div></div>
                <div class="client-table-wrap">
                    <table class="client-table"><thead><tr><th>Client</th><th>Contact</th><th>Produits associés</th><th>Valeur associée</th><th>Actions</th></tr></thead><tbody id="clientRows">
                        <?php foreach ($clients as $client):
                            $fullName = trim($client['prenom'] . ' ' . $client['nom']);
                        ?>
                            <tr class="client-row<?= $editClient && (int) $editClient['codcli'] === (int) $client['codcli'] ? ' selected' : '' ?>" data-search="<?= htmlspecialchars(strtolower($fullName . ' ' . $client['telephone']), ENT_QUOTES, 'UTF-8') ?>">
                                <td><span class="client-name-cell"><strong><?= htmlspecialchars($fullName, ENT_QUOTES, 'UTF-8') ?></strong></span></td>
                                <td><?= htmlspecialchars($client['telephone'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><span class="order-count<?= (int) $client['product_count'] ? ' has-orders' : '' ?>"><?= (int) $client['product_count'] ?></span></td>
                                <td><?= $formatPrice((int) $client['associated_value']) ?></td>
                                <td><div class="category-actions"><a class="category-action edit" href="clients.php?edit=<?= (int) $client['codcli'] ?>" aria-label="Modifier">✎</a><form method="post" data-confirm="Supprimer ce client ?"><input type="hidden" name="csrf_token" value="<?= $csrfToken ?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="codcli" value="<?= (int) $client['codcli'] ?>"><button class="category-action delete" type="submit" aria-label="Supprimer">×</button></form></div></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody></table>
                    <div class="empty-state" id="emptyClients" hidden><span class="empty-mark">U</span><strong>Aucun client trouvé</strong><p>Modifiez votre recherche ou ajoutez un client.</p></div>
                </div>
            </section>

            <aside class="panel client-detail-panel" id="clientDetails" aria-live="polite">
                <?php if ($editClient):
                    $associatedProducts = array_values(array_filter($products, static fn(array $product): bool => (int) ($product['codcli'] ?? 0) === (int) $editClient['codcli']));
                ?>
                    <div class="detail-heading"><div class="detail-identity"><div><p class="panel-kicker">FICHE CLIENT</p><h2><?= htmlspecialchars(trim($editClient['prenom'] . ' ' . $editClient['nom']), ENT_QUOTES, 'UTF-8') ?></h2></div></div></div>
                    <div class="client-contact"><span><?= htmlspecialchars($editClient['telephone'], ENT_QUOTES, 'UTF-8') ?></span></div>
                    <div class="orders-heading"><div><p class="panel-kicker">COMMANDES</p><h3>Produits associés</h3></div></div>
                    <div class="orders-list">
                        <?php if ($associatedProducts): foreach ($associatedProducts as $product): ?>
                            <article class="order-card"><header class="order-card-header"><div><span class="order-reference"><?= htmlspecialchars($product['nom_produit'], ENT_QUOTES, 'UTF-8') ?></span><time><?= htmlspecialchars((string) ($product['categorie'] ?? 'Sans catégorie'), ENT_QUOTES, 'UTF-8') ?></time></div><strong><?= $formatPrice((int) $product['prix_vente']) ?></strong></header></article>
                        <?php endforeach; else: ?>
                            <div class="no-orders"><strong>Aucun produit associé</strong><p>Choisissez un produit dans le formulaire pour enregistrer la commande.</p></div>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="detail-empty"><span class="empty-mark">U</span><strong>Sélectionnez un client</strong><p>Son profil et ses produits associés apparaîtront ici.</p></div>
                <?php endif; ?>
            </aside>
        </div>
    </section>
</main>

<div class="modal-backdrop" id="clientModal"<?= $editClient || $isNewClient ? '' : ' hidden' ?>>
    <section class="modal client-modal" role="dialog" aria-modal="true" aria-labelledby="clientModalTitle">
        <header class="modal-header"><div><p class="panel-kicker">FICHIER CLIENT</p><h2 id="clientModalTitle"><?= $editClient ? 'Modifier le client' : 'Nouveau client' ?></h2></div><a class="icon-button" href="clients.php" aria-label="Fermer">×</a></header>
        <form id="clientForm" method="post">
            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
            <input type="hidden" name="action" value="save">
            <input type="hidden" name="codcli" value="<?= $editClient ? (int) $editClient['codcli'] : '' ?>">
            <div class="client-form-fields">
                <label>Nom<input name="nom" required maxlength="100" value="<?= htmlspecialchars((string) ($editClient['nom'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" placeholder="Ex. Diallo"></label>
                <label>Prénom<input name="prenom" required maxlength="100" value="<?= htmlspecialchars((string) ($editClient['prenom'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" placeholder="Ex. Aïcha"></label>
                <label>Téléphone<input name="telephone" type="tel" required maxlength="30" value="<?= htmlspecialchars((string) ($editClient['telephone'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" placeholder="Ex. +221 77 000 00 00"></label>
                <label>Produit commandé<select name="id_produits"><option value="">Aucun produit associé</option><?php foreach ($products as $product): $selected = $editClient && (int) ($product['codcli'] ?? 0) === (int) $editClient['codcli']; ?><option value="<?= (int) $product['id_produits'] ?>"<?= $selected ? ' selected' : '' ?>><?= htmlspecialchars($product['nom_produit'], ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select></label>
            </div>
            <footer class="client-form-footer"><a class="secondary-button" href="clients.php">Annuler</a><button class="primary-button" type="submit">Enregistrer</button></footer>
        </form>
    </section>
</div>

<script>
const searchInput = document.getElementById("clientSearch");
const rows = [...document.querySelectorAll("#clientRows tr")];
searchInput.addEventListener("input", () => {
    const query = searchInput.value.trim().toLocaleLowerCase("fr");
    let visible = 0;
    rows.forEach(row => {
        const match = row.dataset.search.includes(query);
        row.hidden = !match;
        if (match) visible++;
    });
    document.getElementById("emptyClients").hidden = visible > 0;
});
document.getElementById("newClientButton").addEventListener("click", () => {
    window.location.href = "ajouterclients.php";
});
document.getElementById("menuButton").addEventListener("click", () => document.getElementById("sidebar").classList.toggle("open"));
document.querySelectorAll("form[data-confirm]").forEach(form => form.addEventListener("submit", event => { if (!window.confirm(form.dataset.confirm)) event.preventDefault(); }));
</script>
</body>
</html>
