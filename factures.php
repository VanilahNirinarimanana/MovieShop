<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit();
}
require_once 'model/Achat.php';
require_once 'model/Client.php';
require_once 'model/Film.php';

// Regrouper les achats par client+date
$search = $_GET['q'] ?? '';
$factures = [];
foreach (Achat::getAll() as $achat) {
    $client = Client::getById($achat['id_client']);
    $date = $achat['date_achat']; // Utilise la date complète avec secondes
    $nom = strtolower($client['nom'] ?? '');
    $prenom = strtolower($client['prenom'] ?? '');
    $searchLower = strtolower($search);
    // Filtrage si recherche
    if ($search && strpos($nom, $searchLower) === false && strpos($prenom, $searchLower) === false && strpos($date, $searchLower) === false) {
        continue;
    }
    $key = $achat['id_client'] . '|' . $date;
    if (!isset($factures[$key])) {
        $factures[$key] = [
            'id_client' => $achat['id_client'],
            'date_achat' => $achat['date_achat'],
            'client' => $client,
            'films' => []
        ];
    }
    $film = Film::getById($achat['id_film']);
    $factures[$key]['films'][] = [
        'titre' => $film['titre'],
        'quantite' => $achat['quantite'],
        'prix' => $film['prix'],
        'sousTotal' => $film['prix'] * $achat['quantite']
    ];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>MovieShop - Factures</title>
  <link rel="stylesheet" href="assets/style/style.css">
</head>
<body>
  <nav class="navbar">
    <div class="logo">🎬 MovieShop</div>
    <ul class="nav-links">
      <li><a href="index.php">Dashboard</a></li>
      <li><a href="films.php">Films</a></li>
      <li><a href="clients.php">Clients</a></li>
      <li style="position:relative;">
        <a href="panier.php" id="panier-link">
          Panier
          <span id="panier-badge" class="panier-badge" style="display:none;">0</span>
        </a>
      </li>
      <li><a href="factures.php" class="active">Factures</a></li>
    </ul>
    <button id="settingsBtn" class="settings-btn" title="Paramètres" style="background:none;border:none;cursor:pointer;font-size:2rem;line-height:1;">
      <span role="img" aria-label="Paramètres">⚙️</span>
    </button>
  </nav>

  <!-- Popup principal paramètres -->
  <div id="settingsModal" class="modal">
    <div class="modal-content" style="min-width:320px;">
      <span class="close" id="closeSettingsModal">&times;</span>
      <h2 style="margin-bottom:1.5rem;">Paramètres</h2>
      <button class="btn" id="btnInfosAdmin" style="width:100%;margin-bottom:1rem;">🔐 Infos admin</button>
      <button class="btn" id="btnModifierInfos" style="width:100%;margin-bottom:1rem;">✏️ Modifier mes informations</button>
      <button class="btn" id="btnHistoriqueVentes" style="width:100%;margin-bottom:1rem;">📊 Historique de ventes</button>
      <button class="btn btn-remove" id="btnLogout" style="width:100%;">🚪 Déconnexion</button>
    </div>
  </div>

  <!-- Popup Infos admin -->
  <div id="infosAdminModal" class="modal">
    <div class="modal-content">
      <span class="close" id="closeInfosAdminModal">&times;</span>
      <h2>Informations Administrateur</h2>
      <?php if (isset($_SESSION['admin'])): ?>
        <p>Nom : <?= htmlspecialchars($_SESSION['admin']['nom']) ?></p>
        <p>Prénom : <?= htmlspecialchars($_SESSION['admin']['prenom']) ?></p>
        <p>Email : <?= htmlspecialchars($_SESSION['admin']['email']) ?></p>
      <?php else: ?>
        <p>Veuillez vous connecter</p>
      <?php endif; ?>
    </div>
  </div>

  <!-- Popup Modifier informations admin -->
  <div id="modifierInfosModal" class="modal">
    <div class="modal-content">
      <span class="close" id="closeModifierInfosModal">&times;</span>
      <h2>Modifier mes informations</h2>
      <form id="modifierInfosForm" method="POST" action="traitement_admin.php">
        <input type="hidden" name="action" value="update_admin">
        <div class="form-group">
          <label for="nom">Nom :</label>
          <input type="text" name="nom" id="nom" value="<?= htmlspecialchars($_SESSION['admin']['nom']) ?>" required>
        </div>
        <div class="form-group">
          <label for="prenom">Prénom :</label>
          <input type="text" name="prenom" id="prenom" value="<?= htmlspecialchars($_SESSION['admin']['prenom']) ?>" required>
        </div>
        <div class="form-group">
          <label for="email">Email :</label>
          <input type="email" name="email" id="email" value="<?= htmlspecialchars($_SESSION['admin']['email']) ?>" required>
        </div>
        <div class="form-group">
          <label for="password">Nouveau mot de passe :</label>
          <input type="password" name="password" id="password" placeholder="Laisser vide pour ne pas changer">
        </div>
        <div class="form-group">
          <label for="confirm_password">Confirmer le mot de passe :</label>
          <input type="password" name="confirm_password" id="confirm_password" placeholder="Laisser vide pour ne pas changer">
        </div>
        <button type="submit" class="btn" style="width:100%;margin-top:1rem;">Enregistrer les modifications</button>
      </form>
    </div>
  </div>

  <!-- Popup Historique de ventes -->
  <div id="historiqueVentesModal" class="modal">
    <div class="modal-content" style="max-width:600px;">
      <span class="close" id="closeHistoriqueVentesModal">&times;</span>
      <h2>Historique de ventes</h2>
      <div style="max-height:350px;overflow-y:auto;">
        <table style="width:100%;border-collapse:collapse;">
          <thead>
            <tr style="background:#222;"><th style="padding:8px;">Client</th><th>Film</th><th>Montant</th></tr>
          </thead>
          <tbody>
            <?php
            require_once 'model/Achat.php';
            require_once 'model/Client.php';
            require_once 'model/Film.php';
            foreach (Achat::getAll() as $achat):
              $client = Client::getById($achat['id_client']);
              $film = Film::getById($achat['id_film']);
              if ($client && $film):
            ?>
              <tr>
                <td style="padding:6px;"> <?= htmlspecialchars($client['prenom'].' '.$client['nom']) ?> </td>
                <td> <?= htmlspecialchars($film['titre']) ?> </td>
                <td> <?= number_format($film['prix'] * $achat['quantite'], 0, ',', ' ') ?> MGA </td>
              </tr>
            <?php endif; endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <main class="factures-list">
    <div class="facture-header-flex">
        <h2 class="factures-title">Liste des factures</h2>
        <form class="facture-search-form" onsubmit="return false;">
            <input type="text" id="facture-search-input" placeholder="Rechercher par nom ou date (AAAA-MM-JJ)">
        </form>
    </div>
    <div class="factures-container" id="factures-container">
      <?php
      foreach($factures as $facture): 
        $client = $facture['client'];
        $total = array_sum(array_column($facture['films'], 'sousTotal'));
      ?>
        <div class="facture-card" data-date="<?= htmlspecialchars($facture['date_achat']) ?>">
          <div class="facture-header">
            <h3>Facture</h3>
            <span class="date"><?= date('d/m/Y', strtotime($facture['date_achat'])) ?></span>
          </div>
          <div class="facture-content">
            <p class="client">Client : <?= htmlspecialchars($client['prenom'].' '.$client['nom']) ?></p>
            <ul class="facture-films">
              <?php foreach($facture['films'] as $film): ?>
                <li>
                  <span class="film">Film : <?= htmlspecialchars($film['titre']) ?></span> |
                  <span class="quantite">Quantité : <?= $film['quantite'] ?></span> |
                  <span class="sous-total">Sous-total : <?= number_format($film['sousTotal'], 0) ?> MGA</span>
                </li>
              <?php endforeach; ?>
            </ul>
            <p class="total">Total : <?= number_format($total, 0) ?> MGA</p>
          </div>
          <div class="facture-actions">
            <a href="view/facture.php?id_client=<?= $facture['id_client'] ?>&date=<?= urlencode($facture['date_achat']) ?>" target="_blank" class="view-btn">Voir facture</a>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </main>
  <script>
    function printFacture(idAchat) {
      const printWindow = window.open('view/facture.php?id_achat=' + idAchat, '_blank');
      printWindow.onload = function() {
        printWindow.print();
      };
    }
  </script>
  <script src="assets/script/script.js"></script>
</body>
</html> 