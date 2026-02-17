<?php
class Achat {
    private $conn;
    private $table = "bngrc_achat";

    public $id;
    public $besoin_id;
    public $ville_id;
    public $type_besoin_id;
    public $quantite;
    public $prix_unitaire;
    public $frais_pourcent;
    public $montant_total;
    public $don_argent_id;
    public $date_achat;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getAll() {
        $query = "SELECT a.*, v.nom as ville_nom, tb.nom as type_besoin_nom, 
                  tb.unite, cb.nom as categorie_nom, b.quantite_demandee,
                  b.quantite_satisfaite
                  FROM " . $this->table . " a
                  LEFT JOIN bngrc_ville v ON a.ville_id = v.id
                  LEFT JOIN bngrc_type_besoin tb ON a.type_besoin_id = tb.id
                  LEFT JOIN bngrc_categorie_besoin cb ON tb.categorie_id = cb.id
                  LEFT JOIN bngrc_besoin b ON a.besoin_id = b.id
                  ORDER BY a.date_achat DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function getAllByVille($ville_id) {
        $query = "SELECT a.*, v.nom as ville_nom, tb.nom as type_besoin_nom, 
                  tb.unite, cb.nom as categorie_nom, b.quantite_demandee,
                  b.quantite_satisfaite
                  FROM " . $this->table . " a
                  LEFT JOIN bngrc_ville v ON a.ville_id = v.id
                  LEFT JOIN bngrc_type_besoin tb ON a.type_besoin_id = tb.id
                  LEFT JOIN bngrc_categorie_besoin cb ON tb.categorie_id = cb.id
                  LEFT JOIN bngrc_besoin b ON a.besoin_id = b.id
                  WHERE a.ville_id = ?
                  ORDER BY a.date_achat DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $ville_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table . " 
                  (besoin_id, ville_id, type_besoin_id, quantite, prix_unitaire, frais_pourcent, montant_total, don_argent_id) 
                  VALUES (:besoin_id, :ville_id, :type_besoin_id, :quantite, :prix_unitaire, :frais_pourcent, :montant_total, :don_argent_id)";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(":besoin_id", $this->besoin_id, PDO::PARAM_INT);
        $stmt->bindParam(":ville_id", $this->ville_id, PDO::PARAM_INT);
        $stmt->bindParam(":type_besoin_id", $this->type_besoin_id, PDO::PARAM_INT);
        $stmt->bindParam(":quantite", $this->quantite);
        $stmt->bindParam(":prix_unitaire", $this->prix_unitaire);
        $stmt->bindParam(":frais_pourcent", $this->frais_pourcent);
        $stmt->bindParam(":montant_total", $this->montant_total);
        $stmt->bindParam(":don_argent_id", $this->don_argent_id, PDO::PARAM_INT);
        
        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    
    public function existeDansDonsRestants($type_besoin_id) {
        $query = "SELECT COUNT(*) as nb FROM bngrc_don d
                  WHERE d.type_besoin_id = ? 
                  AND d.quantite_restante > 0";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $type_besoin_id, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['nb'] > 0;
    }


    public function getTotalAchats() {
        $query = "SELECT COALESCE(SUM(montant_total), 0) as total FROM " . $this->table;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }

   
    public function getById($id) {
        $query = "SELECT a.*, v.nom as ville_nom, tb.nom as type_besoin_nom,
                  tb.unite, tb.prix_unitaire, cb.nom as categorie_nom
                  FROM " . $this->table . " a
                  LEFT JOIN bngrc_ville v ON a.ville_id = v.id
                  LEFT JOIN bngrc_type_besoin tb ON a.type_besoin_id = tb.id
                  LEFT JOIN bngrc_categorie_besoin cb ON tb.categorie_id = cb.id
                  WHERE a.id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    
    public function delete($id) {
        $query = "DELETE FROM " . $this->table . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    
    public function getByBesoinId($besoin_id) {
        $query = "SELECT * FROM " . $this->table . " WHERE besoin_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $besoin_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
