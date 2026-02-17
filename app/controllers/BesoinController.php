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
                    (1, 6, 200,      '2026-02-15 00:01:00'),
                    (4, 5, 40,       '2026-02-15 00:02:00'),
                    (2, 10, 6000000, '2026-02-15 00:03:00'),
                    (1, 2, 1500,     '2026-02-15 00:04:00'),
                    (4, 1, 300,      '2026-02-15 00:05:00'),
                    (2, 5, 80,       '2026-02-15 00:06:00'),
                    (4, 10, 4000000, '2026-02-15 00:07:00'),
                    (3, 6, 150,      '2026-02-16 00:08:00'),
                    (2, 1, 500,      '2026-02-15 00:09:00'),
                    (3, 10, 8000000, '2026-02-16 00:10:00'),
                    (5, 1, 700,      '2026-02-16 00:11:00'),
                    (1, 10, 12000000,'2026-02-16 00:12:00'),
                    (5, 10, 10000000,'2026-02-16 00:13:00'),
                    (3, 2, 1000,     '2026-02-15 00:14:00'),
                    (5, 6, 180,      '2026-02-16 00:15:00'),
                    (1, 9, 3,        '2026-02-15 00:16:00'),
                    (1, 1, 800,      '2026-02-16 00:17:00'),
                    (4, 4, 200,      '2026-02-16 00:18:00'),
                    (2, 7, 60,       '2026-02-16 00:19:00'),
                    (5, 2, 1200,     '2026-02-15 00:20:00'),
                    (3, 1, 600,      '2026-02-16 00:21:00'),
                    (5, 8, 150,      '2026-02-15 00:22:00'),
                    (1, 5, 120,      '2026-02-16 00:23:00'),
                    (4, 7, 30,       '2026-02-16 00:24:00'),
                    (2, 3, 120,      '2026-02-16 00:25:00'),
                    (3, 8, 100,      '2026-02-15 00:26:00')";
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