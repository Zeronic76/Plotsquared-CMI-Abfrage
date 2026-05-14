<?php
/**
 * Installer für PlotSquared/CMI/LuckPerms Web Query v2.5.0
 * Dynamische Sprach-Erkennung + vollständige Konfiguration
 *
 * License: GNU General Public License v3.0
 * Copyright (c) 2026 Jörg Stöhrmann / Zeronic76
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation.
 */


session_start();

$configInc = __DIR__ . '/config.inc.php';
$configPhp = __DIR__ . '/config.php';

// 1. Wenn config.php existiert → Installer deaktivieren
if (file_exists($configPhp)) {
    header("Location: plot.php");
    exit;
}

if (!file_exists($configInc)) {
    die("FEHLER: config.inc.php fehlt! Bitte plot.php erneut aufrufen.");
}

$config = include($configInc);

// 2. Sprachsteuerung für den Installer (DE/EN)
$available_installer_langs = ['de', 'en'];
if (isset($_GET['set_install_lang']) && in_array($_GET['set_install_lang'], $available_installer_langs)) {
    $_SESSION['install_lang'] = $_GET['set_install_lang'];
}
$instLang = $_SESSION['install_lang'] ?? 'de';

$t = [
    'de' => [
        'title' => 'PlotSquared / CMI / LuckPerms Installer',
        'lang_section' => 'Sprache & Server',
        'lang_select' => 'Standardsprache für das Skript',
        'server_name' => 'Servername',
        'db_section' => 'Datenbank (Allgemein)',
        'db_host' => 'DB Host',
        'db_port' => 'DB Port',
        'db_user' => 'DB Benutzer',
        'db_pass' => 'DB Passwort',
        'ps_section' => 'PlotSquared Tabellen',
        'ps_db' => 'Plot DB Name',
        'ps_table' => 'Plot Tabelle',
        'ps_helpers' => 'plot_helpers Tabelle',
        'ps_trusted' => 'plot_trusted Tabelle',
        'source_section' => 'Spielerquelle',
        'cmi_section' => 'CMI Einstellungen',
        'lp_section' => 'LuckPerms Einstellungen',
        'label_db' => 'Datenbank',
        'label_table' => 'Tabelle',
        'label_columns' => 'Spaltennamen (UUID | Name)',
        'btn_save' => 'Konfiguration speichern',
        'switch' => 'English'
    ],
    'en' => [
        'title' => 'PlotSquared / CMI / LuckPerms Installer',
        'lang_section' => 'Language & Server',
        'lang_select' => 'Default Script Language',
        'server_name' => 'Server Name',
        'db_section' => 'Database (General)',
        'db_host' => 'DB Host',
        'db_port' => 'DB Port',
        'db_user' => 'DB Username',
        'db_pass' => 'DB Password',
        'ps_section' => 'PlotSquared Tables',
        'ps_db' => 'Plot DB Name',
        'ps_table' => 'Plot Table',
        'ps_helpers' => 'plot_helpers Table',
        'ps_trusted' => 'plot_trusted Table',
        'source_section' => 'Player Data Source',
        'cmi_section' => 'CMI Settings',
        'lp_section' => 'LuckPerms Settings',
        'label_db' => 'Database Name',
        'label_table' => 'Table Name',
        'label_columns' => 'Column Names (UUID | Name)',
        'btn_save' => 'Save Configuration',
        'switch' => 'Deutsch'
    ]
];
$curr = $t[$instLang];

