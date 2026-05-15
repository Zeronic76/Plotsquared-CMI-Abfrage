<?php
/**
 * Project: PlotSquared / CMI / LuckPerms Web Query
 * License: GNU General Public License v3.0
 * Copyright (c) 2026 Jörg Stöhrmann / Zeronic76
 *   Version: 2.5.0
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation.
 */

// ------------------------------------------------------------
// LOADER-LOGIK
// ------------------------------------------------------------

$configPhp = __DIR__ . '/config.php';
$configInc = __DIR__ . '/config.inc.php';

if (file_exists($configPhp)) {
    $config = include($configPhp);
} else {

    if (!file_exists($configInc)) {
        $url = "https://raw.githubusercontent.com/Zeronic76/Plotsquared-CMI-Abfrage/main/config.inc.php";
        $data = @file_get_contents($url);

        if ($data === false) {
            die("FEHLER: config.inc.php konnte nicht von GitHub geladen werden!");
        }

        file_put_contents($configInc, $data);
    }

    header("Location: install.php");
    exit;
}

// ------------------------------------------------------------
// SPRACHE LADEN
// ------------------------------------------------------------

$langFile = __DIR__ . '/lang/' . ($config['language'] ?? 'de') . '.php';

if (!file_exists($langFile)) {
    $langFile = __DIR__ . '/lang/de.php'; // Fallback
}

include $langFile;

// ------------------------------------------------------------
// DB-VERBINDUNG
// ------------------------------------------------------------

