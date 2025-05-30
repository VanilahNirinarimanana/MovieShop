<?php
require_once 'controller/ClientController.php';
header('Content-Type: application/json');
echo json_encode(ClientController::getClients());
?> 