<?php require_once __DIR__ . '/../layout/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>🛒 Nouvel achat</h1>
    <a href="/achats" class="btn btn-secondary">← Retour à la liste</a>
</div>

<!-- Solde disponible -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card stat-card bg-primary text-white">
            <div class="card-body">
                <h5>💰 Solde argent disponible</h5>
                <div class="stat-number"><?php echo number_format($solde_argent, 0, ',', ' '); ?> Ar</div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card stat-card bg-warning text-dark">
            <div class="card-body">
                <h5>📊 Frais d'achat appliqué</h5>
                <div class="stat-number"><?php echo $frais_achat; ?>%</div>
            </div>
        </div>
    </div>
</div>

<?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <strong>⚠️ Erreur !</strong> <?php echo htmlspecialchars($error); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if ($success): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <strong>✅</strong> <?php echo htmlspecialchars($success); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if ($solde_argent <= 0): ?>
    <div class="alert alert-warning">
        <strong>⚠️ Aucun don en argent disponible.</strong> Pour effectuer des achats, vous devez d'abord 
        <a href="/dons/create" class="alert-link">ajouter un don en Argent</a> (catégorie « en Argent », type « Espèces »).
    </div>
<?php endif; ?>

<?php if (empty($besoins_restants)): ?>
    <div class="alert alert-info">
        <strong>ℹ️</strong> Aucun besoin en nature ou matériaux restant à satisfaire.
    </div>
<?php else: ?>

<!-- Tableau des besoins restants -->
<div class="card mb-4">
    <div class="card-header bg-primary text-white">
        <h4 class="mb-0">Besoins restants (Nature & Matériaux)</h4>
    </div>
    <div class="card-body">
        <!-- Filtre par ville -->
        <div class="row mb-3">
            <div class="col-md-4">
                <label for="filtre_ville" class="form-label fw-bold">Filtrer par ville</label>
                <select id="filtre_ville" class="form-select">
                    <option value="">— Toutes les villes —</option>
                    <?php foreach ($villes as $ville): ?>
                    <option value="<?php echo htmlspecialchars($ville['nom']); ?>">
                        <?php echo htmlspecialchars($ville['nom']) . ' (' . htmlspecialchars($ville['region_nom']) . ')'; ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-striped table-bordered table-hover" id="tableBesoins">
                <thead class="table-dark">
                    <tr>
                        <th>Ville</th>
                        <th>Type</th>
                        <th>Catégorie</th>
                        <th>Qté restante</th>
                        <th>Prix unit.</th>
                        <th>Coût estimé (+ <?php echo $frais_achat; ?>%)</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($besoins_restants as $besoin): ?>
                    <?php 
                        $qte_restante = $besoin['quantite_restante'];
                        $cout_ht = $qte_restante * $besoin['prix_unitaire'];
                        $cout_total = $cout_ht * (1 + $frais_achat / 100);
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($besoin['ville_nom']); ?></td>
                        <td><?php echo htmlspecialchars($besoin['type_besoin_nom']); ?></td>
                        <td>
                            <span class="badge bg-<?php echo $besoin['categorie_nom'] === 'en Nature' ? 'success' : 'warning'; ?>">
                                <?php echo htmlspecialchars($besoin['categorie_nom']); ?>
                            </span>
                        </td>
                        <td><?php echo $qte_restante . ' ' . htmlspecialchars($besoin['unite']); ?></td>
                        <td><?php echo number_format($besoin['prix_unitaire'], 0, ',', ' '); ?> Ar</td>
                        <td><strong><?php echo number_format($cout_total, 0, ',', ' '); ?> Ar</strong></td>
                        <td>
                            <button type="button" class="btn btn-sm btn-primary btn-acheter"
                                data-besoin-id="<?php echo $besoin['id']; ?>"
                                data-ville="<?php echo htmlspecialchars($besoin['ville_nom']); ?>"
                                data-type="<?php echo htmlspecialchars($besoin['type_besoin_nom']); ?>"
                                data-unite="<?php echo htmlspecialchars($besoin['unite']); ?>"
                                data-prix="<?php echo $besoin['prix_unitaire']; ?>"
                                data-max="<?php echo $qte_restante; ?>"
                                data-frais="<?php echo $frais_achat; ?>">
                                🛒 Acheter
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Formulaire d'achat (modal-like section) -->
<div class="card mb-4" id="formAchat" style="display: none;">
    <div class="card-header bg-success text-white">
        <h4 class="mb-0">Formulaire d'achat</h4>
    </div>
    <div class="card-body">
        <form method="POST" action="/achats/store" id="achatForm">
            <input type="hidden" name="besoin_id" id="input_besoin_id">

            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Ville</label>
                    <input type="text" class="form-control" id="display_ville" readonly>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Type de besoin</label>
                    <input type="text" class="form-control" id="display_type" readonly>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-4">
                    <label class="form-label">Prix unitaire</label>
                    <input type="text" class="form-control" id="display_prix" readonly>
                </div>
                <div class="col-md-4">
                    <label for="quantite" class="form-label">Quantité à acheter <span class="text-danger">*</span></label>
                    <input type="number" name="quantite" id="input_quantite" class="form-control" 
                           min="1" required placeholder="Ex: 10">
                    <div class="form-text" id="info_max"></div>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Frais d'achat</label>
                    <input type="text" class="form-control" id="display_frais" readonly>
                </div>
            </div>

            <!-- Résumé du calcul -->
            <div class="card bg-light mb-3" id="calcul_resume" style="display: none;">
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-4">
                            <small class="text-muted">Sous-total HT</small>
                            <div class="fw-bold fs-5" id="calc_ht">0 Ar</div>
                        </div>
                        <div class="col-md-4">
                            <small class="text-muted">Frais (<span id="calc_frais_pct">0</span>%)</small>
                            <div class="fw-bold fs-5 text-warning" id="calc_frais">0 Ar</div>
                        </div>
                        <div class="col-md-4">
                            <small class="text-muted">Montant total TTC</small>
                            <div class="fw-bold fs-4 text-primary" id="calc_total">0 Ar</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-success">✅ Confirmer l'achat</button>
                <button type="button" class="btn btn-secondary" id="btnAnnuler">Annuler</button>
            </div>
        </form>
    </div>
