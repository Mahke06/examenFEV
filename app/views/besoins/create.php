<?php require_once __DIR__ . '/../layout/header.php'; ?>

<h1 class="mb-4">Ajouter un besoin</h1>

<div class="card">
    <div class="card-body">
        <form method="POST" action="/besoins/store">
            <div class="mb-3">
                <label for="ville_id" class="form-label">Ville</label>
                <select class="form-select" id="ville_id" name="ville_id" required>
                    <option value="">Sélectionner une ville</option>
                    <?php foreach($villes as $ville): ?>
                    <option value="<?php echo $ville['id']; ?>">
                        <?php echo $ville['nom'] . ' (' . $ville['region_nom'] . ')'; ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

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
                    <option value="<?php echo $type['id']; ?>" data-categorie="<?php echo $type['categorie_id']; ?>" data-unite="<?php echo $type['unite']; ?>" data-prix="<?php echo $type['prix_unitaire']; ?>">
                        <?php echo $type['nom'] . ' (' . number_format($type['prix_unitaire'], 0, ',', ' ') . ' Ar/' . $type['unite'] . ')'; ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="quantite_demandee" class="form-label">Quantité demandée</label>
                <input type="number" class="form-control" id="quantite_demandee" name="quantite_demandee" min="1" required>
                <small class="form-text text-muted" id="unite_text"></small>
            </div>

            <div class="mb-3" id="valeur_display" style="display:none;">
                <label class="form-label">Valeur totale estimée</label>
                <div class="alert alert-info" id="valeur_totale">0 Ar</div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">Enregistrer</button>
                <a href="/besoins" class="btn btn-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>

<script src="/js/besoins-create.js"></script>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>