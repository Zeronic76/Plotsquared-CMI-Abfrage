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
    'language'    => 'de',             // 'de', 'en', 'pl' 'ro'

    // DATENBANK ZUGANGSADATEN
    'db_host'     => '127.0.0.1',
    'db_user'     => 'benutzername',
    'db_pass'     => 'passwort',

    // DATENBANK NAMEN & TABELLEN
    'db_plots'    => 'plotsquared',    // Name PlotSquared DB
    // TABELLEN-NAMEN - PLOTSQUARED (Normalerweise nicht ändern / do not change)
    'table_plot'  => 'plot',           // Meistens 'plot'

    'db_user_sys' => 'cmi',            // Name CMI DB
    // TABELLEN-NAMEN - CMI (Normalerweise nicht ändern / do not change)
    'table_users' => 'CMI_users',      // Meistens 'CMI_users'

    // SPALTEN-NAMEN - CMI (Normalerweise nicht ändern / do not change)
    'col_uuid'    => 'player_uuid',
    'col_name'    => 'username'
];
