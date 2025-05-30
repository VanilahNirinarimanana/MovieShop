<?php
require_once __DIR__ . '/../model/Achat.php';

class AchatController {
    public static function addAchat($id_client, $id_film, $quantite) {
        return Achat::add($id_client, $id_film, $quantite);
    }
    public static function getAchat($id_achat) {
        return Achat::getById($id_achat);
    }
    public static function getAchatsByClient($id_client) {
        return Achat::getByClient($id_client);
    }
}
?> 