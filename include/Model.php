<?
class Model
{
	protected $_vo;
	protected $_dao;

	protected function __construct($vo, $db='t3apidev')
	{
		$this->_vo  = $vo;
		$this->_dao = DAO::getInstance($db, true, true);
		DAO::includeVO($this->_vo);
	}

	protected function resetDAO()
	{
		$this->_dao->reset();
		$this->_dao->bind($this->_vo);
	}
}
?>
