<?php
require_once __DIR__ . '/../models/Besoin.php';
require_once __DIR__ . '/../models/Ville.php';
require_once __DIR__ . '/../models/TypeBesoin.php';
require_once __DIR__ . '/../models/CategorieBesoin.php';

class BesoinController {
    private $db;
    private $besoin;
    private $ville;
    private $typeBesoin;
    private $categorie;
    
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
        } catch(PDOException $exception) {
            die("Erreur de connexion: " . $exception->getMessage());
        }
        
        $this->besoin = new Besoin($this->db);
        $this->ville = new Ville($this->db);
        $this->typeBesoin = new TypeBesoin($this->db);
        $this->categorie = new CategorieBesoin($this->db);
    }
    
    public function index() {
        $besoins = $this->besoin->getAll()->fetchAll(PDO::FETCH_ASSOC);
        require_once __DIR__ . '/../views/besoins/index.php';
    }
    
    public function create() {
        $villes = $this->ville->getAll()->fetchAll(PDO::FETCH_ASSOC);
        $categories = $this->categorie->getAll()->fetchAll(PDO::FETCH_ASSOC);
        $types_besoins = $this->typeBesoin->getAll()->fetchAll(PDO::FETCH_ASSOC);
        require_once __DIR__ . '/../views/besoins/create.php';
    }
    
    public function store() {
        if($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->besoin->ville_id = $_POST['ville_id'];
            $this->besoin->type_besoin_id = $_POST['type_besoin_id'];
            $this->besoin->quantite_demandee = $_POST['quantite_demandee'];
            
            if($this->besoin->create()) {
                header("Location: /besoins");
                exit();
            } else {
                echo "Erreur lors de la création du besoin";
            }
        }
    }

    /**
     * Supprime un besoin et annule ses attributions et achats liés
     */
    public function delete() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: /besoins");
            exit();
        }

        $besoin_id = (int)$_POST['id'];

        // Charger les modèles nécessaires
        require_once __DIR__ . '/../models/Attribution.php';
        require_once __DIR__ . '/../models/Don.php';
        require_once __DIR__ . '/../models/Achat.php';
        $attribution = new Attribution($this->db);
        $don = new Don($this->db);
        $achat = new Achat($this->db);

        try {
            $this->db->beginTransaction();

            // 1. Récupérer et annuler les attributions liées à ce besoin
            $attributions = $attribution->getByBesoinId($besoin_id);
            foreach ($attributions as $attr) {
                // Restaurer la quantité restante du don
                $don->restoreQuantiteRestante($attr['don_id'], $attr['quantite_attribuee']);
                // Remettre le statut du don
                $don_data = $don->getById($attr['don_id']);
                if ($don_data) {
                    if ($don_data['quantite_restante'] + $attr['quantite_attribuee'] >= $don_data['quantite']) {
                        $don->updateStatut($attr['don_id'], 'non distribué');
                    } else {
                        $don->updateStatut($attr['don_id'], 'partiel');
                    }
                }
            }
            $attribution->deleteByBesoinId($besoin_id);

            // 2. Récupérer et annuler les achats liés à ce besoin
            $achats = $achat->getByBesoinId($besoin_id);
            foreach ($achats as $a) {
                // Restaurer le montant du don en argent
                $don->restoreQuantiteRestante($a['don_argent_id'], $a['montant_total']);
                $don_data = $don->getById($a['don_argent_id']);
                if ($don_data && ($don_data['quantite_restante'] + $a['montant_total']) >= $don_data['quantite']) {
                    $don->updateStatut($a['don_argent_id'], 'non distribué');
                } else {
                    $don->updateStatut($a['don_argent_id'], 'partiellement utilisé');
                }
                $achat->delete($a['id']);
            }

            // 3. Supprimer le besoin
            $this->besoin->delete($besoin_id);

            $this->db->commit();
            header("Location: /besoins");
            exit();
        } catch (Exception $e) {
            $this->db->rollBack();
            die("Erreur lors de la suppression du besoin : " . $e->getMessage());
        }
    }
}
?>