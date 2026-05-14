<?php
/**
 * Installer für PlotSquared/CMI/LuckPerms Web Query
 * Dynamische Sprach-Erkennung + vollständige Konfiguration
 *
 * License: GNU General Public License v3.0
 * Copyright (c) 2026 Jörg Stöhrmann / Zeronic76
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation.
 */

$configInc = __DIR__ . '/config.inc.php';
$configPhp = __DIR__ . '/config.php';

// Wenn config.php existiert → Installer nicht mehr nötig
if (file_exists($configPhp)) {
    header("Location: plot.php");
    exit;
}

// Wenn config.inc.php fehlt → Fehler
if (!file_exists($configInc)) {
    die("FEHLER: config.inc.php fehlt! Bitte plot.php erneut aufrufen.");
}

// Vorlage laden
$config = include($configInc);

// Formular abgeschickt?
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Neue Config aus POST-Daten
    $newConfig = [
        'db_host'        => $_POST['db_host'],
        'db_port'        => $_POST['db_port'],
        'db_user'        => $_POST['db_user'],
        'db_pass'        => $_POST['db_pass'],

        // PlotSquared
        'db_plots'       => $_POST['db_plots'],
        'table_plot'     => $_POST['table_plot'],
        'table_helpers'  => $_POST['table_helpers'],
        'table_trusted'  => $_POST['table_trusted'],

        // Spielerquelle
        'player_source'  => $_POST['player_source'],

        // CMI
        'cmi' => [
            'db'           => $_POST['cmi_db'],
            'table_users'  => $_POST['cmi_table'],
            'col_uuid'     => $_POST['cmi_uuid'] ?: 'player_uuid',
            'col_name'     => $_POST['cmi_name'] ?: 'username'
        ],

        // LuckPerms
        'luckperms' => [
            'db'           => $_POST['lp_db'],
            'table_users'  => $_POST['lp_table'],
            'col_uuid'     => $_POST['lp_uuid'] ?: 'uuid',
            'col_name'     => $_POST['lp_name'] ?: 'username'
        ],

        // Sprache
        'language' => $_POST['language'],

        // Servername
        'server_name' => $_POST['server_name'],
    ];

    // config.php schreiben
    $export = "<?php\nreturn " . var_export($newConfig, true) . ";\n";
    file_put_contents($configPhp, $export);

    // config.inc.php löschen
    unlink($configInc);

    // Weiterleitung
    header("Location: plot.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Installation</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">

    <style>
        body { background-color: #0d0d0d; color: #e0e0e0; font-family: sans-serif; }
        .admin-card { background-color: #1a1a1a; border: 1px solid #333; border-radius: 12px; }
        .input-group-text { background-color: #252525; border-color: #333; color: #aaa; }
        .section-title { color: #cccccc; font-weight: 600; }
    </style>
</head>
<body>

<div class="container py-5">

    <h3 class="mb-4 text-white">PlotSquared / CMI / LuckPerms Installer</h3>

    <form method="POST" class="card admin-card p-4">

        <!-- SPRACHE -->
        <h5 class="section-title mb-3">Sprache</h5>

        <?php
        // Sprachdateien automatisch laden
        $langDir = __DIR__ . '/lang/';
        $langFiles = glob($langDir . '*.php');

        $languages = [];
        foreach ($langFiles as $file) {
            $base = basename($file, '.php');
            $languages[] = $base;
        }

        // Alphabetisch sortieren
        sort($languages);

        // Aktuelle Sprache aus config
        $currentLang = $config['language'] ?? 'de';
        ?>

        <div class="mb-3">
            <label class="form-label">Sprache auswählen</label>
            <select name="language" class="form-control bg-dark text-white border-secondary">

                <?php foreach ($languages as $langCode): ?>
                    <option value="<?= htmlspecialchars($langCode) ?>"
                        <?= $langCode === $currentLang ? 'selected' : '' ?>>
                        <?= strtoupper($langCode) ?>
                    </option>
                <?php endforeach; ?>

            </select>
        </div>

        <hr class="border-secondary">

        <!-- SERVERNAME -->
        <h5 class="section-title mb-3">Servername</h5>

        <div class="mb-3">
            <label class="form-label">Servername</label>
            <input type="text" name="server_name" class="form-control bg-dark text-white border-secondary"
                   value="<?= htmlspecialchars($config['server_name'] ?? '') ?>" required>
        </div>

        <hr class="border-secondary">

        <!-- DB ALLGEMEIN -->
        <h5 class="section-title mb-3">Datenbank (Allgemein)</h5>

        <div class="mb-3">
            <label class="form-label">DB Host</label>
            <input type="text" name="db_host" class="form-control bg-dark text-white border-secondary"
                   value="<?= htmlspecialchars($config['db_host']) ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">DB Port</label>
            <input type="text" name="db_port" class="form-control bg-dark text-white border-secondary"
                   value="<?= htmlspecialchars($config['db_port']) ?>">
        </div>

        <div class="mb-3">
            <label class="form-label">DB Benutzer</label>
            <input type="text" name="db_user" class="form-control bg-dark text-white border-secondary"
                   value="<?= htmlspecialchars($config['db_user']) ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">DB Passwort</label>
            <input type="password" name="db_pass" class="form-control bg-dark text-white border-secondary"
                   value="<?= htmlspecialchars($config['db_pass']) ?>">
        </div>

        <hr class="border-secondary">

        <!-- PLOTSQUARED -->
        <h5 class="section-title mb-3">PlotSquared Tabellen</h5>

        <div class="mb-3">
            <label class="form-label">Plot DB Name</label>
            <input type="text" name="db_plots" class="form-control bg-dark text-white border-secondary"
                   value="<?= htmlspecialchars($config['db_plots']) ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Plot Tabelle</label>
            <input type="text" name="table_plot" class="form-control bg-dark text-white border-secondary"
                   value="<?= htmlspecialchars($config['table_plot']) ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">plot_helpers Tabelle</label>
            <input type="text" name="table_helpers" class="form-control bg-dark text-white border-secondary"
                   value="<?= htmlspecialchars($config['table_helpers']) ?>">
        </div>

        <div class="mb-3">
            <label class="form-label">plot_trusted Tabelle</label>
            <input type="text" name="table_trusted" class="form-control bg-dark text-white border-secondary"
                   value="<?= htmlspecialchars($config['table_trusted']) ?>">
        </div>

        <hr class="border-secondary">

        <!-- SPIELERQUELLE -->
        <h5 class="section-title mb-3">Spielerquelle</h5>

        <div class="input-group-text mb-3">
            <div class="form-check form-check-inline mb-0">
                <input class="form-check-input" type="radio" name="player_source" value="cmi"
                       <?= $config['player_source'] === 'cmi' ? 'checked' : '' ?>>
                <label class="form-check-label small ms-1">CMI</label>
            </div>

            <div class="form-check form-check-inline mb-0 ms-3">
                <input class="form-check-input" type="radio" name="player_source" value="luckperms"
                       <?= $config['player_source'] === 'luckperms' ? 'checked' : '' ?>>
                <label class="form-check-label small ms-1">LuckPerms</label>
            </div>
        </div>

        <!-- CMI -->
        <h5 class="section-title mb-3">CMI Tabellen</h5>

        <div class="mb-3">
            <label class="form-label">CMI Datenbank</label>
            <input type="text" name="cmi_db" class="form-control bg-dark text-white border-secondary"
                   value="<?= htmlspecialchars($config['cmi']['db']) ?>">
        </div>

        <div class="mb-3">
            <label class="form-label">CMI Users Tabelle</label>
            <input type="text" name="cmi_table" class="form-control bg-dark text-white border-secondary"
                   value="<?= htmlspecialchars($config['cmi']['table_users']) ?>">
        </div>

        <div class="mb-3">
            <label class="form-label">CMI UUID Spalte</label>
            <input type="text" name="cmi_uuid" class="form-control bg-dark text-white border-secondary"
                   value="<?= htmlspecialchars($config['cmi']['col_uuid']) ?>">
        </div>

        <div class="mb-3">
            <label class="form-label">CMI Name Spalte</label>
            <input type="text" name="cmi_name" class="form-control bg-dark text-white border-secondary"
                   value="<?= htmlspecialchars($config['cmi']['col_name']) ?>">
        </div>

        <hr class="border-secondary">

        <!-- LUCKPERMS -->
        <h5 class="section-title mb-3">LuckPerms Tabellen</h5>

        <div class="mb-3">
            <label class="form-label">LuckPerms Datenbank</label>
            <input type="text" name="lp_db" class="form-control bg-dark text-white border-secondary"
                   value="<?= htmlspecialchars($config['luckperms']['db']) ?>">
        </div>

        <div class="mb-3">
            <label class="form-label">LuckPerms Users Tabelle</label>
            <input type="text" name="lp_table" class="form-control bg-dark text-white border-secondary"
                   value="<?= htmlspecialchars($config['luckperms']['table_users']) ?>">
        </div>

        <div class="mb-3">
            <label class="form-label">LuckPerms UUID Spalte</label>
            <input type="text" name="lp_uuid" class="form-control bg-dark text-white border-secondary"
                   value="<?= htmlspecialchars($config['luckperms']['col_uuid']) ?>">
        </div>

        <div class="mb-3">
            <label class="form-label">LuckPerms Name Spalte</label>
            <input type="text" name="lp_name" class="form-control bg-dark text-white border-secondary"
                   value="<?= htmlspecialchars($config['luckperms']['col_name']) ?>">
        </div>

        <button type="submit" class="btn btn-primary mt-3 fw-bold">Konfiguration speichern</button>

    </form>

</div>

</body>
</html>
