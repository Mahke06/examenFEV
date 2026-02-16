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
     * Traitement de l'achat
     */
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: /achats/create");
            exit();
        }

        $besoin_id = (int)$_POST['besoin_id'];
        $quantite = (float)$_POST['quantite'];

        // Récupérer les infos du besoin
        $besoin = $this->besoin->getById($besoin_id);

        if (!$besoin) {
            header("Location: /achats/create?error=" . urlencode("Besoin introuvable."));
            exit();
        }

        // Vérifier que le besoin est bien en nature ou matériaux (pas argent)
        if ($besoin['categorie_nom'] === 'en Argent') {
            header("Location: /achats/create?error=" . urlencode("Impossible d'acheter un besoin en argent."));
            exit();
        }

        // Vérifier si ce type de besoin existe encore dans les dons restants (nature/matériaux)
        if ($this->achat->existeDansDonsRestants($besoin['type_besoin_id'])) {
            header("Location: /achats/create?error=" . urlencode("Ce type d'article (« " . $besoin['type_besoin_nom'] . " ») est encore disponible dans les dons restants. Utilisez d'abord les dons existants avant d'acheter."));
            exit();
        }

        // Vérifier que la quantité ne dépasse pas le besoin restant
        $quantite_restante = $besoin['quantite_demandee'] - $besoin['quantite_satisfaite'];
        if ($quantite > $quantite_restante) {
            header("Location: /achats/create?error=" . urlencode("Quantité demandée (" . $quantite . ") supérieure au besoin restant (" . $quantite_restante . ")."));
            exit();
        }

        if ($quantite <= 0) {
            header("Location: /achats/create?error=" . urlencode("La quantité doit être supérieure à 0."));
            exit();
        }

        // Calculer le montant avec frais
        $prix_unitaire = $besoin['prix_unitaire'];
        $montant_ht = $quantite * $prix_unitaire;
        $montant_total = $montant_ht * (1 + $this->frais_achat / 100);

        // Chercher un don en argent disponible avec assez de fonds
        $dons_argent = $this->don->getDonsArgentDisponibles()->fetchAll(PDO::FETCH_ASSOC);
        
        $don_argent_id = null;
        $montant_restant = $montant_total;

        // On utilise le premier don en argent ayant assez de fonds
        // (on pourrait répartir sur plusieurs dons, mais on simplifie)
        foreach ($dons_argent as $don) {
            if ($don['quantite_restante'] >= $montant_total) {
                $don_argent_id = $don['id'];
                break;
            }
        }

        if ($don_argent_id === null) {
            // Vérifier si le solde total couvre le montant
            $solde_total = 0;
            foreach ($dons_argent as $don) {
                $solde_total += $don['quantite_restante'];
            }
            if ($solde_total < $montant_total) {
                header("Location: /achats/create?error=" . urlencode("Solde en argent insuffisant. Montant nécessaire : " . number_format($montant_total, 0, ',', ' ') . " Ar. Solde disponible : " . number_format($solde_total, 0, ',', ' ') . " Ar."));
                exit();
            }
            // Prendre le premier don pour commencer
            $don_argent_id = $dons_argent[0]['id'];
        }

        // Transaction pour l'achat
        try {
            $this->db->beginTransaction();

            // 1. Créer l'achat
            $this->achat->besoin_id = $besoin_id;
            $this->achat->ville_id = $besoin['ville_id'];
            $this->achat->type_besoin_id = $besoin['type_besoin_id'];
            $this->achat->quantite = $quantite;
            $this->achat->prix_unitaire = $prix_unitaire;
            $this->achat->frais_pourcent = $this->frais_achat;
            $this->achat->montant_total = $montant_total;
            $this->achat->don_argent_id = $don_argent_id;

            $achat_id = $this->achat->create();

            if (!$achat_id) {
                throw new Exception("Erreur lors de la création de l'achat.");
            }

            // 2. Déduire le montant du don en argent
            // Déduire du/des dons en argent
            $montant_a_deduire = $montant_total;
            foreach ($dons_argent as $don) {
                if ($montant_a_deduire <= 0) break;

                $deduction = min($montant_a_deduire, $don['quantite_restante']);
                
                $stmt = $this->db->prepare("UPDATE bngrc_don SET quantite_restante = quantite_restante - ? WHERE id = ?");
                $stmt->execute([$deduction, $don['id']]);

                // Mettre à jour le statut
                $nouveau_restant = $don['quantite_restante'] - $deduction;
                if ($nouveau_restant <= 0) {
                    $this->don->updateStatut($don['id'], 'épuisé');
                } else {
                    $this->don->updateStatut($don['id'], 'partiellement utilisé');
                }

                $montant_a_deduire -= $deduction;
            }

            // 3. Mettre à jour la quantité satisfaite du besoin
            $this->besoin->updateQuantiteSatisfaite($besoin_id, $quantite);

            $this->db->commit();

            header("Location: /achats?success=" . urlencode("Achat effectué avec succès ! Montant total : " . number_format($montant_total, 0, ',', ' ') . " Ar (dont " . $this->frais_achat . "% de frais)."));
            exit();
        } catch (Exception $e) {
            $this->db->rollBack();
            header("Location: /achats/create?error=" . urlencode("Erreur : " . $e->getMessage()));
            exit();
        }
    }

    /**
     * Supprime un achat et restaure le besoin + le don en argent
     */
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

            // 1. Restaurer la quantité satisfaite du besoin
            $this->besoin->reduireQuantiteSatisfaite($achat_data['besoin_id'], $achat_data['quantite']);

            // 2. Restaurer le montant du don en argent
            $this->don->restoreQuantiteRestante($achat_data['don_argent_id'], $achat_data['montant_total']);
            $don_data = $this->don->getById($achat_data['don_argent_id']);
            if ($don_data) {
                $nouveau_restant = $don_data['quantite_restante'] + $achat_data['montant_total'];
                if ($nouveau_restant >= $don_data['quantite']) {
                    $this->don->updateStatut($achat_data['don_argent_id'], 'non distribué');
                } else {
                    $this->don->updateStatut($achat_data['don_argent_id'], 'partiellement utilisé');
                }
            }

            // 3. Supprimer l'achat
            $this->achat->delete($achat_id);

            $this->db->commit();
            header("Location: /achats?success=" . urlencode("Achat supprimé avec succès."));
            exit();
        } catch (Exception $e) {
            $this->db->rollBack();
            header("Location: /achats?error=" . urlencode("Erreur lors de la suppression : " . $e->getMessage()));
            exit();
        }
    }
}
?>
