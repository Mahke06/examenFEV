<?php require_once __DIR__ . '/../layout/header.php'; ?>

<h1 class="mb-4">Tableau de bord</h1>

<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white stat-card">
            <div class="card-body">
                <h5>Villes</h5>
                <div class="stat-number"><?php echo count($dashboard_data); ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
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
    <div class="col-md-3">
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
    <div class="col-md-3">
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

<!-- Filtre par ville -->
<div class="card mb-4">
    <div class="card-body py-3">
        <div class="row align-items-end">
            <div class="col-md-4">
                <label for="filtre_ville_dashboard" class="form-label fw-bold">Filtrer par ville</label>
                <select id="filtre_ville_dashboard" class="form-select">
                    <option value="">— Toutes les villes —</option>
                    <?php foreach($dashboard_data as $data): ?>
                    <option value="<?php echo htmlspecialchars($data['ville']['nom']); ?>">
                        <?php echo htmlspecialchars($data['ville']['nom']) . ' (' . htmlspecialchars($data['ville']['region_nom']) . ')'; ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>
</div>

<?php foreach($dashboard_data as $data): ?>
<div class="card ville-card" data-ville="<?php echo htmlspecialchars($data['ville']['nom']); ?>">
    <div class="card-header bg-primary text-white">
        <h4 class="mb-0"><?php echo $data['ville']['nom']; ?> - <?php echo $data['ville']['region_nom']; ?></h4>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <h5>Besoins</h5>
                <?php if(count($data['besoins']) > 0): ?>
                <table class="table table-sm table-bordered">
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Catégorie</th>
                            <th>Demandé</th>
                            <th>Satisfait</th>
                            <th>Reste</th>
                            <th>Valeur totale</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($data['besoins'] as $besoin): ?>
                        <tr>
                            <td><?php echo $besoin['type_besoin_nom']; ?></td>
                            <td><?php echo $besoin['categorie_nom']; ?></td>
                            <td><?php echo $besoin['quantite_demandee'] . ' ' . $besoin['unite']; ?></td>
                            <td><?php echo $besoin['quantite_satisfaite'] . ' ' . $besoin['unite']; ?></td>
                            <td>
                                <?php 
                                $reste = $besoin['quantite_demandee'] - $besoin['quantite_satisfaite'];
                                echo $reste . ' ' . $besoin['unite']; 
                                ?>
                            </td>
                            <td><?php echo number_format($besoin['quantite_demandee'] * $besoin['prix_unitaire'], 0, ',', ' ') . ' Ar'; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <p class="text-muted">Aucun besoin enregistré</p>
                <?php endif; ?>
            </div>
            
            <div class="col-md-6">
                <h5>Dons attribués</h5>
                <?php if(count($data['attributions']) > 0): ?>
                <table class="table table-sm table-bordered">
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Quantité</th>
                            <th>Valeur</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($data['attributions'] as $attribution): ?>
                        <tr>
                            <td><?php echo $attribution['type_besoin_nom']; ?></td>
                            <td><?php echo $attribution['quantite_attribuee'] . ' ' . $attribution['unite']; ?></td>
                            <td><?php echo number_format($attribution['quantite_attribuee'] * $attribution['prix_unitaire'], 0, ',', ' ') . ' Ar'; ?></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($attribution['date_attribution'])); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <p class="text-muted">Aucun don attribué</p>
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
        if (villeChoisie === '' || card.dataset.ville === villeChoisie) {
            card.style.display = '';
        } else {
            card.style.display = 'none';
        }
    });
});
</script>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>