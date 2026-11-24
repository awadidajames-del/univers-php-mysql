<?php
declare(strict_types=1);

session_start();
require __DIR__ . '/connexion.php';

$pageTitle = 'Gestion des catégories — Univer Cosmetics';
$currentPage = 'categories';
$extraStyles = ['categorie.css'];
$message = '';
$error = '';

if (empty($_SESSION['category_csrf'])) {
    $_SESSION['category_csrf'] = bin2hex(random_bytes(32));
}
$csrfToken = $_SESSION['category_csrf'];

try {
    $pdo = getDatabaseConnection();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!hash_equals($csrfToken, (string) ($_POST['csrf_token'] ?? ''))) {
            throw new RuntimeException('Session expirée. Rechargez la page puis réessayez.');
        }

        $action = (string) ($_POST['action'] ?? '');

        if ($action === 'save') {
            $id = filter_input(INPUT_POST, 'id_categorie', FILTER_VALIDATE_INT);
            $name = trim((string) ($_POST['nom'] ?? ''));
            $description = trim((string) ($_POST['description'] ?? ''));
            $active = isset($_POST['actif']) ? 1 : 0;

            if ($name === '') {
                throw new RuntimeException('Le nom de la catégorie est obligatoire.');
            }

            if ($id) {
                $statement = $pdo->prepare('UPDATE categories
                    SET nom = :name, description = :description, actif = :active
                    WHERE id_categorie = :id');
                $statement->execute([
                    'name' => $name,
                    'description' => $description !== '' ? $description : null,
                    'active' => $active,
                    'id' => $id,
                ]);
                header('Location: categorie.php?status=updated');
                exit;
            }

            $statement = $pdo->prepare('INSERT INTO categories (nom, description, actif)
                VALUES (:name, :description, :active)');
            $statement->execute([
                'name' => $name,
                'description' => $description !== '' ? $description : null,
                'active' => $active,
            ]);
            header('Location: categorie.php?status=created');
            exit;
        }

        if ($action === 'toggle') {
            $id = filter_input(INPUT_POST, 'id_categorie', FILTER_VALIDATE_INT);
            $active = filter_input(INPUT_POST, 'actif', FILTER_VALIDATE_INT);
            if (!$id) {
                throw new RuntimeException('Catégorie introuvable.');
            }
            $statement = $pdo->prepare('UPDATE categories SET actif = :active WHERE id_categorie = :id');
            $statement->execute(['active' => $active ? 0 : 1, 'id' => $id]);
            header('Location: categorie.php?status=updated');
            exit;
        }

        if ($action === 'delete') {
            $id = filter_input(INPUT_POST, 'id_categorie', FILTER_VALIDATE_INT);
            if (!$id) {
                throw new RuntimeException('Catégorie introuvable.');
            }

            $statement = $pdo->prepare('SELECT COUNT(*) FROM produits WHERE id_categorie = :id');
            $statement->execute(['id' => $id]);
            if ((int) $statement->fetchColumn() > 0) {
                throw new RuntimeException('Cette catégorie est utilisée par des produits. Désactivez-la plutôt que de la supprimer.');
            }

            $statement = $pdo->prepare('DELETE FROM categories WHERE id_categorie = :id');
            $statement->execute(['id' => $id]);
            header('Location: categorie.php?status=deleted');
            exit;
        }
    }

    $categories = $pdo->query('SELECT c.*, COUNT(p.id_produits) AS product_count
        FROM categories c
        LEFT JOIN produits p ON p.id_categorie = c.id_categorie
        GROUP BY c.id_categorie
        ORDER BY c.nom')->fetchAll();

    $categoryProducts = $pdo->query('SELECT id_produits, id_categorie, nom_produit, quantite
        FROM produits
        WHERE id_categorie IS NOT NULL
        ORDER BY nom_produit')->fetchAll();

    $productsByCategory = [];
    foreach ($categoryProducts as $product) {
        $productsByCategory[(int) $product['id_categorie']][] = $product;
    }

    $status = (string) ($_GET['status'] ?? '');
    if ($status === 'created') {
        $message = 'Catégorie enregistrée avec succès.';
    } elseif ($status === 'updated') {
        $message = 'Catégorie mise à jour.';
    } elseif ($status === 'deleted') {
        $message = 'Catégorie supprimée.';
    }
} catch (Throwable $exception) {
    $error = $exception instanceof PDOException
        ? 'Impossible d’accéder aux catégories dans la base univers.'
        : $exception->getMessage();
    $categories = $categories ?? [];
}

$editCategory = null;
$editId = filter_input(INPUT_GET, 'edit', FILTER_VALIDATE_INT);
foreach ($categories as $category) {
    if ($editId && (int) $category['id_categorie'] === $editId) {
        $editCategory = $category;
        break;
    }
}

$activeCount = 0;
$inactiveCount = 0;
foreach ($categories as $category) {
    if ((int) $category['actif'] === 1) {
        $activeCount++;
    } else {
        $inactiveCount++;
    }
}

require __DIR__ . '/partials/head.php';
require __DIR__ . '/partials/sidebar.php';
?>

<main class="main">
    <header class="topbar">
        <button class="menu-button" id="menuButton" type="button" aria-label="Ouvrir le menu"><span></span><span></span><span></span></button>
        <label class="search category-search">
            <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="M20 20l-4-4"/></svg>
            <input id="categorySearch" type="search" placeholder="Rechercher une catégorie..." aria-label="Rechercher une catégorie">
        </label>
        <div class="topbar-actions"><span class="sync-status"><span class="status-dot"></span><span>Données univers</span></span></div>
    </header>

    <section class="content category-content">
        <div class="page-heading">
            <div><p class="eyebrow">PARAMÈTRES DU CATALOGUE</p><h1>Catégories</h1></div>
            <button class="primary-button" id="newCategoryButton" type="button"><span>+</span> Nouvelle catégorie</button>
        </div>

        <?php if ($message !== ''): ?>
            <div class="catalog-message success" role="status"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>
        <?php if ($error !== ''): ?>
            <div class="catalog-message error" role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <div class="stats-grid category-stats" aria-label="Aperçu des catégories">
            <article class="stat-card"><div class="stat-icon brown"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16"/></svg></div><div class="stat-copy"><span>Total</span><strong><?= count($categories) ?></strong><em>Catégories enregistrées</em></div></article>
            <article class="stat-card"><div class="stat-icon rose"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M8 12l3 3 5-6"/></svg></div><div class="stat-copy"><span>Actives</span><strong><?= $activeCount ?></strong><em>Disponibles au choix</em></div></article>
            <article class="stat-card"><div class="stat-icon beige"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M8 12h8"/></svg></div><div class="stat-copy"><span>Inactives</span><strong><?= $inactiveCount ?></strong><em>Masquées des formulaires</em></div></article>
            <article class="stat-card"><div class="stat-icon cream"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M21 8l-9-5-9 5 9 5 9-5z"/><path d="M3 8v8l9 5 9-5V8"/></svg></div><div class="stat-copy"><span>Produits classés</span><strong><?= array_sum(array_map(static fn(array $category): int => (int) $category['product_count'], $categories)) ?></strong><em>Dans toutes les catégories</em></div></article>
        </div>

        <div class="category-layout">
            <section class="panel category-list-panel" aria-labelledby="categoryListTitle">
                <div class="panel-header category-panel-header">
                    <div><p class="panel-kicker">CATALOGUE</p><h2 id="categoryListTitle">Toutes les catégories</h2></div>
                    <span class="category-result-count" id="categoryCount"><?= count($categories) ?> catégorie(s)</span>
                </div>
                <div class="category-table-wrap">
                    <table class="category-table">
                        <thead><tr><th>Catégorie</th><th>Produits associés</th><th>État</th><th>Actions</th></tr></thead>
                        <tbody id="categoryRows">
                        <?php foreach ($categories as $category):
                            $associatedProducts = $productsByCategory[(int) $category['id_categorie']] ?? [];
                            $associatedSearch = implode(' ', array_map(static fn(array $product): string => (string) $product['nom_produit'], $associatedProducts));
                        ?>
                            <tr data-search="<?= htmlspecialchars(strtolower($category['nom'] . ' ' . ($category['description'] ?? '') . ' ' . $associatedSearch), ENT_QUOTES, 'UTF-8') ?>">
                                <td><strong><?= htmlspecialchars($category['nom'], ENT_QUOTES, 'UTF-8') ?></strong></td>
                                <td>
                                    <div class="category-products">
                                        <?php if ($associatedProducts === []): ?>
                                            <span class="category-no-product">Aucun produit associé</span>
                                        <?php else: ?>
                                            <?php foreach ($associatedProducts as $product): ?>
                                                <a href="stock.php?edit=<?= (int) $product['id_produits'] ?>"><span><?= htmlspecialchars((string) $product['nom_produit'], ENT_QUOTES, 'UTF-8') ?></span><b><?= (int) $product['quantite'] ?></b></a>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td><span class="category-status <?= (int) $category['actif'] === 1 ? 'active' : 'inactive' ?>"><?= (int) $category['actif'] === 1 ? 'Active' : 'Inactive' ?></span></td>
                                <td>
                                    <div class="category-actions">
                                        <a class="category-action edit" href="categorie.php?edit=<?= (int) $category['id_categorie'] ?>" aria-label="Modifier <?= htmlspecialchars($category['nom'], ENT_QUOTES, 'UTF-8') ?>">✎</a>
                                        <form method="post"><input type="hidden" name="csrf_token" value="<?= $csrfToken ?>"><input type="hidden" name="action" value="toggle"><input type="hidden" name="id_categorie" value="<?= (int) $category['id_categorie'] ?>"><input type="hidden" name="actif" value="<?= (int) $category['actif'] ?>"><button class="category-action" type="submit" aria-label="Changer l’état"><?= (int) $category['actif'] === 1 ? '◉' : '○' ?></button></form>
                                        <form method="post" data-confirm="Supprimer cette catégorie ?"><input type="hidden" name="csrf_token" value="<?= $csrfToken ?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="id_categorie" value="<?= (int) $category['id_categorie'] ?>"><button class="category-action delete" type="submit" aria-label="Supprimer">×</button></form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                    <div class="empty-state" id="emptyCategories" hidden><span class="empty-mark">U</span><strong>Aucune catégorie trouvée</strong><p>Modifiez votre recherche ou créez une catégorie.</p></div>
                </div>
            </section>

            <aside class="panel category-form-panel">
                <div class="panel-header"><div><p class="panel-kicker">Ajout de catégorie</p><h2><?= $editCategory ? 'Modifier la catégorie' : 'Nouvelle catégorie' ?></h2></div></div>
                <form class="category-form" method="post">
                    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                    <input type="hidden" name="action" value="save">
                    <input type="hidden" name="id_categorie" value="<?= $editCategory ? (int) $editCategory['id_categorie'] : '' ?>">
                    <label>Nom de la catégorie<input name="nom" required maxlength="120" value="<?= htmlspecialchars((string) ($editCategory['nom'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" placeholder="Ex. Soins du visage"></label>
                   
                    <label class="category-active-control"><input name="actif" type="checkbox"<?= $editCategory ? ((int) $editCategory['actif'] === 1 ? ' checked' : '') : ' checked' ?>><span>Catégorie active</span></label>
                    <div class="category-form-actions"><button class="primary-button" type="submit"><?= $editCategory ? 'Enregistrer les modifications' : 'Ajouter la catégorie' ?></button><?php if ($editCategory): ?><a class="secondary-button" href="categorie.php">Annuler</a><?php endif; ?></div>
                </form>
            </aside>
        </div>
    </section>
</main>

<script>
const searchInput = document.getElementById("categorySearch");
const rows = [...document.querySelectorAll("#categoryRows tr")];
const emptyState = document.getElementById("emptyCategories");
const countLabel = document.getElementById("categoryCount");

function filterCategories() {
    const query = searchInput.value.trim().toLocaleLowerCase("fr");
    let visible = 0;
    rows.forEach(row => {
        const match = row.dataset.search.includes(query);
        row.hidden = !match;
        if (match) visible++;
    });
    emptyState.hidden = visible > 0;
    countLabel.textContent = visible + " catégorie(s)";
}

searchInput.addEventListener("input", filterCategories);
document.getElementById("menuButton").addEventListener("click", () => document.getElementById("sidebar").classList.toggle("open"));
document.getElementById("newCategoryButton").addEventListener("click", () => {
    const form = document.querySelector(".category-form");
    form.reset();
    form.querySelector('[name="id_categorie"]').value = "";
    form.querySelector('[name="nom"]').focus();
    history.replaceState(null, "", "categorie.php");
});
document.querySelectorAll("form[data-confirm]").forEach(form => {
    form.addEventListener("submit", event => {
        if (!window.confirm(form.dataset.confirm)) event.preventDefault();
    });
});
</script>
</body>
</html>
