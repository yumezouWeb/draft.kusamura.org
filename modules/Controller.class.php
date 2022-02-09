<?php 


/**
 * 
 */
class Controller {
	
	protected $response;
	
	public function __construct() {
		$this->response = new Response();
	}
	
	/**
	 * http-optionsメソッド
	 */
	public function options()
	{
		$methods = array('get','post','put','head','options','delete','trace','connect','patch');
		$result = array();
		foreach($methods as $method)
			if(method_exists($this, $method))
				$result[] = $method;
		$this->response->writeHead(200, array('Allow', strtoupper(join(',', $result))));
		$this->response->end();
	}
	
	/**
	 * 400、500番台レスポンスコード(client or server error)の簡易化メソッド
	 */
	public function error($code) {
		$this->response->writeHead($code);
	}
	
	/**
	 * リダイレクション専用メソッド
	 */
	public function redirect($url, $code = 302) {
		$this->response->writeHead($code, array('Location' =>  $url));
		$this->response->end();
	}
	
	/**
	 * エラーハンドリング
	 */
	public function handleException(Exception $e, $debug_mode = false) {
		$this->response->writeHead(500);
		$this->response->end($debug_mode ? $e->getTraceAsString() : $e->getMessage());
	}
	
	public function execute() {
		$method = strtolower($_SERVER['REQUEST_METHOD']);
		try {
			// リクエストメソッドが非対応ならば405, そうでなければ呼び出し
			if(method_exists($this, $method))
				$this->{$method}();
			else
				$this->error(405);
		} catch(Exception $e) {
			$this->handleException($e);
		}
		if(!$this->response->headersSent)
			$this->error(500);
		return $this->response;
	}	
	
}


?>