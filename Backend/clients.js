const storageKey = "univer-cosmetics-clients-v1";
const starterClients = [
  {
    id: "client-fatou",
    name: "Fatou Diallo",
    phone: "+221 77 245 18 30",
    email: "fatou.diallo@email.com",
    orders: [
      { id: "VT-2841", date: "2026-11-23", items: [{ name: "Glossier Lip Oil", quantity: 2, price: 1500 }, { name: "Nivea Soft Crème", quantity: 1, price: 900 }] },
      { id: "VT-2816", date: "2026-11-20", items: [{ name: "Maybelline Mascara", quantity: 1, price: 1500 }, { name: "Bodycology Mist", quantity: 2, price: 1400 }] }
    ]
  },
  {
    id: "client-awa",
    name: "Awa Ndiaye",
    phone: "+221 76 510 42 08",
    email: "awa.ndiaye@email.com",
    orders: [{ id: "VT-2830", date: "2026-11-22", items: [{ name: "Nivea Soft Crème", quantity: 3, price: 900 }] }]
  },
  { id: "client-mariama", name: "Mariama Ba", phone: "+221 78 632 17 45", email: "mariama.ba@email.com", orders: [] },
  {
    id: "client-khadija",
    name: "Khadija Sow",
    phone: "+221 70 842 09 11",
    email: "khadija.sow@email.com",
    orders: [{ id: "VT-2792", date: "2026-11-18", items: [{ name: "Glossier Lip Oil", quantity: 1, price: 1500 }, { name: "Bodycology Mist", quantity: 1, price: 1400 }] }]
  }
];

const clientRows = document.getElementById("clientRows");
const clientDetails = document.getElementById("clientDetails");
const clientSearch = document.getElementById("clientSearch");
const clientModal = document.getElementById("clientModal");
const clientForm = document.getElementById("clientForm");
const toast = document.getElementById("clientToast");
const sidebar = document.getElementById("sidebar");
let clients = loadClients();
let selectedClientId = clients[0]?.id ?? null;
let activeFilter = "all";
let editingClientId = null;
let toastTimer;

function loadClients() {
  try {
    const savedClients = JSON.parse(localStorage.getItem(storageKey));
    return Array.isArray(savedClients) ? savedClients : starterClients;
  } catch {
    return starterClients;
  }
}

function saveClients() {
  localStorage.setItem(storageKey, JSON.stringify(clients));
}

function escapeHtml(value) {
  return String(value ?? "").replace(/[&<>"']/g, character => ({
    "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;"
  })[character]);
}

function formatPrice(value) {
  return new Intl.NumberFormat("fr-FR").format(value) + " FCFA";
}

function getOrdersTotal(client) {
  return (client.orders ?? []).reduce((total, order) => total + (order.items ?? []).reduce((sum, item) => sum + item.quantity * item.price, 0), 0);
}

function formatDate(value) {
  if (!value) return "—";
  const date = new Date(`${value}T12:00:00`);
  return Number.isNaN(date.getTime()) ? "—" : new Intl.DateTimeFormat("fr-FR", { day: "numeric", month: "short", year: "numeric" }).format(date);
}

function getInitials(name) {
  return name.trim().split(/\s+/).slice(0, 2).map(part => part.charAt(0)).join("").toUpperCase();
}

function renderStats() {
  const buyers = clients.filter(client => client.orders?.length);
  const orderCount = clients.reduce((sum, client) => sum + (client.orders?.length ?? 0), 0);
  const revenue = clients.reduce((sum, client) => sum + getOrdersTotal(client), 0);
  document.getElementById("clientCount").textContent = clients.length;
  document.getElementById("buyerCount").textContent = buyers.length;
  document.getElementById("orderCount").textContent = orderCount;
  document.getElementById("customerRevenue").innerHTML = `${escapeHtml(new Intl.NumberFormat("fr-FR").format(revenue))} <small>FCFA</small>`;
}

