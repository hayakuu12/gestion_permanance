<?php

require_once __DIR__ . '/../config/database.php';

class Observation
{
    private $conn;

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function ajouterObservation(
        $id_liste,
        $type,
        $message,
        $niveau
    ) {

        $sql = "
            INSERT INTO observations (

                id_liste,
                type_observation,
                message,
                niveau

            )

            VALUES (?, ?, ?, ?)
        ";

        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([

            $id_liste,
            $type,
            $message,
            $niveau

        ]);
    }

    public function getObservations()
    {
        $sql = "

            SELECT
                o.*,
                l.type_liste,
                l.service

            FROM observations o

            INNER JOIN listes l
            ON o.id_liste = l.id_liste

            ORDER BY o.id_observation DESC

        ";

        $stmt = $this->conn->prepare($sql);

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function countObservations()
    {
        $sql = "
        SELECT COUNT(*) as total
        FROM observations
    ";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }
}