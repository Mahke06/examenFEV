<?php require_once __DIR__ . '/../layout/header.php'; ?>

<h1 class="mb-4">Tableau de bord</h1>

<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="card bg-primary text-white stat-card">
            <div class="card-body">
                <h5>Villes</h5>
                <div class="stat-number"><?php echo count($dashboard_data); ?></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card bg-success text-white stat-card">
            <div class="card-body">
                <h5>Besoins totaux</h5>
                <div class="stat-number">
                    <?php 
                    $total_besoins = 0;
                    foreach($dashboard_data as $data) {
                        $total_besoins += count($data['besoins']);
                    }
                    echo $total_besoins;
                    ?>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card bg-warning text-white stat-card">
            <div class="card-body">
                <h5>Attributions</h5>
                <div class="stat-number">
                    <?php 
                    $total_attributions = 0;
                    foreach($dashboard_data as $data) {
                        $total_attributions += count($data['attributions']);
                    }
                    echo $total_attributions;
                    ?>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card bg-info text-white stat-card">
            <div class="card-body">
                <h5>Valeur totale</h5>
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


<div class="card mb-4">
    <div class="card-body py-3">
        <div class="row align-items-end">
            <div class="col-lg-4 col-md-6">
                <label for="filtre_ville_dashboard" class="form-label fw-bold">Filtrer par ville</label>
                <select id="filtre_ville_dashboard" class="form-select">
                    <option value="">— Toutes les villes —</option>
                    <?php foreach($dashboard_data as $data): ?>
                    <option value="<?php echo $data['ville']['nom']; ?>">
                        <?php echo $data['ville']['nom'] . ' (' . $data['ville']['region_nom'] . ')'; ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>
</div>

<?php foreach($dashboard_data as $data): ?>
<div class="card ville-card" data-ville="<?php echo $data['ville']['nom']; ?>">
    <div class="card-header bg-primary text-white">
        <h4 class="mb-0"><?php echo $data['ville']['nom']; ?> - <?php echo $data['ville']['region_nom']; ?></h4>
    </div>
    <div class="card-body">
        <div class="row g-4">
            <div class="col-12 col-xl-6">
                <h5>Besoins</h5>
                <?php if(count($data['besoins']) > 0): ?>
                <div class="table-responsive">
                <table class="table table-sm table-bordered align-middle">
                    <thead>
                        <tr>
                            <th class="text-nowrap">Type</th>
                            <th class="text-nowrap">Catégorie</th>
                            <th class="text-nowrap">Demandé</th>
                            <th class="text-nowrap">Satisfait</th>
                            <th class="text-nowrap">Reste</th>
                            <th class="text-nowrap">Valeur totale</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($data['besoins'] as $besoin): ?>
                        <tr>
                            <td class="text-nowrap"><?php echo $besoin['type_besoin_nom']; ?></td>
                            <td class="text-nowrap"><?php echo $besoin['categorie_nom']; ?></td>
                            <td class="text-nowrap"><?php echo $besoin['quantite_demandee'] . ' ' . $besoin['unite']; ?></td>
                            <td class="text-nowrap"><?php echo $besoin['quantite_satisfaite'] . ' ' . $besoin['unite']; ?></td>
                            <td class="text-nowrap">
                                <?php 
                                $reste = $besoin['quantite_demandee'] - $besoin['quantite_satisfaite'];
                                echo $reste . ' ' . $besoin['unite']; 
                                ?>
                            </td>
                            <td class="text-nowrap"><?php echo number_format($besoin['quantite_demandee'] * $besoin['prix_unitaire'], 0, ',', ' ') . ' Ar'; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                </div>
                <?php else: ?>
                <p class="text-muted">Aucun besoin enregistré</p>
                <?php endif; ?>
            </div>
            
            <div class="col-12 col-xl-6">
                <h5>Dons attribués</h5>
                <?php if(count($data['attributions']) > 0): ?>
                <div class="table-responsive">
                <table class="table table-sm table-bordered align-middle">
                    <thead>
                        <tr>
                            <th class="text-nowrap">Type</th>
                            <th class="text-nowrap">Quantité</th>
                            <th class="text-nowrap">Valeur</th>
                            <th class="text-nowrap">Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($data['attributions'] as $attribution): ?>
                        <tr>
                            <td class="text-nowrap"><?php echo $attribution['type_besoin_nom']; ?></td>
                            <td class="text-nowrap"><?php echo $attribution['quantite_attribuee'] . ' ' . $attribution['unite']; ?></td>
                            <td class="text-nowrap"><?php echo number_format($attribution['quantite_attribuee'] * $attribution['prix_unitaire'], 0, ',', ' ') . ' Ar'; ?></td>
                            <td class="text-nowrap"><?php echo date('d/m/Y H:i', strtotime($attribution['date_attribution'])); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                </div>
                <?php else: ?>
                <p class="text-muted">Aucun don attribué</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php endforeach; ?>

<script src="/js/dashboard.js"></script>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>