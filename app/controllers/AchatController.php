<?php
require_once __DIR__ . '/../models/Achat.php';
require_once __DIR__ . '/../models/Don.php';
require_once __DIR__ . '/../models/Besoin.php';
require_once __DIR__ . '/../models/Ville.php';
require_once __DIR__ . '/../models/TypeBesoin.php';
require_once __DIR__ . '/../models/CategorieBesoin.php';
require_once __DIR__ . '/../models/Attribution.php';

class AchatController {
    private $db;
    private $achat;
    private $don;
    private $besoin;
    private $ville;
    private $typeBesoin;
    private $categorie;
    private $frais_achat;

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
        } catch (PDOException $exception) {
            die("Erreur de connexion: " . $exception->getMessage());
        }

        $this->achat = new Achat($this->db);
        $this->don = new Don($this->db);
        $this->besoin = new Besoin($this->db);
        $this->ville = new Ville($this->db);
        $this->typeBesoin = new TypeBesoin($this->db);
        $this->categorie = new CategorieBesoin($this->db);
        $this->frais_achat = $config['frais_achat'] ?? 10;
    }

    /**
     * Liste des achats — filtrable par ville
     */
    public function index() {
        $ville_id = isset($_GET['ville_id']) ? (int)$_GET['ville_id'] : null;

        if ($ville_id) {
            $achats = $this->achat->getAllByVille($ville_id)->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $achats = $this->achat->getAll()->fetchAll(PDO::FETCH_ASSOC);
        }

        $villes = $this->ville->getAll()->fetchAll(PDO::FETCH_ASSOC);
        $frais_achat = $this->frais_achat;

        require_once __DIR__ . '/../views/achats/index.php';
    }

    /**
     * Formulaire d'achat — utilise la page des besoins restants
     */
    public function create() {
        $besoins_restants = $this->besoin->getBesoinsRestantsNonArgent()->fetchAll(PDO::FETCH_ASSOC);
        $dons_argent = $this->don->getDonsArgentDisponibles()->fetchAll(PDO::FETCH_ASSOC);
        $villes = $this->ville->getAll()->fetchAll(PDO::FETCH_ASSOC);
        $frais_achat = $this->frais_achat;

        // Calculer le solde total disponible en argent (quantite_restante en Ar directement)
        $solde_argent = 0;
        foreach ($dons_argent as $don) {
            $solde_argent += $don['quantite_restante'];
        }

        $error = isset($_GET['error']) ? urldecode($_GET['error']) : null;
        $success = isset($_GET['success']) ? urldecode($_GET['success']) : null;

        require_once __DIR__ . '/../views/achats/create.php';
    }

