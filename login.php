<?php
session_start();
require_once 'model/Admin.php';

// Si déjà connecté, rediriger vers l'accueil
if (isset($_SESSION['admin'])) {
    header('Location: index.php');
    exit();
}

// Traitement du formulaire de connexion
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    $admin = Admin::getByEmail($username);
    if ($admin && password_verify($password, $admin['mdp_admin'])) {
        $_SESSION['admin'] = [
            'id' => $admin['id_admin'],
            'nom' => $admin['nom_admin'],
            'prenom' => $admin['prenom_admin'],
            'email' => $admin['email']
        ];
        header('Location: index.php');
        exit();
    } else {
        $error = "Nom d'utilisateur ou mot de passe incorrect";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion - MovieShop</title>
    <link rel="stylesheet" href="assets/style/style.css">
    <style>
        .login-container {
            max-width: 400px;
            margin: 100px auto;
            padding: 2rem;
            background: white;
            border-radius: 1rem;
            box-shadow: 0 2px 12px rgba(30,60,114,0.08);
        }

        .login-container h2 {
            color: #1e3c72;
            margin-bottom: 1.5rem;
            text-align: center;
        }

        .login-form {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .login-form label {
            color: #333;
            font-weight: 500;
        }

        .login-form input {
            width: 100%;
            padding: 0.7rem;
            border: 1px solid #ddd;
            border-radius: 0.5rem;
            font-size: 1rem;
        }

        .login-btn {
            background: #1e3c72;
            color: white;
            padding: 0.8rem;
            border: none;
            border-radius: 0.5rem;
            font-size: 1rem;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .login-btn:hover {
            background: #2a5298;
        }

        .error-message {
            color: #dc3545;
            text-align: center;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Connexion MovieShop</h2>
        
        <?php if (isset($error)): ?>
            <div class="error-message"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form class="login-form" method="POST">
            <label>
                Email :
                <input type="text" name="username" required>
            </label>
            <label>
                Mot de passe :
                <input type="password" name="password" required>
            </label>
            <button type="submit" class="login-btn">Se connecter</button>
        </form>
        <div style="text-align:center; margin-top:1rem;">
            <a href="register.php" style="color:#2a5298; text-decoration:underline;">Créer un compte</a>
        </div>
    </div>
    <script src="assets/script/validation.js"></script>
</body>
</html> 