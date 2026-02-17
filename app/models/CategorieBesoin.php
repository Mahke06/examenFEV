<?php
class CategorieBesoin {
    private $conn;
    private $table = "bngrc_categorie_besoin";

    public $id;
    public $nom;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getAll() {
        $query = "SELECT * FROM " . $this->table . " ORDER BY nom";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }
}
?>