<?php

require_once 'models/Liste.php';
require_once 'models/Controle.php';
require_once 'controllers/GeminiController.php';

class ImportController
{
    private $listeModel;
    private $controleModel;
    private $gemini;

    public function __construct()
    {
        $this->listeModel = new Liste();
        $this->controleModel = new Controle();
        $this->gemini = new GeminiController();
    }

    public function importerPDF($file, $type_liste, $trimestre, $annee, $service)
    {
        if ($file['error'] != 0) {
            return [
                "success" => false,
                "manual" => true,
                "message" => "خطأ أثناء رفع الملف"
            ];
        }

        if (!is_dir("uploads")) {
            mkdir("uploads", 0777, true);
        }

        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if ($extension != "pdf") {
            return [
                "success" => false,
                "manual" => true,
                "message" => "يجب رفع ملف PDF فقط"
            ];
        }

        $nomFichier = time() . "_" . $file['name'];
        $chemin = "uploads/" . $nomFichier;

        move_uploaded_file($file['tmp_name'], $chemin);

        $id_liste = $this->listeModel->ajouterListe(
            $type_liste,
            $trimestre,
            $annee,
            $service,
            $nomFichier
        );

        $data = $this->gemini->extractFromPdf($chemin, $type_liste);

        if (empty($data)) {
            return [
                "success" => false,
                "manual" => true,
                "id_liste" => $id_liste,
                "message" => "تم رفع PDF لكن تعذر استخراج المعطيات تلقائياً."
            ];
        }

        $count = 0;

        foreach ($data as $row) {

            $nom_complet = trim($row['nom_complet'] ?? '');
            $numero_tajir = trim($row['numero_tajir'] ?? '');
            $cin = trim($row['cin'] ?? '');
            $cadre = trim($row['cadre'] ?? '');
            $travaux = trim($row['travaux'] ?? '');

            $date_debut = $row['date_debut'] ?? null;
            $date_fin = $row['date_fin'] ?? null;

            $mois = intval($row['mois'] ?? 0);
            $valeur = floatval($row['valeur'] ?? 0);

            if ($nom_complet == "" || $numero_tajir == "" || $mois == 0) {
                continue;
            }

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
                $nombre_jours,
                $nombre_heures,
                $travaux,
                $date_debut,
                $date_fin
            );

            $count++;
        }

        if ($count == 0) {
            return [
                "success" => false,
                "manual" => true,
                "id_liste" => $id_liste,
                "message" => "لم يتم العثور على معطيات صالحة داخل الملف."
            ];
        }

        $this->lancerControles($id_liste, $type_liste);

        return [
            "success" => true,
            "manual" => false,
            "id_liste" => $id_liste,
            "message" => "تم استخراج المعطيات بواسطة Gemini بنجاح"
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
        $valeur,
        $travaux = null,
        $date_debut = null,
        $date_fin = null
    ) {
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
            $nombre_jours,
            $nombre_heures,
            $travaux,
            $date_debut,
            $date_fin
        );

        $this->lancerControles($id_liste, $type_liste);
    }

    private function lancerControles($id_liste, $type_liste)
    {
        $elements = $this->listeModel->getElementsByListe($id_liste);

        foreach ($elements as $element) {

            if ($type_liste == "permanence") {
                $this->controleModel->verifierDepassementPermanence(
                    $element['id_element'],
                    $element['nombre_jours']
                );
            }

            if ($type_liste == "heures_supp") {
                $this->controleModel->verifierDepassementHeures(
                    $element['id_element'],
                    $element['nombre_heures']
                );
            }
        }

        $this->controleModel->verifierConflitPermanenceHeures();
    }
}