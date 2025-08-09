<?php
/**
 * Devuelve un array [$chef, $metricas] donde:
 *  - $chef: ['chef_id'=>int, 'nombre'=>string]
 *  - $metricas: [
 *       'estres' => ['valor'=>float,'estado'=>string,'min'=>float,'fecha'=>string],
 *       'energia'=>[…], …
 *     ]
 */
function obtenerChefYMetricas(PDO $pdo, int $chef_id): array
{
    $stmt = $pdo->prepare("CALL sp_kpis_por_chef_id(:chef_id)");
    $stmt->bindParam(':chef_id', $chef_id, PDO::PARAM_INT);
    $stmt->execute();

    $chef     = null;
    $metricas = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        if ($chef === null) {
            $chef = [
                'chef_id' => (int)$row['chef_id'],
                'nombre_completo'  => $row['nombre_completo'],
            ];
        }
        $kpi = $row['kpi'];
        $metricas[$kpi] = [
            'Valor'  => (float)$row['Valor'],
            'estado' => $row['estado'],
            'min'    => (float)$row['min'],
            'fecha'  => $row['fecha'],
        ];
    }
    // Importante para permitir otras llamadas a SP
    $stmt->closeCursor();

    return [$chef, $metricas];
}
