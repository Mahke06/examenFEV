<?php require_once __DIR__ . '/../layout/header.php'; ?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <h1>📊 Récapitulation financière</h1>
    <button id="btn-actualiser" class="btn btn-primary" onclick="actualiserDonnees()">
        🔄 Actualiser
    </button>
</div>

<!-- KPI Cards -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center" style="background: linear-gradient(135deg, var(--primary), var(--primary-dark)); border-radius: var(--radius-lg); color: white;">
                <h6 class="text-uppercase mb-2" style="opacity:0.85;">Besoins Totaux</h6>
                <div id="kpi-besoins-totaux" class="fs-3 fw-bold">
                    <?php echo number_format($data['besoins_totaux'], 0, ',', ' '); ?> Ar
                </div>
                <small style="opacity:0.7;">Σ (quantité demandée × prix unitaire)</small>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center" style="background: linear-gradient(135deg, var(--success), var(--success-light)); border-radius: var(--radius-lg); color: white;">
                <h6 class="text-uppercase mb-2" style="opacity:0.85;">Besoins Satisfaits</h6>
                <div id="kpi-besoins-satisfaits" class="fs-3 fw-bold">
                    <?php echo number_format($data['besoins_satisfaits'], 0, ',', ' '); ?> Ar
                </div>
                <small style="opacity:0.7;">Σ (quantité satisfaite × prix unitaire)</small>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center" style="background: linear-gradient(135deg, var(--danger), var(--danger-light)); border-radius: var(--radius-lg); color: white;">
                <h6 class="text-uppercase mb-2" style="opacity:0.85;">Besoins Restants</h6>
                <div id="kpi-besoins-restants" class="fs-3 fw-bold">
                    <?php echo number_format($data['besoins_restants'], 0, ',', ' '); ?> Ar
                </div>
                <small style="opacity:0.7;">Totaux − Satisfaits</small>
            </div>
        </div>
    </div>
</div>


<div class="card mb-4">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="fw-bold">Taux de satisfaction global</span>
            <span id="kpi-pourcentage" class="badge bg-<?php echo $data['pourcentage'] >= 75 ? 'success' : ($data['pourcentage'] >= 40 ? 'warning' : 'danger'); ?> fs-6">
                <?php echo $data['pourcentage']; ?>%
            </span>
        </div>
        <div class="progress" style="height: 24px;">
            <div id="progress-bar" class="progress-bar bg-success progress-bar-striped progress-bar-animated" 
                 role="progressbar" 
                 style="width: <?php echo $data['pourcentage']; ?>%"
                 aria-valuenow="<?php echo $data['pourcentage']; ?>" 
                 aria-valuemin="0" 
                 aria-valuemax="100">
                <?php echo $data['pourcentage']; ?>%
            </div>
        </div>
    </div>
</div>

<!-- KPI secondaires -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card">
            <div class="card-body text-center">
                <h6 class="text-muted text-uppercase mb-1">Dons Totaux Reçus</h6>
                <div id="kpi-dons-totaux" class="fs-4 fw-bold text-primary">
                    <?php echo number_format($data['dons_totaux'], 0, ',', ' '); ?> Ar
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-4">
        <div class="card">
            <div class="card-body text-center">
                <h6 class="text-muted text-uppercase mb-1">Total Achats Effectués</h6>
                <div id="kpi-achats-totaux" class="fs-4 fw-bold" style="color: var(--accent);">
                    <?php echo number_format($data['achats_totaux'], 0, ',', ' '); ?> Ar
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-4">
        <div class="card">
            <div class="card-body text-center">
                <h6 class="text-muted text-uppercase mb-1">Solde Argent Disponible</h6>
                <div id="kpi-solde-argent" class="fs-4 fw-bold text-success">
                    <?php echo number_format($data['solde_argent'], 0, ',', ' '); ?> Ar
                </div>
            </div>
        </div>
    </div>
</div>


<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">🏙️ Détail par ville</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped mb-0">
                <thead class="table-dark">
                    <tr>
                        <th class="text-nowrap">Ville</th>
                        <th class="text-end text-nowrap">Besoins Totaux (Ar)</th>
                        <th class="text-end text-nowrap">Satisfaits (Ar)</th>
                        <th class="text-end text-nowrap">Restants (Ar)</th>
                        <th class="text-nowrap" style="min-width: 150px;">Progression</th>
                    </tr>
                </thead>
                <tbody id="table-villes">
                    <?php foreach ($data['detail_villes'] as $ville): ?>
                    <?php $pct = $ville['besoins_totaux'] > 0 ? round(($ville['besoins_satisfaits'] / $ville['besoins_totaux']) * 100, 1) : 0; ?>
                    <tr>
                        <td class="fw-bold"><?php echo $ville['ville_nom']; ?></td>
                        <td class="text-end"><?php echo number_format($ville['besoins_totaux'], 0, ',', ' '); ?></td>
                        <td class="text-end text-success"><?php echo number_format($ville['besoins_satisfaits'], 0, ',', ' '); ?></td>
                        <td class="text-end text-danger"><?php echo number_format($ville['besoins_restants'], 0, ',', ' '); ?></td>
                        <td>
                            <div class="progress" style="height: 18px;">
                                <div class="progress-bar bg-<?php echo $pct >= 75 ? 'success' : ($pct >= 40 ? 'warning' : 'danger'); ?>" 
                                     style="width: <?php echo $pct; ?>%"><?php echo $pct; ?>%</div>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>


<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">📁 Détail par catégorie</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped mb-0">
                <thead class="table-dark">
                    <tr>
                        <th class="text-nowrap">Catégorie</th>
                        <th class="text-end text-nowrap">Besoins Totaux (Ar)</th>
                        <th class="text-end text-nowrap">Satisfaits (Ar)</th>
                        <th class="text-end text-nowrap">Restants (Ar)</th>
                        <th class="text-nowrap" style="min-width: 150px;">Progression</th>
                    </tr>
                </thead>
                <tbody id="table-categories">
                    <?php foreach ($data['detail_categories'] as $cat): ?>
                    <?php $pct = $cat['besoins_totaux'] > 0 ? round(($cat['besoins_satisfaits'] / $cat['besoins_totaux']) * 100, 1) : 0; ?>
                    <tr>
                        <td>
                            <span class="badge bg-<?php 
                                echo $cat['categorie_nom'] === 'en Nature' ? 'success' : 
                                    ($cat['categorie_nom'] === 'en Materiaux' ? 'warning' : 'info'); 
                            ?>">
                                <?php echo $cat['categorie_nom']; ?>
                            </span>
                        </td>
                        <td class="text-end"><?php echo number_format($cat['besoins_totaux'], 0, ',', ' '); ?></td>
                        <td class="text-end text-success"><?php echo number_format($cat['besoins_satisfaits'], 0, ',', ' '); ?></td>
                        <td class="text-end text-danger"><?php echo number_format($cat['besoins_restants'], 0, ',', ' '); ?></td>
                        <td>
                            <div class="progress" style="height: 18px;">
                                <div class="progress-bar bg-<?php echo $pct >= 75 ? 'success' : ($pct >= 40 ? 'warning' : 'danger'); ?>" 
                                     style="width: <?php echo $pct; ?>%"><?php echo $pct; ?>%</div>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="/js/recapitulation.js"></script>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
