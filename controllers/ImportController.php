<?php

require_once 'controllers/GeminiController.php';
require_once 'models/Liste.php';
require_once 'models/Observation.php';

class ImportController
{
    private $gemini;
    private $listeModel;
    private $observationModel;
    private $employeModel;

    public function __construct()
    {
        $this->gemini           = new GeminiController();
        $this->listeModel       = new Liste();
        $this->observationModel = new Observation();
        $this->employeModel     = new Employe();
    }

    public function importerPDF($file, $type_liste, $trimestre, $annee, $service)
    {
        if ($file['error'] != 0) {
            return [
                "success" => false,
                "message" => "خطأ أثناء رفع الملف",
                "manual" => false
            ];
        }

        $uploadDir = "uploads/";

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $fileName = time() . "_" . basename($file['name']);
        $filePath = $uploadDir . $fileName;

        move_uploaded_file($file['tmp_name'], $filePath);

        $id_liste = $this->listeModel->ajouterListe(
            $type_liste,
            $trimestre,
            $annee,
            $service,
            $filePath
        );

        $rows = $this->gemini->extractFromPdf($filePath, $type_liste, $trimestre);

        if (empty($rows)) {
            return [
                "success" => false,
                "message" => "تعذر استخراج البيانات",
                "manual" => true,
                "id_liste" => $id_liste
            ];
        }

        $seen         = [];
        $seenWarnings = [];
        $inserted     = 0;

        foreach ($rows as $row) {

            $nom_complet = trim($row['nom_complet'] ?? '');
            $numero_tajir = trim($row['numero_tajir'] ?? '');
            $cin = trim($row['cin'] ?? '');
            $cadre = trim($row['cadre'] ?? '');

            $date_debut = $row['date_debut'] ?? null;
            $date_fin = $row['date_fin'] ?? null;

            $jour = trim($row['jour'] ?? '');
            $type_jour = trim($row['type_jour'] ?? '');

            $mois = intval($row['mois'] ?? 0);
            $valeur = floatval($row['valeur'] ?? 0);
            $travaux = trim($row['travaux'] ?? '');

            if ($nom_complet == "" && $numero_tajir == "" && $cin == "") {
                continue;
            }

            /* CHECK: هل الموظف مسجل في قاعدة البيانات؟ */
            if ($numero_tajir != "" && !isset($seenWarnings[$numero_tajir])) {
                $employeExiste = $this->employeModel->getEmployeByTajir($numero_tajir);
                if (!$employeExiste) {
                    $dept_id = $this->listeModel->getDeptIdFromList($id_liste);
                    $this->employeModel->createEmploye($numero_tajir, $nom_complet, $cadre, $dept_id ?? '');
                    $this->observationModel->ajouterObservation(
                        $id_liste,
                        "موظف غير مسجل",
                        "رقم التأجير " . $numero_tajir . " (" . $nom_complet . ") لم يكن موجودا في قاعدة البيانات. تم إنشاء سجل جديد تلقائيا.",
                        "attention"
                    );
                }
                $seenWarnings[$numero_tajir] = true;
            }

            if ($mois == 0 && !empty($date_debut)) {
                $mois = intval(date("m", strtotime($date_debut)));
            }

            if ($mois == 0) {
                $mois = 1;
            }

            if ($type_liste == "permanence") {

                /*
                    مهم:
                    نفس الموظف مسموح يتكرر إذا التاريخ مختلف.
                    منع التكرار يكون بالتاريخ، ماشي بالاسم فقط.
                */

                $key = strtolower(
                    $numero_tajir . "_" .
                    $cin . "_" .
                    $date_debut . "_" .
                    $date_fin . "_" .
                    $type_jour
                );

            } else {

                $key = strtolower(
                    $numero_tajir . "_" .
                    $nom_complet . "_" .
                    $mois . "_" .
                    $valeur . "_" .
                    $travaux
                );
            }

            $key = preg_replace('/\s+/', '', $key);

            if (isset($seen[$key])) {
                continue;
            }

            $seen[$key] = true;

            $nombre_jours = 0;
            $nombre_heures = 0;

            if ($type_liste == "permanence") {
                $nombre_jours = $valeur;
            } else {
                $nombre_heures = $valeur;
            }

            $this->listeModel->ajouterElement(
                $id_liste,
                $nom_complet,
                $numero_tajir,
                $cin,
                $cadre,
                $mois,
                $jour,
                $type_jour,
                $nombre_jours,
                $nombre_heures,
                $travaux,
                $date_debut,
                $date_fin
            );

            $inserted++;

            if ($numero_tajir == "") {
                $this->observationModel->ajouterObservation(
                    $id_liste,
                    "رقم التأجير",
                    "رقم التأجير غير موجود",
                    "attention"
                );
            }

            if ($type_liste == "heures_supp" && $travaux == "") {
                $this->observationModel->ajouterObservation(
                    $id_liste,
                    "الأشغال",
                    "الأشغال المنجزة غير موجودة",
                    "attention"
                );
            }
        }

        if ($inserted == 0) {
            return [
                "success" => false,
                "message" => "لم يتم إدخال أي معطيات صالحة",
                "manual" => true,
                "id_liste" => $id_liste
            ];
        }

        return [
            "success" => true,
            "message" => "تم استيراد الملف بنجاح",
            "manual" => false,
            "id_liste" => $id_liste
        ];
    }

    public function ajouterManuel(
        $id_liste,
        $type_liste,
        $nom_complet,
        $numero_tajir,
        $cin,
        $cadre,
        $mois,
        $jour,
        $type_jour,
        $valeur,
        $nombre_heures = 0,
        $travaux = "",
        $date_debut = null,
        $date_fin = null
    ) {
        $nombre_jours = 0;
        $heures = 0;

        if ($type_liste == "permanence") {
            $nombre_jours = $valeur;
        } else {
            $heures = $valeur;
        }

        return $this->listeModel->ajouterElement(
            $id_liste,
            $nom_complet,
            $numero_tajir,
            $cin,
            $cadre,
            $mois,
            $jour,
            $type_jour,
            $nombre_jours,
            $heures,
            $travaux,
            $date_debut,
            $date_fin
        );
    }
}