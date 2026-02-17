<?php require_once __DIR__ . '/../layout/header.php'; ?>

<h1 class="mb-4">Ajouter un don (proportionnel par ville)</h1>

<div class="card">
    <div class="card-body">
        <div class="alert alert-info">
            <strong>Information:</strong> Le don sera distribué <strong>proportionnellement</strong> entre toutes les villes ayant des besoins pour ce type.
            <br><br>
            <strong>Formule :</strong> <code>distribution = (besoin / besoin_total) × don</code>
            <br>
            Le résultat est arrondi à l'entier inférieur (floor), puis le reste est distribué un par un aux villes ayant la <strong>partie décimale la plus haute</strong>.
            <br><br>
            <strong>Exemple :</strong> Si un don de 10 est ajouté et que :
            <ul class="mb-0 mt-1">
                <li>Ville A a besoin de 30 (30/70 × 10 = 4.28) → reçoit <strong>4</strong></li>
                <li>Ville B a besoin de 40 (40/70 × 10 = 5.71) → reçoit <strong>5 + 1 = 6</strong> (décimale la plus haute)</li>
            </ul>
            Total distribué = 10. Le reste (1) va à la ville avec la décimale la plus haute (0.71 > 0.28).
        </div>

        <form method="POST" action="/dons/store-proportionnel">
            <div class="mb-3">
                <label for="type_besoin_id" class="form-label">Type de besoin</label>
                <select class="form-select" id="type_besoin_id" name="type_besoin_id" required>
                    <option value="">Sélectionner un type de besoin</option>
                    <?php foreach($types_besoins as $type): ?>
                    <option value="<?php echo $type['id']; ?>" data-categorie="<?php echo $type['categorie_id']; ?>" data-unite="<?php echo $type['unite']; ?>">
                        <?php echo $type['nom'] . ' (' . $type['unite'] . ')'; ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="quantite" class="form-label">Quantité</label>
                <input type="number" class="form-control" id="quantite" name="quantite" min="1" required>
                <small class="form-text text-muted" id="unite_text"></small>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-success">Enregistrer et distribuer proportionnellement</button>
                <a href="/dons" class="btn btn-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>

<script src="/js/dons-create.js"></script>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
