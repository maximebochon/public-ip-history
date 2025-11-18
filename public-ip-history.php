<?php

// Chemin vers le fichier d'historique d'adresse IP (prédéfini ou mis à une valeur par défaut)
defined('ipHistoryFileName') or define('ipHistoryFileName', '../history.json');

// Format d'horodatage, fuseau Français et instant courant
date_default_timezone_set('Europe/Paris');
$dateTimeFormat = 'Y-m-d,H:i:s';
$currentDateTime = date($dateTimeFormat);

// Récupérer l'adresse IP de l'émetteur de la requête HTTP
$clientIpAddress = $_SERVER['REMOTE_ADDR'];

// Vérifier que le fichier d'historique d'adresse IP existe
if (file_exists($ipHistoryFileName)) {
    // Lire son contenu
    $fileContent = file_get_contents($ipHistoryFileName);

    // Décoder le contenu (supposé être du JSON)
    $ipHistory = json_decode($fileContent);

    // TODO : vérifier qu'il s'agit de JSON et qu'il contient un tableau
    
    // Vérifier que chaque objet a exactement la forme attendue
    foreach ($ipHistory as $entry) {
        if (count(get_object_vars($entry)) !== 3
            || !isset($entry->ipAddress)
            || !isset($entry->firstDateTime)
            || !isset($entry->latestDateTime)
        ) {
            die("Erreur : Fichier invalide, la forme n'est pas comme attendue.");
        }

        // Vérifier la validité des horodatages
        $firstDateTime = DateTime::createFromFormat($dateTimeFormat, $entry->firstDateTime);     
        if (!$firstDateTime || $firstDateTime->format($dateTimeFormat) !== $entry->firstDateTime) {
            die("Erreur : Fichier invalide, un horodatage initial est invalide.");
        }
        $latestDateTime = DateTime::createFromFormat($dateTimeFormat, $entry->latestDateTime);
        if (!$latestDateTime || $latestDateTime->format($dateTimeFormat) !== $entry->latestDateTime) {
            die("Erreur : Fichier invalide, un horodatage final est invalide.");
        }
        // TODO : mutualiser les deux vérifications précédentes
    }

    // Vérifier que la liste est triée par ordre décroissant
    for ($i = 1; $i < count($ipHistory); $i++) {
        if (strtotime($ipHistory[$i]->firstDateTime) >= strtotime($ipHistory[$i - 1]->firstDateTime)) {
            die("Erreur : La liste n'est pas triée par ordre décroissant.");
        }
    }
} else {
    // Si le fichier n'existe pas, initialiser une liste vide
    $ipHistory = [];
}

// Vérifier que la liste est vide ou que l'adresse IP est différente de la plus récente
if (empty($ipHistory) || $ipHistory[0]->ipAddress !== $clientIpAddress) {
    // Ajouter un nouvel objet en tête de liste
    array_unshift($ipHistory, [
        'ipAddress' => $clientIpAddress,
        'firstDateTime' => $currentDateTime,
        'latestDateTime' => $currentDateTime
    ]);
} else {
    // Sinon, mettre à jour le dernier horodatage de l'adresse IP courante
    $ipHistory[0]->latestDateTime = $currentDateTime;
}

// Sérialiser et stocker la liste d'objets au format JSON
$ipHistoryJsonString = json_encode($ipHistory, JSON_PRETTY_PRINT);
file_put_contents($ipHistoryFileName, $ipHistoryJsonString);

?>
