<?php
require_once __DIR__ . '/../model/Client.php';

class ClientController {
    public static function getClients() {
        return Client::getAll();
    }
    public static function addClient($nom, $prenom, $contact) {
        return Client::add($nom, $prenom, $contact);
    }
}
?> 