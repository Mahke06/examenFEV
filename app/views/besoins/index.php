<?php require_once __DIR__ . '/../layout/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Liste des besoins</h1>
    <a href="/besoins/create" class="btn btn-primary">Ajouter un besoin</a>
</div>

<div class="table-responsive">
    <table class="table table-striped table-bordered">
        <thead class="table-dark">
            <tr>
                <th>Ville</th>
                <th>Type de besoin</th>
                <th>Catégorie</th>
                <th>Quantité demandée</th>
                <th>Quantité satisfaite</th>
                <th>Reste</th>
                <th>Prix unitaire</th>
                <th>Valeur totale</th>
                <th>Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($besoins as $besoin): ?>
            <tr>
                <td><?php echo $besoin['ville_nom']; ?></td>
                <td><?php echo $besoin['type_besoin_nom']; ?></td>
                <td><?php echo $besoin['categorie_nom']; ?></td>
                <td><?php echo $besoin['quantite_demandee'] . ' ' . $besoin['unite']; ?></td>
                <td><?php echo $besoin['quantite_satisfaite'] . ' ' . $besoin['unite']; ?></td>
                <td>
                    <?php 
                    $reste = $besoin['quantite_demandee'] - $besoin['quantite_satisfaite'];
                    $class = $reste == 0 ? 'text-success' : 'text-danger';
                    echo "<span class='$class'>" . $reste . ' ' . $besoin['unite'] . "</span>"; 
                    ?>
                </td>
                <td><?php echo number_format($besoin['prix_unitaire'], 0, ',', ' ') . ' Ar'; ?></td>
                <td><?php echo number_format($besoin['quantite_demandee'] * $besoin['prix_unitaire'], 0, ',', ' ') . ' Ar'; ?></td>
                <td><?php echo date('d/m/Y H:i', strtotime($besoin['date_saisie'])); ?></td>
                <td>
                    <form method="POST" action="/besoins/delete" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce besoin ? Les attributions et achats liés seront annulés.');">
                        <input type="hidden" name="id" value="<?php echo $besoin['id']; ?>">
                        <button type="submit" class="btn btn-danger btn-sm">Supprimer</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>