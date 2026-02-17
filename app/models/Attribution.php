<?php
class Attribution {
    private $conn;
    private $table = "bngrc_attribution";

    public $id;
    public $don_id;
    public $besoin_id;
    public $ville_id;
    public $quantite_attribuee;
    public $date_attribution;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table . " 
                  (don_id, besoin_id, ville_id, quantite_attribuee) 
                  VALUES (:don_id, :besoin_id, :ville_id, :quantite_attribuee)";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(":don_id", $this->don_id);
        $stmt->bindParam(":besoin_id", $this->besoin_id);
        $stmt->bindParam(":ville_id", $this->ville_id);
        $stmt->bindParam(":quantite_attribuee", $this->quantite_attribuee);
        
        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function getByVille($ville_id) {
        $query = "SELECT a.*, tb.nom as type_besoin_nom, tb.unite, tb.prix_unitaire
                  FROM " . $this->table . " a
                  LEFT JOIN bngrc_don d ON a.don_id = d.id
                  LEFT JOIN bngrc_type_besoin tb ON d.type_besoin_id = tb.id
                  WHERE a.ville_id = ?
                  ORDER BY a.date_attribution DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $ville_id);
        $stmt->execute();
        return $stmt;
    }

    public function getAll() {
        $query = "SELECT a.*, v.nom as ville_nom, tb.nom as type_besoin_nom, 
                  tb.unite, tb.prix_unitaire, d.date_saisie as date_don
                  FROM " . $this->table . " a
                  LEFT JOIN bngrc_ville v ON a.ville_id = v.id
                  LEFT JOIN bngrc_don d ON a.don_id = d.id
                  LEFT JOIN bngrc_type_besoin tb ON d.type_besoin_id = tb.id
                  ORDER BY a.date_attribution DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    /**
     * Retourne les attributions liées à un don
     */
    public function getByDonId($don_id) {
        $query = "SELECT * FROM " . $this->table . " WHERE don_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $don_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Retourne les attributions liées à un besoin
     */
    public function getByBesoinId($besoin_id) {
        $query = "SELECT * FROM " . $this->table . " WHERE besoin_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $besoin_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Supprime toutes les attributions liées à un don
     */
    public function deleteByDonId($don_id) {
        $query = "DELETE FROM " . $this->table . " WHERE don_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $don_id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * Supprime toutes les attributions liées à un besoin
     */
    public function deleteByBesoinId($besoin_id) {
        $query = "DELETE FROM " . $this->table . " WHERE besoin_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $besoin_id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
?>