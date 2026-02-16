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
