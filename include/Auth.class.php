<?
class Auth
{
	static public function isLoggedIn()
	{
		return (!empty($_SESSION['_user']));
	}

	static public function getUser()
	{
		return unserialize($_SESSION['_user']);
	}

	static public function authenticate()
	{
		//Check if we are authenticating
		if(!isset($_POST['username']) && !isset($_POST['password']))
			return false;

		//Require the user VO
		DAO::includeVO('user');

		//Authenticate user
		$dao = new DAO('formula1');
		var_dump($dao);
		die();
	}

	static public function display()
	{
		Template::displayFile('login');
	}
}
?>
