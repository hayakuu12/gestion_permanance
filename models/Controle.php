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

    public function ajouterControle($id_element, $type_controle, $message, $niveau)
    {
        $sql = "INSERT INTO controles 
                (id_element, type_controle, message, niveau)
                VALUES (?, ?, ?, ?)";

        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([
            $id_element,
            $type_controle,
            $message,
            $niveau
        ]);
    }

    public function verifierDepassementPermanence($id_element, $nombre_jours)
    {
        if ($nombre_jours > 6) {
            $this->ajouterControle(
                $id_element,
                "تجاوز الديمومة",
                "تجاوز الحد المسموح به للديمومة: 6 أيام في الشهر",
                "grave"
            );
        }
    }

    public function verifierDepassementHeures($id_element, $nombre_heures)
    {
        if ($nombre_heures > 24) {
            $this->ajouterControle(
                $id_element,
                "تجاوز الساعات الإضافية",
                "تجاوز الحد المسموح به للساعات الإضافية: 24 ساعة في الشهر",
                "grave"
            );
        }
    }

    public function verifierConflitPermanenceHeures()
    {
        $sql = "
            SELECT e1.id_element, e1.nom_complet, e1.numero_tajir
            FROM elements_liste e1
            JOIN listes l1 ON e1.id_liste = l1.id_liste
            JOIN elements_liste e2 ON e1.numero_tajir = e2.numero_tajir
            JOIN listes l2 ON e2.id_liste = l2.id_liste
            WHERE l1.type_liste = 'permanence'
            AND l2.type_liste = 'heures_supp'
            AND l1.trimestre = l2.trimestre
            AND l1.annee = l2.annee
        ";

        $stmt = $this->conn->query($sql);
        $resultats = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($resultats as $row) {
            $this->ajouterControle(
                $row['id_element'],
                "تعارض بين اللوائح",
                "الموظف موجود في لائحة الديمومة ولائحة الساعات الإضافية",
                "grave"
            );
        }
    }

    public function getControles()
    {
        $sql = "

        SELECT
            c.*,
            e.nom_complet,
            e.numero_tajir

        FROM controles c

        INNER JOIN elements_liste e
        ON c.id_element = e.id_element

        ORDER BY c.id_controle DESC

    ";

        $stmt =
            $this->conn->prepare($sql);

        $stmt->execute();

        return
            $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function countControles()
    {
        $sql =
            "SELECT COUNT(*) as total
     FROM controles";

        $stmt =
            $this->conn->prepare($sql);

        $stmt->execute();

        return
            $stmt->fetch()['total'];
    }
}