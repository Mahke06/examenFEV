<?php require_once __DIR__ . '/../layout/header.php'; ?>

<h1 class="mb-4">Liste des villes</h1>

<div class="table-responsive">
    <table class="table table-striped table-bordered align-middle">
        <thead class="table-dark">
            <tr>
                <th class="text-nowrap">Ville</th>
                <th class="text-nowrap">Région</th>
                <th class="text-nowrap">Nombre de sinistrés</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($villes as $ville): ?>
            <tr>
                <td><?php echo $ville['nom']; ?></td>
                <td><?php echo $ville['region_nom']; ?></td>
                <td><?php echo number_format($ville['nombre_sinistres'], 0, ',', ' '); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>