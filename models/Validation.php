<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/Liste.php';

class Validation
{
    private $listeModel;

    public function __construct()
    {
        $this->listeModel = new Liste();
    }

    public function validerListe($id_liste, $commentaire = "")
    {
        return $this->listeModel->changerStatut($id_liste, 'تمت المصادقة', $commentaire);
    }

    public function refuserListe($id_liste, $commentaire)
    {
        return $this->listeModel->changerStatut($id_liste, 'مرفوضة', $commentaire);
    }

    public function demanderCorrection($id_liste, $commentaire)
    {
        return $this->listeModel->changerStatut($id_liste, 'في الانتظار', $commentaire);
    }
}
