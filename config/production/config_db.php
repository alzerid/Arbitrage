<?
//Database setup
$_conf['db_driver'] = 'mysql';
$dbs = array();

//ringtone_system database
$dbs['t3_api'] = array('host' => 'domU-12-31-39-16-9C-0C.compute-1.internal',
                       'port' => 3306,
                       'user' => 'root',
                       'pass' => '',
                       'db'   => 'development_api');

$dbs['t3apidev'] = array('host' => 'domU-12-31-39-16-9C-0C.compute-1.internal',
                         'port' => 3306,
                         'user' => 'root',
                         'pass' => '',
                         'db'   => 't3apidev');

$_conf['db'] = $dbs;

$_conf['mongodb'] = array('host' => 'domU-12-31-39-16-9C-0C.compute-1.internal', 'port' => '27017');
$_conf['authdb'] = array('dbname' => 'verticals', 'collection' => 'global_auth');
?>