// 3. Formularverarbeitung
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newConfig = [
        'db_host'        => $_POST['db_host'],
        'db_port'        => $_POST['db_port'],
        'db_user'        => $_POST['db_user'],
        'db_pass'        => $_POST['db_pass'],
        'db_plots'       => $_POST['db_plots'],
        'table_plot'     => $_POST['table_plot'],
        'table_helpers'  => $_POST['table_helpers'],
        'table_trusted'  => $_POST['table_trusted'],
        'player_source'  => $_POST['player_source'],
        'cmi' => [
            'db'           => $_POST['cmi_db'],
            'table_users'  => $_POST['cmi_table'],
            'col_uuid'     => $_POST['cmi_uuid'] ?: 'player_uuid',
            'col_name'     => $_POST['cmi_name'] ?: 'username'
        ],
        'luckperms' => [
            'db'           => $_POST['lp_db'],
            'table_users'  => $_POST['lp_table'],
            'col_uuid'     => $_POST['lp_uuid'] ?: 'uuid',
            'col_name'     => $_POST['lp_name'] ?: 'username'
        ],
        'language'    => $_POST['language'],
        'server_name' => $_POST['server_name'],
    ];

    $export = "<?php\nreturn " . var_export($newConfig, true) . ";\n";
    if (file_put_contents($configPhp, $export)) {
        unlink($configInc);
        header("Location: plot.php");
        exit;
    }
}

