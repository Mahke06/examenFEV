document.addEventListener('DOMContentLoaded', function() {
    const categorieSelect = document.getElementById('categorie_id');
    const typeBesoinSelect = document.getElementById('type_besoin_id');
    const uniteText = document.getElementById('unite_text');

    if (!categorieSelect || !typeBesoinSelect) {
        return;
    }

    categorieSelect.addEventListener('change', function() {
        const selectedCategorie = this.value;
        const options = typeBesoinSelect.querySelectorAll('option');

        // If category "en Argent" (id 3) is selected, hide the select and
        // prefill a valid type id (the first type with categorie_id == 3)
        if (selectedCategorie === '3') {
            let firstMatch = null;
            options.forEach(function(option) {
                const categorie = option.getAttribute('data-categorie');
                if (option.value !== '' && categorie === selectedCategorie && firstMatch === null) {
                    firstMatch = option;
                }
                // hide all options in the dropdown UI when money is chosen
                option.style.display = 'none';
            });

            if (firstMatch) {
                typeBesoinSelect.value = firstMatch.value;
            } else {
                typeBesoinSelect.value = '';
            }
            // hide the select visually but keep it enabled so value is submitted
            typeBesoinSelect.style.display = 'none';
            typeBesoinSelect.disabled = false;
            uniteText.textContent = '';
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
    });

    typeBesoinSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const unite = selectedOption.getAttribute('data-unite');
        uniteText.textContent = 'Unité: ' + unite;
    });
});
