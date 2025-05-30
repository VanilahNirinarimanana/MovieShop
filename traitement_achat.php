<?php
require_once 'model/Client.php';
require_once 'model/Film.php';
require_once 'model/Achat.php';

// Définir le fuseau horaire pour Madagascar
date_default_timezone_set('Indian/Antananarivo');

session_start();

// Vérifier si le panier n'est pas vide
if (empty($_SESSION['panier'])) {
    header('Location: panier.php');
    exit();
}

// Récupérer les données du formulaire
$id_client = $_POST['id_client'] ?? '';

// Si pas de client sélectionné, vérifier les données du nouveau client
if (empty($id_client)) {
    $prenom = $_POST['prenom'] ?? '';
    $nom = $_POST['nom'] ?? '';
    $contact = $_POST['telephone'] ?? '';

    // Vérifier que tous les champs sont remplis
    if (empty($prenom) || empty($nom) || empty($contact)) {
        header('Location: panier.php?error=missing_fields');
        exit();
    }

    // Créer le nouveau client
    $id_client = Client::add($prenom, $nom, $contact);
    if (!$id_client) {
        header('Location: panier.php?error=client_creation_failed');
        exit();
    }
}

// Traiter chaque film du panier avec la même date d'achat
$date_achat = date('Y-m-d H:i:s');
foreach ($_SESSION['panier'] as $id_film => $quantite) {
    Achat::add($id_client, $id_film, $quantite, $date_achat);
}

// Vider le panier
$_SESSION['panier'] = [];

// Rediriger vers la facture (client + date)
//var_dump($date_achat);
header("Location: view/facture.php?id_client=$id_client&date=$date_achat");
exit(); 