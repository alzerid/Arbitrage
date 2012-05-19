<?
namespace Arbitrage2\Model2;

abstract class CModelBatch extends CModelQuery
{
	public function execute()
	{
		if($this->_cmd == "insert")
		{
			die("INSERT");
		}
		else
			throw new EModelExceptoin("Cannot do batch operation on '{$this->_cmd}'.");
	}
}
?>
