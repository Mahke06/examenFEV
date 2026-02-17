<?php require_once __DIR__ . '/../layout/header.php'; ?>

<style>

table {
    width: 100%;
    border-collapse: collapse;
    background: #ffffff;
}

table th {
    background-color: #f8f9fa;
    font-size: 0.85rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

table th,
table td {
    padding: 12px 10px;
    vertical-align: middle;
}

table tbody tr {
    transition: background 0.2s ease;
}

table tbody tr:hover {
    background-color: #f5f7fa;
}

.card {
    border-radius: 10px;
}

.badge {
    font-size: 0.75rem;
    padding: 6px 8px;
}

input[type="number"] {
    border-radius: 6px;
    padding: 4px;
}

button.btn-success {
    border-radius: 6px;
    transition: 0.2s ease;
    cursor: pointer;
}

button.btn-success:hover {
    transform: translateY(-2px);
}
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h2">🛒 Nouvel achat</h1>
    <a href="/achats" class="btn btn-outline-secondary btn-sm">
        ← Retour à la liste
    </a>
</div>

<?php if (isset($_GET['error'])): ?>
    <div class="alert alert-danger py-2">
        <strong>⚠️ Erreur :</strong> <?= $_GET['error'] ?>
    </div>
<?php endif; ?>

<?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success py-2">
        <strong>✅ Succès :</strong> <?= $_GET['success'] ?>
    </div>
<?php endif; ?>

<div class="card mb-4 border-0 shadow-sm">
    <div class="card-body py-2 px-3 d-flex justify-content-between align-items-center bg-light rounded">
        <div class="d-flex align-items-center gap-2">
            <span class="fw-bold text-muted small text-uppercase">Solde disponible</span>
            <span class="fs-5 text-primary fw-bold">
                <?= number_format($solde_argent, 0, ',', ' ') ?> Ar
            </span>
        </div>
        <div>
            <span class="badge bg-white text-dark border shadow-sm">
                Frais : <?= $frais_achat ?>%
            </span>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm rounded overflow-hidden">
    <div class="table-responsive">
        <table border="1">
            <thead>
                <tr>
                    <th>Ville</th>
                    <th>Besoin</th>
                    <th>Stock</th>
                    <th style="text-align:right;">P.U (Ar)</th>
                    <th style="width:140px;">Quantité</th>
                    <th style="text-align:right;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($besoins_restants)): ?>
                    <tr>
                        <td colspan="6" style="text-align:center; padding:30px; color:#777;">
                            📦 Aucun besoin en nature ou matériaux disponible.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($besoins_restants as $besoin): ?>
                        <?php 
                            $qte_restante = $besoin['quantite_restante']; 
                            $formId = 'form_achat_' . $besoin['id'];
                        ?>
                        <tr>
                            <form id="<?= $formId ?>" method="POST" action="/achats/store">
                                <input type="hidden" name="besoin_id" value="<?= $besoin['id'] ?>">
                            </form>

                            <td style="font-weight:500; color:#555;">
                                <?= $besoin['ville_nom'] ?>
                            </td>

                            <td>
                                <div>
                                    <div style="font-weight:bold;">
                                        <?= $besoin['type_besoin_nom'] ?>
                                    </div>
                                    <small>
                                        <span class="badge" style="background:#f1f1f1; border:1px solid #ddd; color:#555;">
                                            <?= $besoin['categorie_nom'] ?>
                                        </span>
                                    </small>
                                </div>
                            </td>

                            <td>
                                <span class="badge" style="background:#e7f3ff; border:1px solid #b6dcff; color:#007bff;">
                                    <?= $qte_restante ?> <?= $besoin['unite'] ?>
                                </span>
                            </td>

                            <td style="text-align:right; font-weight:bold; color:#555;">
                                <?= number_format($besoin['prix_unitaire'], 0, ',', ' ') ?>
                            </td>

                            <td>
                                <input type="number" 
                                       name="quantite" 
                                       form="<?= $formId ?>"
                                       min="1" 
                                       max="<?= $qte_restante ?>" 
                                       required 
                                       value="1"
                                       style="width:80px; text-align:center;">
                                <div style="font-size:0.7rem; color:#777;">
                                    Max: <?= $qte_restante ?>
                                </div>
                            </td>

                            <td style="text-align:right;">
                                <button type="submit" 
                                        form="<?= $formId ?>" 
                                        class="btn btn-success btn-sm px-3">
                                    🛒
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
