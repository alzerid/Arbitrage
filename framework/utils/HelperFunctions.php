<?
//Common universal helper functions

//Weighted random function
function w_rand($weights)
{
	$rand = mt_rand(1, 1000);
	$offset = 0;
	foreach($weights as $k => $w)
	{
		$offset += $w * 1000;
		if($rand <= $offset)
			return $k;
	}

	return -1;
}

function safe_ip2long($ip)
{
}

function safe_long2ip($ip)
{
	if(is_string($ip) && !is_numeric($ip))
		return $ip;
	
	return long2ip($ip);
}

function recursive_move($src, $dst)
{
	recursive_copy($src, $dst);
	recursive_rm($src);
}

function recursive_copy($src, $dst)
{
	//Make sure the directory exists
	if(!file_exists($src))
		return false;

	$dir = opendir($src);
	@mkdir($dst);
	while(false !== ( $file = readdir($dir)) )
	{
		if(($file != '.') && ($file != '..' ))
		{
			if(is_dir($src . '/' . $file))
				recursive_copy($src . '/' . $file,$dst . '/' . $file);
			else
				copy($src . '/' . $file,$dst . '/' . $file);
		}
	}

	closedir($dir);

	return true;
}

function recursive_rm($dir)
{
	$files = glob($dir . '*', GLOB_MARK);
	foreach($files as $file)
	{
		if(substr($file, -1) == '/')
			recursive_rm($file);
		else
			unlink($file);
	}

	if(is_dir($dir))
		rmdir($dir);

	return true;
} 

function is_sequential_array($arr)
{
	return (array_merge($arr) == $arr && is_numeric(implode(array_keys($arr))));
}

function is_assoc_array($arr)
{
	return (array_merge($arr) !== $arr || !is_numeric(implode(array_keys($arr))));
}
?>
