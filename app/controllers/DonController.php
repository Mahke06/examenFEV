<?php
require_once __DIR__ . '/../models/Don.php';
require_once __DIR__ . '/../models/TypeBesoin.php';
require_once __DIR__ . '/../models/CategorieBesoin.php';
require_once __DIR__ . '/../models/Besoin.php';
require_once __DIR__ . '/../models/Attribution.php';

class DonController {
    private $db;
    private $don;
    private $typeBesoin;
    private $categorie;
    private $besoin;
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
        } catch(PDOException $exception) {
            die("Erreur de connexion: " . $exception->getMessage());
        }
        
        $this->don = new Don($this->db);
        $this->typeBesoin = new TypeBesoin($this->db);
        $this->categorie = new CategorieBesoin($this->db);
        $this->besoin = new Besoin($this->db);
        $this->attribution = new Attribution($this->db);
    }
    
    public function index() {
        $dons = $this->don->getAll()->fetchAll(PDO::FETCH_ASSOC);
        require_once __DIR__ . '/../views/dons/index.php';
    }
    
    public function create() {
        $categories = $this->categorie->getAll()->fetchAll(PDO::FETCH_ASSOC);
        $types_besoins = $this->typeBesoin->getAll()->fetchAll(PDO::FETCH_ASSOC);
        require_once __DIR__ . '/../views/dons/create.php';
    }
    
    public function store() {
        if($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->don->type_besoin_id = $_POST['type_besoin_id'];
            $this->don->quantite = $_POST['quantite'];
            
            $don_id = $this->don->create();
            
            if($don_id) {
                $this->distribuerAutomatiquement($don_id);
                header("Location: /dons");
                exit();
            } else {
                echo "Erreur lors de la création du don";
            }
        }
    }
    
    private function distribuerAutomatiquement($don_id) {
        $query = "SELECT * FROM bngrc_don WHERE id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$don_id]);
        $don = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $quantite_restante = $don['quantite'];
        
        $besoins_stmt = $this->besoin->getBesoinsNonSatisfaits($don['type_besoin_id']);
        $besoins = $besoins_stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach($besoins as $besoin_item) {
            if($quantite_restante <= 0) break;
            
            $besoin_restant = $besoin_item['quantite_demandee'] - $besoin_item['quantite_satisfaite'];
            $quantite_a_attribuer = min($quantite_restante, $besoin_restant);
            
            $this->attribution->don_id = $don_id;
            $this->attribution->besoin_id = $besoin_item['id'];
            $this->attribution->ville_id = $besoin_item['ville_id'];
            $this->attribution->quantite_attribuee = $quantite_a_attribuer;
            $this->attribution->create();
            
            $this->besoin->updateQuantiteSatisfaite($besoin_item['id'], $quantite_a_attribuer);
            
            $quantite_restante -= $quantite_a_attribuer;
        }
        
        $this->don->updateQuantiteRestante($don_id, $don['quantite'] - $quantite_restante);
        
        if($quantite_restante == 0) {
            $this->don->updateStatut($don_id, 'distribué');
        } else {
            $this->don->updateStatut($don_id, 'partiellement distribué');
        }
    }
}
?>