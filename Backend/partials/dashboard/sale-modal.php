<div class="modal-backdrop" id="saleModal" hidden>
	<section class="modal" role="dialog" aria-modal="true" aria-labelledby="saleTitle">
		<header class="modal-header"><div><p class="panel-kicker">CAISSE</p><h2 id="saleTitle">Nouvelle vente</h2></div><button class="icon-button" id="closeModal" type="button" aria-label="Fermer">×</button></header>
		<div class="sale-search"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="M20 20l-4-4"/></svg><input id="productSearch" type="search" placeholder="Rechercher un produit ou variante" aria-label="Rechercher un produit"></div>
		<div class="sale-products" id="saleProducts"></div>
		<footer class="sale-footer"><div><span>Total</span><strong id="saleTotal">0 FCFA</strong></div><button class="primary-button" id="validateSale" type="button">Valider la vente</button></footer>
	</section>
</div>
<div class="toast" id="toast" role="status" aria-live="polite">Vente enregistrée</div>