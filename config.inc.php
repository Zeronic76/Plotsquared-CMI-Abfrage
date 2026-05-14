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
return [

    // Allgemeine DB-Daten
    'db_host'   => 'localhost',
    'db_port'   => '3306',
    'db_user'   => 'root',
    'db_pass'   => '',

    // PlotSquared
    'db_plots'      => 'plotsquared',
    'table_plot'    => 'plot',
    'table_helpers' => 'plot_helpers',
    'table_trusted' => 'plot_trusted',

    // Spielerquelle
    'player_source' => 'cmi',

    // CMI
    'cmi' => [
        'db'           => 'citybuild',
        'table_users'  => 'CMI_users',
        'col_uuid'     => 'player_uuid',
        'col_name'     => 'username'
    ],

    // LuckPerms
    'luckperms' => [
        'db'           => 'LuckPerms',
        'table_users'  => 'luckperms_players',
        'col_uuid'     => 'uuid',
        'col_name'     => 'username'
    ],

    // Sprache (Default)
    'language' => 'de',
];
