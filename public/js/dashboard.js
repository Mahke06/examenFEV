document.addEventListener('DOMContentLoaded', function() {
    const filtreVille = document.getElementById('filtre_ville_dashboard');
    if (!filtreVille) {
        return;
    }

    filtreVille.addEventListener('change', function() {
        const villeChoisie = this.value;
        document.querySelectorAll('.ville-card').forEach(function(card) {
            if (villeChoisie === '' || card.dataset.ville === villeChoisie) {
                card.style.display = '';
            } else {
                card.style.display = 'none';
            }
        });
    });
});
