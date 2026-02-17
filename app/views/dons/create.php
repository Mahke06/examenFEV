<?php require_once __DIR__ . '/../layout/header.php'; ?>

<h1 class="mb-4">Ajouter un don</h1>

<div class="card">
    <div class="card-body">
        <div class="alert alert-info">
            <strong>Information:</strong> Le don sera automatiquement distribué aux villes ayant des besoins, par ordre chronologique de saisie des besoins.
        </div>

        <form method="POST" action="/dons/store">
            <div class="mb-3">
                <label for="ville_id" class="form-label">Ville</label>
                <select class="form-select" id="ville_id" name="ville_id">
                    <option value="">— Toutes les villes —</option>
                    <?php foreach($villes as $ville): ?>
                    <option value="<?php echo $ville['id']; ?>">
                        <?php echo $ville['nom'] . ' (' . $ville['region_nom'] . ')'; ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                <small class="form-text text-muted">Optionnel : filtrer la distribution vers une ville spécifique</small>
            </div>


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
                <button type="submit" class="btn btn-success">Enregistrer et distribuer</button>
                <a href="/dons" class="btn btn-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>

<script src="/js/dons-create.js"></script>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>