<?php require_once __DIR__ . '/../layout/header.php'; ?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <h1>📊 Récapitulation financière</h1>
    <button id="btn-actualiser" class="btn btn-primary" onclick="actualiserDonnees()">
        🔄 Actualiser
    </button>
</div>

<!-- KPI Cards -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-lg-4">
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

<!-- Barre de progression -->
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
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-lg-4">
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

<!-- Détail par ville -->
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
                        <td class="fw-bold"><?php echo htmlspecialchars($ville['ville_nom']); ?></td>
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

<!-- Détail par catégorie -->
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
                                <?php echo htmlspecialchars($cat['categorie_nom']); ?>
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

<script>
function formatMontant(val) {
    return Math.round(val).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ' ') + ' Ar';
}

function formatMontantSansAr(val) {
    return Math.round(val).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
}

function getBadgeClass(pct) {
    if (pct >= 75) return 'success';
    if (pct >= 40) return 'warning';
    return 'danger';
}

function getCategoryBadge(nom) {
    if (nom === 'en Nature') return 'success';
    if (nom === 'en Materiaux') return 'warning';
    return 'info';
}

function actualiserDonnees() {
    const btn = document.getElementById('btn-actualiser');
    btn.disabled = true;
    btn.innerHTML = '⏳ Chargement...';

    fetch('/recapitulation/api')
        .then(response => response.json())
        .then(data => {
            // KPI principaux
            document.getElementById('kpi-besoins-totaux').textContent = formatMontant(data.besoins_totaux);
            document.getElementById('kpi-besoins-satisfaits').textContent = formatMontant(data.besoins_satisfaits);
            document.getElementById('kpi-besoins-restants').textContent = formatMontant(data.besoins_restants);
            
            // Pourcentage
            const pct = data.pourcentage;
            const pctEl = document.getElementById('kpi-pourcentage');
            pctEl.textContent = pct + '%';
            pctEl.className = 'badge bg-' + getBadgeClass(pct) + ' fs-6';

            // Barre de progression
            const bar = document.getElementById('progress-bar');
            bar.style.width = pct + '%';
            bar.textContent = pct + '%';
            bar.setAttribute('aria-valuenow', pct);
            bar.className = 'progress-bar bg-' + getBadgeClass(pct) + ' progress-bar-striped progress-bar-animated';

            // KPI secondaires
            document.getElementById('kpi-dons-totaux').textContent = formatMontant(data.dons_totaux);
            document.getElementById('kpi-achats-totaux').textContent = formatMontant(data.achats_totaux);
            document.getElementById('kpi-solde-argent').textContent = formatMontant(data.solde_argent);

            // Tableau villes
            let villesHtml = '';
            data.detail_villes.forEach(v => {
                const vpct = v.besoins_totaux > 0 ? Math.round((v.besoins_satisfaits / v.besoins_totaux) * 1000) / 10 : 0;
                villesHtml += '<tr>' +
                    '<td class="fw-bold">' + v.ville_nom + '</td>' +
                    '<td class="text-end">' + formatMontantSansAr(v.besoins_totaux) + '</td>' +
                    '<td class="text-end text-success">' + formatMontantSansAr(v.besoins_satisfaits) + '</td>' +
                    '<td class="text-end text-danger">' + formatMontantSansAr(v.besoins_restants) + '</td>' +
                    '<td><div class="progress" style="height:18px;"><div class="progress-bar bg-' + getBadgeClass(vpct) + '" style="width:' + vpct + '%">' + vpct + '%</div></div></td>' +
                    '</tr>';
            });
            document.getElementById('table-villes').innerHTML = villesHtml;

            // Tableau catégories
            let catHtml = '';
            data.detail_categories.forEach(c => {
                const cpct = c.besoins_totaux > 0 ? Math.round((c.besoins_satisfaits / c.besoins_totaux) * 1000) / 10 : 0;
                catHtml += '<tr>' +
                    '<td><span class="badge bg-' + getCategoryBadge(c.categorie_nom) + '">' + c.categorie_nom + '</span></td>' +
                    '<td class="text-end">' + formatMontantSansAr(c.besoins_totaux) + '</td>' +
                    '<td class="text-end text-success">' + formatMontantSansAr(c.besoins_satisfaits) + '</td>' +
                    '<td class="text-end text-danger">' + formatMontantSansAr(c.besoins_restants) + '</td>' +
                    '<td><div class="progress" style="height:18px;"><div class="progress-bar bg-' + getBadgeClass(cpct) + '" style="width:' + cpct + '%">' + cpct + '%</div></div></td>' +
                    '</tr>';
            });
            document.getElementById('table-categories').innerHTML = catHtml;

            btn.disabled = false;
            btn.innerHTML = '🔄 Actualiser';
        })
        .catch(err => {
            console.error('Erreur:', err);
            btn.disabled = false;
            btn.innerHTML = '🔄 Actualiser';
            alert('Erreur lors de l\'actualisation des données.');
        });
}
</script>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
