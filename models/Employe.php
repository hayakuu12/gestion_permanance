<?php

require_once __DIR__ . '/../config/database.php';

class Employe
{
    private $conn;

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function getEmployeByTajir($numero_tajir)
    {
        $sql = "SELECT * FROM employes WHERE numero_tajir = ? LIMIT 1";

        $stmt = $this->conn->prepare($sql);

        $stmt->execute([$numero_tajir]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}