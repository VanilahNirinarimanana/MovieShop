<?php
require_once 'Database.php';

class Film {
    public static function getById($id) {
        $pdo = Database::connect();
        $stmt = $pdo->prepare("SELECT * FROM film WHERE id_film = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    // Nouvelle méthode pour récupérer tous les films (avec poster)
    public static function getAll() {
        $pdo = Database::connect();
        $stmt = $pdo->query("SELECT * FROM film");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function add($titre, $prix, $poster, $genre) {
        $pdo = Database::connect();
        
        // Gérer l'upload de l'image
        $upload_dir = 'assets/images/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        // Générer un nom unique pour l'image
        $file_extension = strtolower(pathinfo($poster['name'], PATHINFO_EXTENSION));
        $new_filename = uniqid() . '.' . $file_extension;
        $upload_path = $upload_dir . $new_filename;

        // Vérifier le type de fichier
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array($file_extension, $allowed_types)) {
            throw new Exception('Type de fichier non autorisé. Types acceptés : ' . implode(', ', $allowed_types));
        }

        // Déplacer l'image uploadée
        if (!move_uploaded_file($poster['tmp_name'], $upload_path)) {
            throw new Exception('Erreur lors de l\'upload de l\'image');
        }

        // Insérer le film dans la base de données
        $stmt = $pdo->prepare("INSERT INTO film (titre, prix, poster, genre) VALUES (?, ?, ?, ?)");
        $stmt->execute([$titre, $prix, $new_filename, $genre]);
        return $pdo->lastInsertId();
    }

    public static function update($id_film, $titre, $prix, $genre, $poster = null) {
        $pdo = Database::connect();
        if ($poster && $poster['error'] === 0) {
            // Gérer l'upload de l'image
            $upload_dir = 'assets/images/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            $file_extension = strtolower(pathinfo($poster['name'], PATHINFO_EXTENSION));
            $new_filename = uniqid() . '.' . $file_extension;
            $upload_path = $upload_dir . $new_filename;
            $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
            if (!in_array($file_extension, $allowed_types)) {
                throw new Exception('Type de fichier non autorisé. Types acceptés : ' . implode(', ', $allowed_types));
            }
            if (!move_uploaded_file($poster['tmp_name'], $upload_path)) {
                throw new Exception('Erreur lors de l\'upload de l\'image');
            }
            $stmt = $pdo->prepare("UPDATE film SET titre = ?, prix = ?, genre = ?, poster = ? WHERE id_film = ?");
            return $stmt->execute([$titre, $prix, $genre, $new_filename, $id_film]);
        } else {
            $stmt = $pdo->prepare("UPDATE film SET titre = ?, prix = ?, genre = ? WHERE id_film = ?");
            return $stmt->execute([$titre, $prix, $genre, $id_film]);
        }
    }

    public static function delete($id_film) {
        $pdo = Database::connect();
        $stmt = $pdo->prepare("DELETE FROM film WHERE id_film = ?");
        return $stmt->execute([$id_film]);
    }
}
?> 