<?php
session_start();
require_once 'model/Admin.php';

// Si déjà connecté, rediriger vers l'accueil
if (isset($_SESSION['admin'])) {
    header('Location: index.php');
    exit();
}

$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validation des champs nom et prénom (lettres uniquement)
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    if (!preg_match('/^[a-zA-ZÀ-ÿ\s-]+$/u', $nom)) {
        $error = 'Le nom ne doit contenir que des lettres.';
    } elseif (!preg_match('/^[a-zA-ZÀ-ÿ\s-]+$/u', $prenom)) {
        $error = 'Le prénom ne doit contenir que des lettres.';
    }
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($nom && $prenom && $email && $password) {
        // Vérifier si l'email existe déjà
        $admin = Admin::getByEmail($email);
        if ($admin) {
            $error = "Cet email est déjà utilisé.";
        } else {
            Admin::add($nom, $prenom, $email, password_hash($password, PASSWORD_DEFAULT));
            header('Location: login.php?register=success');
            exit();
        }
    } else {
        $error = "Tous les champs sont obligatoires.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Créer un compte admin - MovieShop</title>
    <link rel="stylesheet" href="assets/style/style.css">
    <style>
        .register-container {
            max-width: 400px;
            margin: 100px auto;
            padding: 2rem;
            background: white;
            border-radius: 1rem;
            box-shadow: 0 2px 12px rgba(30,60,114,0.08);
        }
        .register-container h2 {
            color: #1e3c72;
            margin-bottom: 1.5rem;
            text-align: center;
        }
        .register-form {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }
        .register-form label {
            color: #333;
            font-weight: 500;
        }
        .register-form input {
            width: 100%;
            padding: 0.7rem;
            border: 1px solid #ddd;
            border-radius: 0.5rem;
            font-size: 1rem;
        }
        .register-btn {
            background: #2a5298;
            color: white;
            padding: 0.8rem;
            border: none;
            border-radius: 0.5rem;
            font-size: 1rem;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        .register-btn:hover {
            background: #1e3c72;
        }
        .error-message {
            color: #dc3545;
            text-align: center;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <h2>Créer un compte administrateur</h2>
        <?php if ($error): ?>
            <div class="error-message"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form class="register-form" method="POST">
            <label>
                Nom :
                <input type="text" name="nom" required>
            </label>
            <label>
                Prénom :
                <input type="text" name="prenom" required>
            </label>
            <label>
                Email :
                <input type="email" name="email" required>
            </label>
            <label>
                Mot de passe :
                <input type="password" name="password" required>
            </label>
            <button type="submit" class="register-btn">Créer le compte</button>
        </form>
        <div style="text-align:center; margin-top:1rem;">
            <a href="login.php" style="color:#2a5298; text-decoration:underline;">Retour à la connexion</a>
        </div>
    </div>
    <!-- Validation JS universelle pour nom, prénom et téléphone -->
    <script src="assets/script/validation.js"></script>
</body>
</html> 