<?php 

class Response {
	
	public $headers;
	public $statusCode;
	protected $isEnd;
	protected $headersSent;
	
	public function __construct($debug_mode = false) {
		$this->statusCode = 200;
		$this->headers = function_exists('apache_response_headers')
			? apache_response_headers()
			: array();
		$this->headersSent = function_exists('headers_sent') && headers_sent();
		$this->isEnd = false;
	}
	
	public function setHeader($name, $value)
	{
		if($this->headersSent)
			throw new Exception('headers already sent');
		$this->headers[$name] = $value;
	}
	
	public function removeHeader($name)
	{
		if($this->headersSent)
			throw new Exception('headers already sent');
		if(array_key_exists($name, $this->headers))
			unset($this->headers[$name]);
		if(function_exists('header_remove'))
			header_remove($name);
	}
	
	public function getHeader($name)
	{
		return array_key_exists($name, $this->headers) ? $this->headers[$name] : null;
	}
	
	/**
	 * ヘッダを送信する。
	 */
	public function writeHead($code = null, array $headers = array())
	{
		if($this->isEnd)
			throw new Exception("response body already sent");
		if($this->headersSent)
			throw new Exception('headers already sent');
		
		if(!is_null($code))
			$this->statusCode = $code;
		http_response_code($code);
		
		$this->headers = array_merge($this->headers, $headers);
		foreach($this->headers as $key => $value) 
			header("$key: $value");
		$this->headersSent = true;
	}
	
	/**
	 * レスポンスボディを書き出す
	 * @param $chunk
	 */
	public function write($chunk)
	{
		if($this->isEnd)
			throw new Exception("response body already sent");
		if(!$this->headersSent)
			$this->writeHead($this->statusCode, $this->headers);
		echo $chunk;
	}
	
	/**
	 * レスポンスを出力する
	 */
	public function end($chunk = null)
	{
		if(!is_null($chunk))
			$this->write($chunk);
		$this->isEnd = true;
	}
	
}

if (!function_exists('http_response_code')) {
	function http_response_code($code = null) {
		if(!is_null($code)) {
			switch ($code) {
				case 100: $text = 'Continue'; break;
				case 101: $text = 'Switching Protocols'; break;
				case 200: $text = 'OK'; break;
				case 201: $text = 'Created'; break;
				case 202: $text = 'Accepted'; break;
				case 203: $text = 'Non-Authoritative Information'; break;
				case 204: $text = 'No Content'; break;
				case 205: $text = 'Reset Content'; break;
				case 206: $text = 'Partial Content'; break;
				case 300: $text = 'Multiple Choices'; break;
				case 301: $text = 'Moved Permanently'; break;
				case 302: $text = 'Moved Temporarily'; break;
				case 303: $text = 'See Other'; break;
				case 304: $text = 'Not Modified'; break;
				case 305: $text = 'Use Proxy'; break;
				case 400: $text = 'Bad Request'; break;
				case 401: $text = 'Unauthorized'; break;
				case 402: $text = 'Payment Required'; break;
				case 403: $text = 'Forbidden'; break;
				case 404: $text = 'Not Found'; break;
				case 405: $text = 'Method Not Allowed'; break;
				case 406: $text = 'Not Acceptable'; break;
				case 407: $text = 'Proxy Authentication Required'; break;
				case 408: $text = 'Request Time-out'; break;
				case 409: $text = 'Conflict'; break;
				case 410: $text = 'Gone'; break;
				case 411: $text = 'Length Required'; break;
				case 412: $text = 'Precondition Failed'; break;
				case 413: $text = 'Request Entity Too Large'; break;
				case 414: $text = 'Request-URI Too Large'; break;
				case 415: $text = 'Unsupported Media Type'; break;
				case 429: $text = 'Too Many Requests'; break; // RFC 6585
				case 500: $text = 'Internal Server Error'; break;
				case 501: $text = 'Not Implemented'; break;
				case 502: $text = 'Bad Gateway'; break;
				case 503: $text = 'Service Unavailable'; break;
				case 504: $text = 'Gateway Time-out'; break;
				case 505: $text = 'HTTP Version not supported'; break;
				default:
					exit('Unknown http status code "' . htmlentities($code) . '"');
					break;
			}
			$protocol = (isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0');
			header("$protocol $code $text");
			$_GLOBALS['http_response_code'] = $code;
		} else {
			$code = (isset($GLOBALS['http_response_code']) ? $GLOBALS['http_response_code'] : 200);
		}
		return $code;
	}
}

?>