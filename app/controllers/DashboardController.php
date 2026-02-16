<?php
require_once __DIR__ . '/../models/Ville.php';
require_once __DIR__ . '/../models/Besoin.php';
require_once __DIR__ . '/../models/Attribution.php';
require_once __DIR__ . '/../models/TypeBesoin.php';

class DashboardController {
    private $db;
    private $ville;
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
        
        $this->ville = new Ville($this->db);
        $this->besoin = new Besoin($this->db);
        $this->attribution = new Attribution($this->db);
    }
    
    public function index() {
        $villes = $this->ville->getAll()->fetchAll(PDO::FETCH_ASSOC);
        
        $dashboard_data = [];
        foreach($villes as $ville) {
            $besoins_query = "SELECT b.*, tb.nom as type_besoin_nom, tb.prix_unitaire, tb.unite, cb.nom as categorie_nom
                             FROM bngrc_besoin b
                             LEFT JOIN bngrc_type_besoin tb ON b.type_besoin_id = tb.id
                             LEFT JOIN bngrc_categorie_besoin cb ON tb.categorie_id = cb.id
                             WHERE b.ville_id = ?";
            $stmt = $this->db->prepare($besoins_query);
            $stmt->execute([$ville['id']]);
            $besoins = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $attributions_query = "SELECT a.*, tb.nom as type_besoin_nom, tb.prix_unitaire, tb.unite
                                  FROM bngrc_attribution a
                                  LEFT JOIN bngrc_don d ON a.don_id = d.id
                                  LEFT JOIN bngrc_type_besoin tb ON d.type_besoin_id = tb.id
                                  WHERE a.ville_id = ?";
            $stmt = $this->db->prepare($attributions_query);
            $stmt->execute([$ville['id']]);
            $attributions = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $dashboard_data[] = [
                'ville' => $ville,
                'besoins' => $besoins,
                'attributions' => $attributions
            ];
        }
        
        require_once __DIR__ . '/../views/dashboard/index.php';
    }
}
?>