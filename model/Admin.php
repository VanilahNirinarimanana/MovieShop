<?php
require_once 'Database.php';

class Admin {
    public static function getByEmail($email) {
        $pdo = Database::connect();
        $stmt = $pdo->prepare('SELECT * FROM admin WHERE email = ?');
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function add($nom, $prenom, $email, $mdp) {
        $pdo = Database::connect();
        $stmt = $pdo->prepare('INSERT INTO admin (nom_admin, prenom_admin, email, mdp_admin) VALUES (?, ?, ?, ?)');
        return $stmt->execute([$nom, $prenom, $email, $mdp]);
    }

    public static function count() {
        $pdo = Database::connect();
        $stmt = $pdo->query('SELECT COUNT(*) FROM admin');
        return $stmt->fetchColumn();
    }

    public static function getById($id) {
        $pdo = Database::connect();
        $stmt = $pdo->prepare('SELECT * FROM admin WHERE id_admin = ?');
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
} 