<?php require_once __DIR__ . '/../layout/header.php'; ?>

<h1 class="mb-4">Liste des villes</h1>

<div class="table-responsive">
    <table class="table table-striped table-bordered">
        <thead class="table-dark">
            <tr>
                <th>Ville</th>
                <th>Région</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($villes as $ville): ?>
            <tr>
                <td><?php echo $ville['nom']; ?></td>
                <td><?php echo $ville['region_nom']; ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>