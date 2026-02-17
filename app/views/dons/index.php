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
        font-size: 0.85rem;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        border: 1px solid #dee2e6;
    }

    .w-type { width: 15%; }
    .w-cat { width: 15%; }
    .w-qte { width: 12%; }
    .w-statut { width: 12%; }
    .w-date { width: 18%; }
    .w-action { width: 100px; }

    .badge-simple {
        padding: 2px 6px;
        font-weight: bold;
        font-size: 0.75rem;
        border-radius: 3px;
    }
</style>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <h1>Liste des dons</h1>
    <div class="d-flex gap-2">
        <form method="POST" action="/besoins/reinitialiser" onsubmit="return confirm('Êtes-vous sûr de vouloir réinitialiser toutes les données ? Tous les dons, attributions, achats et besoins seront supprimés et les besoins initiaux seront restaurés.');">
            <button type="submit" class="btn btn-warning">Réinitialiser les données</button>
        </form>
        <a href="/dons/create" class="btn btn-success">Ajouter un don par rapport au date</a>
        <a href="/dons/create" class="btn btn-success">Ajouter un don par rapport au plus petit</a>
        <a href="/dons/create" class="btn btn-success">Ajouter un don proportionnel</a>
    </div>
</div>

<div class="table-responsive">
    <table border="1" class="table-simple">
        <thead style="background-color: #212529; color: white;">
            <tr>
                <th class="w-type">Type de besoin</th>
                <th class="w-cat">Catégorie</th>
                <th class="w-qte">Quantité</th>
                <th class="w-qte">Qté restante</th>
                <th class="w-statut">Statut</th>
                <th class="w-date">Date de saisie</th>
                <th class="w-action">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($dons as $don): ?>
            <tr>
                <td title="<?php echo $don['type_besoin_nom']; ?>">
                    <b><?php echo !empty($don['type_besoin_nom']) ? $don['type_besoin_nom'] : 'N/A'; ?></b>
                </td>
                <td><?php echo $don['categorie_nom']; ?></td>
                <td><?php echo $don['quantite'] . ' ' . $don['unite']; ?></td>
                <td>
                    <?php 
                    $reste = $don['quantite_restante'];
                    $color = $reste == 0 ? "gray" : "#198754";
                    echo "<span style='color: $color; font-weight: bold;'>" . $reste . ' ' . $don['unite'] . "</span>"; 
                    ?>
                </td>
                <td align="center">
                    <?php 
                    $status_text = !empty($don['statut']) ? $don['statut'] : 'en attente';
                    $bg_color = ($status_text == 'distribué') ? '#198754' : (($status_text == 'partiel') ? '#ffc107' : '#6c757d');
                    $text_color = ($status_text == 'partiel') ? 'black' : 'white';
                    ?>
                    <span class="badge-simple" style="background-color: <?php echo $bg_color; ?>; color: <?php echo $text_color; ?>;">
                        <?php echo $status_text; ?>
                    </span>
                </td>
                <td class="small text-muted"><?php echo date('d/m/Y H:i', strtotime($don['date_saisie'])); ?></td>
                <td align="center">
                    <form method="POST" action="/dons/delete" onsubmit="return confirm('Supprimer ce don ? Les attributions liées seront annulées.');">
                        <input type="hidden" name="id" value="<?php echo $don['id']; ?>">
                        <button type="submit" style="color: #dc3545; background: none; border: none; cursor: pointer; font-size: 0.8rem; text-decoration: underline;">
                            Supprimer
                        </button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>