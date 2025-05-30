<?php
require_once '../model/Client.php';
require_once '../model/Achat.php';
require_once '../model/Film.php';

$id_client = isset($_GET['id_client']) ? intval($_GET['id_client']) : 0;
$achats = Achat::getByClient($id_client);
$client = Client::getById($id_client);
?>
<h2>Historique des achats de <?= htmlspecialchars($client['prenom'].' '.$client['nom']) ?></h2>
<table>
  <tr><th>Date</th><th>Film</th><th>Poster</th><th>Quantité</th><th>Total</th><th>Action</th></tr>
  <?php foreach($achats as $achat):
    $film = Film::getById($achat['id_film']); ?>
    <tr>
      <td><?= $achat['date_achat'] ?></td>
      <td><?= htmlspecialchars($film['titre']) ?></td>
      <td><img src="<?= htmlspecialchars($film['poster']) ?>" alt="Poster" style="height:60px;"></td>
      <td><?= $achat['quantite'] ?></td>
      <td><?= number_format($film['prix'] * $achat['quantite'], 2) ?> €</td>
      <td><a href="facture.php?id_achat=<?= $achat['id_acheter'] ?>">Voir facture</a></td>
    </tr>
  <?php endforeach; ?>
</table> 