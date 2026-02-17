<?php
class Ville {
    private $conn;
    private $table = "bngrc_ville";

    public $id;
    public $nom;
    public $region_id;
    public $nombre_sinistres;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getAll() {
        $query = "SELECT v.*, r.nom as region_nom 
                  FROM " . $this->table . " v 
                  LEFT JOIN bngrc_region r ON v.region_id = r.id 
                  ORDER BY v.nom";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function getById($id) {
        $query = "SELECT v.*, r.nom as region_nom 
                  FROM " . $this->table . " v 
                  LEFT JOIN bngrc_region r ON v.region_id = r.id 
                  WHERE v.id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>