<?php

$servername = "";
$username = "";
$password = "";
$dbname = "";

function connectToDatabase() {
    global $servername, $username, $password, $dbname;
    return new mysqli($servername, $username, $password, $dbname);
}

function getFromDatabase($query) {
    $conn = connectToDatabase();
    $sql = "SELECT suggestions_json FROM suggestions WHERE query = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $query);
    $stmt->execute();
    $stmt->bind_result($suggestionsJson);
    $stmt->fetch();
    $stmt->close();
    $conn->close();

    return $suggestionsJson;
}

function saveToDatabase($query, $suggestions) {
    $conn = connectToDatabase();
    $sql = "INSERT INTO suggestions (query, suggestions_json) VALUES (?, ?) ON DUPLICATE KEY UPDATE suggestions_json = VALUES(suggestions_json)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ss', $query, json_encode(['query' => $query, 'suggestions' => $suggestions]));
    $stmt->execute();
    $stmt->close();
    $conn->close();
}

?>
