<?php 

/**
 * レスポンスを表現するインターフェース。node.jsライク。
 */
interface Responsible {
	abstract public function setHeader($name, $value);
	abstract public function getHeader($name);
	abstract public function removeHeader($name);
	abstract public function writeHead($code, array $headers = null);
	abstract public function write($chunk);
	abstract public function end($chunk);
}


?>