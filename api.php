<?php

$allowedOrigins = array('https://aparda.com');
$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';

if (in_array($origin, $allowedOrigins)) {
    header("Access-Control-Allow-Origin: $origin");
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Allow-Headers: Origin, Content-Type, Accept, Authorization');
    header('Access-Control-Allow-Credentials: true');
}

include 'database.php';
include 'http.php';

function getSuggestions($query) {
    $result = getFromDatabase($query);

    // Se o resultado não estiver na base de dados, busca nas APIs externas
    if (!$result) {
        $apiUrls = [
            'https://search.brave.com/api/suggest?q=' . urlencode($query),
            'https://ac.ecosia.org/autocomplete?q=' . urlencode($query) . '&type=list',
            'https://duckduckgo.com/ac/?q=' . urlencode($query) . '&kl=br-pt'
        ];

        $mh = curl_multi_init();
        $handles = [];

        foreach ($apiUrls as $url) {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            // Adiciona o cookie do Brave pra trazer resultados do brasil
            if (strpos($url, 'brave.com') !== false) {
                curl_setopt($ch, CURLOPT_COOKIE, 'country=br');
            }

            // Adiciona o cookie do Ecosia pra trazer resultados do brasil
            if (strpos($url, 'ecosia.org') !== false) {
                curl_setopt($ch, CURLOPT_COOKIE, 'ECFG=pt-br');
            }

            curl_multi_add_handle($mh, $ch);
            $handles[] = $ch;
        }

        $running = null;
        do {
            curl_multi_exec($mh, $running);
        } while ($running > 0);

        $responses = [];
        foreach ($handles as $ch) {
            $responses[] = json_decode(curl_multi_getcontent($ch), true);
            curl_multi_remove_handle($mh, $ch);
        }

        curl_multi_close($mh);

        // Combina os resultados
        $mergedSuggestions = array_values(array_unique(call_user_func_array('array_merge', array_column($responses, '1'))));

        // Remove a query da busca dos resultados
        $mergedSuggestions = array_values(array_diff($mergedSuggestions, [$query]));

        // Reordena os resultados pro DuckDuckGo ficar por último porque ele ta trazendo tudo em inglês
        $duckDuckGoResults = array_filter($mergedSuggestions, function ($result) {
            return strpos($result, 'duckduckgo') !== false;
        });

        $mergedSuggestions = array_values(array_diff($mergedSuggestions, $duckDuckGoResults));
        $mergedSuggestions = array_merge($mergedSuggestions, $duckDuckGoResults);

        // Salna na nossa DB os dados se eles forem diferentes DB <x> Api's
        if ($mergedSuggestions !== $result) {
            saveToDatabase($query, $mergedSuggestions);
        }

        $result = [$query, $mergedSuggestions];
    } else {
        // Decodifica o JSON da base de dados
        $decodedResult = json_decode($result, true);

        if ($decodedResult !== null && is_array($decodedResult) && array_key_exists('query', $decodedResult) && array_key_exists('suggestions', $decodedResult)) {
            // Verifica se "suggestions" é um array antes de chamar o "array_values"
            $suggestions = is_array($decodedResult['suggestions']) ? array_values($decodedResult['suggestions']) : [];
            $result = [$decodedResult['query'], $suggestions];
        } else {
            // Se der algo errado, retorna vazio
            $result = [$query, []];
        }
    }

    return $result;
}

$query = isset($_GET['q']) ? $_GET['q'] : '';

$suggestions = getSuggestions($query);

header('Content-Type: application/json');
echo json_encode($suggestions);
?>
