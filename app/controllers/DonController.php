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
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->don->type_besoin_id = (int)$_POST['type_besoin_id'];
            $this->don->quantite = (int)$_POST['quantite'];
            $ville_id = !empty($_POST['ville_id']) ? (int)$_POST['ville_id'] : null;
            
            $don_id = $this->don->create();
            
            if ($don_id) {
                $this->distribuerParDate($don_id, $ville_id);
                header("Location: /dons");
                exit();
            } else {
                echo "Erreur lors de la création du don";
            }
        }
    }

    public function storePlusPetit() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->don->type_besoin_id = (int)$_POST['type_besoin_id'];
            $this->don->quantite = (int)$_POST['quantite'];
            
            $don_id = $this->don->create();
            
            if ($don_id) {
                $this->distribuerParPlusPetit($don_id);
                header("Location: /dons");
                exit();
            } else {
                echo "Erreur lors de la création du don";
            }
        }
    }

    public function storeProportionnel() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->don->type_besoin_id = (int)$_POST['type_besoin_id'];
            $this->don->quantite = (int)$_POST['quantite'];
            
            $don_id = $this->don->create();
            
            if ($don_id) {
                $this->distribuerProportionnellement($don_id);
                header("Location: /dons");
                exit();
            } else {
                echo "Erreur lors de la création du don";
            }
        }
    }
    
    private function distribuerParDate($don_id, $ville_id = null) {
        $don = $this->don->getById($don_id);
        $quantite_restante = (int)$don['quantite'];
        $type_besoin_id = (int)$don['type_besoin_id'];

        if ($ville_id !== null) {
            $query = "SELECT * FROM bngrc_besoin 
                      WHERE type_besoin_id = ? AND ville_id = ? AND quantite_satisfaite < quantite_demandee 
                      ORDER BY date_saisie ASC";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$type_besoin_id, $ville_id]);
        } else {
            $stmt = $this->besoin->getBesoinsNonSatisfaits($type_besoin_id);
        }
        $besoins = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $quantite_distribuee = 0;

        foreach ($besoins as $besoin_item) {
            if ($quantite_restante <= 0) break;

            $besoin_restant = (int)$besoin_item['quantite_demandee'] - (int)$besoin_item['quantite_satisfaite'];
            if ($besoin_restant <= 0) continue;

            $quantite_a_attribuer = min($quantite_restante, $besoin_restant);

            $this->attribution->don_id = $don_id;
            $this->attribution->besoin_id = $besoin_item['id'];
            $this->attribution->ville_id = $besoin_item['ville_id'];
            $this->attribution->quantite_attribuee = $quantite_a_attribuer;
            $this->attribution->create();

            $this->besoin->updateQuantiteSatisfaite($besoin_item['id'], $quantite_a_attribuer);

            $quantite_restante -= $quantite_a_attribuer;
            $quantite_distribuee += $quantite_a_attribuer;
        }

        $this->mettreAJourStatutDon($don_id, $quantite_distribuee, (int)$don['quantite']);
    }

    private function distribuerParPlusPetit($don_id) {
        $don = $this->don->getById($don_id);
        $quantite_restante = (int)$don['quantite'];
        $type_besoin_id = (int)$don['type_besoin_id'];

        $besoins = $this->besoin->getBesoinsNonSatisfaitsParPlusPetit($type_besoin_id)->fetchAll(PDO::FETCH_ASSOC);

        $quantite_distribuee = 0;

        foreach ($besoins as $besoin_item) {
            if ($quantite_restante <= 0) break;

            $besoin_restant = (int)$besoin_item['quantite_demandee'] - (int)$besoin_item['quantite_satisfaite'];
            if ($besoin_restant <= 0) continue;

            $quantite_a_attribuer = min($quantite_restante, $besoin_restant);

            $this->attribution->don_id = $don_id;
            $this->attribution->besoin_id = $besoin_item['id'];
            $this->attribution->ville_id = $besoin_item['ville_id'];
            $this->attribution->quantite_attribuee = $quantite_a_attribuer;
            $this->attribution->create();

            $this->besoin->updateQuantiteSatisfaite($besoin_item['id'], $quantite_a_attribuer);

            $quantite_restante -= $quantite_a_attribuer;
            $quantite_distribuee += $quantite_a_attribuer;
        }

        $this->mettreAJourStatutDon($don_id, $quantite_distribuee, (int)$don['quantite']);
    }

    private function distribuerProportionnellement($don_id) {
        $don = $this->don->getById($don_id);
        $quantite_don = (int)$don['quantite'];
        $type_besoin_id = (int)$don['type_besoin_id'];

        $besoins = $this->besoin->getBesoinsNonSatisfaitsAvecRestant($type_besoin_id)->fetchAll(PDO::FETCH_ASSOC);

        if (empty($besoins)) {
            $this->don->updateStatut($don_id, 'disponible');
            return;
        }

        $total_besoin_restant = 0;
        foreach ($besoins as $b) {
            $total_besoin_restant += (int)$b['quantite_restante'];
        }

        if ($total_besoin_restant <= 0) {
            $this->don->updateStatut($don_id, 'disponible');
            return;
        }

        $quantite_a_distribuer = min($quantite_don, $total_besoin_restant);

        $attributions_prevues = [];

        foreach ($besoins as $b) {
            $besoin_restant = (int)$b['quantite_restante'];
            $part_exacte = ($besoin_restant / $total_besoin_restant) * $quantite_a_distribuer;
            $part_entiere = (int)floor($part_exacte);
            $partie_decimale = $part_exacte - $part_entiere;

            $attributions_prevues[] = [
                'besoin_id' => $b['id'],
                'ville_id' => $b['ville_id'],
                'quantite' => $part_entiere,
                'besoin_restant' => $besoin_restant,
                'decimal' => $partie_decimale,
            ];
        }

        $quantite_distribuee_totale = 0;
        foreach ($attributions_prevues as $a) {
            $quantite_distribuee_totale += $a['quantite'];
        }

        $reste = $quantite_a_distribuer - $quantite_distribuee_totale;

        if ($reste > 0) {
            usort($attributions_prevues, function($a, $b) {
                if ($b['decimal'] !== $a['decimal']) {
                    return ($b['decimal'] > $a['decimal']) ? 1 : -1;
                }
                return $b['besoin_restant'] - $a['besoin_restant'];
            });

            foreach ($attributions_prevues as &$attr) {
                if ($reste <= 0) break;
                $max_ajout = $attr['besoin_restant'] - $attr['quantite'];
                if ($max_ajout > 0) {
                    $attr['quantite'] += 1;
                    $reste -= 1;
                }
            }
            unset($attr);
        }

        $quantite_reellement_distribuee = 0;
        foreach ($attributions_prevues as $attr) {
            if ($attr['quantite'] <= 0) continue;

            $this->attribution->don_id = $don_id;
            $this->attribution->besoin_id = $attr['besoin_id'];
            $this->attribution->ville_id = $attr['ville_id'];
            $this->attribution->quantite_attribuee = $attr['quantite'];
            $this->attribution->create();

            $this->besoin->updateQuantiteSatisfaite($attr['besoin_id'], $attr['quantite']);
            $quantite_reellement_distribuee += $attr['quantite'];
        }

        $this->mettreAJourStatutDon($don_id, $quantite_reellement_distribuee, $quantite_don);
    }

    private function mettreAJourStatutDon($don_id, $quantite_distribuee, $quantite_totale) {
        if ($quantite_distribuee <= 0) {
            $this->don->updateStatut($don_id, 'disponible');
            return;
        }

        $query = "UPDATE bngrc_don SET quantite_restante = ? WHERE id = ?";
        $stmt = $this->db->prepare($query);
        $quantite_restante = $quantite_totale - $quantite_distribuee;
        $stmt->execute([$quantite_restante, $don_id]);

        if ($quantite_distribuee >= $quantite_totale) {
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