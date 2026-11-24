const products = [
  { name: "Glossier Lip Oil", detail: "Beauté · 4 ml", price: 1500, color: "blush", qty: 0 },
  { name: "Nivea Soft Crème", detail: "Corps · 200 ml", price: 900, color: "ivory", qty: 0 },
  { name: "Maybelline Mascara", detail: "Maquillage · Noir", price: 1500, color: "brown-tube", qty: 0 },
  { name: "Bodycology Mist", detail: "Corps · 236 ml", price: 1400, color: "gold", qty: 0 }
];

const sidebar = document.getElementById("sidebar");
const menuButton = document.getElementById("menuButton");
const dashboardView = document.getElementById("dashboardView");
const genericView = document.getElementById("genericView");
const placeholderTitle = document.getElementById("placeholderTitle");
const placeholderText = document.getElementById("placeholderText");
const placeholderIcon = document.getElementById("placeholderIcon");

const titles = {
  sales: ["Ventes", "Consultez les ventes enregistrées et créez une nouvelle transaction."],
  inventory: ["Stock", "Suivez les niveaux, les ruptures et les mouvements de vos produits."],
  customers: ["Clients", "Gérez les informations et l’historique de vos clients."],
  reports: ["Rapports", "Analysez les performances de la boutique et les écarts."],
  settings: ["Paramètres", "Configurez les comptes, les droits et les préférences."]
};

menuButton.addEventListener("click", () => sidebar.classList.toggle("open"));

const requestedView = new URLSearchParams(window.location.search).get("view");
if (titles[requestedView]) {
  const [title, text] = titles[requestedView];
  placeholderTitle.textContent = title;
  placeholderText.textContent = text;
  placeholderIcon.textContent = title.charAt(0);
  dashboardView.hidden = true;
  genericView.hidden = false;
  document.querySelectorAll(".sidebar .nav-item").forEach(item => item.classList.remove("active"));
  document.querySelector(`[data-page="${requestedView}"]`)?.classList.add("active");
}

const saleModal = document.getElementById("saleModal");
const saleProducts = document.getElementById("saleProducts");
const saleTotal = document.getElementById("saleTotal");

function formatPrice(value) {
  return new Intl.NumberFormat("fr-FR").format(value) + " FCFA";
}

function renderSaleProducts(filter = "") {
  saleProducts.innerHTML = products
    .filter(product => product.name.toLowerCase().includes(filter.toLowerCase()))
    .map((product, index) => `
      <div class="sale-product">
        <div class="product-thumb ${product.color}"></div>
        <div><strong>${product.name}</strong><small>${product.detail} · ${formatPrice(product.price)}</small></div>
        <div class="qty-control">
          <button data-action="minus" data-index="${index}" aria-label="Retirer">−</button>
          <span>${product.qty}</span>
          <button data-action="plus" data-index="${index}" aria-label="Ajouter">+</button>
        </div>
      </div>
    `).join("");
  updateTotal();
}

function updateTotal() {
  const total = products.reduce((sum, product) => sum + product.price * product.qty, 0);
  saleTotal.textContent = formatPrice(total);
}

document.getElementById("newSaleButton").addEventListener("click", () => {
  saleModal.hidden = false;
  renderSaleProducts();
  document.getElementById("productSearch").focus();
});

document.getElementById("closeModal").addEventListener("click", () => saleModal.hidden = true);
saleModal.addEventListener("click", event => {
  if (event.target === saleModal) saleModal.hidden = true;
});

saleProducts.addEventListener("click", event => {
  const button = event.target.closest("button");
  if (!button) return;
  const index = Number(button.dataset.index);
  if (button.dataset.action === "plus") products[index].qty += 1;
  if (button.dataset.action === "minus") products[index].qty = Math.max(0, products[index].qty - 1);
  renderSaleProducts(document.getElementById("productSearch").value);
});

document.getElementById("productSearch").addEventListener("input", event => renderSaleProducts(event.target.value));

document.getElementById("validateSale").addEventListener("click", () => {
  const total = products.reduce((sum, product) => sum + product.price * product.qty, 0);
  if (!total) return;
  saleModal.hidden = true;
  const toast = document.getElementById("toast");
  toast.textContent = `Vente enregistrée · ${formatPrice(total)}`;
  toast.classList.add("show");
  products.forEach(product => product.qty = 0);
  setTimeout(() => toast.classList.remove("show"), 2600);
});

document.addEventListener("keydown", event => {
  if (event.key === "Escape") saleModal.hidden = true;
  if ((event.metaKey || event.ctrlKey) && event.key.toLowerCase() === "k") {
    event.preventDefault();
    document.querySelector(".search input").focus();
  }
});
