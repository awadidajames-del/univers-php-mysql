const searchInput = document.getElementById("catalogSearch");
const categoryFilter = document.getElementById("categoryFilter");
const brandFilter = document.getElementById("brandFilter");
const catalogRows = [...document.querySelectorAll("#catalogRows tr")];
const emptyCatalog = document.getElementById("emptyCatalog");
const sidebar = document.getElementById("sidebar");
const movementType = document.querySelector('[name="type_mouvement"]');
const movementQuantity = document.querySelector('[name="quantite_mouvement"]');
const movementQuantityLabel = document.getElementById("movementQuantityLabel");

function filterCatalog() {
	const query = searchInput.value.trim().toLocaleLowerCase("fr");
	let visibleCount = 0;
	for (const row of catalogRows) {
		const matchesQuery = row.dataset.search.includes(query);
		const matchesCategory = !categoryFilter.value || row.dataset.category === categoryFilter.value;
		const matchesBrand = !brandFilter.value || row.dataset.brand === brandFilter.value;
		row.hidden = !(matchesQuery && matchesCategory && matchesBrand);
		if (!row.hidden) visibleCount += 1;
	}
	emptyCatalog.hidden = visibleCount > 0;
}

searchInput.addEventListener("input", filterCatalog);
categoryFilter.addEventListener("change", filterCatalog);
brandFilter.addEventListener("change", filterCatalog);
document.getElementById("menuButton")?.addEventListener("click", () => sidebar?.classList.toggle("open"));

if (movementType && movementQuantity && movementQuantityLabel) {
	movementType.addEventListener("change", () => {
		const isAdjustment = movementType.value === "ajustement";
		movementQuantity.min = isAdjustment ? "0" : "1";
		movementQuantity.value = "";
		movementQuantityLabel.firstChild.textContent = isAdjustment ? "Quantité réelle constatée" : "Quantité à ajouter";
	});
}

document.querySelectorAll("form[data-confirm]").forEach(form => {
	form.addEventListener("submit", event => {
		if (!window.confirm(form.dataset.confirm)) event.preventDefault();
	});
});

document.addEventListener("keydown", event => {
	if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === "k") {
		event.preventDefault();
		searchInput.focus();
	}
});