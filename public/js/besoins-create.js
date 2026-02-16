document.addEventListener('DOMContentLoaded', function() {
    const categorieSelect = document.getElementById('categorie_id');
    const typeBesoinSelect = document.getElementById('type_besoin_id');
    const quantiteInput = document.getElementById('quantite_demandee');
    const uniteText = document.getElementById('unite_text');
    const valeurDisplay = document.getElementById('valeur_display');
    const valeurTotale = document.getElementById('valeur_totale');

    if (!categorieSelect || !typeBesoinSelect) {
        return;
    }

    categorieSelect.addEventListener('change', function() {
        const selectedCategorie = this.value;
        const options = typeBesoinSelect.querySelectorAll('option');

        options.forEach(function(option) {
            if (option.value === '') {
                option.style.display = 'block';
            } else {
                const categorie = option.getAttribute('data-categorie');
                option.style.display = categorie === selectedCategorie ? 'block' : 'none';
            }
        });

        typeBesoinSelect.value = '';
        uniteText.textContent = '';
        valeurDisplay.style.display = 'none';
    });

    typeBesoinSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const unite = selectedOption.getAttribute('data-unite');
        uniteText.textContent = 'Unité: ' + unite;
        calculateValeur();
    });

    quantiteInput.addEventListener('input', calculateValeur);

    function calculateValeur() {
        const selectedOption = typeBesoinSelect.options[typeBesoinSelect.selectedIndex];
        const prix = parseFloat(selectedOption.getAttribute('data-prix')) || 0;
        const quantite = parseFloat(quantiteInput.value) || 0;

        if (prix > 0 && quantite > 0) {
            const total = prix * quantite;
            valeurTotale.textContent = new Intl.NumberFormat('fr-FR').format(total) + ' Ar';
            valeurDisplay.style.display = 'block';
        } else {
            valeurDisplay.style.display = 'none';
        }
    }
});
