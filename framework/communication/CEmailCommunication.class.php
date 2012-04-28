<?
class CEmailCommunication
{
	private $_to;
	private $_subject;
	private $_body;
	private $_headers;

	public function __construct($to, $subject, $body, array $headers = NULL)
	{
		$this->_to      = $to;
		$this->_subject = $subject;
		$this->_body    = $body;


		if($headers == NULL)
			$headers = array();

		$this->_headers = array_merge($headers, array('X-Mailer' => "PHP/" . phpversion()));
	}

	public function send()
	{
		$headers = "";
		foreach($this->_headers as $key => $val)
			$headers .= "$key: " . trim($val) . "\n";

		return mail($this->_to, $this->_subject, $this->_body, trim($headers));
	}
}
?>
