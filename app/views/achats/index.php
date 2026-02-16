<?php require_once __DIR__ . '/../layout/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>📦 Liste des achats</h1>
    <a href="/achats/create" class="btn btn-primary">Nouvel achat</a>
</div>

<?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <strong>✅ Succès !</strong> <?php echo htmlspecialchars(urldecode($_GET['success'])); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Filtre par ville -->
<div class="card mb-4">
    <div class="card-body py-3">
        <form method="GET" action="/achats" class="row align-items-end g-3">
            <div class="col-md-4">
                <label for="ville_id" class="form-label">Filtrer par ville</label>
                <select name="ville_id" id="ville_id" class="form-select">
                    <option value="">— Toutes les villes —</option>
                    <?php foreach ($villes as $ville): ?>
                        <option value="<?php echo $ville['id']; ?>" <?php echo (isset($_GET['ville_id']) && $_GET['ville_id'] == $ville['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($ville['nom']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary">Filtrer</button>
                <?php if (isset($_GET['ville_id']) && $_GET['ville_id'] !== ''): ?>
                    <a href="/achats" class="btn btn-secondary ms-2">Réinitialiser</a>
                <?php endif; ?>
            </div>
            <div class="col-md-5 text-end">
                <span class="badge bg-info fs-6">
                    Frais d'achat : <?php echo $frais_achat; ?>%
                </span>
            </div>
        </form>
    </div>
</div>

<?php if (empty($achats)): ?>
    <div class="alert alert-info">
        Aucun achat enregistré<?php echo (isset($_GET['ville_id']) && $_GET['ville_id'] !== '') ? ' pour cette ville' : ''; ?>.
    </div>
<?php else: ?>
    <?php 
    $total_general = 0;
    foreach ($achats as $a) { $total_general += $a['montant_total']; }
    ?>
    <div class="table-responsive">
        <table class="table table-striped table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>#</th>
                    <th>Ville</th>
                    <th>Type de besoin</th>
                    <th>Catégorie</th>
                    <th>Quantité</th>
                    <th>Prix unitaire</th>
                    <th>Sous-total HT</th>
                    <th>Frais (%)</th>
                    <th>Montant total</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($achats as $index => $achat): ?>
                <tr>
                    <td><?php echo $index + 1; ?></td>
                    <td><?php echo htmlspecialchars($achat['ville_nom']); ?></td>
                    <td><?php echo htmlspecialchars($achat['type_besoin_nom']); ?></td>
                    <td>
                        <span class="badge bg-<?php echo $achat['categorie_nom'] === 'en Nature' ? 'success' : 'warning'; ?>">
                            <?php echo htmlspecialchars($achat['categorie_nom']); ?>
                        </span>
                    </td>
                    <td><?php echo number_format($achat['quantite'], 0, ',', ' ') . ' ' . htmlspecialchars($achat['unite']); ?></td>
                    <td><?php echo number_format($achat['prix_unitaire'], 0, ',', ' '); ?> Ar</td>
                    <td><?php echo number_format($achat['quantite'] * $achat['prix_unitaire'], 0, ',', ' '); ?> Ar</td>
                    <td><span class="badge bg-info"><?php echo $achat['frais_pourcent']; ?>%</span></td>
                    <td><strong><?php echo number_format($achat['montant_total'], 0, ',', ' '); ?> Ar</strong></td>
                    <td><?php echo date('d/m/Y H:i', strtotime($achat['date_achat'])); ?></td>
                    <td>
                        <form method="POST" action="/achats/delete" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet achat ? Le montant sera restauré au don en argent.');">
                            <input type="hidden" name="id" value="<?php echo $achat['id']; ?>">
                            <button type="submit" class="btn btn-danger btn-sm">Supprimer</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr class="table-dark">
                    <td colspan="9" class="text-end fw-bold">Total général :</td>
                    <td colspan="2" class="fw-bold"><?php echo number_format($total_general, 0, ',', ' '); ?> Ar</td>
                </tr>
            </tfoot>
        </table>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
