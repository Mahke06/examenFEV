<?php
class Don {
    private $conn;
    private $table = "bngrc_don";

    public $id;
    public $type_besoin_id;
    public $quantite;
    public $quantite_restante;
    public $date_saisie;
    public $statut;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getAll() {
        $query = "SELECT d.*, tb.nom as type_besoin_nom, tb.unite, cb.nom as categorie_nom
                  FROM " . $this->table . " d
                  LEFT JOIN bngrc_type_besoin tb ON d.type_besoin_id = tb.id
                  LEFT JOIN bngrc_categorie_besoin cb ON tb.categorie_id = cb.id
                  ORDER BY d.date_saisie DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table . " 
                  (type_besoin_id, quantite, quantite_restante) 
                  VALUES (:type_besoin_id, :quantite, :quantite_restante)";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(":type_besoin_id", $this->type_besoin_id);
        $stmt->bindParam(":quantite", $this->quantite);
        $stmt->bindParam(":quantite_restante", $this->quantite);
        
        if($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    public function updateQuantiteRestante($don_id, $quantite) {
        $query = "UPDATE " . $this->table . " 
                  SET quantite_restante = quantite_restante - ? 
                  WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $quantite);
        $stmt->bindParam(2, $don_id);
        return $stmt->execute();
    }

    public function updateStatut($don_id, $statut) {
        $query = "UPDATE " . $this->table . " SET statut = ? WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $statut);
        $stmt->bindParam(2, $don_id);
        return $stmt->execute();
    }
}
?>