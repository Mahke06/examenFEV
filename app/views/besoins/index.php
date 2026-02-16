<?php require_once __DIR__ . '/../layout/header.php'; ?>

<style>
    /* Force le tableau à garder sa structure peu importe la taille du contenu */
    .table-simple {
        table-layout: fixed;
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 1rem;
    }
    
    .table-simple th, .table-simple td {
        padding: 8px;
        font-size: 0.85rem;
        overflow: hidden;
        text-overflow: ellipsis; /* Ajoute "..." si le texte dépasse */
        white-space: nowrap;     /* Empêche le retour à la ligne */
    }

    /* Définition manuelle des largeurs pour garantir l'alignement */
    .w-15 { width: 15%; }
    .w-10 { width: 10%; }
    .w-action { width: 80px; }
</style>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <h1>Liste des besoins</h1>
    <a href="/besoins/create" class="btn btn-primary">Ajouter un besoin</a>
</div>

<div class="table-responsive">
    <table border="1" class="table-simple">
        <thead style="background-color: #f8f9fa;">
            <tr>
                <th class="w-15">Ville</th>
                <th class="w-15">Type de besoin</th>
                <th class="w-15">Catégorie</th>
                <th class="w-10">Qté Dem.</th>
                <th class="w-10">Qté Sat.</th>
                <th class="w-10">Reste</th>
                <th class="w-10">P.U.</th>
                <th class="w-10">Total</th>
                <th class="w-15">Date</th>
                <th class="w-action">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($besoins as $besoin): ?>
            <tr>
                <td title="<?php echo $besoin['ville_nom']; ?>"><?php echo $besoin['ville_nom']; ?></td>
                <td><?php echo !empty($besoin['type_besoin_nom']) ? $besoin['type_besoin_nom'] : 'N/A'; ?></td>
                <td><?php echo $besoin['categorie_nom']; ?></td>
                <td><?php echo $besoin['quantite_demandee'] . ' ' . $besoin['unite']; ?></td>
                <td><?php echo $besoin['quantite_satisfaite'] . ' ' . $besoin['unite']; ?></td>
                <td>
                    <?php 
                    $reste = $besoin['quantite_demandee'] - $besoin['quantite_satisfaite'];
                    $color = $reste == 0 ? "green" : "red";
                    echo "<b style='color: $color;'>" . $reste . ' ' . $besoin['unite'] . "</b>"; 
                    ?>
                </td>
                <td align="right"><?php echo number_format($besoin['prix_unitaire'], 0, ',', ' '); ?></td>
                <td align="right"><b><?php echo number_format($besoin['quantite_demandee'] * $besoin['prix_unitaire'], 0, ',', ' '); ?></b></td>
                <td><?php echo date('d/m/Y H:i', strtotime($besoin['date_saisie'])); ?></td>
                <td align="center">
                    <form method="POST" action="/besoins/delete" onsubmit="return confirm('Supprimer ce besoin ?');">
                        <input type="hidden" name="id" value="<?php echo $besoin['id']; ?>">
                        <button type="submit" style="color: red; cursor: pointer;">Supprimer</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>