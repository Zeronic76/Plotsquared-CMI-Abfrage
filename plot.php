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
// 1. KONFIGURATION LADEN
if (file_exists(__DIR__ . '/config.php')) {
    include(__DIR__ . '/config.php');
} else {
    die("FEHLER: Die Datei config.php wurde nicht gefunden! Bitte erstelle sie basierend auf der Vorlage.");
}

// 2. SPRACHE LADEN
$lang_file = __DIR__ . "/lang/{$config['language']}.php";
if (file_exists($lang_file)) {
    include($lang_file);
} else {
    die("Language file not found: " . htmlspecialchars($lang_file));
}

// --- 2. SPRACHE LADEN ---
$lang_file = __DIR__ . "/lang/{$config['language']}.php";
if (file_exists($lang_file)) {
    include($lang_file);
} else {
    die("Language file not found: " . htmlspecialchars($lang_file));
}

// --- 3. DATENBANK VERBINDUNG ---
try {
    $pdo = new PDO("mysql:host={$config['db_host']};dbname={$config['db_plots']};charset=utf8mb4", $config['db_user'], $config['db_pass'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
} catch (Exception $e) {
    die("Datenbank-Fehler: " . $e->getMessage());
}

// --- 4. LOGIK ---
$search = isset($_GET['query']) ? trim($_GET['query']) : '';
$type = isset($_GET['type']) ? $_GET['type'] : 'name';
$results = [];
$displayName = "";
$uuid = "";
$errorMsg = "";

if ($search) {
    if ($type === 'name') {
        $api_url = "https://api.mojang.com/users/profiles/minecraft/" . urlencode($search);
        $response = @file_get_contents($api_url);
        if ($response) {
            $mojangdata = json_decode($response, true);
            $raw_uuid = $mojangdata['id'];
            $uuid = substr($raw_uuid, 0, 8) . '-' . substr($raw_uuid, 8, 4) . '-' . substr($raw_uuid, 12, 4) . '-' . substr($raw_uuid, 16, 4) . '-' . substr($raw_uuid, 20);
            $displayName = $mojangdata['name'];
        } else {
            $errorMsg = $lang['error_mojang'];
        }
    } else {
        $uuid = $search;
        $stmtName = $pdo->prepare("SELECT {$config['col_name']} FROM {$config['db_user_sys']}.{$config['table_users']} WHERE {$config['col_uuid']} = ? LIMIT 1");
        $stmtName->execute([$uuid]);
        $dbPlayer = $stmtName->fetch(PDO::FETCH_ASSOC);
        $displayName = $dbPlayer ? $dbPlayer[$config['col_name']] : "Unknown Player";
    }

    if ($uuid && empty($errorMsg)) {
        $sql = " SELECT p.world, p.plot_id_x, p.plot_id_z, 'OWNER' as role, u.{$config['col_name']} as owner_name
                 FROM {$config['table_plot']} p
                 LEFT JOIN {$config['db_user_sys']}.{$config['table_users']} u ON p.owner = u.{$config['col_uuid']}
                 WHERE p.owner = :uuid
                 UNION
                 SELECT p.world, p.plot_id_x, p.plot_id_z, 'HELPED' as role, u.{$config['col_name']} as owner_name
                 FROM {$config['table_plot']} p
                 INNER JOIN plot_helpers ph ON ph.plot_plot_id = p.id
                 LEFT JOIN {$config['db_user_sys']}.{$config['table_users']} u ON p.owner = u.{$config['col_uuid']}
                 WHERE ph.user_uuid = :uuid
                 UNION
                 SELECT p.world, p.plot_id_x, p.plot_id_z, 'TRUSTED' as role, u.{$config['col_name']} as owner_name
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
<html lang="<?= $config['language'] ?>">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($config['server_name']) ?> | <?= $lang['title'] ?></title>
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
    <h3 class="mb-4 text-white"><?= htmlspecialchars($config['server_name']) ?> <span class="text-primary"><?= $lang['title'] ?></span></h3>

    <div class="card admin-card mb-4 shadow-lg">
        <div class="card-body">
            <form action="" method="GET">
                <div class="input-group">
                    <input type="text" name="query" class="form-control bg-dark text-white border-secondary"
                           placeholder="<?= $lang['search_placeholder'] ?>" value="<?= htmlspecialchars($search) ?>" required>

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

                    <button type="submit" class="btn btn-primary px-4 fw-bold"><?= $lang['search_btn'] ?></button>
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
                <span class="text-white-50 font-monospace"><?= $lang['results_for'] ?> <strong class="text-info" style="font-size: 1.2rem;"><?= htmlspecialchars($displayName) ?></strong></span>
                <small class="text-white-50 font-monospace"><?= htmlspecialchars($uuid) ?></small>
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
                            <tr>
                                <td class="align-middle"><?= htmlspecialchars($plot['world']) ?></td>
                                <td class="align-middle"><strong><?= $plot['plot_id_x'] ?>;<?= $plot['plot_id_z'] ?></strong></td>
                                <td class="align-middle text-warning">
                                    <?= $plot['owner_name'] ? htmlspecialchars($plot['owner_name']) : '<small class="text-muted">?</small>' ?>
                                </td>
                                <td class="align-middle">
                                    <button class="btn btn-sm btn-outline-light" style="font-size: 0.75rem;"
                                            onclick="navigator.clipboard.writeText('/plot visit <?= $plot['plot_id_x'] ?>;<?= $plot['plot_id_z'] ?>'); alert('Copied!');">
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
