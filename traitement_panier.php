<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Activer la journalisation des erreurs
ini_set('log_errors', 1);
ini_set('error_log', 'C:/wamp64/logs/php_error.log');

// Journaliser l'état initial de la session
error_log("Session ID: " . session_id());
error_log("État initial de la session : " . print_r($_SESSION, true));

// Initialiser le panier s'il n'existe pas
if (!isset($_SESSION['panier'])) {
    $_SESSION['panier'] = [];
    error_log("Panier initialisé");
}

// Récupérer les données JSON
$json = file_get_contents('php://input');
$data = json_decode($json, true);

error_log("Données reçues : " . print_r($data, true));

// Vérifier le type d'opération
$operation = isset($data['operation']) ? $data['operation'] : 'add';

switch ($operation) {
    case 'add':
        // Ajout d'un film au panier
        if (isset($data['id_film']) && is_numeric($data['id_film'])) {
            $id_film = (int)$data['id_film'];
            
            if (isset($_SESSION['panier'][$id_film])) {
                $_SESSION['panier'][$id_film]++;
                error_log("Quantité incrémentée pour le film $id_film");
            } else {
                $_SESSION['panier'][$id_film] = 1;
                error_log("Nouveau film ajouté au panier : $id_film");
            }
            
            $response = [
                'success' => true,
                'message' => 'Film ajouté au panier',
                'panier' => $_SESSION['panier']
            ];
        } else {
            $response = [
                'success' => false,
                'message' => 'ID de film invalide'
            ];
        }
        break;

    case 'update':
        // Mise à jour de la quantité
        if (isset($data['id_film']) && is_numeric($data['id_film']) && isset($data['quantite'])) {
            $id_film = (int)$data['id_film'];
            $quantite = (int)$data['quantite'];
            if (isset($_SESSION['panier'][$id_film])) {
                $_SESSION['panier'][$id_film] += $quantite;
                if ($_SESSION['panier'][$id_film] <= 0) {
                    unset($_SESSION['panier'][$id_film]);
                    error_log("Film supprimé du panier : $id_film");
                } else {
                    error_log("Quantité mise à jour pour le film $id_film : " . $_SESSION['panier'][$id_film]);
                }
            } else if ($quantite > 0) {
                $_SESSION['panier'][$id_film] = $quantite;
                error_log("Nouveau film ajouté au panier : $id_film");
            }
            $response = [
                'success' => true,
                'message' => 'Quantité mise à jour',
                'panier' => $_SESSION['panier']
            ];
        } else {
            $response = [
                'success' => false,
                'message' => 'Données invalides'
            ];
        }
        break;

    case 'remove':
        // Suppression d'un film
        if (isset($data['id_film']) && is_numeric($data['id_film'])) {
            $id_film = (int)$data['id_film'];
            
            if (isset($_SESSION['panier'][$id_film])) {
                unset($_SESSION['panier'][$id_film]);
                error_log("Film supprimé du panier : $id_film");
                
                $response = [
                    'success' => true,
                    'message' => 'Film supprimé du panier',
                    'panier' => $_SESSION['panier']
                ];
            } else {
                $response = [
                    'success' => false,
                    'message' => 'Film non trouvé dans le panier'
                ];
            }
        } else {
            $response = [
                'success' => false,
                'message' => 'ID de film invalide'
            ];
        }
        break;

    default:
        $response = [
            'success' => false,
            'message' => 'Opération non reconnue'
        ];
}

// Journaliser l'état final de la session
error_log("État final de la session : " . print_r($_SESSION, true));

// Retourner la réponse JSON
header('Content-Type: application/json');
echo json_encode($response);
?> 