function getVisibleClients() {
  const query = clientSearch.value.trim().toLocaleLowerCase("fr");
  return clients.filter(client => {
    if (activeFilter === "buyers" && !client.orders?.length) return false;
    if (!query) return true;
    const orderProducts = (client.orders ?? []).flatMap(order => order.items ?? []).map(item => item.name).join(" ");
    return `${client.name} ${client.phone} ${client.email} ${orderProducts}`.toLocaleLowerCase("fr").includes(query);
  }).sort((first, second) => first.name.localeCompare(second.name, "fr"));
}

function renderClients() {
  const visibleClients = getVisibleClients();
  clientRows.innerHTML = visibleClients.map(client => {
    const orders = client.orders ?? [];
    const latestOrder = [...orders].sort((first, second) => second.date.localeCompare(first.date))[0];
    return `<tr class="client-row${client.id === selectedClientId ? " selected" : ""}" data-client-id="${escapeHtml(client.id)}" tabindex="0" aria-selected="${client.id === selectedClientId}">
      <td><span class="client-name-cell"><span class="client-avatar">${escapeHtml(getInitials(client.name))}</span><span><strong>${escapeHtml(client.name)}</strong><small>${escapeHtml(client.email || "E-mail non renseigné")}</small></span></span></td>
      <td>${escapeHtml(client.phone || "—")}</td><td><span class="order-count${orders.length ? " has-orders" : ""}">${orders.length}</span></td>
      <td class="spent-cell">${formatPrice(getOrdersTotal(client))}</td><td>${formatDate(latestOrder?.date)}</td>
    </tr>`;
  }).join("");
  document.getElementById("emptyClients").hidden = visibleClients.length > 0;
  renderDetails();
}

function renderDetails() {
  const client = clients.find(entry => entry.id === selectedClientId);
  if (!client) {
    clientDetails.innerHTML = `<div class="detail-empty"><span class="empty-mark">U</span><strong>Sélectionnez un client</strong><p>Son profil et ses commandes apparaîtront ici.</p></div>`;
    return;
  }

  const orders = [...(client.orders ?? [])].sort((first, second) => second.date.localeCompare(first.date));
  const orderMarkup = orders.length ? orders.map(order => {
    const items = order.items ?? [];
    const orderTotal = items.reduce((sum, item) => sum + item.quantity * item.price, 0);
    return `<article class="order-card">
      <header class="order-card-header"><div><span class="order-reference">${escapeHtml(order.id)}</span><time>${formatDate(order.date)}</time></div><strong>${formatPrice(orderTotal)}</strong></header>
      <div class="order-items">${items.map(item => `<div class="order-item"><span class="order-item-name"><strong>${escapeHtml(item.name)}</strong><small>${item.quantity} × ${formatPrice(item.price)}</small></span><b>${formatPrice(item.quantity * item.price)}</b></div>`).join("")}</div>
    </article>`;
  }).join("") : `<div class="no-orders"><span class="no-orders-icon">—</span><strong>Aucune commande enregistrée</strong><p>Les achats de ce client s’afficheront ici.</p></div>`;

  clientDetails.innerHTML = `<div class="detail-heading"><div class="detail-identity"><span class="detail-avatar">${escapeHtml(getInitials(client.name))}</span><div><p class="panel-kicker">FICHE CLIENT</p><h2>${escapeHtml(client.name)}</h2></div></div><div class="detail-actions"><button class="icon-action" type="button" data-action="edit" aria-label="Modifier ce client" title="Modifier">✎</button><button class="icon-action danger-action" type="button" data-action="delete" aria-label="Supprimer ce client" title="Supprimer">×</button></div></div>
    <div class="client-contact"><span>${escapeHtml(client.phone || "Téléphone non renseigné")}</span><span>${escapeHtml(client.email || "E-mail non renseigné")}</span></div>
    <div class="detail-totals"><div><span>Commandes</span><strong>${orders.length}</strong></div><div><span>Total dépensé</span><strong>${formatPrice(getOrdersTotal(client))}</strong></div></div>
    <div class="orders-heading"><div><p class="panel-kicker">ACHATS</p><h3>Historique des commandes</h3></div><span>${orders.length} commande${orders.length === 1 ? "" : "s"}</span></div>
    <div class="orders-list">${orderMarkup}</div>`;
}

