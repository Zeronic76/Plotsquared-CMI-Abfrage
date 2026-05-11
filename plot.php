<?php
/**
 * Project: PlotSquared & CMI Web Query
 * License: GNU General Public License v3.0
 * Copyright (c) 2026 Jörg Stöhrmann / Zeronic76
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation.
 */

// --- 1. ZENTRALE KONFIGURATION ---
$config = [
    'server_name' => 'DeinServer',            // Hier den Namen eintragen
    'db_host' => '127.0.0.1',                 // IP oder localhost
    'db_user' => 'DEIN_DATENBANK_USER',       // Dein DB-Benutzer
    'db_pass' => 'DEIN_DATENBANK_PASSWORT',   // Dein DB-Passwort
    'db_plots' => 'plotsquared',              // Name der PlotSquared Datenbank
    'table_plot' => 'plot',
    'db_user_sys' => 'cmi',                   // Name der CMI Datenbank
    'table_users' => 'CMI_users',
    'col_uuid' => 'player_uuid',
    'col_name' => 'username'
];

// Fehleranzeige (für Entwicklung aktiv)
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

try {
    $pdo = new PDO("mysql:host={$config['db_host']};dbname={$config['db_plots']};charset=utf8mb4", $config['db_user'], $config['db_pass'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
} catch (Exception $e) {
    die("Datenbank-Fehler: " . $e->getMessage());
}

// --- 2. LOGIK ---
$search = isset($_GET['query']) ? trim($_GET['query']) : '';
$type = isset($_GET['type']) ? $_GET['type'] : 'name';
$results = [];
$playerData = null;
$displayName = "";
$uuid = "";
$errorMsg = "";

if ($search) {
    if ($type === 'name') {
        // SUCHE ÜBER NAME (Mojang API)
        $api_url = "https://api.mojang.com/users/profiles/minecraft/" . urlencode($search);
        $response = @file_get_contents($api_url);
        if ($response) {
            $mojangdata = json_decode($response, true);
            $raw_uuid = $mojangdata['id'];
            $uuid = substr($raw_uuid, 0, 8) . '-' . substr($raw_uuid, 8, 4) . '-' . substr($raw_uuid, 12, 4) . '-' . substr($raw_uuid, 16, 4) . '-' . substr($raw_uuid, 20);
            $displayName = $mojangdata['name'];
        } else {
            $errorMsg = "Spielername konnte bei Mojang nicht gefunden werden.";
        }
    } else {
        // SUCHE ÜBER UUID (Direkt)
        $uuid = $search;
        // Name aus CMI Datenbank holen für die Anzeige
        $stmtName = $pdo->prepare("SELECT {$config['col_name']} FROM {$config['db_user_sys']}.{$config['table_users']} WHERE {$config['col_uuid']} = ? LIMIT 1");
        $stmtName->execute([$uuid]);
        $dbPlayer = $stmtName->fetch(PDO::FETCH_ASSOC);
        $displayName = $dbPlayer ? $dbPlayer[$config['col_name']] : "Unbekannter Spieler";
    }

    if ($uuid && empty($errorMsg)) {
        $sql = " SELECT p.world, p.plot_id_x, p.plot_id_z, p.timestamp, 'OWNER' as role, u.{$config['col_name']} as owner_name
                 FROM {$config['table_plot']} p
                 LEFT JOIN {$config['db_user_sys']}.{$config['table_users']} u ON p.owner = u.{$config['col_uuid']}
                 WHERE p.owner = :uuid
                 UNION
                 SELECT p.world, p.plot_id_x, p.plot_id_z, p.timestamp, 'HELPED' as role, u.{$config['col_name']} as owner_name
                 FROM {$config['table_plot']} p
                 INNER JOIN plot_helpers ph ON ph.plot_plot_id = p.id
                 LEFT JOIN {$config['db_user_sys']}.{$config['table_users']} u ON p.owner = u.{$config['col_uuid']}
                 WHERE ph.user_uuid = :uuid
                 UNION
                 SELECT p.world, p.plot_id_x, p.plot_id_z, p.timestamp, 'TRUSTED' as role, u.{$config['col_name']} as owner_name
                 FROM {$config['table_plot']} p
                 INNER JOIN plot_trusted pt ON pt.plot_plot_id = p.id
                 LEFT JOIN {$config['db_user_sys']}.{$config['table_users']} u ON p.owner = u.{$config['col_uuid']}
                 WHERE pt.user_uuid = :uuid
                 ORDER BY world ASC ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute(['uuid' => $uuid]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($config['server_name']) ?> | Plot-Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #0d0d0d; color: #e0e0e0; font-family: sans-serif; }
        .admin-card { background-color: #1a1a1a; border: 1px solid #333; border-radius: 12px; }
        .input-group-text { background-color: #252525; border-color: #333; color: #aaa; }
        .badge-OWNER { background-color: #d63031; }
        .badge-TRUSTED { background-color: #fdcb6e; color: #000; }
        .badge-HELPED { background-color: #0984e3; }
        .font-monospace { font-family: 'Courier New', Courier, monospace; }
    </style>
</head>
<body>

<div class="container py-5">
    <h3 class="mb-4 text-white"><?= htmlspecialchars($config['server_name']) ?> <span class="text-primary">Plot-Admin</span></h3>
        <span class="badge bg-dark border border-secondary text-white-50">5.2.1</span>

    <!-- Suchbereich -->
    <div class="card admin-card mb-4 shadow-lg">
        <div class="card-body">
            <form action="" method="GET">
                <div class="input-group">
                    <input type="text" name="query" class="form-control bg-dark text-white border-secondary"
                           placeholder="Name oder UUID..." value="<?= htmlspecialchars($search) ?>" required>

                    <span class="input-group-text">
                        <div class="form-check form-check-inline mb-0">
                            <input class="form-check-input" type="radio" name="type" id="type_name" value="name" <?= $type === 'name' ? 'checked' : '' ?>>
                            <label class="form-check-label small" for="type_name">Name</label>
                        </div>
                        <div class="form-check form-check-inline mb-0">
                            <input class="form-check-input" type="radio" name="type" id="type_uuid" value="uuid" <?= $type === 'uuid' ? 'checked' : '' ?>>
                            <label class="form-check-label small" for="type_uuid">UUID</label>
                        </div>
                    </span>

                    <button type="submit" class="btn btn-primary px-4 fw-bold">SUCHEN</button>
                </div>
            </form>
        </div>
    </div>

    <?php if ($errorMsg): ?>
        <div class="alert alert-danger border-0"><?= $errorMsg ?></div>
    <?php endif; ?>
    <!-- Ausgabe -->
    <?php if ($uuid && empty($errorMsg)): ?>
        <div class="card admin-card shadow-lg">
            <div class="card-header border-secondary d-flex justify-content-between align-items-center bg-transparent py-3">
                <span class="text-white-50 font-monospace">Ergebnisse für: <strong class="text-info" style="font-size: 1.2rem;"><?= htmlspecialchars($displayName) ?></strong></span>
                <small class="text-white-50 font-monospace"><?= htmlspecialchars($uuid) ?></small>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-dark table-hover mb-0 text-center">
                        <thead>
                            <tr class="text-muted small">
                                <th>WELT</th>
                                <th>KOORDINATEN (X;Z)</th>
                                <th>BESITZER</th>
                                <th>AKTION</th>
                                <th>ROLLE</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($results as $plot): ?>
                            <tr>
                                <td class="align-middle"><?= htmlspecialchars($plot['world']) ?></td>
                                <td class="align-middle"><strong><?= $plot['plot_id_x'] ?>;<?= $plot['plot_id_z'] ?></strong></td>
                                <td class="align-middle text-warning">
                                    <?= $plot['owner_name'] ? htmlspecialchars($plot['owner_name']) : '<small class="text-muted">Unbekannt</small>' ?>
                                </td>
                                <td class="align-middle">
                                    <button class="btn btn-sm btn-outline-light" style="font-size: 0.75rem;"
                                            onclick="navigator.clipboard.writeText('/plot visit <?= $plot['plot_id_x'] ?>;<?= $plot['plot_id_z'] ?>'); alert('Befehl kopiert!');">
                                        VISIT KOPIEREN
                                    </button>
                                </td>
                                <td class="align-middle">
                                    <span class="badge badge-<?= $plot['role'] ?>"><?= $plot['role'] ?></span>
                                </td>
                            </tr>
                            <?php endforeach; ?>

                            <?php if (empty($results)): ?>
                                <tr>
                                    <td colspan="5" class="py-5 text-muted italic">Keine Grundstücke gefunden.</td>
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
