<?php require_once __DIR__ . '/../layout/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Liste des dons</h1>
    <a href="/dons/create" class="btn btn-success">Ajouter un don</a>
</div>

<div class="table-responsive">
    <table class="table table-striped table-bordered">
        <thead class="table-dark">
            <tr>
                <th>Type de besoin</th>
                <th>Catégorie</th>
                <th>Quantité</th>
                <th>Quantité restante</th>
                <th>Statut</th>
                <th>Date de saisie</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($dons as $don): ?>
            <tr>
                <td><?php echo htmlspecialchars($don['type_besoin_nom']); ?></td>
                <td><?php echo htmlspecialchars($don['categorie_nom']); ?></td>
                <td><?php echo $don['quantite'] . ' ' . htmlspecialchars($don['unite']); ?></td>
                <td><?php echo $don['quantite_restante'] . ' ' . htmlspecialchars($don['unite']); ?></td>
                <td>
                    <?php 
                    $badge_class = $don['statut'] == 'distribué' ? 'bg-success' : ($don['statut'] == 'partiel' ? 'bg-warning' : 'bg-secondary');
                    echo "<span class='badge $badge_class'>" . htmlspecialchars($don['statut']) . "</span>"; 
                    ?>
                </td>
                <td><?php echo date('d/m/Y H:i', strtotime($don['date_saisie'])); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>