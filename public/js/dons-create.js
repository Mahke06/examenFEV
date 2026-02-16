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
    });

    typeBesoinSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const unite = selectedOption.getAttribute('data-unite');
        uniteText.textContent = 'Unité: ' + unite;
    });
});
