<?php
class TypeBesoin {
    private $conn;
    private $table = "bngrc_type_besoin";

    public $id;
    public $nom;
    public $categorie_id;
    public $prix_unitaire;
    public $unite;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getAll() {
        $query = "SELECT tb.*, cb.nom as categorie_nom 
                  FROM " . $this->table . " tb 
                  LEFT JOIN bngrc_categorie_besoin cb ON tb.categorie_id = cb.id 
                  ORDER BY tb.nom";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function getById($id) {
        $query = "SELECT tb.*, cb.nom as categorie_nom 
                  FROM " . $this->table . " tb 
                  LEFT JOIN bngrc_categorie_besoin cb ON tb.categorie_id = cb.id 
                  WHERE tb.id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getByCategorie($categorie_id) {
        $query = "SELECT * FROM " . $this->table . " WHERE categorie_id = ? ORDER BY nom";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $categorie_id);
        $stmt->execute();
        return $stmt;
    }
}
?>