<?
namespace Framework\Database;

abstract class CDriverBatch extends CDriverQuery
{
	public function execute(\Framework\Database\CDatabaseModelCollection $results)
	{
		throw new \Framework\Database\Exceptions\EModelException("Cannot do batch operation on '{$this->_cmd}'.");
	}
}
?>
