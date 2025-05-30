<?php
require_once __DIR__ . '/../model/Film.php';

class FilmController {
    public static function addFilm($titre, $prix, $poster, $genre) {
        return Film::add($titre, $prix, $poster, $genre);
    }

    public static function updateFilm($id_film, $titre, $prix, $genre, $poster = null) {
        return Film::update($id_film, $titre, $prix, $genre, $poster);
    }

    public static function deleteFilm($id_film) {
        return Film::delete($id_film);
    }
}
?> 