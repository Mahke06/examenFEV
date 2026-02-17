<?php
require_once __DIR__ . '/../models/Don.php';
require_once __DIR__ . '/../models/TypeBesoin.php';
require_once __DIR__ . '/../models/CategorieBesoin.php';
require_once __DIR__ . '/../models/Besoin.php';
require_once __DIR__ . '/../models/Attribution.php';
require_once __DIR__ . '/../models/Ville.php';

class DonController {
    private $db;
    private $don;
    private $typeBesoin;
    private $categorie;
    private $besoin;
    private $attribution;
    private $ville;
    
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
        $this->ville = new Ville($this->db);
    }
    
    public function index() {
        $dons = $this->don->getAll()->fetchAll(PDO::FETCH_ASSOC);
        require_once __DIR__ . '/../views/dons/index.php';
    }
    
    public function create() {
        $categories = $this->categorie->getAll()->fetchAll(PDO::FETCH_ASSOC);
        $types_besoins = $this->typeBesoin->getAll()->fetchAll(PDO::FETCH_ASSOC);
        $villes = $this->ville->getAll()->fetchAll(PDO::FETCH_ASSOC);
        require_once __DIR__ . '/../views/dons/create.php';
    }

    public function createPlusPetit() {
        $categories = $this->categorie->getAll()->fetchAll(PDO::FETCH_ASSOC);
        $types_besoins = $this->typeBesoin->getAll()->fetchAll(PDO::FETCH_ASSOC);
        $villes = $this->ville->getAll()->fetchAll(PDO::FETCH_ASSOC);
        require_once __DIR__ . '/../views/dons/create_plus_petit.php';
    }

    public function createProportionnel() {
        $categories = $this->categorie->getAll()->fetchAll(PDO::FETCH_ASSOC);
        $types_besoins = $this->typeBesoin->getAll()->fetchAll(PDO::FETCH_ASSOC);
        $villes = $this->ville->getAll()->fetchAll(PDO::FETCH_ASSOC);
        require_once __DIR__ . '/../views/dons/create_proportionnel.php';
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

    public function storePlusPetit() {
        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->don->type_besoin_id = $_POST['type_besoin_id'];
            $this->don->quantite = $_POST['quantite'];
            
            $don_id = $this->don->create();
            
            if($don_id) {
                $this->distribuerParPlusPetit($don_id);
                header("Location: /dons");
                exit();
            } else {
                echo "Erreur lors de la création du don";
            }
        }
    }

    public function storeProportionnel() {
        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->don->type_besoin_id = $_POST['type_besoin_id'];
            $this->don->quantite = $_POST['quantite'];
            
            $don_id = $this->don->create();
            
            if($don_id) {
                $this->distribuerProportionnellement($don_id);
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
            $this->don->updateStatut($don_id, 'partiel');
        }
    }


    private function distribuerParPlusPetit($don_id) {
        $query = "SELECT * FROM bngrc_don WHERE id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$don_id]);
        $don = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $quantite_restante = $don['quantite'];
        
        $besoins_stmt = $this->besoin->getBesoinsNonSatisfaitsParPlusPetit($don['type_besoin_id']);
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
        
        if($quantite_restante === 0) {
            $this->don->updateStatut($don_id, 'distribué');
        } else {
            $this->don->updateStatut($don_id, 'partiel');
        }
    }

 
    private function distribuerProportionnellement($don_id) {
        $query = "SELECT * FROM bngrc_don WHERE id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$don_id]);
        $don = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $quantite_don = $don['quantite'];
        
        $besoins_stmt = $this->besoin->getBesoinsNonSatisfaitsAvecRestant($don['type_besoin_id']);
        $besoins = $besoins_stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if(empty($besoins)) {
            $this->don->updateStatut($don_id, 'disponible');
            return;
        }
        
        $total_besoin_restant = 0;
        foreach($besoins as $besoin_item) {
            $total_besoin_restant += (int)$besoin_item['quantite_restante'];
        }
        
        if($total_besoin_restant <= 0) {
            $this->don->updateStatut($don_id, 'disponible');
            return;
        }
        
        if($quantite_don >= $total_besoin_restant) {
            $quantite_distribuee = 0;
            foreach($besoins as $besoin_item) {
                $quantite_a_attribuer = (int)$besoin_item['quantite_restante'];
                
                $this->attribution->don_id = $don_id;
                $this->attribution->besoin_id = $besoin_item['id'];
                $this->attribution->ville_id = $besoin_item['ville_id'];
                $this->attribution->quantite_attribuee = $quantite_a_attribuer;
                $this->attribution->create();
                
                $this->besoin->updateQuantiteSatisfaite($besoin_item['id'], $quantite_a_attribuer);
                $quantite_distribuee += $quantite_a_attribuer;
            }
            
            $this->don->updateQuantiteRestante($don_id, $quantite_distribuee);
            
            if($quantite_don === $total_besoin_restant) {
                $this->don->updateStatut($don_id, 'distribué');
            } else {
                $this->don->updateStatut($don_id, 'partiel');
            }
            return;
        }
        
        $quantite_distribuee_totale = 0;
        $attributions_prevues = [];
        
        foreach($besoins as $besoin_item) {
            $besoin_restant = (int)$besoin_item['quantite_restante'];
            // Proportion = besoin_restant / total * quantite_don
            $part_proportionnelle = floor(($besoin_restant / $total_besoin_restant) * $quantite_don);
            
            // Ne pas dépasser le besoin restant
            $quantite_a_attribuer = min($part_proportionnelle, $besoin_restant);
            
            if($quantite_a_attribuer > 0) {
                $attributions_prevues[] = [
                    'besoin_id' => $besoin_item['id'],
                    'ville_id' => $besoin_item['ville_id'],
                    'quantite' => $quantite_a_attribuer,
                    'besoin_restant' => $besoin_restant
                ];
                $quantite_distribuee_totale += $quantite_a_attribuer;
            }
        }
        
    
        $reste = $quantite_don - $quantite_distribuee_totale;
        if($reste > 0 && !empty($attributions_prevues)) {
    
            usort($attributions_prevues, function($a, $b) {
                return $b['besoin_restant'] - $a['besoin_restant'];
            });
            
            foreach($attributions_prevues as &$attr) {
                if($reste <= 0) break;
                $peut_ajouter = min($reste, $attr['besoin_restant'] - $attr['quantite']);
                $attr['quantite'] += $peut_ajouter;
                $reste -= $peut_ajouter;
            }
            unset($attr);
        }
        
    
        $quantite_reellement_distribuee = 0;
        foreach($attributions_prevues as $attr) {
            if($attr['quantite'] <= 0) continue;
            
            $this->attribution->don_id = $don_id;
            $this->attribution->besoin_id = $attr['besoin_id'];
            $this->attribution->ville_id = $attr['ville_id'];
            $this->attribution->quantite_attribuee = $attr['quantite'];
            $this->attribution->create();
            
            $this->besoin->updateQuantiteSatisfaite($attr['besoin_id'], $attr['quantite']);
            $quantite_reellement_distribuee += $attr['quantite'];
        }
        
        $this->don->updateQuantiteRestante($don_id, $quantite_reellement_distribuee);
        
        if($quantite_reellement_distribuee >= $quantite_don) {
            $this->don->updateStatut($don_id, 'distribué');
        } else {
            $this->don->updateStatut($don_id, 'partiel');
        }
    }

   
    public function delete() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: /dons");
            exit();
        }

        $don_id = (int)$_POST['id'];

        try {
            $this->db->beginTransaction();

        
            $attributions = $this->attribution->getByDonId($don_id);

            foreach ($attributions as $attr) {
                $this->besoin->reduireQuantiteSatisfaite($attr['besoin_id'], $attr['quantite_attribuee']);
            }

            $this->attribution->deleteByDonId($don_id);

            $this->don->delete($don_id);

            $this->db->commit();
            header("Location: /dons");
            exit();
        } catch (Exception $e) {
            $this->db->rollBack();
            die("Erreur lors de la suppression du don : " . $e->getMessage());
        }
    }
}
?>