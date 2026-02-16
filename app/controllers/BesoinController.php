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
}
?>