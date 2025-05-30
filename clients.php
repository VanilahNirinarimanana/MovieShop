<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit();
}
require_once 'model/Client.php';
$clients = Client::getAll();

// Traitement des actions (ajout, modification, suppression)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validation des champs nom et prénom (lettres uniquement)
    $nom = $_POST['nom'] ?? '';
    $prenom = $_POST['prenom'] ?? '';
    $telephone = $_POST['telephone'] ?? '';
    if (!preg_match('/^[a-zA-ZÀ-ÿ\s-]+$/u', $nom)) {
        die('Erreur : Le nom ne doit contenir que des lettres.');
    }
    if (!preg_match('/^[a-zA-ZÀ-ÿ\s-]+$/u', $prenom)) {
        die('Erreur : Le prénom ne doit contenir que des lettres.');
    }
    // Validation du numéro de téléphone (10 chiffres, commence par 033/034/038/032/037)
    if (!preg_match('/^(033|034|038|032|037)[0-9]{7}$/', $telephone)) {
        die('Erreur : Le numéro doit commencer par 033, 034, 038, 032 ou 037 et contenir 10 chiffres.');
    }
    if ($_POST['action'] === 'add') {
        Client::add($_POST['nom'], $_POST['prenom'], $_POST['telephone']);
    } elseif ($_POST['action'] === 'edit') {
        Client::update($_POST['id_client'], $_POST['nom'], $_POST['prenom'], $_POST['telephone']);
    } elseif ($_POST['action'] === 'delete') {
        Client::delete($_POST['id_client']);
    }
    header('Location: clients.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>MovieShop - Clients</title>
  <link rel="stylesheet" href="assets/style/style.css">
  <style>
    .modal { display: none; position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(0,0,0,0.4); justify-content: center; align-items: center; z-index: 1000; }
    .modal-content { background: #fff; padding: 2rem 3rem; border-radius: 1rem; position: relative; min-width: 300px; }
    .close { position: absolute; top: 1rem; right: 1rem; font-size: 1.5rem; cursor: pointer; }
    .add-btn { background: #2ecc71; color: #fff; border: none; border-radius: 0.5rem; padding: 0.7rem 1.5rem; font-size: 1.1rem; cursor: pointer; margin-bottom: 1.5rem; }
    .add-btn:hover { background: #27ae60; }
    .btn-remove { background: #e74c3c; color: #fff; border: none; border-radius: 0.5rem; padding: 0.5rem 1.2rem; font-size: 1rem; cursor: pointer; margin-left: 0.5rem; min-width: 100px; }
    .btn-remove:hover { background: #c0392b; }
    .clients-table-container { margin-top: 2rem; }
    .clients-table { width: 100%; border-collapse: collapse; background: #fff; border-radius: 1rem; overflow: hidden; box-shadow: 0 2px 8px rgba(30,60,114,0.07); }
    .clients-table th, .clients-table td { padding: 1rem; text-align: left; }
    .clients-table th { background: #f8f9fa; color: #1e3c72; font-weight: 600; }
    .clients-table tr:not(:last-child) { border-bottom: 1px solid #eee; }
    .clients-table td { vertical-align: middle; }
    @media (max-width: 700px) {
      .clients-table th, .clients-table td { padding: 0.5rem; font-size: 0.95rem; }
      .modal-content { padding: 1rem; }
    }
  </style>
</head>
<body>
  <nav class="navbar">
    <div class="logo">🎬 MovieShop</div>
    <ul class="nav-links">
      <li><a href="index.php">Dashboard</a></li>
      <li><a href="films.php">Films</a></li>
      <li><a href="clients.php" class="active">Clients</a></li>
      <li style="position:relative;">
        <a href="panier.php" id="panier-link">
          Panier
          <span id="panier-badge" class="panier-badge" style="display:none;">0</span>
        </a>
      </li>
      <li><a href="factures.php">Factures</a></li>
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

  <main class="clients-list">
    <h2>Liste des clients</h2>
    <button onclick="showAddClientForm()" class="add-btn">Ajouter un client</button>
    <div class="clients-table-container">
      <table class="clients-table">
        <thead>
          <tr>
            <th>Nom</th>
            <th>Prénom</th>
            <th>Contact</th>
            <th style="text-align:right;">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach($clients as $client): ?>
            <tr>
              <td><?= htmlspecialchars($client['nom']) ?></td>
              <td><?= htmlspecialchars($client['prenom']) ?></td>
              <td><?= htmlspecialchars($client['contact']) ?></td>
              <td style="text-align:right;">
                <div class="actions-btns" style="display:flex; gap:0.5rem; align-items:center; justify-content:flex-end;">
                  <button class="btn" onclick="editClient(<?= $client['id_client'] ?>, '<?= htmlspecialchars($client['nom'], ENT_QUOTES) ?>', '<?= htmlspecialchars($client['prenom'], ENT_QUOTES) ?>', '<?= htmlspecialchars($client['contact'], ENT_QUOTES) ?>')">Modifier</button>
                  <form method="POST" style="display:inline;" onsubmit="return confirm('Supprimer ce client ?');">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id_client" value="<?= $client['id_client'] ?>">
                    <button type="submit" class="btn btn-remove">Supprimer</button>
                  </form>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </main>

  <!-- Modal formulaire ajout/modification client -->
  <div id="clientFormModal" class="modal">
    <div class="modal-content">
      <span class="close" onclick="closeClientForm()">&times;</span>
      <h2 id="clientFormTitle">Ajouter un client</h2>
      <form id="clientForm" method="POST">
        <input type="hidden" name="action" id="clientFormAction" value="add">
        <input type="hidden" name="id_client" id="clientFormId">
        <div class="form-group">
          <label for="clientFormNom">Nom :</label>
          <input type="text" name="nom" id="clientFormNom" required>
        </div>
        <div class="form-group">
          <label for="clientFormPrenom">Prénom :</label>
          <input type="text" name="prenom" id="clientFormPrenom" required>
        </div>
        <div class="form-group">
          <label for="clientFormTelephone">Téléphone :</label>
          <input type="text" name="telephone" id="clientFormTelephone" required>
        </div>
        <button type="submit" class="btn">Valider</button>
      </form>
    </div>
  </div>

  <script src="assets/script/script.js"></script>
  <script src="assets/script/validation.js"></script>
  <script>
    // Gestion du formulaire modal client
    function showAddClientForm() {
      document.getElementById('clientFormTitle').textContent = 'Ajouter un client';
      document.getElementById('clientFormAction').value = 'add';
      document.getElementById('clientFormId').value = '';
      document.getElementById('clientFormNom').value = '';
      document.getElementById('clientFormPrenom').value = '';
      document.getElementById('clientFormTelephone').value = '';
      document.getElementById('clientFormModal').style.display = 'flex';
    }
    function editClient(id, nom, prenom, telephone) {
      document.getElementById('clientFormTitle').textContent = 'Modifier le client';
      document.getElementById('clientFormAction').value = 'edit';
      document.getElementById('clientFormId').value = id;
      document.getElementById('clientFormNom').value = nom;
      document.getElementById('clientFormPrenom').value = prenom;
      document.getElementById('clientFormTelephone').value = telephone;
      document.getElementById('clientFormModal').style.display = 'flex';
    }
    function closeClientForm() {
      document.getElementById('clientFormModal').style.display = 'none';
    }
    // Fermer le modal si clic en dehors
    document.addEventListener('click', function(event) {
      const modal = document.getElementById('clientFormModal');
      if (modal && event.target === modal) {
        modal.style.display = 'none';
      }
    });
  </script>
</body>
</html> 