/**
     * Traitement de l'achat via AJAX
     */
   /**
   
     * Traitement de l'achat (Version Standard PHP)
     */
    public function store() {
        // 1. Vérifications de base
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Flight::redirect('/achats/create');
            exit();
        }

        $besoin_id = (int)$_POST['besoin_id'];
        $quantite = (float)$_POST['quantite'];

        // Récupérer le besoin
        $besoin = $this->besoin->getById($besoin_id);
        if (!$besoin) {
            Flight::redirect('/achats/create?error=' . urlencode('Besoin introuvable.'));
            exit();
        }

        // Vérifications stocks et validité
        if ($this->achat->existeDansDonsRestants($besoin['type_besoin_id'])) {
            Flight::redirect('/achats/create?error=' . urlencode("Cet article est disponible en don. Utilisez le stock avant d'acheter."));
            exit();
        }

        $quantite_restante = $besoin['quantite_demandee'] - $besoin['quantite_satisfaite'];
        if ($quantite > $quantite_restante) {
            Flight::redirect('/achats/create?error=' . urlencode("Quantité trop élevée. Max: $quantite_restante"));
            exit();
        }

        // 2. Calcul du montant total à payer
        $prix_unitaire = $besoin['prix_unitaire'];
        $montant_ht = $quantite * $prix_unitaire;
        $montant_total = $montant_ht * (1 + $this->frais_achat / 100);

        // 3. Vérifier si on a assez d'argent
        $dons_argent = $this->don->getDonsArgentDisponibles()->fetchAll(PDO::FETCH_ASSOC);
        
        $solde_total = 0;
        foreach ($dons_argent as $don) {
            $solde_total += $don['quantite_restante'];
        }
        
        if ($solde_total < $montant_total) {
            Flight::redirect('/achats/create?error=' . urlencode("Fonds insuffisants. Manque " . ($montant_total - $solde_total) . " Ar"));
            exit();
        }

        try {
            $this->db->beginTransaction();

            $montant_a_deduire = $montant_total;
            
            $don_argent_id_principal = $dons_argent[0]['id']; 

            foreach ($dons_argent as $don) {
                if ($montant_a_deduire <= 0) break;

                // On prend soit tout ce qui reste dans ce don, soit juste ce qu'il nou
                $deduction = min($montant_a_deduire, $don['quantite_restante']);
                
                // >>> LA REQUÊTE QUI MET À JOUR LE SOLDE <<<
                $stmt = $this->db->prepare("UPDATE bngrc_don SET quantite_restante = quantite_restante - ? WHERE id = ?");
                $stmt->execute([$deduction, $don['id']]);

                // Mise à jour du statut (épuisé ou partiel)
                $nouveau_solde_don = $don['quantite_restante'] - $deduction;
                $statut = ($nouveau_solde_don <= 0) ? 'épuisé' : 'partiellement utilisé';
                $this->don->updateStatut($don['id'], $statut);

                $montant_a_deduire -= $deduction;
            }

    
            $this->achat->besoin_id = $besoin_id;
            $this->achat->ville_id = $besoin['ville_id'];
            $this->achat->type_besoin_id = $besoin['type_besoin_id'];
            $this->achat->quantite = $quantite;
            $this->achat->prix_unitaire = $prix_unitaire;
            $this->achat->frais_pourcent = $this->frais_achat;
            $this->achat->montant_total = $montant_total;
            $this->achat->don_argent_id = $don_argent_id_principal;
            
            $this->achat->create();

            $this->besoin->updateQuantiteSatisfaite($besoin_id, $quantite);

            $this->db->commit();

            Flight::redirect('/achats?success=' . urlencode("Achat effectué ! Solde mis à jour."));
            exit();

        } catch (Exception $e) {
            $this->db->rollBack();
            Flight::redirect('/achats/create?error=' . urlencode("Erreur technique : " . $e->getMessage()));
            exit();
        }
    }
   

    public function delete() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header("Location: /achats");
        exit();
    }

    $achat_id = (int)$_POST['id'];
    $achat_data = $this->achat->getById($achat_id);

    if (!$achat_data) {
        header("Location: /achats?error=" . urlencode("Achat introuvable."));
        exit();
    }

    try {
        $this->db->beginTransaction();

        $montant_total = $achat_data['montant_total'];
        $don_argent_id = $achat_data['don_argent_id'];
        $besoin_id = $achat_data['besoin_id'];
        $quantite = $achat_data['quantite'];

        $stmt = $this->db->prepare("UPDATE bngrc_don 
                                    SET quantite_restante = quantite_restante + ?,
                                        statut = 'disponible'
                                    WHERE id = ?");
        $stmt->execute([$montant_total, $don_argent_id]);

        $this->besoin->updateQuantiteSatisfaite($besoin_id, -$quantite);

        $stmt2 = $this->db->prepare("DELETE FROM bngrc_achat WHERE id = ?");
        $stmt2->execute([$achat_id]);

        $this->db->commit();

        Flight::redirect('/achats?success=' . urlencode("Achat supprimé et solde restauré."));
        exit();

    } catch (Exception $e) {
        $this->db->rollBack();
        Flight::redirect('/achats?error=' . urlencode("Erreur : " . $e->getMessage()));
        exit();
    }
}
}
?>
