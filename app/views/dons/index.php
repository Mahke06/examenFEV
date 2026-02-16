<?php require_once __DIR__ . '/../layout/header.php'; ?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <h1>Liste des dons</h1>
    <a href="/dons/create" class="btn btn-success">Ajouter un don</a>
</div>

<div class="table-responsive">
    <table class="table table-striped table-bordered align-middle">
        <thead class="table-dark">
            <tr>
                <th class="text-nowrap">Type de besoin</th>
                <th class="text-nowrap">Catégorie</th>
                <th class="text-nowrap">Quantité</th>
                <th class="text-nowrap">Qté restante</th>
                <th class="text-nowrap">Statut</th>
                <th class="text-nowrap">Date de saisie</th>
                <th class="text-nowrap">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($dons as $don): ?>
            <tr>
                <td class="text-nowrap"><?php echo $don['type_besoin_nom']; ?></td>
                <td class="text-nowrap"><?php echo $don['categorie_nom']; ?></td>
                <td class="text-nowrap"><?php echo $don['quantite'] . ' ' . ($don['unite']); ?></td>
                <td class="text-nowrap"><?php echo $don['quantite_restante'] . ' ' . ($don['unite']); ?></td>
                <td>
                    <?php 
                    $badge_class = $don['statut'] == 'distribué' ? 'bg-success' : ($don['statut'] == 'partiel' ? 'bg-warning' : 'bg-secondary');
                    echo "<span class='badge $badge_class'>" . ($don['statut']) . "</span>"; 
                    ?>
                </td>
                <td class="text-nowrap"><?php echo date('d/m/Y H:i', strtotime($don['date_saisie'])); ?></td>
                <td>
                    <form method="POST" action="/dons/delete" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce don ? Les attributions liées seront annulées.');">
                        <input type="hidden" name="id" value="<?php echo $don['id']; ?>">
                        <button type="submit" class="btn btn-danger btn-sm">Supprimer</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>