// Verfügbare Sprachen finden
$langDir = __DIR__ . '/lang/';
$languages = array_map(function($f) { return basename($f, '.php'); }, glob($langDir . '*.php'));
sort($languages);
?>
<!DOCTYPE html>
<html lang="<?= $instLang ?>">
<head>
    <meta charset="UTF-8">
    <title><?= $curr['title'] ?></title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #0d0d0d; color: #e0e0e0; font-family: sans-serif; }
        .admin-card { background-color: #1a1a1a; border: 1px solid #333; border-radius: 12px; }
        .section-title { color: #007bff; font-weight: 600; text-transform: uppercase; font-size: 0.9rem; letter-spacing: 1px; }
        .form-label { color: #bbbbbb; font-weight: 500; }
        .sub-label { color: rgba(255, 255, 255, 0.4); font-size: 0.75rem; font-weight: 400; text-transform: uppercase; margin-bottom: 4px; display: block; }
        .form-control:focus { background-color: #151515; color: white; border-color: #007bff; box-shadow: none; }
        hr { border-top: 1px solid #333; opacity: 1; }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="text-white m-0"><?= $curr['title'] ?></h3>
        <a href="?set_install_lang=<?= $instLang === 'de' ? 'en' : 'de' ?>" class="btn btn-sm btn-outline-secondary">
            <?= $curr['switch'] ?>
        </a>
    </div>

    <form method="POST" class="card admin-card p-4 shadow-lg">

        <h5 class="section-title mb-3"><?= $curr['lang_section'] ?></h5>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label small"><?= $curr['lang_select'] ?></label>
                <select name="language" class="form-control bg-dark text-white border-secondary">
                    <?php foreach ($languages as $l): ?>
                        <option value="<?= $l ?>" <?= $l === ($config['language'] ?? 'de') ? 'selected' : '' ?>><?= strtoupper($l) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label small"><?= $curr['server_name'] ?></label>
                <input type="text" name="server_name" class="form-control bg-dark text-white border-secondary" value="<?= htmlspecialchars($config['server_name'] ?? '') ?>" required>
            </div>
        </div>

        <hr>

        <h5 class="section-title mb-3"><?= $curr['db_section'] ?></h5>
        <div class="row">
            <div class="col-md-9 mb-3">
                <label class="form-label small"><?= $curr['db_host'] ?></label>
                <input type="text" name="db_host" class="form-control bg-dark text-white border-secondary" value="<?= htmlspecialchars($config['db_host']) ?>" required>
            </div>
            <div class="col-md-3 mb-3">
                <label class="form-label small"><?= $curr['db_port'] ?></label>
                <input type="text" name="db_port" class="form-control bg-dark text-white border-secondary" value="<?= htmlspecialchars($config['db_port']) ?>">
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label small"><?= $curr['db_user'] ?></label>
                <input type="text" name="db_user" class="form-control bg-dark text-white border-secondary" value="<?= htmlspecialchars($config['db_user']) ?>" required>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label small"><?= $curr['db_pass'] ?></label>
                <input type="password" name="db_pass" class="form-control bg-dark text-white border-secondary" value="<?= htmlspecialchars($config['db_pass']) ?>">
            </div>
        </div>

        <hr>

        <h5 class="section-title mb-3"><?= $curr['ps_section'] ?></h5>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label small"><?= $curr['ps_db'] ?></label>
                <input type="text" name="db_plots" class="form-control bg-dark text-white border-secondary" value="<?= htmlspecialchars($config['db_plots']) ?>" required>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label small"><?= $curr['ps_table'] ?></label>
                <input type="text" name="table_plot" class="form-control bg-dark text-white border-secondary" value="<?= htmlspecialchars($config['table_plot']) ?>" required>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label small"><?= $curr['ps_helpers'] ?></label>
                <input type="text" name="table_helpers" class="form-control bg-dark text-white border-secondary" value="<?= htmlspecialchars($config['table_helpers']) ?>">
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label small"><?= $curr['ps_trusted'] ?></label>
                <input type="text" name="table_trusted" class="form-control bg-dark text-white border-secondary" value="<?= htmlspecialchars($config['table_trusted']) ?>">
            </div>
        </div>

        <hr>

        <h5 class="section-title mb-3"><?= $curr['source_section'] ?></h5>
        <div class="mb-4">
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="player_source" id="s_cmi" value="cmi" <?= ($config['player_source'] ?? 'cmi') === 'cmi' ? 'checked' : '' ?>>
                <label class="form-check-label" for="s_cmi">CMI</label>
            </div>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="player_source" id="s_lp" value="luckperms" <?= ($config['player_source'] ?? '') === 'luckperms' ? 'checked' : '' ?>>
                <label class="form-check-label" for="s_lp">LuckPerms</label>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <h6 class="text-white-50 small mb-3"><?= $curr['cmi_section'] ?></h6>
                
                <span class="sub-label"><?= $curr['label_db'] ?></span>
                <input type="text" name="cmi_db" class="form-control bg-dark text-white border-secondary mb-3" value="<?= htmlspecialchars($config['cmi']['db'] ?? '') ?>">
                
                <span class="sub-label"><?= $curr['label_table'] ?></span>
                <input type="text" name="cmi_table" class="form-control bg-dark text-white border-secondary mb-3" value="<?= htmlspecialchars($config['cmi']['table_users'] ?? '') ?>">
                
                <span class="sub-label"><?= $curr['label_columns'] ?></span>
                <div class="input-group">
                    <input type="text" name="cmi_uuid" class="form-control bg-dark text-white border-secondary" placeholder="UUID" value="<?= htmlspecialchars($config['cmi']['col_uuid'] ?? '') ?>">
                    <input type="text" name="cmi_name" class="form-control bg-dark text-white border-secondary" placeholder="Name" value="<?= htmlspecialchars($config['cmi']['col_name'] ?? '') ?>">
                </div>
            </div>

            <div class="col-md-6">
                <h6 class="text-white-50 small mb-3"><?= $curr['lp_section'] ?></h6>

                <span class="sub-label"><?= $curr['label_db'] ?></span>
                <input type="text" name="lp_db" class="form-control bg-dark text-white border-secondary mb-3" value="<?= htmlspecialchars($config['luckperms']['db'] ?? '') ?>">
                
                <span class="sub-label"><?= $curr['label_table'] ?></span>
                <input type="text" name="lp_table" class="form-control bg-dark text-white border-secondary mb-3" value="<?= htmlspecialchars($config['luckperms']['table_users'] ?? '') ?>">
                
                <span class="sub-label"><?= $curr['label_columns'] ?></span>
                <div class="input-group">
                    <input type="text" name="lp_uuid" class="form-control bg-dark text-white border-secondary" placeholder="UUID" value="<?= htmlspecialchars($config['luckperms']['col_uuid'] ?? '') ?>">
                    <input type="text" name="lp_name" class="form-control bg-dark text-white border-secondary" placeholder="Name" value="<?= htmlspecialchars($config['luckperms']['col_name'] ?? '') ?>">
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-primary btn-lg w-100 mt-4 fw-bold"><?= $curr['btn_save'] ?></button>
    </form>
</div>

</body>
</html>
