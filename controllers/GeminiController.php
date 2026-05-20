<?php

require_once 'config/gemini.php';

class GeminiController
{
    public function extractFromPdf($filePath, $type_liste, $trimestre = 1)
    {
        set_time_limit(300);

        $mimeType = mime_content_type($filePath);
        $fileData = base64_encode(file_get_contents($filePath));

        if ($type_liste == "permanence") {

            $prompt = "
أنت نظام متخصص في استخراج بيانات الديمومة من وثائق PDF الرسمية المغربية.

أرجع JSON فقط بدون أي شرح أو تعليق.

=== قواعد الاستخراج ===

1. استخرج كل سطر في جدول الديمومة كعنصر مستقل في المصفوفة.
2. لا تضف أي تاريخ أو بيانات غير موجودة في الوثيقة.
3. لا تحذف أي سطر موجود في الجدول.
4. كل موظف يمكن أن يظهر عدة مرات إذا كانت له تواريخ ديمومة متعددة.

=== قواعد التواريخ ===

- إذا كان هناك تاريخ واحد (مثال: 07/06/2025):
  date_debut = \"2025-06-07\"
  date_fin   = \"2025-06-07\"
  valeur     = 1

- إذا كان هناك تاريخان متتاليان (مثال: 04/10/2025 - 05/10/2025):
  date_debut = \"2025-10-04\"
  date_fin   = \"2025-10-05\"
  valeur     = 2

- إذا كانت الخانة تحتوي على نطاق أيام محسوب (مثال: 3 أيام):
  valeur = العدد المذكور

- صيغة التاريخ دائماً: YYYY-MM-DD

=== قواعد نوع العطلة ===

- إذا كان التاريخ في خانة \"العطل الأسبوعية\": type_jour = \"عطلة أسبوعية\"
- إذا كان التاريخ في خانة \"الأعياد الوطنية والدينية\": type_jour = \"عيد وطني أو ديني\"
- إذا لم تكن الخانة واضحة: type_jour = \"\"

=== قواعد حقول الموظف ===

- numero_tajir: أرقام فقط، بدون حروف.
- cin: يحتوي على حروف وأرقام (مثال: AB123456).
- لا تخلط أبداً بين CIN ورقم التأجير.
- nom_complet: الاسم الكامل كما هو في الجدول.
- cadre: الإطار الوظيفي.
- mois: رقم الشهر المستخرج من date_debut (عدد صحيح من 1 إلى 12).

=== الصيغة المطلوبة ===

[
  {
    \"nom_complet\": \"\",
    \"numero_tajir\": \"\",
    \"cin\": \"\",
    \"cadre\": \"\",
    \"date_debut\": \"YYYY-MM-DD\",
    \"date_fin\": \"YYYY-MM-DD\",
    \"jour\": \"\",
    \"type_jour\": \"\",
    \"mois\": 1,
    \"valeur\": 1,
    \"travaux\": \"\"
  }
]

إذا لم تتمكن من استخراج أي بيانات، أرجع [] فقط.
أرجع JSON فقط، بدون أي نص إضافي.
";

        } else {

            $trimestreData = [
                1 => [
                    'mois'  => [1, 2, 3],
                    'noms'  => ['يناير', 'فبراير', 'مارس'],
                    'label' => 'الأول (يناير / فبراير / مارس)'
                ],
                2 => [
                    'mois'  => [4, 5, 6],
                    'noms'  => ['أبريل', 'ماي', 'يونيو'],
                    'label' => 'الثاني (أبريل / ماي / يونيو)'
                ],
                3 => [
                    'mois'  => [7, 8, 9],
                    'noms'  => ['يوليوز', 'غشت', 'شتنبر'],
                    'label' => 'الثالث (يوليوز / غشت / شتنبر)'
                ],
                4 => [
                    'mois'  => [10, 11, 12],
                    'noms'  => ['أكتوبر', 'نونبر', 'دجنبر'],
                    'label' => 'الرابع (أكتوبر / نونبر / دجنبر)'
                ],
            ];

            $t     = isset($trimestreData[intval($trimestre)]) ? intval($trimestre) : 1;
            $td    = $trimestreData[$t];
            $m1    = $td['mois'][0]; $n1 = $td['noms'][0];
            $m2    = $td['mois'][1]; $n2 = $td['noms'][1];
            $m3    = $td['mois'][2]; $n3 = $td['noms'][2];

            $prompt = "
أنت نظام متخصص في استخراج بيانات الساعات الإضافية من جداول PDF الرسمية المغربية.
أرجع JSON فقط، بدون أي شرح أو كلام إضافي.

=== معلومات الثلاثية ===

هذا الجدول خاص بالثلاثية {$td['label']}.
الأعمدة الثلاثة للساعات هي بالترتيب:
- العمود الأول للساعات  = شهر {$n1} → mois = {$m1}
- العمود الثاني للساعات = شهر {$n2} → mois = {$m2}
- العمود الثالث للساعات = شهر {$n3} → mois = {$m3}

=== هيكل الجدول (من اليمين إلى اليسار) ===

| الرقم | الاسم الكامل | رقم التأجير | الإطار | {$n1} | {$n2} | {$n3} | المجموع | الأشغال المنجزة |

=== قواعد صارمة ===

1. لكل موظف، أرجع سجلاً منفصلاً لكل شهر فيه رقم > 0.
2. إذا كانت الخانة \"-\" أو فارغة → لا ترجع سجلاً لهذا الشهر.
3. valeur = الرقم الموجود في عمود ذلك الشهر تحديداً، ليس المجموع.
4. عمود \"المجموع\" تجاهله تماماً، لا تستعمله أبداً.
5. numero_tajir: أرقام فقط بدون حروف.
6. travaux = الأشغال المنجزة لذلك الموظف.
7. مهم جداً: لا تخلط بين الأعمدة، احترم الترتيب أعلاه.

=== مثال توضيحي ===

صف في الجدول: اسم=\"محمد العلوي\" | tajir=\"1234\" | {$n1}=\"08\" | {$n2}=\"-\" | {$n3}=\"12\" | المجموع=\"20\"

النتيجة الصحيحة (سجلان فقط لأن {$n2} فارغ):
[
  {\"nom_complet\":\"محمد العلوي\",\"numero_tajir\":\"1234\",\"cadre\":\"\",\"mois\":{$m1},\"valeur\":8,\"travaux\":\"\"},
  {\"nom_complet\":\"محمد العلوي\",\"numero_tajir\":\"1234\",\"cadre\":\"\",\"mois\":{$m3},\"valeur\":12,\"travaux\":\"\"}
]

=== الصيغة المطلوبة ===

[
  {
    \"nom_complet\": \"\",
    \"numero_tajir\": \"\",
    \"cin\": \"\",
    \"cadre\": \"\",
    \"date_debut\": null,
    \"date_fin\": null,
    \"jour\": \"\",
    \"type_jour\": \"\",
    \"mois\": {$m1},
    \"valeur\": 0,
    \"travaux\": \"\"
  }
]

إذا لم تجد بيانات، أرجع [] فقط.
أرجع JSON فقط.
";
        }

        $url =
            "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key="
            . GEMINI_API_KEY;

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
            CURLOPT_HTTPHEADER => [
                "Content-Type: application/json"
            ],
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