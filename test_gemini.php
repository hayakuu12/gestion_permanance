<?php

require_once 'config/gemini.php';

$url =
"https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key="
. GEMINI_API_KEY;

$data = [
    "contents" => [
        [
            "parts" => [
                [
                    "text" => "Say hello in Arabic"
                ]
            ]
        ]
    ]
];

$ch = curl_init($url);

curl_setopt_array($ch, [

    CURLOPT_RETURNTRANSFER => true,

    CURLOPT_POST => true,

    CURLOPT_HTTPHEADER => [
        "Content-Type: application/json"
    ],

    CURLOPT_POSTFIELDS => json_encode($data),

    CURLOPT_SSL_VERIFYPEER => false,

    CURLOPT_SSL_VERIFYHOST => false

]);

$response = curl_exec($ch);

if (curl_errno($ch)) {

    echo "CURL ERROR: " . curl_error($ch);

    exit;
}

curl_close($ch);

echo "<pre>";

print_r(json_decode($response, true));

echo "</pre>";