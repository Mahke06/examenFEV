<?php require_once __DIR__ . '/../layout/header.php'; ?>

<h1 class="mb-4">Ajouter un don (par rapport au plus petit besoin)</h1>

<div class="card">
    <div class="card-body">
        <div class="alert alert-info">
            <strong>Information:</strong> Le don sera automatiquement distribué en priorisant les besoins avec la <strong>plus petite quantité restante</strong>. Le besoin le plus petit sera satisfait en premier, puis le suivant, et ainsi de suite jusqu'à épuisement du don.
        </div>

        <form method="POST" action="/dons/store-plus-petit">
            <div class="mb-3">
                <label for="categorie_id" class="form-label">Catégorie</label>
                <select class="form-select" id="categorie_id" name="categorie_id" required>
                    <option value="">Sélectionner une catégorie</option>
                    <?php foreach($categories as $categorie): ?>
                    <option value="<?php echo $categorie['id']; ?>">
                        <?php echo $categorie['nom']; ?>
                    </option>
                    <?php endforeach; ?>
                </select>
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
                <button type="submit" class="btn btn-success">Enregistrer et distribuer (plus petit en premier)</button>
                <a href="/dons" class="btn btn-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>

<script src="/js/dons-create.js"></script>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