</div>

<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const formAchat = document.getElementById('formAchat');
    const btnAnnuler = document.getElementById('btnAnnuler');
    const inputQte = document.getElementById('input_quantite');

    let currentPrix = 0;
    let currentFrais = 0;
    let currentMax = 0;

    // Clic sur "Acheter"
    document.querySelectorAll('.btn-acheter').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const besoinId = this.dataset.besoinId;
            const ville = this.dataset.ville;
            const type = this.dataset.type;
            const unite = this.dataset.unite;
            const prix = parseFloat(this.dataset.prix);
            const max = parseFloat(this.dataset.max);
            const frais = parseFloat(this.dataset.frais);

            currentPrix = prix;
            currentFrais = frais;
            currentMax = max;

            document.getElementById('input_besoin_id').value = besoinId;
            document.getElementById('display_ville').value = ville;
            document.getElementById('display_type').value = type + ' (' + unite + ')';
            document.getElementById('display_prix').value = prix.toLocaleString('fr-FR') + ' Ar/' + unite;
            document.getElementById('display_frais').value = frais + '%';
            document.getElementById('info_max').textContent = 'Maximum : ' + max + ' ' + unite;
            
            inputQte.value = '';
            inputQte.max = max;
            document.getElementById('calcul_resume').style.display = 'none';

            formAchat.style.display = 'block';
            formAchat.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    });

    // Calcul dynamique
    if (inputQte) {
        inputQte.addEventListener('input', function() {
            const qte = parseFloat(this.value) || 0;
            const resume = document.getElementById('calcul_resume');

            if (qte > 0) {
                const ht = qte * currentPrix;
                const fraisMontant = ht * (currentFrais / 100);
                const total = ht + fraisMontant;

                document.getElementById('calc_ht').textContent = ht.toLocaleString('fr-FR') + ' Ar';
                document.getElementById('calc_frais_pct').textContent = currentFrais;
                document.getElementById('calc_frais').textContent = '+ ' + fraisMontant.toLocaleString('fr-FR') + ' Ar';
                document.getElementById('calc_total').textContent = total.toLocaleString('fr-FR') + ' Ar';

                resume.style.display = 'block';

                // Alerte visuelle si dépasse le max
                if (qte > currentMax) {
                    this.classList.add('is-invalid');
                } else {
                    this.classList.remove('is-invalid');
                }
            } else {
                resume.style.display = 'none';
            }
        });
    }

    // Annuler
    if (btnAnnuler) {
        btnAnnuler.addEventListener('click', function() {
            formAchat.style.display = 'none';
        });
    }

    // Filtre par ville sur le tableau des besoins
    const filtreVille = document.getElementById('filtre_ville');
    if (filtreVille) {
        filtreVille.addEventListener('change', function() {
            const villeChoisie = this.value.toLowerCase();
            const rows = document.querySelectorAll('#tableBesoins tbody tr');
            rows.forEach(function(row) {
                const villeCell = row.querySelector('td:first-child');
                if (!villeChoisie || villeCell.textContent.trim().toLowerCase() === villeChoisie) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    }
});
</script>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
