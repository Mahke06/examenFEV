<?php require_once __DIR__ . '/../layout/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Récapitulatif des besoins</h1>
    <button id="refreshBtn" class="btn btn-primary">Actualiser</button>
</div>

<div id="recapContent">
    <div class="card">
        <div class="card-body">
            <p><strong>Besoins totaux (montant):</strong> <?php echo number_format($total_demande,0,',',' ') . ' Ar'; ?></p>
            <p><strong>Besoins satisfaits (montant):</strong> <?php echo number_format($total_satisfait,0,',',' ') . ' Ar'; ?></p>
            <p><strong>Besoins restants (montant):</strong> <?php echo number_format($total_restant,0,',',' ') . ' Ar'; ?></p>
        </div>
    </div>
</div>

<script>
document.getElementById('refreshBtn').addEventListener('click', function(){
    fetch('/achats/recap')
        .then(r => r.text())
        .then(html => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const newCard = doc.querySelector('#recapContent');
            if(newCard) {
                document.getElementById('recapContent').innerHTML = newCard.innerHTML;
            }
        });
});
</script>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
