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

        // If category "en Argent" (id 3) is selected, hide the type select
        // and prefill the first matching type id so form submission stays valid
        if (selectedCategorie === '3') {
            let firstMatch = null;
            options.forEach(function(option) {
                const categorie = option.getAttribute('data-categorie');
                if (option.value !== '' && categorie === selectedCategorie && firstMatch === null) {
                    firstMatch = option;
                }
                // hide all options in the UI
                option.style.display = 'none';
            });

            if (firstMatch) {
                typeBesoinSelect.value = firstMatch.value;
            } else {
                typeBesoinSelect.value = '';
            }
            typeBesoinSelect.style.display = 'none';
            typeBesoinSelect.disabled = false; // keep enabled to submit value
            uniteText.textContent = '';
            valeurDisplay.style.display = 'none';
            return;
        }

        // Otherwise show only matching types
        options.forEach(function(option) {
            if (option.value === '') {
                option.style.display = 'block';
            } else {
                const categorie = option.getAttribute('data-categorie');
                option.style.display = categorie === selectedCategorie ? 'block' : 'none';
            }
        });

        typeBesoinSelect.value = '';
        typeBesoinSelect.style.display = 'block';
        typeBesoinSelect.disabled = false;
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
