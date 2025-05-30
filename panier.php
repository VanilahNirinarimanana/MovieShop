<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit();
}

require_once 'model/Client.php';
require_once 'model/Film.php';
require_once 'model/Achat.php';

// Récupérer le panier et les clients
$panier = $_SESSION['panier'] ?? [];
$clients = Client::getAll();

// Calculer le total et préparer les données des films
$total = 0;
$films_panier = [];

foreach ($panier as $id_film => $quantite) {
    $film = Film::getById($id_film);
    if ($film) {
        $sousTotal = $film['prix'] * $quantite;
        $total += $sousTotal;
        $films_panier[] = [
            'id' => $id_film,
            'titre' => $film['titre'],
            'prix' => $film['prix'],
            'quantite' => $quantite,
            'sousTotal' => $sousTotal
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panier - MovieShop</title>
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
                <a href="panier.php" id="panier-link" class="active">
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

    <main class="panier-container">
        <h1>Votre Panier</h1>
        
        <?php if (empty($films_panier)): ?>
            <div class="empty-cart">
                <p>Votre panier est vide</p>
                <a href="films.php" class="btn">Continuer vos achats</a>
            </div>
        <?php else: ?>
            <div class="panier-container">
                <?php foreach ($films_panier as $film): ?>
                    <div class="panier-item" data-id="<?php echo $film['id']; ?>">
                        <div class="film-info">
                            <h3><?php echo htmlspecialchars($film['titre']); ?></h3>
                        </div>
                        <div class="prix-unitaire">
                            <?php echo number_format($film['prix'], 0, ',', ' '); ?> MGA
                        </div>
                        <div class="quantite">
                            <button class="btn-quantite" onclick="updateQuantite(<?php echo $film['id']; ?>, -1)">-</button>
                            <span><?php echo $film['quantite']; ?></span>
                            <button class="btn-quantite" onclick="updateQuantite(<?php echo $film['id']; ?>, 1)">+</button>
                        </div>
                        <div class="sous-total">
                            <?php echo number_format($film['sousTotal'], 0, ',', ' '); ?> MGA
                        </div>
                        <button class="btn-remove" onclick="removeFilm(<?php echo $film['id']; ?>)">×</button>
                    </div>
                <?php endforeach; ?>
                
                <div class="panier-total">
                    <span>Total</span>
                    <span><?php echo number_format($total, 0, ',', ' '); ?> MGA</span>
                </div>
            </div>

            <form action="traitement_achat.php" method="POST" class="form-achat">
                <h2>Informations client</h2>
                
                <div class="form-group">
                    <label for="client_type">Type de client :</label>
                    <select name="client_type" id="client_type" required>
                        <option value="new">Nouveau client</option>
                        <option value="existing">Client existant</option>
                    </select>
                </div>

                <div id="new_client_fields">
                    <div class="form-group">
                        <label for="nom">Nom :</label>
                        <input type="text" name="nom" id="nom" required>
                    </div>
                    <div class="form-group">
                        <label for="prenom">Prénom :</label>
                        <input type="text" name="prenom" id="prenom" required>
                    </div>
                    <div class="form-group">
                        <label for="telephone">Téléphone :</label>
                        <input type="tel" name="telephone" id="telephone" required>
                    </div>
                </div>

                <div id="existing_client_fields" style="display: none;">
                    <div class="form-group">
                        <label for="id_client">Sélectionner un client :</label>
                        <select name="id_client" id="id_client">
                            <option value="">Choisir un client...</option>
                            <?php foreach ($clients as $client): ?>
                                <option value="<?php echo $client['id_client']; ?>">
                                    <?php echo htmlspecialchars($client['nom'] . ' ' . $client['prenom']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <button type="submit" class="btn">Procéder au paiement</button>
            </form>
        <?php endif; ?>
    </main>

    <script src="assets/script/script.js"></script>
    <script src="assets/script/validation.js"></script>
    <script>
        // Gestion du panier
        function updateQuantite(idFilm, delta) {
            fetch('traitement_panier.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    operation: 'update',
                    id_film: idFilm,
                    quantite: delta
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.reload();
                } else {
                    alert(data.message || 'Erreur lors de la mise à jour du panier');
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                alert('Erreur de connexion');
            });
        }

        function removeFilm(idFilm) {
            if (confirm('Voulez-vous vraiment retirer ce film du panier ?')) {
                fetch('traitement_panier.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        operation: 'remove',
                        id_film: idFilm
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        window.location.reload();
                    } else {
                        alert(data.message || 'Erreur lors de la suppression du film');
                    }
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    alert('Erreur de connexion');
                });
            }
        }

        // Gestion du formulaire client
        document.getElementById('client_type').addEventListener('change', function() {
            const newClientFields = document.getElementById('new_client_fields');
            const existingClientFields = document.getElementById('existing_client_fields');
            const newClientInputs = newClientFields.querySelectorAll('input');
            const existingClientSelect = document.getElementById('id_client');

            if (this.value === 'new') {
                newClientFields.style.display = 'block';
                existingClientFields.style.display = 'none';
                newClientInputs.forEach(input => input.required = true);
                existingClientSelect.required = false;
            } else {
                newClientFields.style.display = 'none';
                existingClientFields.style.display = 'block';
                newClientInputs.forEach(input => input.required = false);
                existingClientSelect.required = true;
            }
        });
    </script>
</body>
</html> 