try {
    $dsn = "mysql:host={$config['db_host']};port={$config['db_port']};dbname={$config['db_plots']};charset=utf8mb4";
    $pdo = new PDO($dsn, $config['db_user'], $config['db_pass'], [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (Exception $e) {
    die("Datenbankfehler (PlotSquared): " . $e->getMessage());
}

// ------------------------------------------------------------
// NAMENSQUELLE LADEN (CMI oder LuckPerms)
// ------------------------------------------------------------

$source   = $config['player_source'];
$userCfg  = $config[$source];

$dbUserSys  = $userCfg['db'];
$tableUsers = $userCfg['table_users'];
$colUUID    = $userCfg['col_uuid'];
$colName    = $userCfg['col_name'];

// ------------------------------------------------------------
// EINGABE
// ------------------------------------------------------------

$search = isset($_GET['query']) ? trim($_GET['query']) : '';
$results = [];
$displayName = "";
$uuid = "";
$errorMsg = "";
$displayUUID = "";

// ------------------------------------------------------------
// AUTOMATISCHE ERKENNUNG: NAME ODER UUID
// ------------------------------------------------------------

if ($search) {
    if (preg_match('/^[0-9a-fA-F-]{32,36}$/', $search)) {
        $type = 'uuid';
    } else {
        $type = 'name';
    }
}

// ------------------------------------------------------------
// UUID OHNE BINDSTRICHE AUTOMATISCH FORMATIEREN
// ------------------------------------------------------------

if ($search && $type === 'uuid') {

    $clean = str_replace('-', '', $search);

    if (strlen($clean) === 32) {
        $search = substr($clean, 0, 8) . '-' .
                  substr($clean, 8, 4) . '-' .
                  substr($clean, 12, 4) . '-' .
                  substr($clean, 16, 4) . '-' .
                  substr($clean, 20);
    }

    $displayUUID = strtolower($search);
}

// ------------------------------------------------------------
// UUID / NAME ERMITTELN
// ------------------------------------------------------------

if ($search) {

    if ($type === 'name') {

        $api_url = "https://api.mojang.com/users/profiles/minecraft/" . urlencode($search);
        $response = @file_get_contents($api_url);

        if ($response) {
            $mojang = json_decode($response, true);
            $raw = $mojang['id'];

            $uuid = substr($raw,0,8)."-".substr($raw,8,4)."-".substr($raw,12,4)."-".substr($raw,16,4)."-".substr($raw,20);
            $displayName = $mojang['name'];
            $displayUUID = strtolower($uuid);

        } else {
            $errorMsg = $lang['error_mojang'];
        }

    } else {

        $uuid = $search;
        $displayUUID = strtolower($uuid);

        try {
            $stmt = $pdo->prepare("
                SELECT $colName
                FROM $dbUserSys.$tableUsers
                WHERE $colUUID = ?
                LIMIT 1
            ");
            $stmt->execute([$uuid]);
            $row = $stmt->fetch();

            $displayName = $row ? $row[$colName] : "Unknown Player";

        } catch (Exception $e) {
            $errorMsg = "Fehler bei der Namensauflösung: " . $e->getMessage();
        }

        // LUCKPERMS: ECHTEN MOJANG-NAMEN FÜR DEN GESUCHTEN SPIELER HOLEN
        if ($source === 'luckperms' && $uuid) {

            $mojang_api = "https://sessionserver.mojang.com/session/minecraft/profile/" . str_replace('-', '', $uuid);
            $mojang_json = @file_get_contents($mojang_api);

            if ($mojang_json) {
                $mojang_data = json_decode($mojang_json, true);

                if (!empty($mojang_data['name'])) {
                    $displayName = $mojang_data['name']; // Original Case
                }
            }
        }
    }

    // ------------------------------------------------------------
    // PLOT-ABFRAGE
    // ------------------------------------------------------------

    if ($uuid && !$errorMsg) {

        $sql = "
            (
                SELECT
                    p.id AS plot_id,
                    p.world,
                    p.plot_id_x AS x,
                    p.plot_id_z AS z,
                    p.owner AS owner_uuid,
                    'OWNER' AS role,
                    u.$colName AS owner_name
                FROM {$config['table_plot']} p
                LEFT JOIN $dbUserSys.$tableUsers u ON p.owner = u.$colUUID
                WHERE p.owner = :uuid
            )

            UNION ALL

            (
                SELECT
                    p.id AS plot_id,
                    p.world,
                    p.plot_id_x AS x,
                    p.plot_id_z AS z,
                    p.owner AS owner_uuid,
                    'TRUSTED' AS role,
                    u.$colName AS owner_name
                FROM {$config['table_plot']} p
                INNER JOIN {$config['table_helpers']} h ON h.plot_plot_id = p.id
                LEFT JOIN $dbUserSys.$tableUsers u ON p.owner = u.$colUUID
                WHERE h.user_uuid = :uuid
            )

            UNION ALL

            (
                SELECT
                    p.id AS plot_id,
                    p.world,
                    p.plot_id_x AS x,
                    p.plot_id_z AS z,
                    p.owner AS owner_uuid,
                    'HELPER' AS role,
                    u.$colName AS owner_name
                FROM {$config['table_plot']} p
                INNER JOIN {$config['table_trusted']} t ON t.plot_plot_id = p.id
                LEFT JOIN $dbUserSys.$tableUsers u ON p.owner = u.$colUUID
                WHERE t.user_uuid = :uuid
            )

            ORDER BY world ASC, plot_id ASC
        ";

        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['uuid' => $uuid]);
            $results = $stmt->fetchAll();
        } catch (Exception $e) {
            $errorMsg = "Fehler bei der Plot-Abfrage: " . $e->getMessage();
        }
    }
}

// ------------------------------------------------------------
// LUCKPERMS: MOJANG-NAMEN FÜR ALLE OWNER (OWNER/HELPER/TRUSTED)
// ------------------------------------------------------------

$mojangCache = [];

if ($source === 'luckperms' && !empty($results)) {
    foreach ($results as &$plot) {
        $ownerUuid = $plot['owner_uuid'] ?? null;

        if (!$ownerUuid) {
            continue;
        }

        $cleanUuid = str_replace('-', '', $ownerUuid);

        if (isset($mojangCache[$cleanUuid])) {
            $plot['owner_name'] = $mojangCache[$cleanUuid];
            continue;
        }

        $mojang_api = "https://sessionserver.mojang.com/session/minecraft/profile/" . $cleanUuid;
        $mojang_json = @file_get_contents($mojang_api);

        if ($mojang_json) {
            $mojang_data = json_decode($mojang_json, true);

            if (!empty($mojang_data['name'])) {
                $mojangCache[$cleanUuid] = $mojang_data['name'];
                $plot['owner_name']      = $mojang_data['name'];
            }
        }
    }
    unset($plot);
}
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($config['language']) ?>">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($config['server_name']) ?> – <?= $lang['title'] ?></title>

    <link href="css/bootstrap.min.css" rel="stylesheet">

    <style>
        body { background-color: #0d0d0d; color: #e0e0e0; font-family: sans-serif; }
        
        /* Fix für abgerundete Ecken und saubere Optik */
        .admin-card { 
            background-color: #1a1a1a; 
            border: 1px solid #333; 
            border-radius: 12px; 
            overflow: hidden; /* Wichtig für abgerundete Ecken an der Tabelle */
        }
        
        /* Entfernt die letzte Linie der Tabelle für perfekten Abschluss */
        .table > :last-child > :last-child > * {
            border-bottom-width: 0;
        }

        .badge-OWNER { background-color: #d63031; }
        .badge-TRUSTED { background-color: #fdcb6e; color: #000; }
        .badge-HELPER { background-color: #0984e3; }
    </style>
</head>
<body>

<div class="container py-5">

    <h3 class="mb-4 text-white">
        <?= htmlspecialchars($config['server_name']) ?>
        <span class="text-primary"><?= $lang['title'] ?></span>
    </h3>

    <div class="card admin-card mb-4 shadow-lg">
        <div class="card-body">
            <form action="" method="GET">
                <div class="input-group">

                    <input type="text" name="query" class="form-control bg-dark text-white border-secondary"
                           placeholder="<?= $lang['search_placeholder'] ?>"
                           value="<?= htmlspecialchars($search) ?>" required>

                    <button type="submit" class="btn btn-primary px-4 fw-bold">
                        <?= $lang['search_btn'] ?>
                    </button>

                </div>
            </form>
        </div>
    </div>

    <?php if ($errorMsg): ?>
        <div class="alert alert-danger border-0"><?= $errorMsg ?></div>
    <?php endif; ?>

    <?php if ($uuid && empty($errorMsg)): ?>

        <div class="card admin-card shadow-lg">
            <div class="card-header border-secondary d-flex justify-content-between align-items-center bg-transparent py-3">
                <span class="text-white-50 font-monospace">
                    <?= $lang['results_for'] ?>
                    <strong class="text-info" style="font-size: 1.2rem;"><?= htmlspecialchars($displayName) ?></strong>
                </span>
                <small class="text-white-50 font-monospace"><?= htmlspecialchars($displayUUID) ?></small>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-dark table-hover mb-0 text-center">
                        <thead>
                            <tr class="text-muted small">
                                <th><?= $lang['table_world'] ?></th>
                                <th><?= $lang['table_coords'] ?></th>
                                <th><?= $lang['table_owner'] ?></th>
                                <th><?= $lang['table_action'] ?></th>
                                <th><?= $lang['table_role'] ?></th>
                            </tr>
                        </thead>
                        <tbody>

                            <?php foreach ($results as $plot): ?>

                            <?php
                            // Besitzeranzeige:
                            // OWNER  → gesuchter Spieler (displayName)
                            // HELPER/TRUSTED → Plot-Besitzer (owner_name, ggf. Mojang-korrigiert)
                            $ownerDisplay = ($plot['role'] === 'OWNER')
                                ? $displayName
                                : ($plot['owner_name'] ?: '<small class="text-muted">?</small>');
                            ?>

                            <tr>
                                <td class="align-middle"><?= htmlspecialchars($plot['world']) ?></td>

                                <td class="align-middle">
                                    <strong><?= $plot['x'] ?>;<?= $plot['z'] ?></strong>
                                </td>

                                <td class="align-middle text-warning">
                                    <?= is_string($ownerDisplay) ? htmlspecialchars($ownerDisplay) : $ownerDisplay ?>
                                </td>

                                <td class="align-middle">
                                    <button class="btn btn-sm btn-outline-light" style="font-size: 0.75rem;"
                                            onclick="navigator.clipboard.writeText('/plot visit <?= $plot['x'] ?>;<?= $plot['z'] ?>'); alert('Copied!');">
                                        <?= $lang['btn_visit'] ?>
                                    </button>
                                </td>

                                <td class="align-middle">
                                    <span class="badge badge-<?= $plot['role'] ?>"><?= $plot['role'] ?></span>
                                </td>
                            </tr>

                            <?php endforeach; ?>

                            <?php if (empty($results)): ?>
                                <tr>
                                    <td colspan="5" class="py-5 text-muted italic"><?= $lang['no_plots'] ?></td>
                                </tr>
                            <?php endif; ?>

                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    <?php endif; ?>

</div>

</body>
</html>
