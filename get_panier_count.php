<?php
session_start();
$count = 0;
if (isset($_SESSION['panier']) && is_array($_SESSION['panier'])) {
    foreach ($_SESSION['panier'] as $item) {
        if (is_array($item) && isset($item['quantite'])) {
            $count += $item['quantite'];
        } else {
            $count += (int)$item;
        }
    }
}
header('Content-Type: application/json');
echo json_encode(['count' => $count]); 