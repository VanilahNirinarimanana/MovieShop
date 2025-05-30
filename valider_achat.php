<?php
// Ce script reçoit un POST JSON avec le panier et les infos client
require_once 'controller/ClientController.php';
require_once 'controller/AchatController.php';
require_once 'model/Client.php';
require_once 'model/Film.php';

header('Content-Type: application/json');

try {
    // Récupérer les données JSON envoyées par JS
    $data = json_decode(file_get_contents('php://input'), true);

    if (!$data) {
        throw new Exception('Données invalides');
    }

    // Vérifier que le panier n'est pas vide
    if (empty($data['panier'])) {
        throw new Exception('Le panier est vide');
    }

    // Gestion du client
    if ($data['client_type'] === 'new') {
        // Validation des données du nouveau client
        if (empty($data['nom']) || empty($data['prenom']) || empty($data['contact'])) {
            throw new Exception('Tous les champs du client sont requis');
        }
        $id_client = ClientController::addClient($data['nom'], $data['prenom'], $data['contact']);
    } else {
        $id_client = intval($data['id_client']);
        // Vérifier si le client existe
        if (!Client::getById($id_client)) {
            throw new Exception('Client non trouvé');
        }
    }

    // Insertion des achats
    $id_achats = [];
    foreach ($data['panier'] as $item) {
        // Vérifier si le film existe
        if (!Film::getById($item['id'])) {
            throw new Exception('Film non trouvé: ' . $item['id']);
        }
        
        // Vérifier la quantité
        if ($item['quantite'] <= 0) {
            throw new Exception('Quantité invalide pour le film: ' . $item['id']);
        }

        $id_achat = AchatController::addAchat($id_client, $item['id'], $item['quantite']);
        if (!$id_achat) {
            throw new Exception('Erreur lors de l\'ajout de l\'achat');
        }
        $id_achats[] = $id_achat;
    }

    // On retourne l'id du dernier achat pour la facture
    echo json_encode([
        'success' => true, 
        'id_achat' => end($id_achats),
        'message' => 'Achat validé avec succès'
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?> 