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

    
    public function getBesoinsNonSatisfaitsParPlusPetit($type_besoin_id) {
        $query = "SELECT * FROM " . $this->table . " 
                  WHERE type_besoin_id = ? 
                  AND quantite_satisfaite < quantite_demandee 
                  ORDER BY (quantite_demandee - quantite_satisfaite) ASC, date_saisie ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $type_besoin_id);
        $stmt->execute();
        return $stmt;
    }

    public function getBesoinsNonSatisfaitsAvecRestant($type_besoin_id) {
        $query = "SELECT b.*, v.nom as ville_nom,
                  (b.quantite_demandee - b.quantite_satisfaite) as quantite_restante
                  FROM " . $this->table . " b
                  LEFT JOIN bngrc_ville v ON b.ville_id = v.id
                  WHERE b.type_besoin_id = ? 
                  AND b.quantite_satisfaite < b.quantite_demandee 
                  ORDER BY b.ville_id ASC, b.date_saisie ASC";
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

   
    public function getBesoinsRestantsNonArgent() {
        $query = "SELECT b.*, v.nom as ville_nom, tb.nom as type_besoin_nom,
                  tb.prix_unitaire, tb.unite, cb.nom as categorie_nom,
                  (b.quantite_demandee - b.quantite_satisfaite) as quantite_restante
                  FROM " . $this->table . " b
                  LEFT JOIN bngrc_ville v ON b.ville_id = v.id
                  LEFT JOIN bngrc_type_besoin tb ON b.type_besoin_id = tb.id
                  LEFT JOIN bngrc_categorie_besoin cb ON tb.categorie_id = cb.id
                  WHERE cb.id IN (1, 2)
                  AND b.quantite_satisfaite < b.quantite_demandee
                  ORDER BY v.nom ASC, tb.nom ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }


    public function getById($besoin_id) {
        $query = "SELECT b.*, v.nom as ville_nom, tb.nom as type_besoin_nom,
                  tb.prix_unitaire, tb.unite, cb.nom as categorie_nom,
                  (b.quantite_demandee - b.quantite_satisfaite) as quantite_restante
                  FROM " . $this->table . " b
                  LEFT JOIN bngrc_ville v ON b.ville_id = v.id
                  LEFT JOIN bngrc_type_besoin tb ON b.type_besoin_id = tb.id
                  LEFT JOIN bngrc_categorie_besoin cb ON tb.categorie_id = cb.id
                  WHERE b.id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $besoin_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    
    public function delete($id) {
        $query = "DELETE FROM " . $this->table . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    
    public function reduireQuantiteSatisfaite($besoin_id, $quantite) {
        $query = "UPDATE " . $this->table . " 
                  SET quantite_satisfaite = GREATEST(0, quantite_satisfaite - ?) 
                  WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $quantite);
        $stmt->bindParam(2, $besoin_id);
        return $stmt->execute();
    }
}
?>