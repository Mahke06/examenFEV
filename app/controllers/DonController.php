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

 
    /**
     * Distribution proportionnelle par ville :
     * Formule : distribution_ville = (besoin_ville / besoin_total) × don
     * Le résultat est arrondi par floor, puis le reste est distribué
     * un par un aux villes ayant la partie décimale la plus haute.
     * Ensuite, la part de chaque ville est répartie entre ses besoins individuels.
     */
    private function distribuerProportionnellement($don_id) {
        $query = "SELECT * FROM bngrc_don WHERE id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$don_id]);
        $don = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $quantite_don = (int)$don['quantite'];
        
        // Récupérer tous les besoins non satisfaits pour ce type
        $besoins_stmt = $this->besoin->getBesoinsNonSatisfaitsAvecRestant($don['type_besoin_id']);
        $besoins = $besoins_stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if(empty($besoins)) {
            $this->don->updateStatut($don_id, 'disponible');
            return;
        }
        
        // Regrouper les besoins par ville
        $besoins_par_ville = [];
        foreach($besoins as $besoin_item) {
            $ville_id = (int)$besoin_item['ville_id'];
            if(!isset($besoins_par_ville[$ville_id])) {
                $besoins_par_ville[$ville_id] = [
                    'ville_id' => $ville_id,
                    'ville_nom' => $besoin_item['ville_nom'],
                    'total_restant' => 0,
                    'besoins' => []
                ];
            }
            $besoin_restant = (int)$besoin_item['quantite_restante'];
            $besoins_par_ville[$ville_id]['total_restant'] += $besoin_restant;
            $besoins_par_ville[$ville_id]['besoins'][] = $besoin_item;
        }
        
        // Calculer le total global des besoins restants
        $total_besoin_restant = 0;
        foreach($besoins_par_ville as $ville_data) {
            $total_besoin_restant += $ville_data['total_restant'];
        }
        
        if($total_besoin_restant <= 0) {
            $this->don->updateStatut($don_id, 'disponible');
            return;
        }
        
        // Si le don couvre tous les besoins, distribuer tout
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
        
        // === Distribution proportionnelle par ville ===
        // Étape 1 : Calculer la part de chaque ville avec floor + partie décimale
        $parts_par_ville = [];
        $quantite_distribuee_totale = 0;
        
        foreach($besoins_par_ville as $ville_id => $ville_data) {
            // Formule : (besoin_ville / besoin_total) × don
            $part_exacte = ($ville_data['total_restant'] / $total_besoin_restant) * $quantite_don;
            $part_entiere = (int)floor($part_exacte);
            $partie_decimale = $part_exacte - $part_entiere;
            
            // Ne pas dépasser le besoin restant de la ville
            $part_entiere = min($part_entiere, $ville_data['total_restant']);
            
            $parts_par_ville[$ville_id] = [
                'ville_id' => $ville_id,
                'quantite' => $part_entiere,
                'besoin_restant_ville' => $ville_data['total_restant'],
                'partie_decimale' => $partie_decimale,
                'besoins' => $ville_data['besoins']
            ];
            $quantite_distribuee_totale += $part_entiere;
        }
        
        // Étape 2 : Distribuer le reste aux villes avec la décimale la plus haute
        $reste = $quantite_don - $quantite_distribuee_totale;
        
        if($reste > 0) {
            // Trier par partie décimale décroissante
            uasort($parts_par_ville, function($a, $b) {
                $cmp = $b['partie_decimale'] <=> $a['partie_decimale'];
                if($cmp !== 0) {
                    return $cmp;
                }
                // En cas d'égalité, la ville avec le plus grand besoin en premier
                return $b['besoin_restant_ville'] <=> $a['besoin_restant_ville'];
            });
            
            foreach($parts_par_ville as &$part_ville) {
                if($reste <= 0) break;
                if($part_ville['quantite'] < $part_ville['besoin_restant_ville']) {
                    $part_ville['quantite'] += 1;
                    $quantite_distribuee_totale += 1;
                    $reste -= 1;
                }
            }
            unset($part_ville);
        }
        
        // Étape 3 : Pour chaque ville, répartir sa part entre ses besoins individuels
        $quantite_reellement_distribuee = 0;
        
        foreach($parts_par_ville as $part_ville) {
            $quantite_ville = $part_ville['quantite'];
            if($quantite_ville <= 0) continue;
            
            $restant_a_distribuer = $quantite_ville;
            
            foreach($part_ville['besoins'] as $besoin_item) {
                if($restant_a_distribuer <= 0) break;
                
                $besoin_restant = (int)$besoin_item['quantite_restante'];
                $quantite_a_attribuer = min($restant_a_distribuer, $besoin_restant);
                
                if($quantite_a_attribuer <= 0) continue;
                
                $this->attribution->don_id = $don_id;
                $this->attribution->besoin_id = $besoin_item['id'];
                $this->attribution->ville_id = $besoin_item['ville_id'];
                $this->attribution->quantite_attribuee = $quantite_a_attribuer;
                $this->attribution->create();
                
                $this->besoin->updateQuantiteSatisfaite($besoin_item['id'], $quantite_a_attribuer);
                
                $restant_a_distribuer -= $quantite_a_attribuer;
                $quantite_reellement_distribuee += $quantite_a_attribuer;
            }
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