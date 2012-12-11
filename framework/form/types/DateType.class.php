<?
namespace Framework\Form\Types;

class DateType implements \Framework\Interfaces\IHTMLDataTableType
{
	private $_path;
	private $_format;

	/**
	 * Method constructs the DateType.
	 * @param $format The date format.
	 */
	public function __construct($path, $format)
	{
		$this->_path   = $path;
		$this->_format = $format;
	}

	/**
	 * Method renders the type.
	 * @param $table The table to apply this data type to.
	 * @param $entry The current entry.
	 * @return Returns the HTML of the data type.
	 */
	public function render(\Framework\Interfaces\IHTMLDataTable $table, $entry)
	{
		//Get value
		$val = $entry->apath($this->_path);
		if($val instanceof \DateTime)
			$val = $val->getTimestamp();
		else
			throw new \Framework\Exceptions\EGeneralException("Unable to convert value properly");

		return date($this->_format, $val);
	}
}
?>
