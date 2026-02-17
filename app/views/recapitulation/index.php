<?php require_once __DIR__ . '/../layout/header.php'; ?>

<style>
    .table-recap {
        table-layout: fixed;
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 1rem;
    }
    
    .table-recap th, .table-recap td {
        padding: 8px;
        font-size: 0.85rem;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        border: 1px solid #dee2e6;
    }

    .col-name { width: 30%; }
    .col-amount { width: 20%; }
    .col-progress { width: 30%; }

    .stat-box {
        padding: 15px;
        border-radius: 8px;
        color: white;
        text-align: center;
        margin-bottom: 1rem;
    }
</style>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <h1>📊 Récapitulation financière</h1>
    <button id="btn-actualiser" class="btn btn-primary" onclick="actualiserDonnees()">
        🔄 Actualiser
    </button>
</div>

<div class="row mb-2">
    <div class="col-md-4">
        <div class="stat-box" style="background: linear-gradient(135deg, #0d6efd, #0a58ca);">
            <small class="text-uppercase" style="opacity:0.8;">Besoins Totaux</small>
            <div id="kpi-besoins-totaux" class="fs-3 fw-bold">
                <?php echo number_format($data['besoins_totaux'], 0, ',', ' '); ?> Ar
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-box" style="background: linear-gradient(135deg, #198754, #157347);">
            <small class="text-uppercase" style="opacity:0.8;">Besoins Satisfaits</small>
            <div id="kpi-besoins-satisfaits" class="fs-3 fw-bold">
                <?php echo number_format($data['besoins_satisfaits'], 0, ',', ' '); ?> Ar
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-box" style="background: linear-gradient(135deg, #dc3545, #bb2d3b);">
            <small class="text-uppercase" style="opacity:0.8;">Besoins Restants</small>
            <div id="kpi-besoins-restants" class="fs-3 fw-bold">
                <?php echo number_format($data['besoins_restants'], 0, ',', ' '); ?> Ar
            </div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header bg-dark text-white py-2">
        <h5 class="mb-0 small fw-bold">🏙️ Détail par ville</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table border="1" class="table-recap">
                <thead style="background-color: #f8f9fa;">
                    <tr>
                        <th class="col-name">Ville</th>
                        <th class="col-amount text-end">Total (Ar)</th>
                        <th class="col-amount text-end">Satisfaits</th>
                        <th class="col-amount text-end">Restants</th>
                        <th class="col-progress">Progression</th>
                    </tr>
                </thead>
                <tbody id="table-villes">
                    <?php foreach ($data['detail_villes'] as $ville): ?>
                    <?php $pct = $ville['besoins_totaux'] > 0 ? round(($ville['besoins_satisfaits'] / $ville['besoins_totaux']) * 100, 1) : 0; ?>
                    <tr>
                        <td class="fw-bold"><?php echo $ville['ville_nom']; ?></td>
                        <td align="right"><?php echo number_format($ville['besoins_totaux'], 0, ',', ' '); ?></td>
                        <td align="right" style="color: green;"><?php echo number_format($ville['besoins_satisfaits'], 0, ',', ' '); ?></td>
                        <td align="right" style="color: red;"><?php echo number_format($ville['besoins_restants'], 0, ',', ' '); ?></td>
                        <td>
                            <div class="progress" style="height: 12px; margin: 0;">
                                <div class="progress-bar bg-<?php echo $pct >= 75 ? 'success' : ($pct >= 40 ? 'warning' : 'danger'); ?>" 
                                     style="width: <?php echo $pct; ?>%"></div>
                            </div>
                            <small style="font-size: 0.7rem;"><?php echo $pct; ?>%</small>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header bg-dark text-white py-2">
        <h5 class="mb-0 small fw-bold">📁 Détail par catégorie</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table border="1" class="table-recap">
                <thead style="background-color: #f8f9fa;">
                    <tr>
                        <th class="col-name">Catégorie</th>
                        <th class="col-amount text-end">Total (Ar)</th>
                        <th class="col-amount text-end">Satisfaits</th>
                        <th class="col-amount text-end">Restants</th>
                        <th class="col-progress">Progression</th>
                    </tr>
                </thead>
                <tbody id="table-categories">
                    <?php foreach ($data['detail_categories'] as $cat): ?>
                    <?php $pct = $cat['besoins_totaux'] > 0 ? round(($cat['besoins_satisfaits'] / $cat['besoins_totaux']) * 100, 1) : 0; ?>
                    <tr>
                        <td>
                            <b style="color: <?php echo $cat['categorie_nom'] === 'en Nature' ? '#198754' : ($cat['categorie_nom'] === 'en Materiaux' ? '#fd7e14' : '#0dcaf0'); ?>;">
                                <?php echo $cat['categorie_nom']; ?>
                            </b>
                        </td>
                        <td align="right"><?php echo number_format($cat['besoins_totaux'], 0, ',', ' '); ?></td>
                        <td align="right" style="color: green;"><?php echo number_format($cat['besoins_satisfaits'], 0, ',', ' '); ?></td>
                        <td align="right" style="color: red;"><?php echo number_format($cat['besoins_restants'], 0, ',', ' '); ?></td>
                        <td>
                            <div class="progress" style="height: 12px; margin: 0;">
                                <div class="progress-bar bg-<?php echo $pct >= 75 ? 'success' : ($pct >= 40 ? 'warning' : 'danger'); ?>" 
                                     style="width: <?php echo $pct; ?>%"></div>
                            </div>
                            <small style="font-size: 0.7rem;"><?php echo $pct; ?>%</small>
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