<?php require_once __DIR__ . '/../layout/header.php'; ?>

<h1 class="mb-4">Ajouter un don (proportionnel par ville)</h1>

<div class="card">
    <div class="card-body">
        <div class="alert alert-info">
            <strong>Information:</strong> Le don sera distribué <strong>proportionnellement</strong> entre toutes les villes ayant des besoins pour ce type.
            <br><br>
            <strong>Exemple :</strong> Si un don de 300 matelas est ajouté et que :
            <ul class="mb-0 mt-1">
                <li>Ville A a besoin de 100 matelas (1/3 du total) → reçoit 100</li>
                <li>Ville B a besoin de 200 matelas (2/3 du total) → reçoit 200</li>
            </ul>
            Chaque ville reçoit une part proportionnelle à son besoin restant.
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
