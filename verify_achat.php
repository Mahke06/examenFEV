<?php
require 'vendor/autoload.php';
$app = Flight::app();
$c = require 'app/config/config.php';
$db = new PDO('mysql:host='.$c['database']['host'].';dbname='.$c['database']['dbname'], $c['database']['user'], $c['database']['password']);
$db->exec('set names utf8');

echo "=== VERIFICATION POST-ACHAT ===\n\n";

// Besoin #2 (huile, Antananarivo) - should have quantite_satisfaite = 50
$stmt = $db->query("SELECT b.id, v.nom, tb.nom as type_nom, b.quantite_demandee, b.quantite_satisfaite FROM bngrc_besoin b LEFT JOIN bngrc_ville v ON b.ville_id = v.id LEFT JOIN bngrc_type_besoin tb ON b.type_besoin_id = tb.id WHERE b.id = 2");
$b = $stmt->fetch(PDO::FETCH_ASSOC);
echo "Besoin #2: {$b['nom']} | {$b['type_nom']} | Demande:{$b['quantite_demandee']} | Satisfait:{$b['quantite_satisfaite']}\n";

// Don #9 (Especes) - should have deduced 110,000
$stmt = $db->query("SELECT d.id, tb.nom, d.quantite, d.quantite_restante, d.statut FROM bngrc_don d LEFT JOIN bngrc_type_besoin tb ON d.type_besoin_id = tb.id WHERE d.id = 9");
$d = $stmt->fetch(PDO::FETCH_ASSOC);
echo "Don #9: {$d['nom']} | Qte:{$d['quantite']} | Reste:{$d['quantite_restante']} | Statut:{$d['statut']}\n";

// Achat record
echo "\nAchats:\n";
$stmt = $db->query("SELECT a.*, v.nom as ville, tb.nom as type_nom FROM bngrc_achat a LEFT JOIN bngrc_ville v ON a.ville_id = v.id LEFT JOIN bngrc_type_besoin tb ON a.type_besoin_id = tb.id ORDER BY a.id");
foreach ($stmt as $a) {
    echo "  Achat #{$a['id']} | {$a['ville']} | {$a['type_nom']} | Qte:{$a['quantite']} | Prix:{$a['prix_unitaire']} | Frais:{$a['frais_pourcent']}% | Total:{$a['montant_total']} | DonArgent:#$a[don_argent_id] | {$a['date_achat']}\n";
}

echo "\nVerification:\n";
echo "  50 x 2000 = 100000 HT + 10% = 110000 TTC\n";
echo "  Don reste: " . (50000000 - 110000) . " = 49890000\n";
echo "  Besoin satisfait: 0 + 50 = 50\n";
