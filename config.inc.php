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

    // Allgemeine Datenbank
    'db_host' => '',
    'db_port' => '3306',
    'db_user' => '',
    'db_pass' => '',

    // PlotSquared
    'db_plots' => 'plotsquared',         // Plotsquared Database
    'table_plot' => 'plot',              // tablename for plot

    // Sprache
    'language' => 'de',

    // Spielerquelle
    'player_source' => 'cmi', // cmi | luckperms

    // CMI
    'cmi' => [
        'db'          => '',
        'table_users' => 'CMI_users',
        'col_uuid'    => 'uuid',
        'col_name'    => 'username'
    ],

    // LuckPerms
    'luckperms' => [
        'db'          => '',
        'table_users' => 'luckperms_players',
        'col_uuid'    => 'uuid',
        'col_name'    => 'username'
    ]
];


