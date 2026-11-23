const inventoryStorageKey = "univer-cosmetics-inventory-v1";
const starterProducts = [
	{ id: "product-lip-oil", name: "Glossier Lip Oil", category: "Beauté", detail: "4 ml", price: 1500, stock: 68, threshold: 12, color: "blush", movements: [{ id: "m-lip-1", date: "2026-11-20", type: "Entrée", quantity: 20, note: "Réapprovisionnement" }] },
	{ id: "product-nivea", name: "Nivea Soft Crème", category: "Corps", detail: "200 ml", price: 900, stock: 7, threshold: 8, color: "ivory", movements: [{ id: "m-nivea-1", date: "2026-11-21", type: "Sortie", quantity: -3, note: "Vente" }] },
	{ id: "product-mascara", name: "Maybelline Mascara", category: "Maquillage", detail: "Noir", price: 1500, stock: 24, threshold: 8, color: "brown-tube", movements: [{ id: "m-mascara-1", date: "2026-11-19", type: "Entrée", quantity: 12, note: "Réapprovisionnement" }] },
	{ id: "product-mist", name: "Bodycology Mist", category: "Corps", detail: "236 ml", price: 1400, stock: 5, threshold: 6, color: "gold", movements: [{ id: "m-mist-1", date: "2026-11-22", type: "Sortie", quantity: -2, note: "Vente" }] }
];

const productRows = document.getElementById("productRows");
const productDetails = document.getElementById("productDetails");
const stockSearch = document.getElementById("stockSearch");
const categoryFilter = document.getElementById("categoryFilter");
const productModal = document.getElementById("productModal");
const productForm = document.getElementById("productForm");
const movementModal = document.getElementById("movementModal");
const movementForm = document.getElementById("movementForm");
const toast = document.getElementById("inventoryToast");
const sidebar = document.getElementById("sidebar");
let inventory = loadInventory();
let selectedProductId = inventory[0]?.id ?? null;
let activeFilter = "all";
let editingProductId = null;
let movementProductId = null;
let toastTimer;

function loadInventory() {
	try {
		const savedInventory = JSON.parse(localStorage.getItem(inventoryStorageKey));
		return Array.isArray(savedInventory) ? savedInventory : starterProducts;
	} catch {
		return starterProducts;
	}
}

function saveInventory() {
	localStorage.setItem(inventoryStorageKey, JSON.stringify(inventory));
}

function escapeHtml(value) {
	return String(value ?? "").replace(/[&<>"']/g, character => ({
		"&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;"
	})[character]);
}

function formatPrice(value) {
	return new Intl.NumberFormat("fr-FR").format(value) + " FCFA";
}

function localDateString() {
	const date = new Date();
	return `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, "0")}-${String(date.getDate()).padStart(2, "0")}`;
}

function formatDate(value) {
	if (!value) return "—";
	const date = new Date(`${value}T12:00:00`);
	return Number.isNaN(date.getTime()) ? "—" : new Intl.DateTimeFormat("fr-FR", { day: "numeric", month: "short", year: "numeric" }).format(date);
}

function getStockState(product) {
	if (product.stock <= 0) return "out";
	if (product.stock <= product.threshold) return "low";
	return "good";
}

function getVisibleProducts() {
	const query = stockSearch.value.trim().toLocaleLowerCase("fr");
	return inventory.filter(product => {
		const state = getStockState(product);
		if (activeFilter === "low" && state !== "low") return false;
		if (activeFilter === "out" && state !== "out") return false;
		if (categoryFilter.value !== "all" && product.category !== categoryFilter.value) return false;
		return !query || `${product.name} ${product.category} ${product.detail}`.toLocaleLowerCase("fr").includes(query);
	}).sort((first, second) => first.name.localeCompare(second.name, "fr"));
}

