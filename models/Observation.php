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

    private function mapTypeToEnum(string $type): string
    {
        if (mb_strpos($type, 'تجاوز') !== false)                             return 'LIMIT_EXCEEDED';
        if (mb_strpos($type, 'تعارض') !== false || mb_strpos($type, 'جمع بين') !== false) return 'CONFLICT';
        if (mb_strpos($type, 'خطأ') !== false)                               return 'CALCULATION_ERROR';
        if (mb_strpos($type, 'غير مسجل') !== false || mb_strpos($type, 'غير موجود') !== false) return 'NOT_FOUND';
        return 'MISSING_DATA';
    }

    private function enumToAr(string $enum): string
    {
        $map = [
            'LIMIT_EXCEEDED'    => 'تجاوز الحد المسموح',
            'CALCULATION_ERROR' => 'خطأ في الحساب',
            'MISSING_DATA'      => 'بيانات ناقصة',
            'CONFLICT'          => 'تعارض',
            'NOT_FOUND'         => 'موظف غير مسجل',
        ];
        return $map[$enum] ?? $enum;
    }

    public function ajouterObservation($id_liste, $type, $message, $niveau)
    {
        $list_type  = str_starts_with((string)$id_liste, 'oc') ? 'ON_CALL' : 'OVERTIME';
        $error_type = $this->mapTypeToEnum((string)$type);
        $severity   = ($niveau === 'grave') ? 'error' : 'warning';

        $sql  = "INSERT INTO list_validation_errors (list_id, list_type, error_type, message, severity)
                 VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$id_liste, $list_type, $error_type, $message, $severity]);
    }

    public function getObservations()
    {
        $sql = "
            SELECT
                lve.id            AS id_observation,
                lve.list_id       AS id_liste,
                lve.list_type,
                lve.error_type,
                lve.message,
                lve.severity,
                lve.created_at    AS date_observation,
                COALESCE(d1.name, d2.name) AS service
            FROM list_validation_errors lve
            LEFT JOIN on_call_lists ol   ON lve.list_id = ol.id  AND lve.list_type = 'ON_CALL'
            LEFT JOIN departments d1     ON d1.id = ol.department_id
            LEFT JOIN overtime_lists otl ON lve.list_id = otl.id AND lve.list_type = 'OVERTIME'
            LEFT JOIN departments d2     ON d2.id = otl.department_id
            ORDER BY lve.id DESC
        ";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rows as &$r) {
            $r['type_observation'] = $this->enumToAr($r['error_type']);
            $r['type_liste']       = $r['list_type'] === 'ON_CALL' ? 'permanence' : 'heures_supp';
            $r['niveau']           = $r['severity']  === 'error'   ? 'grave'      : 'attention';
        }
        return $rows;
    }

    public function countObservations()
    {
        return (int)$this->conn->query("SELECT COUNT(*) FROM list_validation_errors")->fetchColumn();
    }

    public function countByNiveau()
    {
        $sql  = "SELECT severity, COUNT(*) AS total FROM list_validation_errors GROUP BY severity";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rows as &$r) {
            $r['niveau'] = $r['severity'] === 'error' ? 'grave' : 'attention';
        }
        return $rows;
    }
}
