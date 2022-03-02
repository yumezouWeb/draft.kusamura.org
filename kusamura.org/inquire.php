<?php
// 問い合わせページ
require_once $_SERVER['DOCUMENT_ROOT'] . '/modules/initialize.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/modules/Response.class.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/modules/View.class.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/modules/Session.class.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/modules/Inquire.class.php';

// メール送信用パラメータ
// 問合わせ先アドレス
$toAddr = 'info@kusamura.org';

// 件名選択肢
$categories = array('利用に関すること', '事業に関すること', 'このサイトに関すること', 'その他');

// 自動返信設定
$auto_reply = array();

// 返信不可・配信専用アドレス
$auto_reply['from'] = 'no-reply@kusamura.org';

// 件名
$auto_reply['subject'] = '配信確認メール(返信不可)';

// 自動返信の内容
$auto_reply['message'] = <<<NO_REPLY_MESSAGE
このメールは「NPO法人 多摩草むらの会 本部事務局」宛てにお問合わせいただいた方に対し、
確認のために機械的に自動配信させていただいているものです。
このメール自体にご返信いただいても、返答はいたしかねますのでご了承ください。

なお、もしこのメールの到着に心当たりがない場合は、
お手数ですが info@kusamura.org 宛てにご連絡いただければと思います。

////////////////////////////////////////
NPO法人 多摩草むらの会 本部事務局
電話番号: 042-373-6140
Eメール: info@kusamura.org
所在地: 東京都多摩市鶴牧1-4-10 アネックス鶴牧101
Webサイト: http://kusamura.org
NO_REPLY_MESSAGE;



// ワンタイムチケットの発行用セッション
$session = new Session('inquire');
// レスポンスクラス
$response = new Response();
// htmlレンダリング用ビュー
$view = new View('html');
$view->assign('categories', $categories);
$view->assign('response', $response);

// 実際のメソッド別処理
switch ($_SERVER['REQUEST_METHOD']) {
	
	case 'GET':
		$view->assign('ticket', $session->createTicket());
		$response->setHeader('Content-Type', 'text/html; charset=utf-8');
		$response->end($view->render($_SERVER['DOCUMENT_ROOT'] . '/modules/templates/inquire_form'));
		break;
	
	case 'POST':
		// ワンタイムチケットのチェック。失敗時は401
		if(!array_key_exists('ticket', $_POST) || !$session->checkTicket($_POST['ticket'])) {
			$response->writeHead(401, array('Location' => $_SERVER['REQUEST_URI']));
			$response->end();
			exit;
		}
		
		// テンプレートファイル
		$file = $_SERVER['DOCUMENT_ROOT'] . '/modules/templates/inquire_form';
		
		// 連続投稿のチェック
		$sent = $session->getItem('sent');
		$current_ts = time();
		if(!is_null($sent) && ($current_ts - $sent) < 3600) {
			$response->writeHead(503, array('Retry-After' => $current_ts - $sent));
			$response->end($view->render($file));
			exit;
		}
		
		// チケットの再生成
		$view->assign('ticket', $session->createTicket());
		
		// 必須パラメータのチェック
		$params = array();
		foreach($_POST as $name => $value) {
			$value = trim($value);
			// 不正パラメータ発覚時は400
			if(!validateInput($name, $value)) {
				$response->writeHead(400);
				$response->end();
				exit;
			}
			$params[$name] = $value;
			$view->assign($name, $value);
		}
		
		// モデルにメール送信を依頼
		$inquire = new Inquire();
		$errors = array();
		if(!$inquire->setSubject($categories[intval($params['category'])-1]))
			$errors[] = '件名欄の内容をご確認してください。';
		if(!$inquire->setFromHeader($params['email'], $params['writer']))
			$errors[] = 'お名前・メールアドレスの欄を確認してください。';
		
		// メールヘッダ + 本文の構築開始
		$options = array();
		$options['名前'] = $params['writer'];
		if(isset($params['company']))
			$options['会社名'] = $params['company'];
		$options['メール'] = $params['email'];
		if(isset($params['phone']))
			$options['電話番号'] = $params['phone'];
		if(!$inquire->setMessage($params['content'], $options))
			$errors[] = 'メッセージの内容・文字数をご確認ください。';
		
		
		if(!count($errors)) {
			$no_reply = new Inquire();
			$no_reply->setFromHeader($auto_reply['from']);
			$no_reply->setSubject($auto_reply['subject']);
			$no_reply->setMessage($auto_reply['message']);
			if(!$no_reply->submit($params['email'])) {
				$errors[] = '配信確認メールを送付できません。';
			}
		}
		
		
		// レスポンスヘッダの確定
		$response->setHeader('Content-Type', 'text/html; charset=utf-8');
		if(count($errors)) {
			$response->writeHead(400);
		} else if(!$inquire->submit($toAddr, $options)) {
			$response->writeHead(500);
		} else {
			$response->writeHead(200);
			// 送信成功時刻をメモする
			$session->setItem('sent', $current_ts);
			// テンプレートファイルを完了報告用に切り替え
			$file = $_SERVER['DOCUMENT_ROOT'] . '/modules/templates/inquire_complete';
		}
		
		$view->assign('errors', $errors);
		$response->end($view->render($file));
		exit;
	
}


/**
 * 入力値の正当性をチェックする。
 */
function validateInput($name, $value)
{
	// カテゴリは登録済みテキストのインデックスかをチェック
	if($name === 'category')
		return array_key_exists(intval($value)-1, $GLOBALS['categories']);
	
	// 必須項目は空白以外であること
	if(preg_match("/^(?:writer|content|email)$/", $name))
		return preg_match("/\S/", $value);
	
	// その他は事前に許されたキーかどうかをチェック
	return preg_match('/^(?:company|phone|ticket)$/', $name);
}

?>