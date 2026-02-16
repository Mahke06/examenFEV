<?php require_once __DIR__ . '/../layout/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>🔄 Simulation de distribution</h1>
</div>

<?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <strong>✅ Succès !</strong> <?php echo $_GET['success']; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if (isset($_GET['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <strong>⚠️ Erreur !</strong> <?php echo $_GET['error']; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- État actuel -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">🎁 Dons disponibles</h4>
            </div>
            <div class="card-body p-0">
                <?php if (empty($dons_disponibles)): ?>
                    <div class="p-3 text-muted">Aucun don disponible à distribuer.</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm table-striped mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th>Don #</th>
                                    <th>Type</th>
                                    <th>Catégorie</th>
                                    <th>Qté restante</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($dons_disponibles as $don): ?>
                                <tr>
                                    <td><?php echo $don['id']; ?></td>
                                    <td><?php echo $don['type_besoin_nom']; ?></td>
                                    <td>
                                        <span class="badge bg-<?php 
                                            echo $don['categorie_nom'] === 'en Nature' ? 'success' : 
                                                ($don['categorie_nom'] === 'en Materiaux' ? 'warning' : 'info'); 
                                        ?>">
                                            <?php echo $don['categorie_nom']; ?>
                                        </span>
                                    </td>
                                    <td><strong><?php echo $don['quantite_restante'] . ' ' . $don['unite']; ?></strong></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-12 col-xl-6">
        <div class="card">
            <div class="card-header bg-danger text-white">
                <h4 class="mb-0">📋 Besoins non satisfaits</h4>
            </div>
            <div class="card-body p-0">
                <?php if (empty($besoins_non_satisfaits)): ?>
                    <div class="p-3 text-muted">Tous les besoins sont satisfaits !</div>
                <?php else: ?>
                    <div class="table-responsive" style="max-height: 350px; overflow-y: auto;">
                        <table class="table table-sm table-striped mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th>Ville</th>
                                    <th>Type</th>
                                    <th>Restant</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($besoins_non_satisfaits as $besoin): ?>
                                <tr>
                                    <td><?php echo $besoin['ville_nom']; ?></td>
                                    <td><?php echo $besoin['type_besoin_nom']; ?></td>
                                    <td>
                                        <span class="text-danger fw-bold">
                                            <?php echo ($besoin['quantite_demandee'] - $besoin['quantite_satisfaite']) . ' ' . $besoin['unite']; ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>


<div class="text-center mb-4">
    <?php if (!empty($dons_disponibles) && !empty($besoins_non_satisfaits)): ?>
        <a href="/simulation/simuler" class="btn btn-primary btn-lg">
            🔍 Simuler la distribution
        </a>
    <?php else: ?>
        <button class="btn btn-secondary btn-lg" disabled>
            🔍 Simuler la distribution
        </button>
        <p class="text-muted mt-2">
            <?php if (empty($dons_disponibles)): ?>
                Aucun don disponible à distribuer.
            <?php else: ?>
                Aucun besoin non satisfait.
            <?php endif; ?>
        </p>
    <?php endif; ?>
</div>


<?php if ($simulation !== null): ?>
<div class="card mb-4">
    <div class="card-header bg-success text-white">
        <h4 class="mb-0">📊 Résultat de la simulation</h4>
    </div>
    <div class="card-body">

        <?php if (empty($simulation['attributions'])): ?>
            <div class="alert alert-warning">
                <strong>ℹ️</strong> Aucune distribution possible — les types des dons disponibles ne correspondent à aucun besoin non satisfait.
            </div>
        <?php else: ?>

            <!-- Résumé -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card stat-card bg-primary text-white">
                        <div class="card-body">
                            <h5>Attributions à créer</h5>
                            <div class="stat-number"><?php echo $simulation['total_attributions']; ?></div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="card stat-card bg-success text-white">
                        <div class="card-body">
                            <h5>Quantité totale distribuée</h5>
                            <div class="stat-number"><?php echo $simulation['total_quantite']; ?></div>
                        </div>
                    </div>
                </div>
            </div>


            <h5 class="mb-3">📝 Détail des attributions prévues</h5>
            <div class="table-responsive mb-4">
                <table class="table table-striped table-bordered align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th class="text-nowrap">Don #</th>
                            <th class="text-nowrap">Type</th>
                            <th class="text-nowrap">Catégorie</th>
                            <th class="text-nowrap">→ Ville</th>
                            <th class="text-nowrap">Qté attribuée</th>
                            <th class="text-nowrap">Besoin avant</th>
                            <th class="text-nowrap">Besoin après</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($simulation['attributions'] as $attr): ?>
                        <tr>
                            <td><?php echo $attr['don_id']; ?></td>
                            <td><?php echo $attr['type_besoin_nom']; ?></td>
                            <td>
                                <span class="badge bg-<?php 
                                    echo $attr['categorie_nom'] === 'en Nature' ? 'success' : 
                                        ($attr['categorie_nom'] === 'en Materiaux' ? 'warning' : 'info'); 
                                ?>">
                                    <?php echo $attr['categorie_nom']; ?>
                                </span>
                            </td>
                            <td><strong><?php echo $attr['ville_nom']; ?></strong></td>
                            <td>
                                <span class="badge bg-primary fs-6">
                                    <?php echo $attr['quantite'] . ' ' . $attr['unite']; ?>
                                </span>
                            </td>
                            <td><?php echo $attr['besoin_avant'] . ' ' . $attr['unite']; ?></td>
                            <td>
                                <?php if ($attr['besoin_apres'] <= 0): ?>
                                    <span class="badge bg-success">✅ Satisfait</span>
                                <?php else: ?>
                                    <span class="text-danger"><?php echo $attr['besoin_apres'] . ' ' . $attr['unite']; ?></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            
            <h5 class="mb-3">🎁 Impact sur les dons</h5>
            <div class="table-responsive mb-4">
                <table class="table table-striped table-bordered align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th class="text-nowrap">Don #</th>
                            <th class="text-nowrap">Type</th>
                            <th class="text-nowrap">Restant avant</th>
                            <th class="text-nowrap">Distribué</th>
                            <th class="text-nowrap">Restant après</th>
                            <th class="text-nowrap">Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($simulation['dons_apres'] as $don_info): ?>
                        <?php if ($don_info['distribue'] > 0): ?>
                        <tr>
                            <td><?php echo $don_info['don_id']; ?></td>
                            <td><?php echo $don_info['type_besoin_nom']; ?></td>
                            <td><?php echo $don_info['restant_avant'] . ' ' . $don_info['unite']; ?></td>
                            <td><span class="text-success fw-bold">-<?php echo $don_info['distribue'] . ' ' . $don_info['unite']; ?></span></td>
                            <td><?php echo $don_info['restant_apres'] . ' ' . $don_info['unite']; ?></td>
                            <td>
                                <?php if ($don_info['restant_apres'] <= 0): ?>
                                    <span class="badge bg-success">Entièrement distribué</span>
                                <?php else: ?>
                                    <span class="badge bg-warning">Partiellement distribué</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endif; ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            
            <div class="text-center">
                <form method="POST" action="/simulation/valider" onsubmit="return confirm('Êtes-vous sûr de vouloir valider cette distribution ? Cette action est irréversible.');">
                    <button type="submit" class="btn btn-success btn-lg">
                        ✅ Valider et exécuter la distribution
                    </button>
                    <a href="/simulation" class="btn btn-secondary btn-lg ms-2">
                        ← Annuler
                    </a>
                </form>
            </div>

        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
