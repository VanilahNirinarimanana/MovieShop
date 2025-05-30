<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit();
}

require_once 'model/Admin.php';
require_once 'model/Database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_admin') {
    $id_admin = $_SESSION['admin']['id'];
    $nom = $_POST['nom'] ?? '';
    $prenom = $_POST['prenom'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validation des données
    $errors = [];
    
    if (empty($nom)) {
        $errors[] = "Le nom est requis";
    }
    if (empty($prenom)) {
        $errors[] = "Le prénom est requis";
    }
    if (empty($email)) {
        $errors[] = "L'email est requis";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "L'email n'est pas valide";
    }

    // Vérifier si l'email existe déjà pour un autre admin
    $admin = Admin::getByEmail($email);
    if ($admin && $admin['id_admin'] != $id_admin) {
        $errors[] = "Cet email est déjà utilisé par un autre administrateur";
    }

    // Validation du mot de passe si fourni
    if (!empty($password)) {
        if ($password !== $confirm_password) {
            $errors[] = "Les mots de passe ne correspondent pas";
        }
        if (strlen($password) < 6) {
            $errors[] = "Le mot de passe doit contenir au moins 6 caractères";
        }
    }

    if (empty($errors)) {
        try {
            $pdo = Database::connect();
            
            // Préparer la requête SQL
            if (!empty($password)) {
                $sql = "UPDATE admin SET nom_admin = ?, prenom_admin = ?, email = ?, mdp_admin = ? WHERE id_admin = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$nom, $prenom, $email, password_hash($password, PASSWORD_DEFAULT), $id_admin]);
            } else {
                $sql = "UPDATE admin SET nom_admin = ?, prenom_admin = ?, email = ? WHERE id_admin = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$nom, $prenom, $email, $id_admin]);
            }

            // Mettre à jour la session
            $_SESSION['admin']['nom'] = $nom;
            $_SESSION['admin']['prenom'] = $prenom;
            $_SESSION['admin']['email'] = $email;

            header('Location: ' . $_SERVER['HTTP_REFERER'] . '?success=update');
            exit();
        } catch (PDOException $e) {
            $errors[] = "Erreur lors de la mise à jour : " . $e->getMessage();
        }
    }

    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
        header('Location: ' . $_SERVER['HTTP_REFERER'] . '?error=update');
        exit();
    }
} else {
    header('Location: index.php');
    exit();
} 