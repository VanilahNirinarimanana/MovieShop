<?php
require_once 'Database.php';

class Achat {
    public static function add($id_client, $id_film, $quantite, $date_achat = null) {
        $db = Database::connect();
        if ($date_achat) {
            $sql = "INSERT INTO acheter (id_client, id_film, quantite, date_achat) VALUES (?, ?, ?, ?)";
            return $db->prepare($sql)->execute([$id_client, $id_film, $quantite, $date_achat]);
        } else {
            $sql = "INSERT INTO acheter (id_client, id_film, quantite, date_achat) VALUES (?, ?, ?, NOW())";
            return $db->prepare($sql)->execute([$id_client, $id_film, $quantite]);
        }
    }

    public static function getById($id_acheter) {
        $db = Database::connect();
        $sql = "SELECT * FROM acheter WHERE id_acheter = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$id_acheter]);
        return $stmt->fetch();
    }

    public static function getByClient($id_client) {
        $db = Database::connect();
        $sql = "SELECT * FROM acheter WHERE id_client = ? ORDER BY date_achat DESC";
        $stmt = $db->prepare($sql);
        $stmt->execute([$id_client]);
        return $stmt->fetchAll();
    }

    public static function getAll() {
        $db = Database::connect();
        $sql = "SELECT * FROM acheter ORDER BY date_achat DESC";
        $stmt = $db->query($sql);
        return $stmt->fetchAll();
    }

    public static function getLastId() {
        $db = Database::connect();
        $sql = "SELECT MAX(id_acheter) as last_id FROM acheter";
        $stmt = $db->query($sql);
        $result = $stmt->fetch();
        return $result['last_id'];
    }
}
?> 