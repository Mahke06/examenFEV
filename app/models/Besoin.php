<?php
class Besoin {
    private $conn;
    private $table = "bngrc_besoin";

    public $id;
    public $ville_id;
    public $type_besoin_id;
    public $quantite_demandee;
    public $quantite_satisfaite;
    public $date_saisie;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getAll() {
        $query = "SELECT b.*, v.nom as ville_nom, tb.nom as type_besoin_nom, 
                  tb.prix_unitaire, tb.unite, cb.nom as categorie_nom
                  FROM " . $this->table . " b
                  LEFT JOIN bngrc_ville v ON b.ville_id = v.id
                  LEFT JOIN bngrc_type_besoin tb ON b.type_besoin_id = tb.id
                  LEFT JOIN bngrc_categorie_besoin cb ON tb.categorie_id = cb.id
                  ORDER BY b.date_saisie DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table . " 
                  (ville_id, type_besoin_id, quantite_demandee) 
                  VALUES (:ville_id, :type_besoin_id, :quantite_demandee)";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(":ville_id", $this->ville_id);
        $stmt->bindParam(":type_besoin_id", $this->type_besoin_id);
        $stmt->bindParam(":quantite_demandee", $this->quantite_demandee);
        
        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function getBesoinsNonSatisfaits($type_besoin_id) {
        $query = "SELECT * FROM " . $this->table . " 
                  WHERE type_besoin_id = ? 
                  AND quantite_satisfaite < quantite_demandee 
                  ORDER BY date_saisie ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $type_besoin_id);
        $stmt->execute();
        return $stmt;
    }

    public function updateQuantiteSatisfaite($besoin_id, $quantite) {
        $query = "UPDATE " . $this->table . " 
                  SET quantite_satisfaite = quantite_satisfaite + ? 
                  WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $quantite);
        $stmt->bindParam(2, $besoin_id);
        return $stmt->execute();
    }
}
?>