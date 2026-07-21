<?php
header('Content-Type: application/json');

$response = [
    "response" => "/Lc1GVbcwa+XZHTMBHrVdNTReZuru7wpvlU59u3+ClMQIyxJHS0rXDHTe2nPzfxucqqRPhn60buc6IoXzM8dEI/JW2QB6yZeuJb/Q9vvU+E+aQub713p45cBeV2gXLRD",
    "metaData" => [
        "message" => "Created",
        "code" => 201
    ]
];


echo json_encode($response, JSON_PRETTY_PRINT);
