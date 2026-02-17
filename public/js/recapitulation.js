function formatMontant(val) {
    return Math.round(val).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ' ') + ' Ar';
}

function formatMontantSansAr(val) {
    return Math.round(val).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
}

function getBadgeClass(pct) {
    if (pct >= 75) return 'success';
    if (pct >= 40) return 'warning';
    return 'danger';
}

function getCategoryBadge(nom) {
    if (nom === 'en Nature') return 'success';
    if (nom === 'en Materiaux') return 'warning';
    return 'info';
}

function actualiserDonnees() {
    const btn = document.getElementById('btn-actualiser');
    btn.disabled = true;
    btn.innerHTML = '⏳ Chargement...';

    fetch('/recapitulation/api')
        .then(function(response) { return response.json(); })
        .then(function(data) {
            // KPI principaux
            document.getElementById('kpi-besoins-totaux').textContent = formatMontant(data.besoins_totaux);
            document.getElementById('kpi-besoins-satisfaits').textContent = formatMontant(data.besoins_satisfaits);
            document.getElementById('kpi-besoins-restants').textContent = formatMontant(data.besoins_restants);

            // Pourcentage
            var pct = data.pourcentage;
            var pctEl = document.getElementById('kpi-pourcentage');
            pctEl.textContent = pct + '%';
            pctEl.className = 'badge bg-' + getBadgeClass(pct) + ' fs-6';

            // Barre de progression
            var bar = document.getElementById('progress-bar');
            bar.style.width = pct + '%';
            bar.textContent = pct + '%';
            bar.setAttribute('aria-valuenow', pct);
            bar.className = 'progress-bar bg-' + getBadgeClass(pct) + ' progress-bar-striped progress-bar-animated';

            // KPI secondaires
            document.getElementById('kpi-dons-totaux').textContent = formatMontant(data.dons_totaux);
            document.getElementById('kpi-achats-totaux').textContent = formatMontant(data.achats_totaux);
            document.getElementById('kpi-solde-argent').textContent = formatMontant(data.solde_argent);

            // Tableau villes
            var villesHtml = '';
            data.detail_villes.forEach(function(v) {
                var vpct = v.besoins_totaux > 0 ? Math.round((v.besoins_satisfaits / v.besoins_totaux) * 1000) / 10 : 0;
                villesHtml += '<tr>' +
                    '<td class="fw-bold">' + v.ville_nom + '</td>' +
                    '<td class="text-end">' + formatMontantSansAr(v.besoins_totaux) + '</td>' +
                    '<td class="text-end text-success">' + formatMontantSansAr(v.besoins_satisfaits) + '</td>' +
                    '<td class="text-end text-danger">' + formatMontantSansAr(v.besoins_restants) + '</td>' +
                    '<td><div class="progress" style="height:18px;"><div class="progress-bar bg-' + getBadgeClass(vpct) + '" style="width:' + vpct + '%">' + vpct + '%</div></div></td>' +
                    '</tr>';
            });
            document.getElementById('table-villes').innerHTML = villesHtml;

            // Tableau catégories
            var catHtml = '';
            data.detail_categories.forEach(function(c) {
                var cpct = c.besoins_totaux > 0 ? Math.round((c.besoins_satisfaits / c.besoins_totaux) * 1000) / 10 : 0;
                catHtml += '<tr>' +
                    '<td><span class="badge bg-' + getCategoryBadge(c.categorie_nom) + '">' + c.categorie_nom + '</span></td>' +
                    '<td class="text-end">' + formatMontantSansAr(c.besoins_totaux) + '</td>' +
                    '<td class="text-end text-success">' + formatMontantSansAr(c.besoins_satisfaits) + '</td>' +
                    '<td class="text-end text-danger">' + formatMontantSansAr(c.besoins_restants) + '</td>' +
                    '<td><div class="progress" style="height:18px;"><div class="progress-bar bg-' + getBadgeClass(cpct) + '" style="width:' + cpct + '%">' + cpct + '%</div></div></td>' +
                    '</tr>';
            });
            document.getElementById('table-categories').innerHTML = catHtml;

            btn.disabled = false;
            btn.innerHTML = '🔄 Actualiser';
        })
        .catch(function(err) {
            console.error('Erreur:', err);
            btn.disabled = false;
            btn.innerHTML = '🔄 Actualiser';
            alert('Erreur lors de l\'actualisation des données.');
        });
}
