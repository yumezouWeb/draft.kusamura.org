<?php 


/**
 * 問合わせ処理を行う
 */
class Inquire {
	
	private static $exclude_strings = array("\r","\n",":","<",">");
	private $fromHeader = null;
	private $subject = null;
	private $message = null;
	
	public function __construct() {
		$this->fromHeader = null;
		$this->subject = null;
		$this->message = null;
	}
	
	/**
	 * 件名を設定する
	 */
	public function setSubject($subject)
	{
		$subject = str_replace(self::$exclude_strings, "", $subject);
		if(!preg_match("/\S/", $subject))
			return false;
		$this->subject = $subject . ' 問合わせフォームより';
		return true;
	}
	
	/**
	 * Fromヘッダを設定する
	 */
	public function setFromHeader($from, $alias = null)
	{
		$from = str_replace(self::$exclude_strings, "", $from);
		if(!is_null($alias)) {
			$alias = str_replace(self::$exclude_strings, "", $alias);
			if(mb_strlen($alias))
				$from = "$alias <$from>";
		}
		if(!preg_match("/\S/", $from)) return false;
		$this->fromHeader = "From: $from\n";
		return true;
	}
	
	/**
	 * メッセージボディを設定する。
	 */
	public function setMessage($message, array $options = null)
	{
		if(is_array($options)) {
			$str = array("---------------------");
			foreach($options as $name => $value) {
				$value = join("\n", $this->splitContents($value));
				if($value !== false) $str[] = "$name: $value";
			}
			$message .= "\n" . join("\n", $str);
		}
		$message = $this->splitContents($message);
		if($message === false) return false;
		$this->message = join("\n", $message) . "\n";
		return true;
	}
	
	/**
	 * メールの送信を行う
	 */
	public function submit($to)
	{
		$to = str_replace(self::$exclude_strings, "", $to);
		if(mb_strlen($to) > 0x100)
			throw new Exception('送信先アドレスが長すぎます。');
		
		$subject = $this->subject;
		if(is_null($subject))
			throw new Exception('件名が設定されていません。');
		if(mb_strlen($subject) > 100)
			throw new Exception('件名が長すぎます。100文字以内に収めてください。');
		
		$message = $this->message;
		if(is_null($message))
			throw new Exception('本文が記入されていません。');
		if(mb_strlen($message) > 10000)
			throw new Exception('本文が長すぎます。10000文字以内にしてください。');
				
		$from = $this->fromHeader;
		if(is_null($from))
			throw new Exception('送信者が記入されていません');
		if(mb_strlen($from) > 0x100)
			throw new Exception('送信者名・メールアドレスが長すぎます。');
		
		return mb_send_mail($to, $subject, $message, $from);
	}
	
	/**
	 * 文字列を規定文字数で改行を加える。
	 */
	protected function splitContents($data)
	{
		// 一行の規定文字数(RFCでは、CR,LFを含めて78文字以内が推奨される)
		$n = 76;
		// もともとの改行を反映するよう、事前に分割する
		$list = preg_split("/(?:\r\n|\r|\n)/", trim($data));
		if(!count($list)) return false;
		
		$result = array();
		foreach($list as $line){
			$len = 0;
			$line_len = mb_strlen($line);
			for($i = 0; $i < $line_len; $i += $len) {
				for($j = 1; $j <= $n; $j++) {
					$wk = mb_substr($line, $i, $j);
					if(strlen($wk) >= $n) break;
				}
				$len = mb_strlen($wk);
				$result[] = "$wk\n";
			}
		}
		return $result;
	}
	
	
}

?>