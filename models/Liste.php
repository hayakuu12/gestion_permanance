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

    /* ===== INTERNAL HELPERS ===== */

    private function getDeptIdByName(string $name): ?string
    {
        $stmt = $this->conn->prepare("SELECT id FROM departments WHERE name = ? LIMIT 1");
        $stmt->execute([$name]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $row['id'] : null;
    }

    private function newId(string $prefix): string
    {
        return $prefix . bin2hex(random_bytes(7));
    }

    private function mapStatus(string $arabic): string
    {
        $map = ['تمت المصادقة' => 'VALID', 'مرفوضة' => 'INVALID'];
        return $map[$arabic] ?? 'PENDING';
    }

    public function statusAr(string $status): string
    {
        $map = ['VALID' => 'تمت المصادقة', 'INVALID' => 'مرفوضة', 'PENDING' => 'في الانتظار'];
        return $map[$status] ?? 'في الانتظار';
    }

    private function jourAr(string $date): string
    {
        $days = [1=>'الاثنين',2=>'الثلاثاء',3=>'الأربعاء',4=>'الخميس',5=>'الجمعة',6=>'السبت',7=>'الأحد'];
        $n = (int) date('N', strtotime($date));
        return $days[$n] ?? '';
    }

    private function typeJourAr(string $date_type): string
    {
        return $date_type === 'HOLIDAY' ? 'عيد وطني أو ديني' : 'عطلة أسبوعية';
    }

    /* ===== LISTS: CREATE ===== */

    public function ajouterListe($type_liste, $trimestre, $annee, $service, $fichier_original)
    {
        $dept_id = $this->getDeptIdByName($service);
        $ts      = (int)(microtime(true) * 1000);
        $fname   = $fichier_original ? basename($fichier_original) : null;

        if ($type_liste === 'permanence') {
            $id  = $this->newId('oc');
            $sql = "INSERT INTO on_call_lists
                        (id, department_id, year, period, status, incoming_pdf, incoming_filename, created_at)
                    VALUES (?, ?, ?, ?, 'PENDING', ?, ?, ?)";
            $this->conn->prepare($sql)->execute([$id, $dept_id, $annee, $trimestre, $fichier_original, $fname, $ts]);
        } else {
            $id  = $this->newId('ot');
            $sql = "INSERT INTO overtime_lists
                        (id, department_id, year, period, status, incoming_pdf, incoming_filename, created_at)
                    VALUES (?, ?, ?, ?, 'PENDING', ?, ?, ?)";
            $this->conn->prepare($sql)->execute([$id, $dept_id, $annee, $trimestre, $fichier_original, $fname, $ts]);
        }

        return $id;
    }

    /* ===== ELEMENTS: CREATE ===== */

    public function ajouterElement(
        $id_liste, $nom_complet, $numero_tajir, $cin, $cadre,
        $mois, $jour, $type_jour, $nombre_jours, $nombre_heures,
        $travaux = null, $date_debut = null, $date_fin = null
    ) {
        if (str_starts_with((string)$id_liste, 'oc')) {
            return $this->_insertOnCallDate($id_liste, $numero_tajir, $date_debut, $date_fin, $type_jour);
        }
        return $this->_insertOvertimeHours($id_liste, $numero_tajir, $mois, $nombre_heures, $travaux);
    }

    private function _insertOnCallDate($list_id, $employee_id, $date_debut, $date_fin, $type_jour)
    {
        if (empty($date_debut) || empty($employee_id)) return false;

        $date_type = (mb_strpos((string)$type_jour, 'عيد')    !== false
                   || mb_strpos((string)$type_jour, 'ديني')   !== false
                   || mb_strpos((string)$type_jour, 'وطني')   !== false)
            ? 'HOLIDAY' : 'WEEKEND';

        // Find or create on_call_record for this employee + list
        $stmt = $this->conn->prepare("SELECT id FROM on_call_records WHERE list_id = ? AND employee_id = ? LIMIT 1");
        $stmt->execute([$list_id, $employee_id]);
        $rec = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($rec) {
            $record_id = $rec['id'];
        } else {
            $this->conn->prepare("INSERT INTO on_call_records (list_id, employee_id, days_count) VALUES (?, ?, 0)")
                ->execute([$list_id, $employee_id]);
            $record_id = $this->conn->lastInsertId();
        }

        // Insert each date in the range
        $start = strtotime($date_debut);
        $end   = strtotime($date_fin ?? $date_debut);
        if ($start > $end) $end = $start;

        $inserted = 0;
        for ($ts = $start; $ts <= $end; $ts += 86400) {
            $d = date('Y-m-d', $ts);

            // Duplicate check: same employee + list + date
            $chk = $this->conn->prepare(
                "SELECT COUNT(*) FROM on_call_dates ocd
                 JOIN on_call_records ocr ON ocd.record_id = ocr.id
                 WHERE ocr.list_id = ? AND ocr.employee_id = ? AND ocd.date = ?"
            );
            $chk->execute([$list_id, $employee_id, $d]);
            if ($chk->fetchColumn() > 0) continue;

            $this->conn->prepare("INSERT INTO on_call_dates (record_id, date, date_type) VALUES (?, ?, ?)")
                ->execute([$record_id, $d, $date_type]);
            $inserted++;
        }

        // Sync days_count
        $this->conn->prepare(
            "UPDATE on_call_records SET days_count = (SELECT COUNT(*) FROM on_call_dates WHERE record_id = ?) WHERE id = ?"
        )->execute([$record_id, $record_id]);

        return $inserted > 0;
    }

    private function _insertOvertimeHours($list_id, $employee_id, $mois, $heures, $travaux)
    {
        $heures = floatval(str_replace(',', '.', $heures ?? 0));
        if ($heures <= 0 || empty($employee_id)) return false;

        $mois = intval($mois);
        if ($mois < 1 || $mois > 12) return false;

        // Get list period
        $stmt = $this->conn->prepare("SELECT period FROM overtime_lists WHERE id = ? LIMIT 1");
        $stmt->execute([$list_id]);
        $list = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$list) return false;

        $period = intval($list['period']);
        $bm     = ($period - 1) * 3 + 1;

        $col = null;
        if ($mois === $bm)       $col = 'month_1_hours';
        elseif ($mois === $bm+1) $col = 'month_2_hours';
        elseif ($mois === $bm+2) $col = 'month_3_hours';
        else return false;

        // Find or create overtime_record
        $stmt = $this->conn->prepare("SELECT id FROM overtime_records WHERE list_id = ? AND employee_id = ? LIMIT 1");
        $stmt->execute([$list_id, $employee_id]);
        $rec = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($rec) {
            $rid = $rec['id'];
            $this->conn->prepare("UPDATE overtime_records SET `$col` = `$col` + ? WHERE id = ?")
                ->execute([$heures, $rid]);
            $this->conn->prepare("UPDATE overtime_records SET total_hours = month_1_hours + month_2_hours + month_3_hours WHERE id = ?")
                ->execute([$rid]);
            if (!empty($travaux)) {
                $this->conn->prepare("UPDATE overtime_records SET work_done = ? WHERE id = ?")->execute([$travaux, $rid]);
            }
        } else {
            $cols = ['month_1_hours' => 0.0, 'month_2_hours' => 0.0, 'month_3_hours' => 0.0];
            $cols[$col] = $heures;
            $this->conn->prepare(
                "INSERT INTO overtime_records (list_id, employee_id, month_1_hours, month_2_hours, month_3_hours, total_hours, work_done)
                 VALUES (?, ?, ?, ?, ?, ?, ?)"
            )->execute([
                $list_id, $employee_id,
                $cols['month_1_hours'], $cols['month_2_hours'], $cols['month_3_hours'],
                array_sum($cols), $travaux
            ]);
        }

        return true;
    }

    /* ===== ELEMENTS: READ ===== */

    public function getAllElements()
    {
        return array_merge($this->_getPermRows([]), $this->_getHeuresRows([]));
    }

    public function getElementsByType($type)
    {
        if ($type === 'permanence')  return $this->_getPermRows([]);
        if ($type === 'heures_supp') return $this->_getHeuresRows([]);
        return [];
    }

    public function getElementsByListe($id_liste)
    {
        if (str_starts_with((string)$id_liste, 'oc')) {
            return $this->_getPermRows(['id_liste' => $id_liste]);
        }
        return $this->_getHeuresRows(['id_liste' => $id_liste]);
    }

    public function filtrerElements($annee=null, $trimestre=null, $service=null, $numero_tajir=null, $type_liste=null, $nom=null)
    {
        $f = compact('annee', 'trimestre', 'service', 'numero_tajir', 'nom');
        if ($type_liste === 'permanence')  return $this->_getPermRows($f);
        if ($type_liste === 'heures_supp') return $this->_getHeuresRows($f);
        return array_merge($this->_getPermRows($f), $this->_getHeuresRows($f));
    }

    private function _getPermRows(array $f): array
    {
        $sql = "
            SELECT
                ocd.id                              AS id_element,
                ocr.id                              AS record_id,
                ocr.list_id                         AS id_liste,
                ocr.employee_id                     AS numero_tajir,
                COALESCE(e.full_name, ocr.employee_id) AS nom_complet,
                COALESCE(e.position, '')            AS cadre,
                '' AS cin,
                d.name                              AS service,
                ol.year                             AS annee,
                ol.period                           AS trimestre,
                ol.status                           AS statut_en,
                ocr.days_count                      AS nombre_jours,
                ocd.date                            AS date_debut,
                ocd.date                            AS date_fin,
                ocd.date_type,
                'permanence'                        AS type_liste,
                NULL                                AS commentaire_validation
            FROM on_call_dates ocd
            JOIN on_call_records ocr ON ocd.record_id = ocr.id
            JOIN on_call_lists ol    ON ol.id = ocr.list_id
            LEFT JOIN employees e    ON e.id  = ocr.employee_id
            JOIN departments d       ON d.id  = ol.department_id
            WHERE 1=1
        ";
        $params = [];
        if (!empty($f['annee']))        { $sql .= " AND ol.year = ?";              $params[] = $f['annee']; }
        if (!empty($f['trimestre']))    { $sql .= " AND ol.period = ?";            $params[] = $f['trimestre']; }
        if (!empty($f['service']))      { $sql .= " AND d.name = ?";              $params[] = $f['service']; }
        if (!empty($f['numero_tajir'])) { $sql .= " AND ocr.employee_id LIKE ?";                         $params[] = '%'.$f['numero_tajir'].'%'; }
        if (!empty($f['nom']))          { $sql .= " AND COALESCE(e.full_name, ocr.employee_id) LIKE ?"; $params[] = '%'.$f['nom'].'%'; }
        if (!empty($f['id_liste']))     { $sql .= " AND ocr.list_id = ?";                               $params[] = $f['id_liste']; }
        $sql .= " ORDER BY ocd.id DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rows as &$r) {
            $r['statut']     = $this->statusAr($r['statut_en']);
            $r['jour']       = $this->jourAr($r['date_debut']);
            $r['type_jour']  = $this->typeJourAr($r['date_type']);
            $r['mois']       = intval(date('n', strtotime($r['date_debut'])));
            $r['nombre_heures'] = 0;
            $r['travaux']    = '';
        }
        return $rows;
    }

    private function _getHeuresRows(array $f): array
    {
        $sql = "
            SELECT
                otr.id                              AS id_element,
                otr.list_id                         AS id_liste,
                otr.employee_id                     AS numero_tajir,
                COALESCE(e.full_name, otr.employee_id) AS nom_complet,
                COALESCE(e.position, '')            AS cadre,
                '' AS cin,
                d.name                              AS service,
                otl.year                            AS annee,
                otl.period                          AS trimestre,
                otl.status                          AS statut_en,
                otr.month_1_hours,
                otr.month_2_hours,
                otr.month_3_hours,
                otr.total_hours                     AS nombre_heures,
                otr.work_done                       AS travaux,
                'heures_supp'                       AS type_liste,
                NULL                                AS commentaire_validation
            FROM overtime_records otr
            JOIN overtime_lists otl ON otl.id = otr.list_id
            LEFT JOIN employees e   ON e.id   = otr.employee_id
            JOIN departments d      ON d.id   = otl.department_id
            WHERE 1=1
        ";
        $params = [];
        if (!empty($f['annee']))        { $sql .= " AND otl.year = ?";             $params[] = $f['annee']; }
        if (!empty($f['trimestre']))    { $sql .= " AND otl.period = ?";           $params[] = $f['trimestre']; }
        if (!empty($f['service']))      { $sql .= " AND d.name = ?";              $params[] = $f['service']; }
        if (!empty($f['numero_tajir'])) { $sql .= " AND otr.employee_id LIKE ?";                         $params[] = '%'.$f['numero_tajir'].'%'; }
        if (!empty($f['nom']))          { $sql .= " AND COALESCE(e.full_name, otr.employee_id) LIKE ?"; $params[] = '%'.$f['nom'].'%'; }
        if (!empty($f['id_liste']))     { $sql .= " AND otr.list_id = ?";                               $params[] = $f['id_liste']; }
        $sql .= " ORDER BY otr.id DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rows as &$r) {
            $r['statut']     = $this->statusAr($r['statut_en']);
            $r['base_month'] = (intval($r['trimestre']) - 1) * 3 + 1;
            $r['date_debut'] = null;
            $r['date_fin']   = null;
            $r['jour']       = '';
            $r['type_jour']  = '';
            $r['mois']       = $r['base_month'];
            $r['nombre_jours'] = 0;
        }
        return $rows;
    }

    public function getElementById($id_element)
    {
        // Try overtime_records first
        $stmt = $this->conn->prepare(
            "SELECT otr.*, otl.period AS trimestre, otl.year AS annee, otl.status AS statut_en,
                    otr.list_id AS id_liste, d.name AS service,
                    COALESCE(e.full_name, otr.employee_id) AS nom_complet,
                    COALESCE(e.position, '') AS cadre
             FROM overtime_records otr
             JOIN overtime_lists otl  ON otl.id = otr.list_id
             LEFT JOIN employees e    ON e.id   = otr.employee_id
             JOIN departments d       ON d.id   = otl.department_id
             WHERE otr.id = ?"
        );
        $stmt->execute([$id_element]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $row['type_liste']   = 'heures_supp';
            $row['statut']       = $this->statusAr($row['statut_en']);
            $row['numero_tajir'] = $row['employee_id'];
            $row['base_month']   = (intval($row['trimestre']) - 1) * 3 + 1;
            return $row;
        }

        // Try on_call_dates
        $stmt = $this->conn->prepare(
            "SELECT ocd.id AS id_element, ocd.date AS date_debut, ocd.date AS date_fin, ocd.date_type,
                    ocr.list_id AS id_liste, ocr.employee_id AS numero_tajir, ocr.days_count AS nombre_jours, ocr.id AS record_id,
                    ol.period AS trimestre, ol.year AS annee, ol.status AS statut_en,
                    COALESCE(e.full_name, ocr.employee_id) AS nom_complet,
                    COALESCE(e.position, '') AS cadre,
                    d.name AS service
             FROM on_call_dates ocd
             JOIN on_call_records ocr ON ocd.record_id = ocr.id
             JOIN on_call_lists ol    ON ol.id = ocr.list_id
             LEFT JOIN employees e    ON e.id  = ocr.employee_id
             JOIN departments d       ON d.id  = ol.department_id
             WHERE ocd.id = ?"
        );
        $stmt->execute([$id_element]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $row['type_liste'] = 'permanence';
            $row['statut']     = $this->statusAr($row['statut_en']);
            $row['jour']       = $this->jourAr($row['date_debut']);
            $row['type_jour']  = $this->typeJourAr($row['date_type']);
            $row['mois']       = intval(date('n', strtotime($row['date_debut'])));
        }
        return $row ?: null;
    }

    /* ===== ELEMENTS: UPDATE ===== */

    public function updateElement(
        $id_element, $nom_complet, $numero_tajir, $cin, $cadre,
        $mois, $jour, $type_jour, $nombre_jours, $nombre_heures,
        $travaux, $date_debut, $date_fin
    ) {
        // Detect type
        $stmt = $this->conn->prepare("SELECT list_id FROM overtime_records WHERE id = ?");
        $stmt->execute([$id_element]);
        $rec = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($rec) {
            // Heures supp: update the three month columns and recalculate
            $stmt2 = $this->conn->prepare(
                "SELECT otl.period FROM overtime_records otr JOIN overtime_lists otl ON otr.list_id = otl.id WHERE otr.id = ?"
            );
            $stmt2->execute([$id_element]);
            $lst = $stmt2->fetch(PDO::FETCH_ASSOC);
            $bm  = $lst ? (intval($lst['period']) - 1) * 3 + 1 : 1;

            $this->conn->prepare(
                "UPDATE overtime_records SET month_1_hours=?, month_2_hours=?, month_3_hours=?,
                 total_hours=month_1_hours+month_2_hours+month_3_hours, work_done=? WHERE id=?"
            )->execute([
                floatval($nombre_heures[0] ?? 0),
                floatval($nombre_heures[1] ?? 0),
                floatval($nombre_heures[2] ?? 0),
                $travaux,
                $id_element
            ]);
            $this->conn->prepare("UPDATE overtime_records SET total_hours=month_1_hours+month_2_hours+month_3_hours WHERE id=?")
                ->execute([$id_element]);
            return true;
        }

        // Try on_call_dates
        $stmt = $this->conn->prepare("SELECT record_id FROM on_call_dates WHERE id = ?");
        $stmt->execute([$id_element]);
        if ($stmt->fetch()) {
            $dt        = $date_debut ?: date('Y-m-d');
            $date_type = (mb_strpos((string)$type_jour, 'عيد')  !== false
                       || mb_strpos((string)$type_jour, 'ديني') !== false
                       || mb_strpos((string)$type_jour, 'وطني') !== false)
                ? 'HOLIDAY' : 'WEEKEND';
            $this->conn->prepare("UPDATE on_call_dates SET date = ?, date_type = ? WHERE id = ?")
                ->execute([$dt, $date_type, $id_element]);
            return true;
        }

        return false;
    }

    /* Simplified update for heures_supp (single hours value for one month) */
    public function updateHeuresMonth($id_element, $mois, $heures, $travaux)
    {
        $stmt = $this->conn->prepare(
            "SELECT otr.list_id, otl.period FROM overtime_records otr JOIN overtime_lists otl ON otr.list_id=otl.id WHERE otr.id=?"
        );
        $stmt->execute([$id_element]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) return false;

        $bm = (intval($row['period']) - 1) * 3 + 1;
        $mois = intval($mois);
        $col = null;
        if ($mois === $bm)       $col = 'month_1_hours';
        elseif ($mois === $bm+1) $col = 'month_2_hours';
        elseif ($mois === $bm+2) $col = 'month_3_hours';
        if (!$col) return false;

        $this->conn->prepare("UPDATE overtime_records SET `$col` = ? WHERE id = ?")->execute([floatval($heures), $id_element]);
        $this->conn->prepare("UPDATE overtime_records SET total_hours = month_1_hours+month_2_hours+month_3_hours WHERE id=?")->execute([$id_element]);
        if (!empty($travaux)) {
            $this->conn->prepare("UPDATE overtime_records SET work_done=? WHERE id=?")->execute([$travaux, $id_element]);
        }
        return true;
    }

    /* ===== STATUS ===== */

    public function changerStatut($id_liste, $statut, $commentaire = null)
    {
        $status_en = $this->mapStatus($statut);

        if (str_starts_with((string)$id_liste, 'oc')) {
            return $this->conn->prepare("UPDATE on_call_lists SET status = ? WHERE id = ?")
                ->execute([$status_en, $id_liste]);
        }
        return $this->conn->prepare("UPDATE overtime_lists SET status = ? WHERE id = ?")
            ->execute([$status_en, $id_liste]);
    }

    /* ===== COUNTS ===== */

    public function countByType($type)
    {
        if ($type === 'permanence')  return $this->countPermanences();
        return $this->countHeuresSupp();
    }

    public function countPermanences()
    {
        return (int)$this->conn->query("SELECT COUNT(*) FROM on_call_lists")->fetchColumn();
    }

    public function countHeuresSupp()
    {
        return (int)$this->conn->query("SELECT COUNT(*) FROM overtime_lists")->fetchColumn();
    }

    public function countValides()
    {
        $oc = (int)$this->conn->query("SELECT COUNT(*) FROM on_call_lists WHERE status='VALID'")->fetchColumn();
        $ot = (int)$this->conn->query("SELECT COUNT(*) FROM overtime_lists WHERE status='VALID'")->fetchColumn();
        return $oc + $ot;
    }

    public function countRefusees()
    {
        $oc = (int)$this->conn->query("SELECT COUNT(*) FROM on_call_lists WHERE status='INVALID'")->fetchColumn();
        $ot = (int)$this->conn->query("SELECT COUNT(*) FROM overtime_lists WHERE status='INVALID'")->fetchColumn();
        return $oc + $ot;
    }

    public function countByStatut()
    {
        $sql = "
            SELECT statut_en, SUM(cnt) AS total FROM (
                SELECT status AS statut_en, COUNT(*) AS cnt FROM on_call_lists GROUP BY status
                UNION ALL
                SELECT status AS statut_en, COUNT(*) AS cnt FROM overtime_lists GROUP BY status
            ) u GROUP BY statut_en
        ";
        $rows = $this->conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        $result = [];
        foreach ($rows as $r) {
            $result[] = ['statut' => $this->statusAr($r['statut_en']), 'total' => $r['total']];
        }
        return $result;
    }

    public function countByService()
    {
        $sql = "
            SELECT d.name AS service, COUNT(*) AS total
            FROM (
                SELECT department_id FROM on_call_lists
                UNION ALL
                SELECT department_id FROM overtime_lists
            ) u
            JOIN departments d ON d.id = u.department_id
            GROUP BY d.name
        ";
        return $this->conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    /* ===== RECENT LISTS ===== */

    public function getRecentListes()
    {
        $sql = "
            SELECT id, 'permanence' AS type_liste, year AS annee, period AS trimestre,
                   status, department_id, created_at
            FROM on_call_lists
            UNION ALL
            SELECT id, 'heures_supp' AS type_liste, year AS annee, period AS trimestre,
                   status, department_id, created_at
            FROM overtime_lists
            ORDER BY created_at DESC LIMIT 10
        ";
        $rows = $this->conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as &$r) {
            $s = $this->conn->prepare("SELECT name FROM departments WHERE id = ?");
            $s->execute([$r['department_id']]);
            $d = $s->fetch(PDO::FETCH_ASSOC);
            $r['service'] = $d ? $d['name'] : '';
            $r['statut']  = $this->statusAr($r['status']);
        }
        return $rows;
    }

    /* ===== STATISTICS ===== */

    public function getStatsByService()
    {
        $sql = "
            SELECT d.name AS service,
                   SUM(CASE WHEN src='oc' THEN 1 ELSE 0 END) AS permanence,
                   SUM(CASE WHEN src='ot' THEN 1 ELSE 0 END) AS heures_supp
            FROM (
                SELECT ol.department_id, 'oc' AS src
                FROM on_call_records ocr JOIN on_call_lists ol ON ol.id = ocr.list_id
                UNION ALL
                SELECT otl.department_id, 'ot' AS src
                FROM overtime_records otr JOIN overtime_lists otl ON otl.id = otr.list_id
            ) u
            JOIN departments d ON d.id = u.department_id
            GROUP BY d.name ORDER BY d.name
        ";
        return $this->conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getHeuresSuppCeMois(int $mois, int $annee): float
    {
        $period = (int)ceil($mois / 3);
        $bm     = ($period - 1) * 3 + 1;

        $col = null;
        if ($mois === $bm)       $col = 'month_1_hours';
        elseif ($mois === $bm+1) $col = 'month_2_hours';
        elseif ($mois === $bm+2) $col = 'month_3_hours';
        if (!$col) return 0.0;

        $stmt = $this->conn->prepare(
            "SELECT COALESCE(SUM(otr.`$col`), 0)
             FROM overtime_records otr
             JOIN overtime_lists otl ON otl.id = otr.list_id
             WHERE otl.period = ? AND otl.year = ?"
        );
        $stmt->execute([$period, $annee]);
        return floatval($stmt->fetchColumn());
    }

    public function getPermanencesCeMois(int $mois, int $annee): int
    {
        $stmt = $this->conn->prepare(
            "SELECT COUNT(*) FROM on_call_dates ocd
             JOIN on_call_records ocr ON ocd.record_id = ocr.id
             JOIN on_call_lists ol    ON ol.id = ocr.list_id
             WHERE MONTH(ocd.date) = ? AND YEAR(ocd.date) = ?"
        );
        $stmt->execute([$mois, $annee]);
        return intval($stmt->fetchColumn());
    }

    public function getTopEmployeesHeures(int $limit = 5): array
    {
        $stmt = $this->conn->prepare(
            "SELECT e.full_name AS nom_complet, otr.employee_id AS numero_tajir, d.name AS service,
                    ROUND(SUM(otr.total_hours), 1) AS total_heures
             FROM overtime_records otr
             JOIN overtime_lists otl ON otl.id = otr.list_id
             JOIN employees e        ON e.id   = otr.employee_id
             JOIN departments d      ON d.id   = otl.department_id
             GROUP BY otr.employee_id, e.full_name, d.name
             ORDER BY total_heures DESC LIMIT ?"
        );
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTopEmployeesPermanences(int $limit = 5): array
    {
        $stmt = $this->conn->prepare(
            "SELECT e.full_name AS nom_complet, ocr.employee_id AS numero_tajir, d.name AS service,
                    COUNT(ocd.id) AS total_jours
             FROM on_call_records ocr
             JOIN on_call_dates ocd  ON ocd.record_id = ocr.id
             JOIN on_call_lists ol   ON ol.id = ocr.list_id
             JOIN employees e        ON e.id  = ocr.employee_id
             JOIN departments d      ON d.id  = ol.department_id
             GROUP BY ocr.employee_id, e.full_name, d.name
             ORDER BY total_jours DESC LIMIT ?"
        );
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getEvolutionMensuelle(int $annee): array
    {
        // Overtime: map period+month_col → calendar month
        $sqlH = "
            SELECT
                CASE WHEN period=1 THEN 1 WHEN period=2 THEN 4 WHEN period=3 THEN 7 WHEN period=4 THEN 10 END AS m1,
                CASE WHEN period=1 THEN 2 WHEN period=2 THEN 5 WHEN period=3 THEN 8 WHEN period=4 THEN 11 END AS m2,
                CASE WHEN period=1 THEN 3 WHEN period=2 THEN 6 WHEN period=3 THEN 9 WHEN period=4 THEN 12 END AS m3,
                month_1_hours, month_2_hours, month_3_hours
            FROM overtime_records otr
            JOIN overtime_lists otl ON otl.id = otr.list_id
            WHERE otl.year = ?
        ";
        $stmt = $this->conn->prepare($sqlH);
        $stmt->execute([$annee]);
        $heuresRows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $heuresByMonth = array_fill(1, 12, 0.0);
        foreach ($heuresRows as $r) {
            $heuresByMonth[(int)$r['m1']] += floatval($r['month_1_hours']);
            $heuresByMonth[(int)$r['m2']] += floatval($r['month_2_hours']);
            $heuresByMonth[(int)$r['m3']] += floatval($r['month_3_hours']);
        }

        // On-call: count by month of date
        $stmt = $this->conn->prepare(
            "SELECT MONTH(ocd.date) AS mois, COUNT(*) AS cnt
             FROM on_call_dates ocd
             JOIN on_call_records ocr ON ocd.record_id = ocr.id
             JOIN on_call_lists ol    ON ol.id = ocr.list_id
             WHERE YEAR(ocd.date) = ?
             GROUP BY MONTH(ocd.date)"
        );
        $stmt->execute([$annee]);
        $permByMonth = array_fill(1, 12, 0);
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
            $permByMonth[intval($r['mois'])] = intval($r['cnt']);
        }

        $result = [];
        for ($m = 1; $m <= 12; $m++) {
            $result[] = ['mois' => $m, 'total_heures' => $heuresByMonth[$m], 'total_perm' => $permByMonth[$m]];
        }
        return $result;
    }

    public function getNombreEmployesActifs(): int
    {
        $sql = "SELECT COUNT(DISTINCT employee_id) FROM (
                    SELECT employee_id FROM on_call_records
                    UNION
                    SELECT employee_id FROM overtime_records
                ) u";
        return intval($this->conn->query($sql)->fetchColumn());
    }

    public function getMoyenneHeuresParEmploye(): float
    {
        $sql = "SELECT COALESCE(AVG(sub.total), 0)
                FROM (
                    SELECT employee_id, SUM(total_hours) AS total
                    FROM overtime_records WHERE total_hours > 0
                    GROUP BY employee_id
                ) sub";
        return floatval($this->conn->query($sql)->fetchColumn());
    }

    public function getComparaisonMois(int $moisActuel, int $annee): array
    {
        $moisPrec  = $moisActuel === 1 ? 12 : $moisActuel - 1;
        $anneePrec = $moisActuel === 1 ? $annee - 1 : $annee;

        $heures_actuel    = $this->_getHeuresMois($moisActuel, $annee);
        $heures_precedent = $this->_getHeuresMois($moisPrec, $anneePrec);

        $stmtPerm = $this->conn->prepare(
            "SELECT COUNT(*) FROM on_call_dates ocd
             JOIN on_call_records ocr ON ocd.record_id = ocr.id
             WHERE MONTH(ocd.date) = ? AND YEAR(ocd.date) = ?"
        );
        $stmtPerm->execute([$moisActuel, $annee]);
        $perm_actuel = intval($stmtPerm->fetchColumn());

        $stmtPerm->execute([$moisPrec, $anneePrec]);
        $perm_precedent = intval($stmtPerm->fetchColumn());

        return compact('heures_actuel', 'heures_precedent', 'perm_actuel', 'perm_precedent');
    }

    private function _getHeuresMois(int $mois, int $annee): float
    {
        $period = (int)ceil($mois / 3);
        $bm     = ($period - 1) * 3 + 1;
        $col = null;
        if ($mois === $bm)       $col = 'month_1_hours';
        elseif ($mois === $bm+1) $col = 'month_2_hours';
        elseif ($mois === $bm+2) $col = 'month_3_hours';
        if (!$col) return 0.0;

        $stmt = $this->conn->prepare(
            "SELECT COALESCE(SUM(otr.`$col`), 0)
             FROM overtime_records otr
             JOIN overtime_lists otl ON otl.id = otr.list_id
             WHERE otl.period = ? AND otl.year = ?"
        );
        $stmt->execute([$period, $annee]);
        return floatval($stmt->fetchColumn());
    }

    /* ===== DUPLICATE CHECK ===== */

    public function elementExiste($id_liste, $numero_tajir, $mois, $jour = null)
    {
        if (str_starts_with((string)$id_liste, 'oc')) {
            $stmt = $this->conn->prepare(
                "SELECT COUNT(*) FROM on_call_dates ocd
                 JOIN on_call_records ocr ON ocd.record_id = ocr.id
                 WHERE ocr.list_id = ? AND ocr.employee_id = ? AND MONTH(ocd.date) = ?"
            );
            $stmt->execute([$id_liste, $numero_tajir, $mois]);
        } else {
            $stmt = $this->conn->prepare(
                "SELECT COUNT(*) FROM overtime_records WHERE list_id = ? AND employee_id = ?"
            );
            $stmt->execute([$id_liste, $numero_tajir]);
        }
        return intval($stmt->fetchColumn()) > 0;
    }

    /* ===== DEPARTMENTS (for dropdowns) ===== */

    public function getAllDepartments(): array
    {
        return $this->conn->query("SELECT id, name FROM departments ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getDeptIdFromList(string $id_liste): ?string
    {
        $table = str_starts_with($id_liste, 'oc') ? 'on_call_lists' : 'overtime_lists';
        $stmt  = $this->conn->prepare("SELECT department_id FROM `$table` WHERE id = ? LIMIT 1");
        $stmt->execute([$id_liste]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $row['department_id'] : null;
    }
}
