<?php
/**
 * Configuration for PlotSquared & CMI Web Query
 *
 * Project: PlotSquared & CMI Web Query
 * License: GNU General Public License v3.0
 * Copyright (c) 2026 Jörg Stöhrmann/Zeronic76
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation.
 */

$config = [
    'server_name' => 'Servername',      // Name deines Servers
    'language'    => 'de',              // 'de', 'en', 'pl' 'ro'

    // DATENBANK ZUGANGSADATEN
    'db_host'     => '127.0.0.1',       // DB IP
    'db_port'     => '3306',            // DB Port
    'db_user'     => 'benutzername',    // DB username
    'db_pass'     => 'passwort',        // DB password of username

    // DATENBANK NAMEN & TABELLEN
    'db_plots'    => 'plotsquared',    // Name PlotSquared DB
    // TABELLEN-NAMEN - PLOTSQUARED (Normalerweise nicht ändern / Normally, this does not change.)
    'table_plot'  => 'plot',           // Meistens / mostly 'plot'

    'db_user_sys' => 'cmi',            // Name CMI DB
    // TABELLEN-NAMEN - CMI (Normalerweise nicht ändern / Normally, this does not change.e)
    'table_users' => 'CMI_users',      // Meistens / mostly 'CMI_users'

    // SPALTEN-NAMEN - CMI (Normalerweise nicht ändern / Normally, this does not change.)
    'col_uuid'    => 'player_uuid',
    'col_name'    => 'username'
];
