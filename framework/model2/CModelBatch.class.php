<?
namespace Arbitrage2\Model2;

abstract class CModelBatch extends CModelQuery
{
	public function execute()
	{
		throw new EModelException("Cannot do batch operation on '{$this->_cmd}'.");
	}
}
?>