function render() {
  renderStats();
  renderClients();
}

function openClientModal(client = null) {
  editingClientId = client?.id ?? null;
  clientForm.reset();
  clientForm.elements.name.value = client?.name ?? "";
  clientForm.elements.phone.value = client?.phone ?? "";
  clientForm.elements.email.value = client?.email ?? "";
  document.getElementById("clientModalTitle").textContent = client ? "Modifier le client" : "Nouveau client";
  clientModal.hidden = false;
  clientForm.elements.name.focus();
}

function closeClientModal() {
  clientModal.hidden = true;
  editingClientId = null;
}

function showToast(message) {
  toast.textContent = message;
  toast.classList.add("show");
  window.clearTimeout(toastTimer);
  toastTimer = window.setTimeout(() => toast.classList.remove("show"), 2400);
}

document.getElementById("newClientButton").addEventListener("click", () => openClientModal());
document.getElementById("closeClientModal").addEventListener("click", closeClientModal);
document.getElementById("cancelClientModal").addEventListener("click", closeClientModal);
clientModal.addEventListener("click", event => {
  if (event.target === clientModal) closeClientModal();
});

clientForm.addEventListener("submit", event => {
  event.preventDefault();
  const formData = new FormData(clientForm);
  const values = {
    name: String(formData.get("name")).trim(),
    phone: String(formData.get("phone")).trim(),
    email: String(formData.get("email")).trim()
  };
  const wasEditing = Boolean(editingClientId);
  if (wasEditing) {
    clients = clients.map(client => client.id === editingClientId ? { ...client, ...values } : client);
  } else {
    const client = { id: `client-${Date.now()}`, ...values, orders: [] };
    clients.push(client);
    selectedClientId = client.id;
  }
  saveClients();
  closeClientModal();
  render();
  showToast(wasEditing ? "Client modifié" : "Client ajouté");
});

clientRows.addEventListener("click", event => {
  const row = event.target.closest("[data-client-id]");
  if (!row) return;
  selectedClientId = row.dataset.clientId;
  renderClients();
});

clientRows.addEventListener("keydown", event => {
  if (event.key !== "Enter" && event.key !== " ") return;
  const row = event.target.closest("[data-client-id]");
  if (!row) return;
  event.preventDefault();
  selectedClientId = row.dataset.clientId;
  renderClients();
});

clientDetails.addEventListener("click", event => {
  const button = event.target.closest("[data-action]");
  if (!button) return;
  const client = clients.find(entry => entry.id === selectedClientId);
  if (!client) return;
  if (button.dataset.action === "edit") openClientModal(client);
  if (button.dataset.action === "delete" && window.confirm(`Supprimer la fiche de ${client.name} et son historique ?`)) {
    clients = clients.filter(entry => entry.id !== client.id);
    selectedClientId = clients[0]?.id ?? null;
    saveClients();
    render();
    showToast("Client supprimé");
  }
});

clientSearch.addEventListener("input", renderClients);

document.querySelectorAll("[data-filter]").forEach(button => {
  button.addEventListener("click", () => {
    activeFilter = button.dataset.filter;
    document.querySelectorAll("[data-filter]").forEach(filterButton => {
      const isActive = filterButton === button;
      filterButton.classList.toggle("active", isActive);
      filterButton.setAttribute("aria-pressed", String(isActive));
    });
    renderClients();
  });
});

document.getElementById("menuButton").addEventListener("click", () => sidebar.classList.toggle("open"));
document.addEventListener("keydown", event => {
  if (event.key === "Escape" && !clientModal.hidden) closeClientModal();
  if ((event.metaKey || event.ctrlKey) && event.key.toLowerCase() === "k") {
    event.preventDefault();
    clientSearch.focus();
  }
});

render();