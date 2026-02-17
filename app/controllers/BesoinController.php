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

    public function delete() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: /besoins");
            exit();
        }

        $besoin_id = (int)$_POST['id'];

        
        require_once __DIR__ . '/../models/Attribution.php';
        require_once __DIR__ . '/../models/Don.php';
        require_once __DIR__ . '/../models/Achat.php';
        $attribution = new Attribution($this->db);
        $don = new Don($this->db);
        $achat = new Achat($this->db);

        try {
            $this->db->beginTransaction();

            $attributions = $attribution->getByBesoinId($besoin_id);
            foreach ($attributions as $attr) {
                $don->restoreQuantiteRestante($attr['don_id'], $attr['quantite_attribuee']);
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

        
            $achats = $achat->getByBesoinId($besoin_id);
            foreach ($achats as $a) {
            
                $don->restoreQuantiteRestante($a['don_argent_id'], $a['montant_total']);
                $don_data = $don->getById($a['don_argent_id']);
                if ($don_data && ($don_data['quantite_restante'] + $a['montant_total']) >= $don_data['quantite']) {
                    $don->updateStatut($a['don_argent_id'], 'non distribué');
                } else {
                    $don->updateStatut($a['don_argent_id'], 'partiellement utilisé');
                }
                $achat->delete($a['id']);
            }

        
            $this->besoin->delete($besoin_id);

            $this->db->commit();
            header("Location: /besoins");
            exit();
        } catch (Exception $e) {
            $this->db->rollBack();
            die("Erreur lors de la suppression du besoin : " . $e->getMessage());
        }
    }

    
    public function reinitialiser() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: /besoins");
            exit();
        }

        require_once __DIR__ . '/../models/Attribution.php';
        require_once __DIR__ . '/../models/Don.php';
        require_once __DIR__ . '/../models/Achat.php';

        try {
            $this->db->beginTransaction();
            $this->db->exec("DELETE FROM bngrc_achat");
            $this->db->exec("DELETE FROM bngrc_attribution");
            $this->db->exec("DELETE FROM bngrc_don");
            $this->db->exec("DELETE FROM bngrc_besoin");
            $query = "INSERT INTO bngrc_besoin (ville_id, type_besoin_id, quantite_demandee, date_saisie) VALUES
                (1, 1, 1000, '2026-02-10 08:00:00'),
                (1, 2, 200, '2026-02-10 08:15:00'),
                (1, 11, 500, '2026-02-10 08:30:00'),
                (2, 1, 500, '2026-02-10 10:00:00'),
                (2, 5, 20, '2026-02-10 10:30:00'),
                (2, 6, 30, '2026-02-10 11:00:00'),
                (3, 1, 800, '2026-02-11 09:00:00'),
                (3, 3, 300, '2026-02-11 09:30:00'),
                (3, 7, 15, '2026-02-11 10:00:00'),
                (4, 1, 600, '2026-02-11 14:00:00'),
                (4, 4, 100, '2026-02-11 14:30:00'),
                (5, 10, 50, '2026-02-12 08:00:00'),
                (5, 1, 400, '2026-02-12 08:30:00'),
                (6, 11, 1000, '2026-02-12 10:00:00'),
                (6, 1, 700, '2026-02-12 10:30:00'),
                (7, 12, 40, '2026-02-13 09:00:00'),
                (7, 5, 25, '2026-02-13 09:30:00'),
                (8, 1, 900, '2026-02-13 11:00:00'),
                (8, 8, 50, '2026-02-13 11:30:00'),
                (9, 1, 350, '2026-02-14 08:00:00'),
                (9, 2, 150, '2026-02-14 08:30:00'),
                (10, 1, 1200, '2026-02-14 10:00:00'),
                (10, 3, 400, '2026-02-14 10:30:00'),
                (10, 6, 50, '2026-02-14 11:00:00'),
                (1, 5, 30, '2026-02-15 08:00:00'),
                (2, 7, 10, '2026-02-15 09:00:00'),
                (3, 6, 40, '2026-02-15 10:00:00'),
                (4, 8, 60, '2026-02-15 11:00:00'),
                (5, 1, 250, '2026-02-15 13:00:00'),
                (6, 2, 180, '2026-02-15 14:00:00')";
            $this->db->exec($query);

            $this->db->commit();
            header("Location: /besoins");
            exit();
        } catch (Exception $e) {
            $this->db->rollBack();
            die("Erreur lors de la réinitialisation : " . $e->getMessage());
        }
    }
}
?>