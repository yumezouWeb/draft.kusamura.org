<?php 


/**
 * 
 */
class Session {
	
	protected $prefix;
	protected $context;
	
	public function __construct($prefix = null)
	{
		session_start();
		$this->prefix = is_null($prefix) ? 'default' : $prefix;
		
		if(!array_key_exists($prefix, $_SESSION))
			$_SESSION[$prefix] = array();
		$this->context = &$_SESSION[$prefix];
	}
	
	public function createTicket()
	{
		$ticket = sha1(uniqid(rand()));
		$this->setItem('__ticket', $ticket);
		return $ticket;
	}
	
	public function checkTicket($ticket)
	{
		return (string)$this->getItem('__ticket') === (string)$ticket;
	}
	
	public function deleteTicket()
	{
		$this->removeItem('__ticket');
	}
	
	public function getItem($name)
	{
		return array_key_exists($name, $this->context) ? $this->context[$name] : null;
	}
	
	public function setItem($name, $value)
	{
		$this->context[$name] = $value;
	}
	
	public function removeItem($name)
	{
		unset($this->context[$name]);
	}
	
	public function clear()
	{
		$_SESSION[$this->prefix] = array();
		$this->context = &$_SESSION[$this->prefix];
	}
	
	public function destroy()
	{
		$this->context = null;
		unset($_SESSION[$this->prefix]);
		if(!count($_SESSION)) session_destroy();
	}
	
}

?>