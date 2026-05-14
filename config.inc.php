<?php
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


