<?php

function getFromExternalAPI($url) {
    $response = file_get_contents($url);
    return $response ? json_decode($response)[1] : [];
}

?>
