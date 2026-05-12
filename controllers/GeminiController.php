<?php

require_once 'config/gemini.php';

class GeminiController
{
    public function extractFromPdf($filePath, $type_liste)
    {
        set_time_limit(300);
        ini_set('max_execution_time', 300);

        $mimeType = mime_content_type($filePath);
        $fileData = base64_encode(file_get_contents($filePath));

        if ($type_liste == "permanence") {
            $prompt = "
أنت نظام استخراج معطيات من لائحة الديمومة الإدارية.

استخرج JSON فقط بدون شرح:

[
  {
    \"nom_complet\": \"الاسم الكامل\",
    \"numero_tajir\": \"رقم التأجير أو الرقم المالي\",
    \"cin\": \"رقم البطاقة الوطنية\",
    \"cadre\": \"الإطار\",
    \"date_debut\": \"YYYY-MM-DD\",
    \"date_fin\": \"YYYY-MM-DD\",
    \"mois\": 1,
    \"valeur\": 0,
    \"travaux\": \"\"
  }
]

مهم:
- رقم التأجير يكون أرقام فقط غالباً.
- CIN فيه حروف وأرقام مثل D500249 أو U85660.
- لا تخلط بين رقم التأجير و CIN.
- valeur = عدد أيام الديمومة.
- إذا كان هناك تاريخان فاستعملهما كتاريخ بداية ونهاية.
- إذا فشل الاستخراج أرجع [] فقط.
";
        } else {
            $prompt = "
أنت نظام استخراج معطيات من لائحة الساعات الإضافية الإدارية.

استخرج JSON فقط بدون شرح:

[
  {
    \"nom_complet\": \"الاسم الكامل\",
    \"numero_tajir\": \"رقم التأجير\",
    \"cin\": \"\",
    \"cadre\": \"الإطار\",
    \"date_debut\": null,
    \"date_fin\": null,
    \"mois\": 4,
    \"valeur\": 0,
    \"travaux\": \"الأشغال المنجزة\"
  }
]

مهم:
- valeur = عدد الساعات الإضافية لذلك الشهر.
- إذا كان الموظف عنده ساعات في أبريل وماي ويونيو، أرجع سطر لكل شهر.
- mois: أبريل=4، ماي=5، يونيو=6.
- لا تضع المجموع فقط، بل الساعات حسب كل شهر إن وجدت.
- travaux = الأشغال المنجزة.
- إذا فشل الاستخراج أرجع [] فقط.
";
        }

        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash-lite:generateContent?key=" . GEMINI_API_KEY;

        $data = [
            "contents" => [
                [
                    "parts" => [
                        ["text" => $prompt],
                        [
                            "inline_data" => [
                                "mime_type" => $mimeType,
                                "data" => $fileData
                            ]
                        ]
                    ]
                ]
            ],
            "generationConfig" => [
                "temperature" => 0.1,
                "response_mime_type" => "application/json"
            ]
        ];

        $ch = curl_init($url);

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => ["Content-Type: application/json"],
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_TIMEOUT => 300
        ]);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            curl_close($ch);
            return [];
        }

        curl_close($ch);

        $result = json_decode($response, true);

        if (!isset($result['candidates'][0]['content']['parts'][0]['text'])) {
            return [];
        }

        $text = $result['candidates'][0]['content']['parts'][0]['text'];
        $text = str_replace("```json", "", $text);
        $text = str_replace("```", "", $text);
        $text = trim($text);

        $json = json_decode($text, true);

        if (!is_array($json)) {
            return [];
        }

        return $json;
    }
}