function renderStats() {
	const units = inventory.reduce((sum, product) => sum + product.stock, 0);
	const lowStock = inventory.filter(product => product.stock <= product.threshold).length;
	const value = inventory.reduce((sum, product) => sum + product.price * product.stock, 0);
	document.getElementById("productCount").textContent = inventory.length;
	document.getElementById("unitCount").textContent = new Intl.NumberFormat("fr-FR").format(units);
	document.getElementById("lowStockCount").textContent = lowStock;
	document.getElementById("stockValue").innerHTML = `${escapeHtml(new Intl.NumberFormat("fr-FR").format(value))} <small>FCFA</small>`;
}

function renderCategoryOptions() {
	const selectedCategory = categoryFilter.value || "all";
	const categories = [...new Set(inventory.map(product => product.category))].sort((first, second) => first.localeCompare(second, "fr"));
	categoryFilter.innerHTML = `<option value="all">Toutes catégories</option>${categories.map(category => `<option value="${escapeHtml(category)}">${escapeHtml(category)}</option>`).join("")}`;
	categoryFilter.value = categories.includes(selectedCategory) ? selectedCategory : "all";
}

function renderProducts() {
	const visibleProducts = getVisibleProducts();
	productRows.innerHTML = visibleProducts.map(product => {
		const state = getStockState(product);
		const statusText = state === "out" ? "Rupture" : state === "low" ? "Stock bas" : "Disponible";
		return `<tr class="inventory-row${product.id === selectedProductId ? " selected" : ""}" data-product-id="${escapeHtml(product.id)}" tabindex="0" aria-selected="${product.id === selectedProductId}">
			<td><span class="inventory-product-cell"><span class="product-thumb ${escapeHtml(product.color || "")}"></span><span><strong>${escapeHtml(product.name)}</strong><small>${escapeHtml(product.detail || "Sans variante")}</small></span></span></td>
			<td>${escapeHtml(product.category)}</td><td class="inventory-price">${formatPrice(product.price)}</td><td><strong class="quantity-value ${state === "out" ? "quantity-out" : state === "low" ? "quantity-low" : ""}">${product.stock}</strong></td><td>${product.threshold}</td><td><span class="stock-badge ${state}">${statusText}</span></td>
		</tr>`;
	}).join("");
	document.getElementById("emptyProducts").hidden = visibleProducts.length > 0;
	renderDetails();
}

function renderDetails() {
	const product = inventory.find(entry => entry.id === selectedProductId);
	if (!product) {
		productDetails.innerHTML = `<div class="detail-empty"><span class="empty-mark">U</span><strong>Sélectionnez un produit</strong><p>Son stock et ses mouvements apparaîtront ici.</p></div>`;
		return;
	}

	const state = getStockState(product);
	const history = [...(product.movements ?? [])].sort((first, second) => second.date.localeCompare(first.date));
	const historyMarkup = history.length ? history.map(movement => {
		const delta = Number(movement.quantity);
		const deltaText = delta > 0 ? `+${delta}` : String(delta);
		const movementClass = delta > 0 ? "movement-in" : delta < 0 ? "movement-out" : "movement-neutral";
		return `<div class="movement-row"><span class="movement-symbol ${movementClass}">${delta > 0 ? "+" : delta < 0 ? "−" : "="}</span><span class="movement-copy"><strong>${escapeHtml(movement.type)}</strong><small>${formatDate(movement.date)}${movement.note ? ` · ${escapeHtml(movement.note)}` : ""}</small></span><b class="${movementClass}">${deltaText}</b></div>`;
	}).join("") : `<div class="no-movements"><span class="no-orders-icon">—</span><strong>Aucun mouvement enregistré</strong></div>`;

	productDetails.innerHTML = `<div class="inventory-detail-heading"><div class="inventory-detail-identity"><span class="product-thumb ${escapeHtml(product.color || "")}"></span><div><p class="panel-kicker">FICHE PRODUIT</p><h2>${escapeHtml(product.name)}</h2><span class="inventory-detail-category">${escapeHtml(product.category)}${product.detail ? ` · ${escapeHtml(product.detail)}` : ""}</span></div></div><div class="detail-actions"><button class="icon-action" type="button" data-action="edit" aria-label="Modifier ce produit" title="Modifier">✎</button><button class="icon-action danger-action" type="button" data-action="delete" aria-label="Supprimer ce produit" title="Supprimer">×</button></div></div>
		<div class="inventory-quantity-block"><div><span class="inventory-detail-label">Stock disponible</span><strong class="inventory-big-quantity ${state === "out" ? "quantity-out" : state === "low" ? "quantity-low" : ""}">${product.stock}<small> unités</small></strong></div><span class="stock-badge ${state}">${state === "out" ? "Rupture" : state === "low" ? "Stock bas" : "Disponible"}</span></div>
		<div class="inventory-detail-metrics"><div><span>Prix de vente</span><strong>${formatPrice(product.price)}</strong></div><div><span>Seuil d’alerte</span><strong>${product.threshold} unités</strong></div></div>
		<button class="primary-button movement-button" type="button" data-action="movement"><span>±</span> Enregistrer un mouvement</button>
		<div class="movement-history-heading"><div><p class="panel-kicker">SUIVI</p><h3>Mouvements récents</h3></div><span>${history.length} mouvement${history.length === 1 ? "" : "s"}</span></div>
		<div class="movement-list">${historyMarkup}</div>`;
}

