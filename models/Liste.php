<?php

require_once __DIR__ . '/../config/database.php';

class Liste
{
    private $conn;

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function ajouterListe(
        $type_liste,
        $trimestre,
        $annee,
        $service,
        $fichier_original
    ) {

        $sql = "
            INSERT INTO listes (
                type_liste,
                trimestre,
                annee,
                service,
                fichier_original,
                statut
            )
            VALUES (?, ?, ?, ?, ?, ?)
        ";

        $stmt = $this->conn->prepare($sql);

        $stmt->execute([
            $type_liste,
            $trimestre,
            $annee,
            $service,
            $fichier_original,
            'في الانتظار'
        ]);

        return $this->conn->lastInsertId();
    }

    public function ajouterElement(
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
        $travaux = null,
        $date_debut = null,
        $date_fin = null
    ) {
        $sql = "
        INSERT INTO elements_liste (
            id_liste,
            nom_complet,
            numero_tajir,
            cin,
            cadre,
            mois,
            jour,
            type_jour,
            nombre_jours,
            nombre_heures,
            travaux,
            date_debut,
            date_fin
        )
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ";

        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([
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
        ]);
    }

    public function getAllElements()
    {
        $sql = "
        SELECT
            e.*,

            l.type_liste,
            l.trimestre,
            l.annee,
            l.service,
            l.statut,
            l.commentaire_validation

        FROM elements_liste e

        INNER JOIN listes l
        ON e.id_liste = l.id_liste

        ORDER BY e.id_element DESC
    ";

        $stmt = $this->conn->prepare($sql);

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function getElementsByListe($id_liste)
    {
        $sql = "
        SELECT *
        FROM elements_liste
        WHERE id_liste = ?
    ";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id_liste]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getElementsByType($type)
    {
        $sql = "
            SELECT 
                e.*,
                l.type_liste,
                l.trimestre,
                l.annee,
                l.service,
                l.statut

            FROM elements_liste e

            INNER JOIN listes l
            ON e.id_liste = l.id_liste

            WHERE l.type_liste = ?

            ORDER BY e.id_element DESC
        ";

        $stmt = $this->conn->prepare($sql);

        $stmt->execute([$type]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getElementById($id_element)
    {
        $sql = "
            SELECT *
            FROM elements_liste
            WHERE id_element = ?
        ";

        $stmt = $this->conn->prepare($sql);

        $stmt->execute([$id_element]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateElement(
        $id_element,
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
    ) {
        $sql = "
        UPDATE elements_liste
        SET
            nom_complet = ?,
            numero_tajir = ?,
            cin = ?,
            cadre = ?,
            mois = ?,
            jour = ?,
            type_jour = ?,
            nombre_jours = ?,
            nombre_heures = ?,
            travaux = ?,
            date_debut = ?,
            date_fin = ?
        WHERE id_element = ?
    ";

        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([
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
            $date_fin,
            $id_element
        ]);
    }
    public function countByType($type)
    {
        $sql = "
            SELECT COUNT(*) as total
            FROM listes
            WHERE type_liste = ?
        ";

        $stmt = $this->conn->prepare($sql);

        $stmt->execute([$type]);

        return $stmt->fetch()['total'];
    }

    public function countValides()
    {
        $sql = "
            SELECT COUNT(*) as total
            FROM listes
            WHERE statut = 'تمت المصادقة'
        ";

        $stmt = $this->conn->prepare($sql);

        $stmt->execute();

        return $stmt->fetch()['total'];
    }


    public function getRecentListes()
    {
        $sql = "
            SELECT *
            FROM listes
            ORDER BY id_liste DESC
            LIMIT 10
        ";

        $stmt = $this->conn->prepare($sql);

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function changerStatut(
        $id_liste,
        $statut,
        $commentaire = null
    ) {

        $sql = "
        UPDATE listes

        SET
            statut = ?,
            commentaire_validation = ?

        WHERE id_liste = ?
    ";

        $stmt =
            $this->conn->prepare($sql);

        return $stmt->execute([

            $statut,
            $commentaire,
            $id_liste

        ]);
    }
    public function countPermanences()
    {
        $sql = "
        SELECT COUNT(*) as total
        FROM listes
        WHERE type_liste='permanence'
    ";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }

    public function countHeuresSupp()
    {
        $sql = "
        SELECT COUNT(*) as total
        FROM listes
        WHERE type_liste='heures_supp'
    ";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }

    public function countRefusees()
    {
        $sql = "
        SELECT COUNT(*) as total
        FROM listes
        WHERE statut='مرفوضة'
    ";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }
    public function filtrerElements(
        $annee = null,
        $trimestre = null,
        $service = null,
        $numero_tajir = null
    ) {

        $sql = "

        SELECT
            e.*,

            l.type_liste,
            l.trimestre,
            l.annee,
            l.service,
            l.statut,
            l.commentaire_validation

        FROM elements_liste e

        INNER JOIN listes l
        ON e.id_liste = l.id_liste

        WHERE 1=1
    ";

        $params = [];



        if (!empty($annee)) {

            $sql .= "
            AND l.annee = ?
        ";

            $params[] = $annee;
        }

        /* TRIMESTRE */

        if (!empty($trimestre)) {

            $sql .= "
            AND l.trimestre = ?
        ";

            $params[] = $trimestre;
        }



        if (!empty($service)) {

            $sql .= "
            AND l.service = ?
        ";

            $params[] = $service;
        }



        if (!empty($numero_tajir)) {

            $sql .= "
            AND e.numero_tajir LIKE ?
        ";

            $params[] =
                "%" . $numero_tajir . "%";
        }

        $sql .= "
        ORDER BY e.id_element DESC
    ";

        $stmt =
            $this->conn->prepare($sql);

        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function countByStatut()
    {
        $sql = "
        SELECT statut, COUNT(*) as total
        FROM listes
        GROUP BY statut
    ";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countByService()
    {
        $sql = "
        SELECT service, COUNT(*) as total
        FROM listes
        GROUP BY service
    ";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getStatsByService()
    {
        $sql = "
            SELECT
                l.service,
                l.type_liste,
                COUNT(e.id_element) as total_elements
            FROM elements_liste e
            INNER JOIN listes l ON e.id_liste = l.id_liste
            WHERE l.service != '' AND l.service IS NOT NULL
            GROUP BY l.service, l.type_liste
            ORDER BY l.service
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $result = [];
        foreach ($rows as $row) {
            $service = $row['service'];
            if (!isset($result[$service])) {
                $result[$service] = [
                    'service'     => $service,
                    'permanence'  => 0,
                    'heures_supp' => 0,
                ];
            }
            $result[$service][$row['type_liste']] = intval($row['total_elements']);
        }

        return array_values($result);
    }

    public function elementExiste(
    $id_liste,
    $numero_tajir,
    $mois,
    $jour = null
){
    $sql = "
        SELECT COUNT(*) as total

        FROM elements_liste

        WHERE
            id_liste = ?
            AND numero_tajir = ?
            AND mois = ?
    ";

    $params = [
        $id_liste,
        $numero_tajir,
        $mois
    ];

    if($jour != null){

        $sql .= "
            AND jour = ?
        ";

        $params[] = $jour;
    }

    $stmt =
    $this->conn->prepare($sql);

    $stmt->execute($params);

    return $stmt->fetch()['total'] > 0;
}


}