<?php
declare(strict_types=1);

namespace App\Models;

use PDO;

class Asset extends BaseModel
{

public function search(
    string $search = '',
    string $type = '',
    string $statut = '',
    bool $availableOnly = false
): array {
    $sql = "
        SELECT
            a.*,
            u.nom AS affecte_nom,
            u.prenom AS affecte_prenom,
            u.email AS affecte_email,
            asg.date_fin_prevue
        FROM assets a
        LEFT JOIN assignments asg
            ON a.id = asg.asset_id
            AND asg.date_restitution IS NULL
        LEFT JOIN users u ON asg.user_id = u.id
        WHERE 1=1
    ";

    $params = [];

    $sql .= " AND (
    a.designation LIKE :designation_search
    OR a.code_inventaire LIKE :code_search
)";

$params['designation_search'] = '%' . $search . '%';
$params['code_search'] = '%' . $search . '%';

    if ($type !== '') {
        $sql .= " AND a.type = :type";
        $params['type'] = $type;
    }

    if ($statut !== '') {
        $sql .= " AND a.statut = :statut";
        $params['statut'] = $statut;
    }

    if ($availableOnly) {
        $sql .= " AND a.statut = 'disponible'";
    }

    $sql .= " ORDER BY a.date_achat DESC";

    $stmt = $this->db->prepare($sql);
    $stmt->execute($params);

    return $stmt->fetchAll();
}


    public function findById(int $id): ?array
    {
        $sql = "
            SELECT 
                a.*,
                u.nom AS affecte_nom,
                u.prenom AS affecte_prenom,
                asg.date_debut AS affecte_date_debut,
                asg.date_fin_prevue AS affecte_date_fin_prevue
            FROM assets a
            LEFT JOIN assignments asg ON a.id = asg.asset_id AND asg.date_restitution IS NULL
            LEFT JOIN users u ON asg.user_id = u.id
            WHERE a.id = :id
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $asset = $stmt->fetch();
        return $asset ?: null;
    }
}