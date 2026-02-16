<?php
require_once __DIR__ . '/../models/Besoin.php';
require_once __DIR__ . '/../models/Don.php';
require_once __DIR__ . '/../models/Achat.php';
require_once __DIR__ . '/../models/Attribution.php';

class RecapitulationController {
    private $db;

    public function __construct() {
        $config = require __DIR__ . '/../config/config.php';
        $dbConfig = $config['database'];

        try {
            $this->db = new PDO(
                "mysql:host=" . $dbConfig['host'] . ";dbname=" . $dbConfig['dbname'],
                $dbConfig['user'],
                $dbConfig['password']
            );
            $this->db->exec("set names utf8");
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $exception) {
            die("Erreur de connexion: " . $exception->getMessage());
        }
    }

    /**
     * Page principale récapitulation
     */
    public function index() {
        $data = $this->getRecapData();
        require_once __DIR__ . '/../views/recapitulation/index.php';
    }

    /**
     * Endpoint JSON pour actualisation Ajax
     */
    public function api() {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($this->getRecapData());
        exit();
    }

    /**
     * Calcule toutes les données de récapitulation
     */
    private function getRecapData() {
        // Besoins totaux en montant : Σ(quantite_demandee × prix_unitaire)
        $stmt = $this->db->prepare("
            SELECT COALESCE(SUM(b.quantite_demandee * tb.prix_unitaire), 0) as montant
            FROM bngrc_besoin b
            LEFT JOIN bngrc_type_besoin tb ON b.type_besoin_id = tb.id
        ");
        $stmt->execute();
        $besoins_totaux = (float) $stmt->fetch(PDO::FETCH_ASSOC)['montant'];

        // Besoins satisfaits en montant : Σ(quantite_satisfaite × prix_unitaire)
        $stmt = $this->db->prepare("
            SELECT COALESCE(SUM(b.quantite_satisfaite * tb.prix_unitaire), 0) as montant
            FROM bngrc_besoin b
            LEFT JOIN bngrc_type_besoin tb ON b.type_besoin_id = tb.id
        ");
        $stmt->execute();
        $besoins_satisfaits = (float) $stmt->fetch(PDO::FETCH_ASSOC)['montant'];

        // Besoins restants = totaux - satisfaits
        $besoins_restants = $besoins_totaux - $besoins_satisfaits;

        // Pourcentage de satisfaction
        $pourcentage = $besoins_totaux > 0 ? round(($besoins_satisfaits / $besoins_totaux) * 100, 1) : 0;

        // Total dons reçus en montant
        $stmt = $this->db->prepare("
            SELECT COALESCE(SUM(d.quantite * tb.prix_unitaire), 0) as montant
            FROM bngrc_don d
            LEFT JOIN bngrc_type_besoin tb ON d.type_besoin_id = tb.id
        ");
        $stmt->execute();
        $dons_totaux = (float) $stmt->fetch(PDO::FETCH_ASSOC)['montant'];

        // Total achats effectués
        $stmt = $this->db->prepare("
            SELECT COALESCE(SUM(montant_total), 0) as montant
            FROM bngrc_achat
        ");
        $stmt->execute();
        $achats_totaux = (float) $stmt->fetch(PDO::FETCH_ASSOC)['montant'];

        // Solde argent disponible
        $stmt = $this->db->prepare("
            SELECT COALESCE(SUM(d.quantite_restante * tb.prix_unitaire), 0) as montant
            FROM bngrc_don d
            LEFT JOIN bngrc_type_besoin tb ON d.type_besoin_id = tb.id
            LEFT JOIN bngrc_categorie_besoin cb ON tb.categorie_id = cb.id
            WHERE cb.id = 3 AND d.quantite_restante > 0
        ");
        $stmt->execute();
        $solde_argent = (float) $stmt->fetch(PDO::FETCH_ASSOC)['montant'];

        // Détail par ville
        $stmt = $this->db->prepare("
            SELECT v.nom as ville_nom,
                   COALESCE(SUM(b.quantite_demandee * tb.prix_unitaire), 0) as besoins_totaux,
                   COALESCE(SUM(b.quantite_satisfaite * tb.prix_unitaire), 0) as besoins_satisfaits,
                   COALESCE(SUM((b.quantite_demandee - b.quantite_satisfaite) * tb.prix_unitaire), 0) as besoins_restants
            FROM bngrc_besoin b
            LEFT JOIN bngrc_ville v ON b.ville_id = v.id
            LEFT JOIN bngrc_type_besoin tb ON b.type_besoin_id = tb.id
            GROUP BY v.id, v.nom
            ORDER BY besoins_restants DESC
        ");
        $stmt->execute();
        $detail_villes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Détail par catégorie
        $stmt = $this->db->prepare("
            SELECT cb.nom as categorie_nom,
                   COALESCE(SUM(b.quantite_demandee * tb.prix_unitaire), 0) as besoins_totaux,
                   COALESCE(SUM(b.quantite_satisfaite * tb.prix_unitaire), 0) as besoins_satisfaits,
                   COALESCE(SUM((b.quantite_demandee - b.quantite_satisfaite) * tb.prix_unitaire), 0) as besoins_restants
            FROM bngrc_besoin b
            LEFT JOIN bngrc_type_besoin tb ON b.type_besoin_id = tb.id
            LEFT JOIN bngrc_categorie_besoin cb ON tb.categorie_id = cb.id
            GROUP BY cb.id, cb.nom
            ORDER BY cb.id
        ");
        $stmt->execute();
        $detail_categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'besoins_totaux' => $besoins_totaux,
            'besoins_satisfaits' => $besoins_satisfaits,
            'besoins_restants' => $besoins_restants,
            'pourcentage' => $pourcentage,
            'dons_totaux' => $dons_totaux,
            'achats_totaux' => $achats_totaux,
            'solde_argent' => $solde_argent,
            'detail_villes' => $detail_villes,
            'detail_categories' => $detail_categories,
        ];
    }
}
?>
