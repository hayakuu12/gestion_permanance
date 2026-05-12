<?php
require_once __DIR__ . '/../config/database.php';

class Validation
{
    private $conn;

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function ajouterValidation($id_liste, $decision, $commentaire)
    {
        $sql = "INSERT INTO validations
                (id_liste, decision, commentaire)
                VALUES (?, ?, ?)";

        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([
            $id_liste,
            $decision,
            $commentaire
        ]);
    }

    public function updateStatutListe($id_liste, $statut)
    {
        $sql = "UPDATE listes
                SET statut = ?
                WHERE id_liste = ?";

        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([
            $statut,
            $id_liste
        ]);
    }

    public function validerListe($id_liste, $commentaire = "")
    {
        $this->ajouterValidation(
            $id_liste,
            "valide",
            $commentaire
        );

        return $this->updateStatutListe(
            $id_liste,
            "valide"
        );
    }

    public function refuserListe($id_liste, $commentaire)
    {
        $this->ajouterValidation(
            $id_liste,
            "refuse",
            $commentaire
        );

        return $this->updateStatutListe(
            $id_liste,
            "refuse"
        );
    }

    public function demanderCorrection($id_liste, $commentaire)
    {
        $this->ajouterValidation(
            $id_liste,
            "a_corriger",
            $commentaire
        );

        return $this->updateStatutListe(
            $id_liste,
            "a_corriger"
        );
    }

    public function getValidations()
    {
        $sql = "
            SELECT 
                v.*,
                l.type_liste,
                l.trimestre,
                l.annee
            FROM validations v
            JOIN listes l
            ON v.id_liste = l.id_liste
            ORDER BY v.date_validation DESC
        ";

        $stmt = $this->conn->query($sql);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}   