document.addEventListener('DOMContentLoaded', function() {
    
    // Éléments du DOM
    const buyButtons = document.querySelectorAll('.btn-acheter');
    const formSection = document.getElementById('formAchat');
    const displayVille = document.getElementById('display_ville');
    const displayType = document.getElementById('display_type');
    const displayPrix = document.getElementById('display_prix');
    const displayFrais = document.getElementById('display_frais');
    const inputBesoinId = document.getElementById('input_besoin_id');
    const inputQuantite = document.getElementById('input_quantite');
    const infoMax = document.getElementById('info_max');
    const btnAnnuler = document.getElementById('btnAnnuler');
    const achatForm = document.getElementById('achatForm');

    // Variables globales pour le calcul courant
    let currentPrixUnitaire = 0;
    let currentFraisPourcent = 0;
    let maxQuantite = 0;

    // --- 1. Gestion du clic sur les boutons "Acheter" ---
    buyButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Récupération des données
            const besoinId = this.getAttribute('data-besoin-id');
            const ville = this.getAttribute('data-ville');
            const type = this.getAttribute('data-type');
            const unite = this.getAttribute('data-unite');
            const prix = parseFloat(this.getAttribute('data-prix'));
            const max = parseFloat(this.getAttribute('data-max'));
            const frais = parseFloat(this.getAttribute('data-frais'));

            // Mise à jour des variables
            currentPrixUnitaire = prix;
            currentFraisPourcent = frais;
            maxQuantite = max;

            // Remplissage du formulaire visuel
            displayVille.value = ville;
            displayType.value = type;
            displayPrix.value = new Intl.NumberFormat('fr-FR').format(prix) + ' Ar / ' + unite;
            displayFrais.value = frais + ' %';
            
            // Input caché et quantité
            inputBesoinId.value = besoinId;
            inputQuantite.value = ''; 
            inputQuantite.max = max;
            inputQuantite.placeholder = `Max: ${max}`;
            infoMax.textContent = `Quantité dispo : ${max} ${unite}`;
            infoMax.classList.remove('text-danger');
            inputQuantite.classList.remove('is-invalid');

            // Affichage
            formSection.style.display = 'block';
            formSection.scrollIntoView({ behavior: 'smooth' });
            document.getElementById('calcul_resume').style.display = 'none';
        });
    });

    // --- 2. Bouton Annuler ---
    if(btnAnnuler) {
        btnAnnuler.addEventListener('click', function() {
            formSection.style.display = 'none';
            achatForm.reset();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }

    // --- 3. Calculs en direct (Input Quantité) ---
    inputQuantite.addEventListener('input', function() {
        const quantite = parseFloat(this.value);
        const resumeDiv = document.getElementById('calcul_resume');
        const soldeFeedback = document.getElementById('solde_feedback');
        const btnConfirmer = document.getElementById('btnConfirmer');

        if (isNaN(quantite) || quantite <= 0) {
            resumeDiv.style.display = 'none';
            soldeFeedback.style.display = 'none';
            return;
        }

        // Validation max
        if (quantite > maxQuantite) {
            this.classList.add('is-invalid');
            infoMax.classList.add('text-danger');
            infoMax.textContent = `Erreur : Max ${maxQuantite}`;
            btnConfirmer.disabled = true;
        } else {
            this.classList.remove('is-invalid');
            infoMax.classList.remove('text-danger');
            btnConfirmer.disabled = false;
        }

        // Calculs
        const sousTotal = quantite * currentPrixUnitaire;
        const montantFrais = sousTotal * (currentFraisPourcent / 100);
        const totalTTC = sousTotal + montantFrais;

        // Affichage
        document.getElementById('calc_ht').textContent = new Intl.NumberFormat('fr-FR').format(sousTotal) + ' Ar';
        document.getElementById('calc_frais_pct').textContent = currentFraisPourcent;
        document.getElementById('calc_frais').textContent = new Intl.NumberFormat('fr-FR').format(montantFrais) + ' Ar';
        document.getElementById('calc_total').textContent = new Intl.NumberFormat('fr-FR').format(totalTTC) + ' Ar';
        resumeDiv.style.display = 'block';

        // Vérification Solde
        const soldeDispo = window.SOLDE_ARGENT || 0;
        soldeFeedback.style.display = 'block';
        
        if (totalTTC > soldeDispo) {
            soldeFeedback.innerHTML = `<div class="alert alert-danger">Manque <b>${new Intl.NumberFormat('fr-FR').format(totalTTC - soldeDispo)} Ar</b></div>`;
            btnConfirmer.disabled = true;
        } else {
            soldeFeedback.innerHTML = `<div class="alert alert-info">Reste après achat: <b>${new Intl.NumberFormat('fr-FR').format(soldeDispo - totalTTC)} Ar</b></div>`;
            if (quantite <= maxQuantite) btnConfirmer.disabled = false;
        }
    });

    // --- 4. SOUMISSION AJAX (Le point clé) ---
    achatForm.addEventListener('submit', function(e) {
        e.preventDefault(); // On bloque le rechargement de la page

        const btnConfirmer = document.getElementById('btnConfirmer');
        const originalText = btnConfirmer.innerHTML;
        
        // UI chargement
        btnConfirmer.disabled = true;
        btnConfirmer.textContent = "Traitement en cours...";

        const formData = new FormData(this);

        fetch('/achats/store', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // 1. Cacher le formulaire
                formSection.style.display = 'none';
                achatForm.reset();

                // 2. Mettre à jour le bouton dans le tableau
                const besoinId = formData.get('besoin_id');
                const btnAcheter = document.querySelector(`.btn-acheter[data-besoin-id="${besoinId}"]`);
                
                if (btnAcheter) {
                    btnAcheter.classList.remove('btn-primary');
                    btnAcheter.classList.add('btn-success'); // Vert
                    btnAcheter.innerHTML = '✅ Achat OK';
                    btnAcheter.disabled = true; // Désactivé
                }

                // 3. Mettre à jour le solde global JS et HTML
                if (data.nouveau_solde !== undefined) {
                    window.SOLDE_ARGENT = parseFloat(data.nouveau_solde);
                    // Mise à jour de l'affichage en haut de page si présent
                    const soldeDisplay = document.querySelector('.stat-number');
                    if(soldeDisplay) {
                        soldeDisplay.textContent = new Intl.NumberFormat('fr-FR').format(window.SOLDE_ARGENT) + ' Ar';
                    }
                }

                alert("Succès : " + data.message);
                window.scrollTo({ top: 0, behavior: 'smooth' });

            } else {
                alert("Erreur : " + data.error);
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            alert("Une erreur technique est survenue.");
        })
        .finally(() => {
            // Remettre le bouton normal
            btnConfirmer.disabled = false;
            btnConfirmer.innerHTML = originalText;
        });
    });
});