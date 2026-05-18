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
        $sql = "SELECT * FROM employees WHERE id = ? LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$numero_tajir]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function createEmploye($numero_tajir, $full_name, $position = '', $department_id = '')
    {
        $ts   = (int)(microtime(true) * 1000);
        $sql  = "INSERT IGNORE INTO employees (id, full_name, position, department_id, created_at) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$numero_tajir, $full_name ?: $numero_tajir, $position ?: '', $department_id, $ts]);
    }
}