function render() {
	renderStats();
	renderCategoryOptions();
	renderProducts();
}

function showToast(message) {
	toast.textContent = message;
	toast.classList.add("show");
	window.clearTimeout(toastTimer);
	toastTimer = window.setTimeout(() => toast.classList.remove("show"), 2400);
}

function openProductModal(product = null) {
	editingProductId = product?.id ?? null;
	productForm.reset();
	productForm.elements.name.value = product?.name ?? "";
	productForm.elements.category.value = product?.category ?? "Beauté";
	productForm.elements.detail.value = product?.detail ?? "";
	productForm.elements.price.value = product?.price ?? "";
	productForm.elements.threshold.value = product?.threshold ?? 5;
	productForm.elements.stock.value = product?.stock ?? 0;
	document.getElementById("initialStockField").hidden = Boolean(product);
	document.getElementById("productModalTitle").textContent = product ? "Modifier le produit" : "Nouveau produit";
	productModal.hidden = false;
	productForm.elements.name.focus();
}

function closeProductModal() {
	productModal.hidden = true;
	editingProductId = null;
}

function openMovementModal(product) {
	movementProductId = product.id;
	movementForm.reset();
	movementForm.elements.type.value = "entry";
	movementForm.elements.quantity.min = "1";
	document.getElementById("movementQuantityLabel").firstChild.textContent = "Quantité";
	document.getElementById("movementProductName").textContent = `${product.name} · Stock actuel : ${product.stock} unités`;
	document.getElementById("movementError").hidden = true;
	movementModal.hidden = false;
	movementForm.elements.quantity.focus();
}

function closeMovementModal() {
	movementModal.hidden = true;
	movementProductId = null;
}

function closeAnyModal() {
	if (!productModal.hidden) closeProductModal();
	if (!movementModal.hidden) closeMovementModal();
}

document.getElementById("newProductButton").addEventListener("click", () => openProductModal());
document.getElementById("closeProductModal").addEventListener("click", closeProductModal);
document.getElementById("cancelProductModal").addEventListener("click", closeProductModal);
productModal.addEventListener("click", event => {
	if (event.target === productModal) closeProductModal();
});

productForm.addEventListener("submit", event => {
	event.preventDefault();
	const values = {
		name: productForm.elements.name.value.trim(),
		category: productForm.elements.category.value,
		detail: productForm.elements.detail.value.trim(),
		price: Number(productForm.elements.price.value),
		threshold: Number(productForm.elements.threshold.value)
	};
	const wasEditing = Boolean(editingProductId);
	if (wasEditing) {
		inventory = inventory.map(product => product.id === editingProductId ? { ...product, ...values } : product);
	} else {
		const stock = Number(productForm.elements.stock.value);
		const color = values.category === "Corps" ? "ivory" : values.category === "Maquillage" ? "brown-tube" : values.category === "Parfum" ? "gold" : "blush";
		const movements = stock ? [{ id: `m-${Date.now()}`, date: localDateString(), type: "Stock initial", quantity: stock, note: "Création de la référence" }] : [];
		const product = { id: `product-${Date.now()}`, ...values, stock, color, movements };
		inventory.push(product);
		selectedProductId = product.id;
	}
	saveInventory();
	closeProductModal();
	render();
	showToast(wasEditing ? "Produit modifié" : "Produit ajouté");
});

