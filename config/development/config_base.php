<?
$realpath = realpath(dirname(realpath(__FILE__)) . "/../../../");
$_conf['fsrootpath']   = $realpath . "/";
$_conf['urlrootpath']  = '/';
$_conf['fsfwpath']     = "$realpath/framework/";
$_conf['fwapppath']    = "$realpath/app/";
$_conf['fsapipath']    = "$realpath/app/api/";
?>
