<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit();
}
require_once 'model/Client.php';
require_once 'model/Film.php';
require_once 'model/Achat.php';
require_once 'model/Admin.php';
require_once 'model/database.php';  // Inclusion de la connexion PDO

$nb_clients = count(Client::getAll());
$nb_films = count(Film::getAll());
$achats = Achat::getAll();
$budget = 0;
foreach ($achats as $achat) {
    $film = Film::getById($achat['id_film']);
    if ($film) {
        $budget += $film['prix'] * $achat['quantite'];
    }
}
// Préparer les données pour le graphe (ventes par mois)
$ventes_par_mois = [];
foreach ($achats as $achat) {
    $mois = date('Y-m', strtotime($achat['date_achat']));
    if (!isset($ventes_par_mois[$mois])) $ventes_par_mois[$mois] = 0;
    $ventes_par_mois[$mois] += $achat['quantite'];
}
ksort($ventes_par_mois); // Trie du plus ancien au plus récent
$labels = array_keys($ventes_par_mois);
$values = array_values($ventes_par_mois);

// Ajouter un admin par défaut si la table est vide
if (Admin::count() == 0) {
    Admin::add('Admin', 'Super', 'admin', password_hash('admin123', PASSWORD_DEFAULT));
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>MovieShop - Dashboard</title>
  <link rel="stylesheet" href="assets/style/style.css">
 
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
  <nav class="navbar">
    <div class="logo">🎬 MovieShop</div>
    <ul class="nav-links">
      <li><a href="index.php" class="active">Dashboard</a></li>
      <li><a href="films.php">Films</a></li>
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

  <main class="dashboard">
    <div class="container">
        <?php if (isset($_GET['success']) && $_GET['success'] === 'update'): ?>
            <div class="alert alert-success">
                Vos informations ont été mises à jour avec succès.
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['errors'])): ?>
            <div class="alert alert-danger">
                <?php foreach ($_SESSION['errors'] as $error): ?>
                    <p><?php echo htmlspecialchars($error); ?></p>
                <?php endforeach; ?>
                <?php unset($_SESSION['errors']); ?>
            </div>
        <?php endif; ?>
    </div>
    <div class="dashboard-cards">
      <div class="dashboard-card">
        <h2>BUDGETS</h2>
        <div class="big"><?= number_format($budget, 0, ',', ' ') ?></div>
        <div>
          <!-- Icône SVG sac d'argent -->
          <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#27ae60" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 1v4"/><path d="M17 5H7"/><path d="M5 11h14l-1.68 7.43A2 2 0 0 1 15.36 20H8.64a2 2 0 0 1-1.96-1.57L5 11z"/><path d="M8 11V7h8v4"/></svg>
        </div>
      </div>
      <div class="dashboard-card">
        <h2>CLIENTS</h2>
        <div class="big"><?= $nb_clients ?></div>
        <div>
          <!-- Icône SVG groupe de personnes -->
          <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#2980b9" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="7" r="4"/><path d="M5.5 21v-2a4.5 4.5 0 0 1 9 0v2"/><path d="M17 11a4 4 0 0 1 0 8"/><path d="M7 11a4 4 0 0 0 0 8"/></svg>
        </div>
      </div>
      <div class="dashboard-card">
        <h2>PRODUIT</h2>
        <div class="big"><?= $nb_films ?></div>
        <div>
          <!-- Icône SVG clap de cinéma -->
          <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#e67e22" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="8" width="20" height="14" rx="2"/><path d="M16 8V4a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v4"/><path d="M2 8l20-4"/></svg>
        </div>
      </div>
    </div>
    <div class="dashboard-flex">
        <div class="dashboard-graph">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Ventes mensuelles</h5>
                </div>
                <div class="card-body" style="margin: 50px;">
                    <div style="height: 400px; width: 100%; margin: 0 auto; ">
                        <canvas id="salesChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="dashboard-client">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Client du mois</h5>
                </div>
                <div class="card-body" style="border-radius: 1rem; background: #fff; height: 350px; width: 400px; padding-top: 30px;">
                    <?php
                    require_once 'model/database.php';
                    $sql = "SELECT c.nom, c.prenom, COUNT(*) as total_achats
                            FROM client c
                            JOIN acheter ac ON c.id_client = ac.id_client
                            WHERE MONTH(ac.date_achat) = MONTH(CURRENT_DATE())
                            AND YEAR(ac.date_achat) = YEAR(CURRENT_DATE())
                            GROUP BY c.id_client
                            ORDER BY total_achats DESC
                            LIMIT 1";
                    $stmt = Database::connect()->prepare($sql);
                    $stmt->execute();
                    $client = $stmt->fetch(PDO::FETCH_ASSOC);
                    if ($client) {
                        echo "<div class='confetti-container'>
                                <svg class='confetti-svg' viewBox='0 0 120 30'>
                                    <circle class='confetti c1' cx='15' cy='15' r='4'/>
                                    <circle class='confetti c2' cx='40' cy='10' r='3'/>
                                    <circle class='confetti c3' cx='60' cy='20' r='5'/>
                                    <circle class='confetti c4' cx='90' cy='12' r='3'/>
                                    <circle class='confetti c5' cx='110' cy='18' r='4'/>
                                </svg>
                                <h3 class='client-fete'><span class='fete-emoji'>🎉</span>" . htmlspecialchars($client['prenom'] . " " . $client['nom']) . "</h3>
                                <p class='total-achats'>" . $client['total_achats'] . " films achetés</p>
                                <img src='https://cdn-icons-png.flaticon.com/512/3159/3159066.png' alt='Client content' class='client-happy-img'>
                            </div>";
                    } else {
                        echo "<p>Aucun client trouvé ce mois-ci.</p>";
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
  </main>
  <script src="assets/script/script.js"></script>
  <script>
    // Initialisation du graphique des ventes
    const ctx = document.getElementById('salesChart').getContext('2d');
    const salesChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?= json_encode($labels) ?>,
            datasets: [{
                label: 'Ventes par mois',
                data: <?= json_encode($values) ?>,
                backgroundColor: 'rgba(41, 128, 185, 0.08)',
                borderColor: '#2980b9',
                borderWidth: 2,
                pointBackgroundColor: '#2980b9',
                fill: true,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                }
            },
            scales: {
                y: { 
                    beginAtZero: true,
                    ticks: {
                        maxTicksLimit: 5
                    }
                },
                x: {
                    ticks: {
                        maxTicksLimit: 6
                    }
                }
            }
        }
    });
  </script>
  <style>
    .dashboard-flex {
        display: flex;
        gap: 32px;
        margin-top: 2rem;
        align-items: stretch;
    }
    .dashboard-graph, .dashboard-client {
        flex: 1 1 0;
        min-width: 0;
    }
    .dashboard-graph {
        max-width: 65%;
    }
    .dashboard-client {
        max-width: 35%;
        display: flex;
        align-items: center;
        
    }
    .client-mois {
        text-align: center;
        padding: 20px;
    }
    .client-mois h3 {
        color: #f5f5dc;
        margin-bottom: 15px;
    }
    .total-achats {
        font-size: 1.2em;
        color:rgb(0, 38, 255);
    }
    .dashboard-graph .card-body {
        background: #fff;
        border-radius: 1rem;
    }
    .dashboard-graph canvas {
        background: #fff;
        border-radius: 1rem;
    }
    .card-header {
        /*background: #fff;
        border-bottom: 1px solid #eee;
        border-radius: 1rem 1rem 0 0;*/
        padding: 1rem 1.5rem;
        text-align: center;
    }
    .card-title {
        color:rgb(255, 255, 255);
        font-size: 1.5rem;
        font-weight: bold;
        letter-spacing: 1px;
        margin: 0;
        text-transform: uppercase;
    }
    .fete-emoji {
        display: inline-block;
        font-size: 2.2rem;
        animation: fetePop 1.2s cubic-bezier(.68,-0.55,.27,1.55) 1, feteBounce 1.2s 1.2s infinite;
        vertical-align: middle;
        margin-right: 0.5rem;
    }
    @keyframes fetePop {
        0% {
            transform: translateY(-60px) scale(0.2) rotate(-30deg);
            opacity: 0;
        }
        60% {
            transform: translateY(10px) scale(1.2) rotate(10deg);
            opacity: 1;
        }
        80% {
            transform: translateY(-5px) scale(0.95) rotate(-5deg);
        }
        100% {
            transform: translateY(0) scale(1) rotate(0deg);
            opacity: 1;
        }
    }
    @keyframes feteBounce {
        0%, 100% { transform: translateY(0);}
        50% { transform: translateY(-8px);}
    }
    .confetti-container {
        position: relative;
        display: flex;
        flex-direction: column;
        align-items: center;
    }
    .confetti-svg {
        width: 120px;
        height: 30px;
        margin-bottom: -10px;
        overflow: visible;
    }
    .confetti {
        opacity: 0.8;
        animation: confetti-fall 1.2s cubic-bezier(.68,-0.55,.27,1.55) 1, confetti-bounce 1.2s 1.2s infinite;
    }
    .confetti.c1 { fill: #f39c12; animation-delay: 0s, 0s;}
    .confetti.c2 { fill: #e74c3c; animation-delay: 0.1s, 0.1s;}
    .confetti.c3 { fill: #27ae60; animation-delay: 0.2s, 0.2s;}
    .confetti.c4 { fill: #2980b9; animation-delay: 0.3s, 0.3s;}
    .confetti.c5 { fill: #8e44ad; animation-delay: 0.4s, 0.4s;}

    @keyframes confetti-fall {
        0% { transform: translateY(-40px) scale(0.2); opacity: 0; }
        60% { transform: translateY(10px) scale(1.2); opacity: 1; }
        80% { transform: translateY(-5px) scale(0.95);}
        100% { transform: translateY(0) scale(1); opacity: 1;}
    }
    @keyframes confetti-bounce {
        0%, 100% { transform: translateY(0);}
        50% { transform: translateY(-8px);}
    }
    .client-happy-img {
        display: block;
        margin: 18px auto 0 auto;
        width: 100px;
        height: 100px;
        object-fit: contain;
        filter: drop-shadow(0 2px 8px rgba(41,128,185,0.12));
    }
    .settings-btn {
      color: #fff;
      background: none;
      border: none;
      cursor: pointer;
      font-size: 2rem;
      margin-left: 1.5rem;
      transition: color 0.2s;
    }
    .settings-btn:hover {
      color: var(--accent-primary);
    }
  </style>
</body>
</html> 