movementForm.elements.type.addEventListener("change", () => {
	const isAdjustment = movementForm.elements.type.value === "adjustment";
	const quantityInput = movementForm.elements.quantity;
	quantityInput.min = isAdjustment ? "0" : "1";
	quantityInput.value = "";
	document.getElementById("movementQuantityLabel").firstChild.textContent = isAdjustment ? "Quantité réelle constatée" : "Quantité";
});

document.getElementById("closeMovementModal").addEventListener("click", closeMovementModal);
document.getElementById("cancelMovementModal").addEventListener("click", closeMovementModal);
movementModal.addEventListener("click", event => {
	if (event.target === movementModal) closeMovementModal();
});

movementForm.addEventListener("submit", event => {
	event.preventDefault();
	const product = inventory.find(entry => entry.id === movementProductId);
	if (!product) return;
	const type = movementForm.elements.type.value;
	const quantity = Number(movementForm.elements.quantity.value);
	const note = movementForm.elements.note.value.trim();
	let delta = quantity;
	let movementType = "Entrée";
	if (type === "exit") {
		delta = -quantity;
		movementType = "Sortie";
	}
	if (type === "adjustment") {
		delta = quantity - product.stock;
		movementType = "Ajustement";
	}
	if ((type !== "adjustment" && quantity <= 0) || quantity < 0 || product.stock + delta < 0) {
		const error = document.getElementById("movementError");
		error.textContent = product.stock + delta < 0 ? "La sortie dépasse la quantité disponible." : "Saisissez une quantité valide.";
		error.hidden = false;
		return;
	}
	product.stock += delta;
	product.movements = product.movements ?? [];
	product.movements.unshift({ id: `m-${Date.now()}`, date: localDateString(), type: movementType, quantity: delta, note });
	saveInventory();
	closeMovementModal();
	render();
	showToast("Mouvement de stock enregistré");
});

productRows.addEventListener("click", event => {
	const row = event.target.closest("[data-product-id]");
	if (!row) return;
	selectedProductId = row.dataset.productId;
	renderProducts();
});

productRows.addEventListener("keydown", event => {
	if (event.key !== "Enter" && event.key !== " ") return;
	const row = event.target.closest("[data-product-id]");
	if (!row) return;
	event.preventDefault();
	selectedProductId = row.dataset.productId;
	renderProducts();
});

productDetails.addEventListener("click", event => {
	const button = event.target.closest("[data-action]");
	if (!button) return;
	const product = inventory.find(entry => entry.id === selectedProductId);
	if (!product) return;
	if (button.dataset.action === "edit") openProductModal(product);
	if (button.dataset.action === "movement") openMovementModal(product);
	if (button.dataset.action === "delete" && window.confirm(`Supprimer ${product.name} et son historique de stock ?`)) {
		inventory = inventory.filter(entry => entry.id !== product.id);
		selectedProductId = inventory[0]?.id ?? null;
		saveInventory();
		render();
		showToast("Produit supprimé");
	}
});

stockSearch.addEventListener("input", renderProducts);
categoryFilter.addEventListener("change", renderProducts);
document.querySelectorAll("[data-filter]").forEach(button => {
	button.addEventListener("click", () => {
		activeFilter = button.dataset.filter;
		document.querySelectorAll("[data-filter]").forEach(filterButton => {
			const isActive = filterButton === button;
			filterButton.classList.toggle("active", isActive);
			filterButton.setAttribute("aria-pressed", String(isActive));
		});
		renderProducts();
	});
});

document.getElementById("menuButton").addEventListener("click", () => sidebar.classList.toggle("open"));
document.addEventListener("keydown", event => {
	if (event.key === "Escape") closeAnyModal();
	if ((event.metaKey || event.ctrlKey) && event.key.toLowerCase() === "k") {
		event.preventDefault();
		stockSearch.focus();
	}
});

render();