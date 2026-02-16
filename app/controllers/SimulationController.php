<?php
require_once __DIR__ . '/../models/Don.php';
require_once __DIR__ . '/../models/Besoin.php';
require_once __DIR__ . '/../models/Ville.php';
require_once __DIR__ . '/../models/TypeBesoin.php';
require_once __DIR__ . '/../models/CategorieBesoin.php';
require_once __DIR__ . '/../models/Attribution.php';

class SimulationController {
    private $db;
    private $don;
    private $besoin;
    private $ville;
    private $typeBesoin;
    private $categorie;
    private $attribution;

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

        $this->don = new Don($this->db);
        $this->besoin = new Besoin($this->db);
        $this->ville = new Ville($this->db);
        $this->typeBesoin = new TypeBesoin($this->db);
        $this->categorie = new CategorieBesoin($this->db);
        $this->attribution = new Attribution($this->db);
    }

    /**
     * Page principale de simulation
     */
    public function index() {
        // Dons disponibles (quantite_restante > 0)
        $dons_disponibles = $this->getDonsDisponibles();
        $besoins_non_satisfaits = $this->getBesoinsNonSatisfaits();
        $simulation = null;
        $success = isset($_GET['success']) ? urldecode($_GET['success']) : null;

        require_once __DIR__ . '/../views/simulation/index.php';
    }

    /**
     * Simuler la distribution — aperçu sans enregistrer
     */
    public function simuler() {
        $dons_disponibles = $this->getDonsDisponibles();
        $besoins_non_satisfaits = $this->getBesoinsNonSatisfaits();

        // Calculer la simulation (sans toucher à la BDD)
        $simulation = $this->calculerDistribution($dons_disponibles, $besoins_non_satisfaits);

        require_once __DIR__ . '/../views/simulation/index.php';
    }

    /**
     * Valider et exécuter la distribution réelle
     */
    public function valider() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: /simulation");
            exit();
        }

        $dons_disponibles = $this->getDonsDisponibles();
        $besoins_non_satisfaits = $this->getBesoinsNonSatisfaits();

        $simulation = $this->calculerDistribution($dons_disponibles, $besoins_non_satisfaits);

        if (empty($simulation['attributions'])) {
            header("Location: /simulation?error=" . urlencode("Aucune distribution possible."));
            exit();
        }

        try {
            $this->db->beginTransaction();

            foreach ($simulation['attributions'] as $attr) {
                // Créer l'attribution
                $this->attribution->don_id = $attr['don_id'];
                $this->attribution->besoin_id = $attr['besoin_id'];
                $this->attribution->ville_id = $attr['ville_id'];
                $this->attribution->quantite_attribuee = $attr['quantite'];
                $this->attribution->create();

                // Mettre à jour la quantité satisfaite du besoin
                $this->besoin->updateQuantiteSatisfaite($attr['besoin_id'], $attr['quantite']);

                // Mettre à jour la quantité restante du don
                $this->don->updateQuantiteRestante($attr['don_id'], $attr['quantite']);
            }

            // Mettre à jour les statuts des dons
            foreach ($simulation['dons_apres'] as $don_info) {
                if ($don_info['restant_apres'] <= 0) {
                    $this->don->updateStatut($don_info['don_id'], 'distribué');
                } elseif ($don_info['restant_apres'] < $don_info['restant_avant']) {
                    $this->don->updateStatut($don_info['don_id'], 'partiellement distribué');
                }
            }

            $this->db->commit();

            $nb = count($simulation['attributions']);
            $total_qte = array_sum(array_column($simulation['attributions'], 'quantite'));
            header("Location: /simulation?success=" . urlencode("Distribution validée ! {$nb} attribution(s) créée(s) pour un total de {$total_qte} unités."));
            exit();
        } catch (Exception $e) {
            $this->db->rollBack();
            header("Location: /simulation?error=" . urlencode("Erreur : " . $e->getMessage()));
            exit();
        }
    }

    /**
     * Algorithme de distribution : pour chaque don disponible, distribue aux besoins du même type
     * Retourne un tableau de simulation sans modifier la BDD
     */
    private function calculerDistribution($dons, $besoins) {
        $attributions = [];
        $dons_apres = [];
        $besoins_modifies = [];

        // Copie locale des quantités restantes des besoins pour la simulation
        $besoins_restants = [];
        foreach ($besoins as $b) {
            $besoins_restants[$b['id']] = $b['quantite_demandee'] - $b['quantite_satisfaite'];
        }

        foreach ($dons as $don) {
            $qte_don_restante = $don['quantite_restante'];
            $qte_initiale = $qte_don_restante;

            // Trouver les besoins du même type
            foreach ($besoins as $besoin) {
                if ($qte_don_restante <= 0) break;
                if ($besoin['type_besoin_id'] !== $don['type_besoin_id']) continue;

                $besoin_restant = $besoins_restants[$besoin['id']] ?? 0;
                if ($besoin_restant <= 0) continue;

                $qte_a_attribuer = min($qte_don_restante, $besoin_restant);

                $attributions[] = [
                    'don_id' => $don['id'],
                    'besoin_id' => $besoin['id'],
                    'ville_id' => $besoin['ville_id'],
                    'ville_nom' => $besoin['ville_nom'],
                    'type_besoin_nom' => $don['type_besoin_nom'],
                    'unite' => $don['unite'],
                    'categorie_nom' => $don['categorie_nom'],
                    'quantite' => $qte_a_attribuer,
                    'besoin_avant' => $besoins_restants[$besoin['id']],
                    'besoin_apres' => $besoins_restants[$besoin['id']] - $qte_a_attribuer,
                ];

                $besoins_restants[$besoin['id']] -= $qte_a_attribuer;
                $qte_don_restante -= $qte_a_attribuer;

                if (!isset($besoins_modifies[$besoin['id']])) {
                    $besoins_modifies[$besoin['id']] = $besoin;
                }
            }

            $dons_apres[] = [
                'don_id' => $don['id'],
                'type_besoin_nom' => $don['type_besoin_nom'],
                'unite' => $don['unite'],
                'categorie_nom' => $don['categorie_nom'],
                'restant_avant' => $qte_initiale,
                'distribue' => $qte_initiale - $qte_don_restante,
                'restant_apres' => $qte_don_restante,
            ];
        }

        return [
            'attributions' => $attributions,
            'dons_apres' => $dons_apres,
            'total_attributions' => count($attributions),
            'total_quantite' => array_sum(array_column($attributions, 'quantite')),
        ];
    }

    /**
     * Récupère tous les dons avec quantite_restante > 0
     */
    private function getDonsDisponibles() {
        $query = "SELECT d.*, tb.nom as type_besoin_nom, tb.unite, cb.nom as categorie_nom
                  FROM bngrc_don d
                  LEFT JOIN bngrc_type_besoin tb ON d.type_besoin_id = tb.id
                  LEFT JOIN bngrc_categorie_besoin cb ON tb.categorie_id = cb.id
                  WHERE d.quantite_restante > 0
                  ORDER BY d.date_saisie ASC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère tous les besoins non entièrement satisfaits
     */
    private function getBesoinsNonSatisfaits() {
        $query = "SELECT b.*, v.nom as ville_nom, tb.nom as type_besoin_nom,
                  tb.prix_unitaire, tb.unite, cb.nom as categorie_nom
                  FROM bngrc_besoin b
                  LEFT JOIN bngrc_ville v ON b.ville_id = v.id
                  LEFT JOIN bngrc_type_besoin tb ON b.type_besoin_id = tb.id
                  LEFT JOIN bngrc_categorie_besoin cb ON tb.categorie_id = cb.id
                  WHERE b.quantite_satisfaite < b.quantite_demandee
                  ORDER BY v.nom ASC, tb.nom ASC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
