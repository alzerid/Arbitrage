<?php

//Database setup
$_conf['db_driver'] = 'mysql';
$dbs = array();

//ringtone_system database
$dbs['t3_api'] = array('host' => '127.0.0.1',
                       'port' => 3306,
                       'user' => 'root',
                       'pass' => '',
                       'db'   => 'development_api');

$dbs['t3apidev'] = array('host' => '127.0.0.1',
                         'port' => 3306,
                         'user' => 'root',
                         'pass' => '',
                         'db'   => 't3apidev');

/*$dbs['t3_stats'] = array('host' => '127.0.0.1',
                           'port' => 3306,
                           'user' => 'yixe',
                           'pass' => 'r@ngT0ne$',
                           'db'   => 'ringtone_system_archive');*/

$_conf['db'] = $dbs;

$_conf['mongodb'] = array('host' => '127.0.0.1', 'port' => '27017');
$_conf['authdb'] = array('dbname' => 'verticals', 'collection' => 'global_auth');
?>
