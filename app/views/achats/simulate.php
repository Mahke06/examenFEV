<?php require_once __DIR__ . '/../layout/header.php'; ?>

<h1 class="mb-4">Simulation d'achat</h1>

<div class="card mb-3">
    <div class="card-body">
        <p><strong>Don utilisé :</strong> #<?php echo $don['id']; ?> - <?php echo number_format($don['quantite_restante'],0,',',' ') . ' Ar'; ?></p>
        <p><strong>Type d'achat :</strong> <?php echo $type['nom']; ?> - <?php echo number_format($type['prix_unitaire'],0,',',' ') . ' Ar / ' . $type['unite']; ?></p>
    </div>
</div>

<?php if(empty($allocations)): ?>
    <div class="alert alert-warning">Aucune allocation possible avec ce don et ce type (fonds insuffisants ou besoins remplis).</div>
    <a href="/achats/create" class="btn btn-secondary">Retour</a>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-sm table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>Ville</th>
                    <th>Quantité</th>
                    <th>Montant net</th>
                    <th>Coût (avec frais)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($allocations as $a): ?>
                <tr>
                    <td><?php echo $a['ville_nom']; ?></td>
                    <td><?php echo $a['quantite']; ?></td>
                    <td><?php echo number_format($a['montant_net'],0,',',' ') . ' Ar'; ?></td>
                    <td><?php echo number_format($a['montant_cost'],0,',',' ') . ' Ar'; ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <th>Total</th>
                    <th><?php echo array_sum(array_column($allocations, 'quantite')); ?></th>
                    <th><?php echo number_format($total_net,0,',',' ') . ' Ar'; ?></th>
                    <th><?php echo number_format($total_cost,0,',',' ') . ' Ar'; ?></th>
                </tr>
            </tfoot>
        </table>
    </div>

    <form method="POST" action="/achats/validate">
        <input type="hidden" name="don_id" value="<?php echo $don['id']; ?>">
        <input type="hidden" name="type_besoin_id" value="<?php echo $type['id']; ?>">
        <input type="hidden" name="allocations" value='<?php echo json_encode($allocations); ?>'>
        <a href="/achats/create" class="btn btn-secondary">Retour</a>
        <button class="btn btn-success">Valider (dispatch réel)</button>
    </form>
<?php endif; ?>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
