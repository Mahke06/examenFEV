<?php require_once __DIR__ . '/../layout/header.php'; ?>

<style>
    /* Force la structure rigide pour éviter les décalages */
    .table-simple {
        table-layout: fixed;
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 1rem;
    }
    
    .table-simple th, .table-simple td {
        padding: 6px 8px;
        font-size: 0.8rem;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        border: 1px solid #dee2e6;
    }

    /* Largeurs de colonnes pour les petits tableaux du haut */
    .col-id { width: 50px; }
    .col-type { width: 30%; }
    .col-cat { width: 25%; }
    .col-qte { width: 30%; }

    /* Largeurs pour le grand tableau de simulation */
    .col-sim-id { width: 45px; }
    .col-sim-type { width: 15%; }
    .col-sim-ville { width: 20%; }
    .col-sim-qte { width: 15%; }
    .col-sim-besoin { width: 15%; }

    .stat-number { font-size: 1.3rem; font-weight: bold; }
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>🔄 Simulation de distribution</h1>
</div>

<?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success py-2"><strong>✅ Succès !</strong> <?php echo htmlspecialchars($_GET['success']); ?></div>
<?php endif; ?>

<?php if (isset($_GET['error'])): ?>
    <div class="alert alert-danger py-2"><strong>⚠️ Erreur !</strong> <?php echo htmlspecialchars($_GET['error']); ?></div>
<?php endif; ?>

<div class="row mb-4 g-3">
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header bg-primary text-white py-2"><h6 class="mb-0">🎁 Dons disponibles</h6></div>
            <div class="card-body p-2">
                <?php if (empty($dons_disponibles)): ?>
                    <p class="small text-muted">Aucun don disponible.</p>
                <?php else: ?>
                    <table border="1" class="table-simple">
                        <thead class="table-light">
                            <tr>
                                <th class="col-id">#</th>
                                <th class="col-type">Type</th>
                                <th class="col-cat">Catégorie</th>
                                <th class="col-qte">Restant</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($dons_disponibles as $don): ?>
                            <tr>
                                <td><?php echo $don['id']; ?></td>
                                <td title="<?php echo $don['type_besoin_nom']; ?>"><?php echo $don['type_besoin_nom']; ?></td>
                                <td><small><?php echo $don['categorie_nom']; ?></small></td>
                                <td class="fw-bold"><?php echo $don['quantite_restante'] . ' ' . $don['unite']; ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header bg-danger text-white py-2"><h6 class="mb-0">📋 Besoins non satisfaits</h6></div>
            <div class="card-body p-2">
                <?php if (empty($besoins_non_satisfaits)): ?>
                    <p class="small text-muted">Tous les besoins sont satisfaits !</p>
                <?php else: ?>
                    <table border="1" class="table-simple">
                        <thead class="table-light">
                            <tr>
                                <th class="col-type">Ville</th>
                                <th class="col-type">Type</th>
                                <th class="col-qte">Reste</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($besoins_non_satisfaits as $besoin): ?>
                            <tr>
                                <td><?php echo $besoin['ville_nom']; ?></td>
                                <td><?php echo $besoin['type_besoin_nom']; ?></td>
                                <td class="text-danger fw-bold"><?php echo ($besoin['quantite_demandee'] - $besoin['quantite_satisfaite']) . ' ' . $besoin['unite']; ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="text-center mb-4">
    <a href="/simulation/simuler" class="btn btn-primary <?php echo (empty($dons_disponibles) || empty($besoins_non_satisfaits)) ? 'disabled' : ''; ?>">
        🔍 Simuler la distribution
    </a>
</div>

<?php if ($simulation !== null): ?>
<div class="card mb-4 border-success">
    <div class="card-header bg-success text-white py-2"><h5 class="mb-0 small">📊 Résultat de la simulation</h5></div>
    <div class="card-body p-3">
        <?php if (empty($simulation['attributions'])): ?>
            <div class="alert alert-warning small">Aucune correspondance trouvée entre dons et besoins.</div>
        <?php else: ?>
            <div class="row g-2 mb-3">
                <div class="col-6"><div class="p-2 border rounded bg-light">
                    <small class="text-muted">Attributions</small><div class="stat-number"><?php echo $simulation['total_attributions']; ?></div>
                </div></div>
                <div class="col-6"><div class="p-2 border rounded bg-light">
                    <small class="text-muted">Volume total</small><div class="stat-number"><?php echo $simulation['total_quantite']; ?></div>
                </div></div>
            </div>

            <h6 class="small fw-bold mb-2">Détail des attributions prévues :</h6>
            <table border="1" class="table-simple mb-3">
                <thead class="table-dark">
                    <tr>
                        <th class="col-sim-id">Don</th>
                        <th class="col-sim-type">Type</th>
                        <th class="col-sim-ville">Ville</th>
                        <th class="col-sim-qte">Qté</th>
                        <th class="col-sim-besoin">Avant</th>
                        <th class="col-sim-besoin">Après</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($simulation['attributions'] as $attr): ?>
                    <tr>
                        <td>#<?php echo $attr['don_id']; ?></td>
                        <td><?php echo $attr['type_besoin_nom']; ?></td>
                        <td class="fw-bold"><?php echo $attr['ville_nom']; ?></td>
                        <td class="bg-light fw-bold text-primary"><?php echo $attr['quantite'] . ' ' . $attr['unite']; ?></td>
                        <td><?php echo $attr['besoin_avant']; ?></td>
                        <td>
                            <?php echo $attr['besoin_apres'] <= 0 ? '<b style="color:green;">OK</b>' : '<b style="color:red;">'.$attr['besoin_apres'].'</b>'; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="text-center mt-4">
                <form method="POST" action="/simulation/valider" onsubmit="return confirm('Valider définitivement ?');">
                    <button type="submit" class="btn btn-success btn-lg">✅ Valider le dispatch</button>
                    <a href="/simulation" class="btn btn-link text-muted">Annuler</a>
                </form>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>