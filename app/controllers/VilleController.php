<?php
require_once __DIR__ . '/../models/Ville.php';
require_once __DIR__ . '/../models/Region.php';

class VilleController {
    private $db;
    private $ville;
    private $region;
    
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
        $this->region = new Region($this->db);
    }
    
    public function index() {
        $villes = $this->ville->getAll()->fetchAll(PDO::FETCH_ASSOC);
        require_once __DIR__ . '/../views/villes/index.php';
    }
}
?>