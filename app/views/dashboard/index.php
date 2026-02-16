<?php require_once __DIR__ . '/../layout/header.php'; ?>

<style>
    /* Force la structure du tableau pour éviter le décalage des colonnes */
    .table-fixed {
        table-layout: fixed;
        width: 100%;
        margin-bottom: 0;
    }
    
    .table-fixed th, .table-fixed td {
        overflow: hidden;
        text-overflow: ellipsis; /* Ajoute "..." si le texte est trop long */
        white-space: nowrap;
        font-size: 0.85rem;
        padding: 0.5rem !important;
        vertical-align: middle;
    }

    /* Largeurs fixes pour un alignement parfait entre Header et Body */
    .col-type { width: 25%; }
    .col-cat { width: 20%; }
    .col-qte { width: 15%; }
    .col-val { width: 25%; }

    .stat-number { font-size: 1.25rem; font-weight: bold; }
    .ville-card { margin-bottom: 1.5rem; border: 1px solid #dee2e6; }
</style>

<h1 class="mb-4">Tableau de bord</h1>

<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="card bg-primary text-white">
            <div class="card-body p-3">
                <small>Villes</small>
                <div class="stat-number"><?php echo count($dashboard_data); ?></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card bg-success text-white">
            <div class="card-body p-3">
                <small>Besoins totaux</small>
                <div class="stat-number">
                    <?php 
                    $total_besoins = 0;
                    foreach($dashboard_data as $data) { $total_besoins += count($data['besoins']); }
                    echo $total_besoins;
                    ?>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card bg-warning text-white">
            <div class="card-body p-3">
                <small>Attributions</small>
                <div class="stat-number">
                    <?php 
                    $total_attributions = 0;
                    foreach($dashboard_data as $data) { $total_attributions += count($data['attributions']); }
                    echo $total_attributions;
                    ?>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card bg-info text-white">
            <div class="card-body p-3">
                <small>Valeur totale</small>
                <div class="stat-number">
                    <?php 
                    $valeur_totale = 0;
                    foreach($dashboard_data as $data) {
                        foreach($data['besoins'] as $besoin) {
                            $valeur_totale += $besoin['quantite_demandee'] * $besoin['prix_unitaire'];
                        }
                    }
                    echo number_format($valeur_totale, 0, ',', ' ') . ' Ar';
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card mb-4 shadow-sm">
    <div class="card-body py-2">
        <div class="row align-items-center">
            <div class="col-auto"><label class="small fw-bold">Filtrer par ville :</label></div>
            <div class="col-md-4">
                <select id="filtre_ville_dashboard" class="form-select form-select-sm">
                    <option value="">— Toutes les villes —</option>
                    <?php foreach($dashboard_data as $data): ?>
                    <option value="<?php echo htmlspecialchars($data['ville']['nom']); ?>">
                        <?php echo htmlspecialchars($data['ville']['nom']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>
</div>

<?php foreach($dashboard_data as $data): ?>
<div class="card ville-card shadow-sm" data-ville="<?php echo htmlspecialchars($data['ville']['nom']); ?>">
    <div class="card-header bg-primary text-white py-2">
        <h5 class="mb-0 small fw-bold"><?php echo $data['ville']['nom']; ?> - <?php echo $data['ville']['region_nom']; ?></h5>
    </div>
    <div class="card-body p-2">
        <div class="row g-2">
            <div class="col-12 col-xl-7">
                <h6 class="small fw-bold border-bottom pb-1">Besoins</h6>
                <?php if(count($data['besoins']) > 0): ?>
                <div class="table-responsive">
                    <table >
                        <thead class="table-light">
                            <tr>
                                <th class="col-type">Type</th>
                                <th class="col-cat">Catégorie</th>
                                <th class="col-qte text-center">Dem./Sat.</th>
                                <th class="col-val text-end">Valeur Totale</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($data['besoins'] as $besoin): ?>
                            <tr>
                                <td class="fw-bold">
                                    <?php echo !empty($besoin['type_besoin_nom']) ? $besoin['type_besoin_nom'] : 'N/A'; ?>
                                </td>
                                <td><?php echo $besoin['categorie_nom']; ?></td>
                                <td class="text-center small">
                                    <?php echo $besoin['quantite_demandee']; ?> / <span class="text-success"><?php echo $besoin['quantite_satisfaite']; ?></span>
                                </td>
                                <td class="text-end fw-bold">
                                    <?php echo number_format($besoin['quantite_demandee'] * $besoin['prix_unitaire'], 0, ',', ' '); ?> Ar
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <p class="small text-muted mb-0">Aucun besoin enregistré</p>
                <?php endif; ?>
            </div>
            
            <div class="col-12 col-xl-5">
                <h6 class="small fw-bold border-bottom pb-1">Dons attribués</h6>
                <?php if(count($data['attributions']) > 0): ?>
                <div class="table-responsive">
                    <table >
                        <thead class="table-light">
                            <tr>
                                <th style="width: 40%;">Type</th>
                                <th style="width: 20%;">Qté</th>
                                <th style="width: 40%;" class="text-end">Valeur</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($data['attributions'] as $attribution): ?>
                            <tr>
                                <td class="fw-bold">
                                    <?php echo !empty($attribution['type_besoin_nom']) ? $attribution['type_besoin_nom'] : 'N/A'; ?>
                                </td>
                                <td><?php echo $attribution['quantite_attribuee']; ?> <?php echo $attribution['unite']; ?></td>
                                <td class="text-end">
                                    <?php echo number_format($attribution['quantite_attribuee'] * $attribution['prix_unitaire'], 0, ',', ' '); ?> Ar
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <p class="small text-muted mb-0">Aucun don attribué</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php endforeach; ?>

<script>
document.getElementById('filtre_ville_dashboard').addEventListener('change', function() {
    const villeChoisie = this.value;
    document.querySelectorAll('.ville-card').forEach(function(card) {
        card.style.display = (villeChoisie === '' || card.dataset.ville === villeChoisie) ? '' : 'none';
    });
});
</script>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>