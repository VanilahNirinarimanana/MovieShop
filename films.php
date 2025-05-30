<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit();
}
require_once 'model/Film.php';
require_once 'controller/FilmController.php';

// Gestion de la recherche
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$success_message = null;
if (isset($_GET['success'])) {
    if ($_GET['success'] === 'add') $success_message = 'Film ajouté avec succès !';
    if ($_GET['success'] === 'edit') $success_message = 'Film modifié avec succès !';
    if ($_GET['success'] === 'delete') $success_message = 'Film supprimé avec succès !';
}
if ($search !== '') {
    $films = array_filter(Film::getAll(), function($film) use ($search) {
        return stripos($film['titre'], $search) !== false;
    });
} else {
    $films = Film::getAll();
}

// Traitement de l'ajout et modification de film
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    switch ($_POST['action']) {
        case 'add_film':
            try {
                if (empty($_POST['titre']) || empty($_POST['prix']) || empty($_FILES['poster']) || empty($_POST['genre'])) {
                    throw new Exception('Tous les champs sont requis');
                }
                $titre = trim($_POST['titre']);
                $prix = floatval($_POST['prix']);
                $genre = trim($_POST['genre']);
                if ($prix <= 0) {
                    throw new Exception('Le prix doit être supérieur à 0');
                }
                $id_film = FilmController::addFilm($titre, $prix, $_FILES['poster'], $genre);
                header('Location: films.php?success=add');
                exit;
            } catch (Exception $e) {
                $error_message = $e->getMessage();
            }
            break;
        case 'edit_film':
            try {
                if (empty($_POST['id_film']) || empty($_POST['titre']) || empty($_POST['prix']) || empty($_POST['genre'])) {
                    throw new Exception('Tous les champs sont requis');
                }
                $id_film = intval($_POST['id_film']);
                $titre = trim($_POST['titre']);
                $prix = floatval($_POST['prix']);
                $genre = trim($_POST['genre']);
                $poster = null;
                if (isset($_FILES['poster']) && $_FILES['poster']['error'] === 0) {
                    $poster = $_FILES['poster'];
                }
                FilmController::updateFilm($id_film, $titre, $prix, $genre, $poster);
                header('Location: films.php?success=edit');
                exit;
            } catch (Exception $e) {
                $error_message = $e->getMessage();
            }
            break;
        case 'delete_film':
            if (isset($_POST['id_film'])) {
                try {
                    FilmController::deleteFilm($_POST['id_film']);
                    header('Location: films.php?success=delete');
                    exit;
                } catch (Exception $e) {
                    $error_message = $e->getMessage();
                }
            }
            break;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>MovieShop - Films</title>
  <link rel="stylesheet" href="assets/style/style.css">
</head>
<body>
  <nav class="navbar">
    <div class="logo">🎬 MovieShop</div>
    <ul class="nav-links">
      <li><a href="index.php">Dashboard</a></li>
      <li><a href="films.php" class="active">Films</a></li>
      <li><a href="clients.php">Clients</a></li>
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

  <!-- Barre de recherche -->
  <div class="search-bar-container">
    <form method="GET" action="films.php" class="search-form">
      <input type="text" name="search" class="search-bar" placeholder="Rechercher un film..." value="<?= htmlspecialchars($search) ?>">
      <button type="submit" class="search-bar-btn">Rechercher</button>
    </form>
  </div>
  <main class="films-list">
    <h2>Liste des films</h2>
    
    <!-- Formulaire d'ajout de film -->
    <div class="add-film-form">
      <h3>Ajouter un nouveau film</h3>
      <?php if (isset($success_message)): ?>
        <div class="success-message"><?= htmlspecialchars($success_message) ?></div>
      <?php endif; ?>
      <?php if (isset($error_message)): ?>
        <div class="error-message"><?= htmlspecialchars($error_message) ?></div>
      <?php endif; ?>
      
      <button onclick="showAddFilmForm()" class="add-btn">Ajouter un film</button>
    </div>

    <!-- Modal formulaire ajout/modification film -->
    <div id="filmFormModal" class="modal">
      <div class="modal-content">
        <span class="close" onclick="closeFilmForm()">&times;</span>
        <h2 id="filmFormTitle">Ajouter un film</h2>
        <form id="filmForm" method="POST" enctype="multipart/form-data">
          <input type="hidden" name="action" id="filmFormAction" value="add_film">
          <input type="hidden" name="id_film" id="filmFormId">
          <div class="form-group">
            <label for="filmFormTitre">Titre du film :</label>
            <input type="text" name="titre" id="filmFormTitre" required>
          </div>
          <div class="form-group">
            <label for="filmFormGenre">Genre :</label>
            <select name="genre" id="filmFormGenre" required>
              <option value="">Sélectionnez un genre</option>
              <option value="Action">Action</option>
              <option value="Aventure">Aventure</option>
              <option value="Comédie">Comédie</option>
              <option value="Drame">Drame</option>
              <option value="Fantastique">Fantastique</option>
              <option value="Horreur">Horreur</option>
              <option value="Science-fiction">Science-fiction</option>
              <option value="Thriller">Thriller</option>
            </select>
          </div>
          <div class="form-group">
            <label for="filmFormPrix">Prix (MGA) :</label>
            <input type="number" name="prix" id="filmFormPrix" step="100" min="0" required>
          </div>
          <div class="form-group">
            <label for="filmFormPoster">Affiche du film :</label>
            <input type="file" name="poster" id="filmFormPoster" accept="image/*">
            <small id="currentPoster"></small>
          </div>
          <button type="submit" class="add-btn">Valider</button>
        </form>
      </div>
    </div>

    <div class="films-container" id="filmsContainer">
      <?php foreach($films as $film): ?>
        <div class="film-card" data-id="<?= $film['id_film'] ?>">
          <div class="film-poster-container">
            <?php if (!empty($film['poster']) && file_exists('assets/images/' . $film['poster'])): ?>
              <img src="assets/images/<?= htmlspecialchars($film['poster']) ?>" alt="<?= htmlspecialchars($film['titre']) ?>" class="film-poster">
            <?php else: ?>
              <div class="no-image">
                <span>Image non disponible</span>
              </div>
            <?php endif; ?>
          </div>
          <div class="film-info">
            <h3><?= htmlspecialchars($film['titre']) ?></h3>
            <p class="genre"><?= htmlspecialchars($film['genre']) ?></p>
            <p class="price"><?= number_format($film['prix'],0) ?> MGA</p>
          </div>
          <div class="film-actions">
            <div class="action-buttons">
              <button type="button" class="edit-btn" onclick="editFilm(<?= $film['id_film'] ?>, '<?= htmlspecialchars($film['titre'], ENT_QUOTES) ?>', '<?= htmlspecialchars($film['genre'], ENT_QUOTES) ?>', '<?= $film['prix'] ?>', '<?= htmlspecialchars($film['poster'], ENT_QUOTES) ?>')">Modifier</button>
              <form method="POST" class="action-form" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce film ?');">
                <input type="hidden" name="action" value="delete_film">
                <input type="hidden" name="id_film" value="<?= $film['id_film'] ?>">
                <button type="submit" class="delete-btn">Supprimer</button>
              </form>
            </div>
            <button type="button" class="add-cart-btn">Ajouter au panier</button>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </main>
  <script src="assets/script/script.js"></script>
  <script src="assets/script/validation.js"></script>
  <script>
  // Gestion du formulaire modal film
  function showAddFilmForm() {
    document.getElementById('filmFormTitle').textContent = 'Ajouter un film';
    document.getElementById('filmFormAction').value = 'add_film';
    document.getElementById('filmFormId').value = '';
    document.getElementById('filmFormTitre').value = '';
    document.getElementById('filmFormGenre').value = '';
    document.getElementById('filmFormPrix').value = '';
    document.getElementById('filmFormPoster').value = '';
    document.getElementById('currentPoster').textContent = '';
    document.getElementById('filmFormModal').style.display = 'flex';
  }
  function editFilm(id, titre, genre, prix, poster) {
    document.getElementById('filmFormTitle').textContent = 'Modifier le film';
    document.getElementById('filmFormAction').value = 'edit_film';
    document.getElementById('filmFormId').value = id;
    document.getElementById('filmFormTitre').value = titre;
    document.getElementById('filmFormGenre').value = genre;
    document.getElementById('filmFormPrix').value = prix;
    document.getElementById('filmFormPoster').value = '';
    document.getElementById('currentPoster').textContent = poster ? 'Affiche actuelle : ' + poster : '';
    document.getElementById('filmFormModal').style.display = 'flex';
  }
  function closeFilmForm() {
    document.getElementById('filmFormModal').style.display = 'none';
  }
  document.addEventListener('click', function(event) {
    const modal = document.getElementById('filmFormModal');
    if (modal && event.target === modal) {
      modal.style.display = 'none';
    }
  });
  // Recharge la page après soumission du formulaire d'ajout/modification film
  document.getElementById('filmForm').onsubmit = function() {
    setTimeout(() => window.location.reload(), 100);
  };
  </script>
</body>
</html> 