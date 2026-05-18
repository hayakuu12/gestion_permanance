<?php

header('Content-Type: application/json; charset=utf-8');

require_once '../autoload.php';
require_once '../models/Liste.php';

$id_liste = $_GET['id_liste'] ?? '';

if (!$id_liste) {
    echo json_encode(['error' => 'missing id_liste']);
    exit;
}

$listeModel = new Liste();
$rows = $listeModel->getElementsByListe($id_liste);
$type = str_starts_with((string)$id_liste, 'oc') ? 'permanence' : 'heures_supp';

if ($type === 'permanence') {
    $grouped = [];
    foreach ($rows as $r) {
        $key = $r['numero_tajir'] ?? 'x';
        if (!isset($grouped[$key])) {
            $grouped[$key] = [
                'nom_complet'  => $r['nom_complet']  ?? '',
                'numero_tajir' => $r['numero_tajir']  ?? '',
                'cadre'        => $r['cadre']         ?? '',
                'service'      => $r['service']       ?? '',
                'dates'        => [],
                'total'        => 0,
            ];
        }
        $raw = $r['date_debut'] ?? '';
        $grouped[$key]['dates'][] = $raw ? date('d/m/Y', strtotime($raw)) : ($r['jour'] ?? '');
        $grouped[$key]['total']++;
    }
    $membres = array_values($grouped);
} else {
    $seen = [];
    $membres = [];
    foreach ($rows as $r) {
        $key = $r['numero_tajir'] ?? 'x';
        if (!isset($seen[$key])) {
            $seen[$key] = true;
            $membres[] = [
                'nom_complet'   => $r['nom_complet']            ?? '',
                'numero_tajir'  => $r['numero_tajir']            ?? '',
                'cadre'         => $r['cadre']                   ?? '',
                'service'       => $r['service']                 ?? '',
                'month_1_hours' => floatval($r['month_1_hours']  ?? 0),
                'month_2_hours' => floatval($r['month_2_hours']  ?? 0),
                'month_3_hours' => floatval($r['month_3_hours']  ?? 0),
                'total_hours'   => floatval($r['nombre_heures']  ?? 0),
            ];
        }
    }
}

echo json_encode([
    'type'    => $type,
    'membres' => $membres,
    'count'   => count($membres),
], JSON_UNESCAPED_UNICODE);
