<?php
require_once 'Database.php';

class Client {
    public static function getAll() {
        $pdo = Database::connect();
        $stmt = $pdo->query("SELECT * FROM client");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getById($id) {
        $pdo = Database::connect();
        $stmt = $pdo->prepare("SELECT * FROM client WHERE id_client = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function add($nom, $prenom, $contact) {
        $pdo = Database::connect();
        $stmt = $pdo->prepare("INSERT INTO client (nom, prenom, contact) VALUES (?, ?, ?)");
        $stmt->execute([$nom, $prenom, $contact]);
        return $pdo->lastInsertId();
    }

    public static function update($id_client, $nom, $prenom, $contact) {
        $pdo = Database::connect();
        $stmt = $pdo->prepare("UPDATE client SET nom = ?, prenom = ?, contact = ? WHERE id_client = ?");
        return $stmt->execute([$nom, $prenom, $contact, $id_client]);
    }

    public static function delete($id_client) {
        $pdo = Database::connect();
        $stmt = $pdo->prepare("DELETE FROM client WHERE id_client = ?");
        return $stmt->execute([$id_client]);
    }
}
?> 