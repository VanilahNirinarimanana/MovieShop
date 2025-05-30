<?php
require_once '../model/Client.php';
require_once '../model/Achat.php';
require_once '../model/Film.php';

// Récupérer id_client et date via GET
$id_client = isset($_GET['id_client']) ? intval($_GET['id_client']) : 0;
$date = isset($_GET['date']) ? $_GET['date'] : '';
$client = Client::getById($id_client);
if (!$client) {
    echo "<p style='color:red;'>Client introuvable.</p>";
    exit;
}
$achats = array_filter(Achat::getAll(), function($a) use ($id_client, $date) {
    return $a['id_client'] == $id_client && $a['date_achat'] == $date;
});
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Facture MovieShop</title>
    <link rel="stylesheet" href="../assets/style/facture_view.css">
</head>
<body>
    <div class="facture-container">
        <div class="facture-header">
            <h2>Facture MovieShop</h2>
        </div>

        <div class="facture-section">
            <h3>Informations Client</h3>
            <p><?= htmlspecialchars($client['prenom'].' '.$client['nom']) ?><br>
               Contact : <?= htmlspecialchars($client['contact']) ?></p>
        </div>

        <div class="facture-section">
            <h3>Détails de l'achat</h3>
            <table class="facture-table">
                <tr>
                    <th>Film</th>
                    <th>Genre</th>
                    <th>Poster</th>
                    <th>Prix</th>
                    <th>Quantité</th>
                    <th>Sous-total</th>
                </tr>
                <?php $total = 0; foreach($achats as $achat): 
                    $film = Film::getById($achat['id_film']);
                    $sousTotal = $film['prix'] * $achat['quantite'];
                    $total += $sousTotal;
                ?>
                <tr>
                    <td><?= htmlspecialchars($film['titre']) ?></td>
                    <td><?= htmlspecialchars($film['genre']) ?></td>
                    <td><img src="../assets/images/<?= htmlspecialchars($film['poster']) ?>" alt="Poster" style="height:60px;"></td>
                    <td><?= number_format($film['prix'],0) ?> MGA</td>
                    <td><?= $achat['quantite'] ?></td>
                    <td><?= number_format($sousTotal,0) ?> MGA</td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>

        <div class="facture-total">
            <strong>Total : <?= number_format($total,0) ?> MGA</strong>
        </div>

        <div class="facture-date">
            Date d'achat : <?= date('d/m/Y', strtotime($date)) ?>
        </div>

        <button onclick="window.print()" class="print-button">Imprimer la facture</button>
    </div>
</body>
</html> 