<?php

require_once __DIR__ . '/../config/database.php';

class Controle
{
    private $conn;

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->getConnection();
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

    public function ajouterControle($list_id, $type_controle, $message, $niveau)
    {
        $list_type  = str_starts_with((string)$list_id, 'oc') ? 'ON_CALL' : 'OVERTIME';
        $severity   = ($niveau === 'grave') ? 'error' : 'warning';
        $error_type = 'MISSING_DATA';
        if (mb_strpos($type_controle, 'تجاوز') !== false) $error_type = 'LIMIT_EXCEEDED';
        if (mb_strpos($type_controle, 'تعارض') !== false) $error_type = 'CONFLICT';

        $sql  = "INSERT INTO list_validation_errors (list_id, list_type, error_type, message, severity)
                 VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$list_id, $list_type, $error_type, $message, $severity]);
    }

    public function getControles()
    {
        $sql = "
            SELECT
                lve.id            AS id_controle,
                lve.list_id       AS id_liste,
                lve.error_type    AS type_controle_enum,
                lve.message,
                lve.severity,
                lve.created_at    AS date_controle,
                lve.employee_id   AS numero_tajir,
                e.full_name       AS nom_complet
            FROM list_validation_errors lve
            LEFT JOIN employees e ON e.id = lve.employee_id
            ORDER BY lve.id DESC
        ";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rows as &$r) {
            $r['type_controle'] = $this->enumToAr($r['type_controle_enum']);
            $r['niveau']        = $r['severity'] === 'error' ? 'grave' : 'attention';
            $r['nom_complet']   = $r['nom_complet'] ?? '';
        }
        return $rows;
    }

    public function countControles()
    {
        return (int)$this->conn->query("SELECT COUNT(*) FROM list_validation_errors")->fetchColumn();
    }

    /* Kept for compatibility — inserts into list_validation_errors */
    public function verifierDepassementPermanence($list_id, $nombre_jours)
    {
        if ($nombre_jours > 6) {
            $this->ajouterControle(
                $list_id,
                "تجاوز الديمومة",
                "تجاوز الحد المسموح به للديمومة: 6 أيام في الشهر",
                "grave"
            );
        }
    }

    public function verifierDepassementHeures($list_id, $nombre_heures)
    {
        if ($nombre_heures > 24) {
            $this->ajouterControle(
                $list_id,
                "تجاوز الساعات الإضافية",
                "تجاوز الحد المسموح به للساعات الإضافية: 24 ساعة في الشهر",
                "grave"
            );
        }
    }
}
