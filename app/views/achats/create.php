<?php require_once __DIR__ . '/../layout/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>🛒 Nouvel achat</h1>
    <a href="/achats" class="btn btn-secondary">← Retour à la liste</a>
</div>

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
        <strong>⚠️ Erreur !</strong> <?php echo $error; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if ($success): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <strong>✅</strong> <?php echo $success; ?>
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


<div class="card mb-4">
    <div class="card-header bg-primary text-white">
        <h4 class="mb-0">Besoins restants (Nature & Matériaux)</h4>
    </div>
    <div class="card-body">
        
        <div class="row mb-3">
            <div class="col-md-4">
                <label for="filtre_ville" class="form-label fw-bold">Filtrer par ville</label>
                <select id="filtre_ville" class="form-select">
                    <option value="">— Toutes les villes —</option>
                    <?php foreach ($villes as $ville): ?>
                    <option value="<?php echo $ville['nom']; ?>">
                        <?php echo $ville['nom'] . ' (' . $ville['region_nom'] . ')'; ?>
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
                        <td><?php echo $besoin['ville_nom']; ?></td>
                        <td><?php echo $besoin['type_besoin_nom']; ?></td>
                        <td>
                            <span class="badge bg-<?php echo $besoin['categorie_nom'] === 'en Nature' ? 'success' : 'warning'; ?>">
                                <?php echo $besoin['categorie_nom']; ?>
                            </span>
                        </td>
                        <td><?php echo $qte_restante . ' ' . $besoin['unite']; ?></td>
                        <td><?php echo number_format($besoin['prix_unitaire'], 0, ',', ' '); ?> Ar</td>
                        <td><strong><?php echo number_format($cout_total, 0, ',', ' '); ?> Ar</strong></td>
                        <td>
                            <button type="button" class="btn btn-sm btn-primary btn-acheter"
                                data-besoin-id="<?php echo $besoin['id']; ?>"
                                data-ville="<?php echo $besoin['ville_nom']; ?>"
                                data-type="<?php echo $besoin['type_besoin_nom']; ?>"
                                data-unite="<?php echo $besoin['unite']; ?>"
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

<script src="/js/achats-create.js"></script>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
