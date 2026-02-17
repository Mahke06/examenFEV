<?php require_once __DIR__ . '/../layout/header.php'; ?>

<style>
    .table-simple {
        table-layout: fixed;
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 1rem;
    }
    
    .table-simple th, .table-simple td {
        padding: 8px;
        font-size: 0.8rem;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        border: 1px solid #dee2e6;
    }

    .col-xs { width: 30px; }
    .col-md { width: 12%; }
    .col-lg { width: 15%; }
    .col-price { width: 11%; }
    .col-action { width: 90px; }

    .badge-simple {
        padding: 2px 5px;
        font-size: 0.75rem;
        border-radius: 3px;
        font-weight: bold;
    }   
</style>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <h1>📦 Liste des achats</h1>
    <a href="/achats/create" class="btn btn-primary">Nouvel achat</a>
</div>

<?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success py-2">
        <strong>✅ Succès !</strong> <?php echo urldecode($_GET['success']); ?>
    </div>
<?php endif; ?>

<div class="card mb-3 border-0 bg-light">
    <div class="card-body py-2">
        <form method="GET" action="/achats" class="row g-2 align-items-center">
            <div class="col-auto small fw-bold">Ville:</div>
            <div class="col-md-3">
                <select name="ville_id" class="form-select form-select-sm">
                    <option value="">— Toutes —</option>
                    <?php foreach ($villes as $ville): ?>
                        <option value="<?php echo $ville['id']; ?>" <?php echo (isset($_GET['ville_id']) && $_GET['ville_id'] == $ville['id']) ? 'selected' : ''; ?>>
                            <?php echo $ville['nom']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-sm btn-primary">Filtrer</button>
            </div>
            <div class="col text-end">
                <span class="badge-simple bg-info text-white">Frais: <?php echo $frais_achat; ?>%</span>
            </div>
        </form>
    </div>
</div>

<?php if (empty($achats)): ?>
    <div class="alert alert-info">Aucun achat enregistré.</div>
<?php else: ?>
    <?php 
    $total_general = 0;
    foreach ($achats as $a) { $total_general += $a['montant_total']; }
    ?>
    <div class="table-responsive">
        <table border="1" class="table-simple">
            <thead style="background-color: #212529; color: white;">
                <tr>
                    <th class="col-xs">#</th>
                    <th class="col-md">Ville</th>
                    <th class="col-lg">Type de besoin</th>
                    <th class="col-md">Catégorie</th>
                    <th class="col-md">Quantité</th>
                    <th class="col-price text-end">HT</th>
                    <th class="col-price text-end">Total TTC</th>
                    <th class="col-lg">Date</th>
                    <th class="col-action">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($achats as $index => $achat): ?>
                <tr>
                    <td align="center"><?php echo $index + 1; ?></td>
                    <td><?php echo $achat['ville_nom']; ?></td>
                    <td title="<?php echo $achat['type_besoin_nom']; ?>">
                        <b><?php echo $achat['type_besoin_nom']; ?></b>
                    </td>
                    <td align="center">
                        <?php 
                        $color = $achat['categorie_nom'] === 'en Nature' ? '#198754' : '#ffc107';
                        $text = $achat['categorie_nom'] === 'en Nature' ? 'white' : 'black';
                        ?>
                        <span class="badge-simple" style="background-color:<?php echo $color; ?>; color:<?php echo $text; ?>;">
                            <?php echo $achat['categorie_nom']; ?>
                        </span>
                    </td>
                    <td><?php echo number_format($achat['quantite'], 0, ',', ' ') . ' ' . $achat['unite']; ?></td>
                    <td align="right"><?php echo number_format($achat['quantite'] * $achat['prix_unitaire'], 0, ',', ' '); ?></td>
                    <td align="right"><b><?php echo number_format($achat['montant_total'], 0, ',', ' '); ?></b></td>
                    <td class="small"><?php echo date('d/m/y H:i', strtotime($achat['date_achat'])); ?></td>
                    <td align="center">
                        <form method="POST" action="/achats/delete" onsubmit="return confirm('Supprimer cet achat ?');">
                            <input type="hidden" name="id" value="<?php echo $achat['id']; ?>">
                            <button type="submit" style="color:red; border:none; background:none; cursor:pointer; font-size:0.75rem; text-decoration:underline;">Supprimer</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot style="background-color: #f8f9fa; font-weight: bold;">
                <tr>
                    <td colspan="6" align="right">TOTAL GÉNÉRAL :</td>
                    <td align="right" style="background-color: #e9ecef;"><?php echo number_format($total_general, 0, ',', ' '); ?> Ar</td>
                    <td colspan="2"></td>
                </tr>
            </tfoot>
        </table>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>