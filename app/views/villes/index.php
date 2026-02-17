<?php require_once __DIR__ . '/../layout/header.php'; ?>

<style>
    .table-simple {
        table-layout: fixed;
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 1rem;
    }
    
    .table-simple th, .table-simple td {
        padding: 10px 8px;
        font-size: 0.9rem;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        border: 1px solid #dee2e6;
    }

    .col-ville { width: 40%; }
    .col-region { width: 40%; }
    .col-data { width: 20%; }
</style>

<h1 class="mb-4">Liste des villes</h1>

<div class="table-responsive">
    <table border="1" class="table-simple">
        <thead style="background-color: #212529; color: white;">
            <tr>
                <th class="col-ville">Ville</th>
                <th class="col-region">Région</th>
                <th class="col-data">Statut</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($villes as $ville): ?>
            <tr>
                <td title="<?php echo $ville['nom']; ?>">
                    <b><?php echo $ville['nom']; ?></b>
                </td>
                <td>
                    <?php echo $ville['region_nom']; ?>
                </td>
                <td align="center">
                    <span style="font-size: 0.8rem; color: #6c757d;">Répertoriée</span>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>