<?php
/**
 * Author: Tatsuki Osawa
 * DateTime: 2015-10-07 16:00
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/modules/initialize.php';

// 応募内容の送信先アドレス
const MAIL_TO_ADDR = 'info@kusamura.org';

// 自動返信メール送り主
const MAIL_FROM_ADDR = 'no-reply@kusamura.org';

$MAIL_CONTENTS = extract_as_mail_contents($_POST);
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	http_response_code(405);
} else if (empty($MAIL_CONTENTS)) {
	http_response_code(400);
} else {
	// 本部宛て
	mb_send_mail(
		MAIL_TO_ADDR,
		'問い合わせ・応募 [法人ウェブサイト採用ページ経由]',
		to_info_message($MAIL_CONTENTS),
		sprintf('From: %s', $_POST['email_adr'])
	);
	// 応募者宛て
	mb_send_mail(
		$_POST['email_adr'],
		'[自動返信]問い合わせ・応募を受け付けました',
		auto_reply_message($MAIL_CONTENTS, $_POST['name']),
		sprintf('From: %s', MAIL_FROM_ADDR)
	);
	header('Content-Type: text/html; charset=utf-8');
	echo call_html_template('recruit-entry-complete',
		array('MAIL_CONTENTS' => $MAIL_CONTENTS));
}

function to_info_message($contents) {
	$str = join(PHP_EOL, $contents);
	return <<<MAIL
下記の通り職員募集へのエントリーがありました。対応のほどよろしくお願いします。
---
{$str}
MAIL;
}

function auto_reply_message($contents, $to_name) {
	$str = join(PHP_EOL, $contents);
	return <<<MAIL
{$to_name}様
この度は「多摩草むらの会職員募集」にご応募いただきありがとうございました。
下記の通りご応募を受付ました。後ほど担当よりご連絡させていただきます。
---
{$str}
---
このメールは送信専用メールのためこのメールへの返信はできません。お問合せは下記へお願いします。

NPO法人多摩草むらの会　本部事務局
Mail: info@kusamura.org
Tel: 042-339-8022
MAIL;
}

/**
 * @param array $input
 * @return array
 */
function extract_as_mail_contents(array $input)
{
	$contents = array_filter(array(
		name_of($input),
		age_of($input),
		postal_code_of($input),
		address_of($input),
		phone_of($input),
		mail_of($input),
		license_of($input),
		history_of($input)
	), 'mb_strlen');

	if(count($contents) < 8)
		return array();
	if(isset($input['question']))
		return array_merge($contents,
			array(sprintf('ご質問その他: %s', $input['question'])));
	return $contents;
}


function _every($data, $cb)
{
	$a = _to_array($data);
	foreach ($a as $key => $val) if (!$cb($val, $key)) return false;
	return true;
}

function _to_array($data)
{
	return ($data instanceof Traversable)
		? iterator_to_array($data)
		: (array)$data;
}

function _isset($data)
{
	$a = _to_array($data);
	return function ($key) use ($a) {
		return isset($a[$key]);
	};
}

function _has($keys, $input)
{
	return _every($keys, _isset($input));
}

/**
 * マルチバイトwordwrap. 公式マニュアルのコメント欄から拝借。
 * @see http://php.net/manual/ja/function.wordwrap.php
 * @param $string
 * @param $limit
 * @return mixed|string
 */
function mb_wordwrap($string, $limit = 70)
{
	$string = strip_tags($string); //Strip HTML tags off the text
	$string = html_entity_decode($string); //Convert HTML special chars into normal text
	$string = str_replace(array("\r", "\n"), "", $string); //Also cut line breaks
	if (mb_strlen($string, "UTF-8") <= $limit) return $string; //If input string's length is no more than cut length, return untouched
	$last_space = mb_strrpos(mb_substr($string, 0, $limit, "UTF-8"), " ", 0, "UTF-8"); //Find the last space symbol position
	return mb_substr($string, 0, $last_space, "UTF-8") . ' ...'; //Return the string's length substracted till the last space and add three points
}

/**
 * @param $input
 * @return string
 */
function name_of($input)
{
	return _has(array('name', 'ruby'), $input)
		? sprintf('お名前: %s(%s)', $input['name'], $input['ruby'])
		: '';
}

/**
 * @param $input
 * @return string
 */
function age_of($input)
{
	return _has(array('age'), $input)
		? sprintf('年齢: %d歳', $input['age'])
		: '';
}

/**
 * @param $input
 * @return string
 */
function postal_code_of($input)
{
	$not_exists = '郵便番号: 未入力';
	if (!isset($input['postal-code']) || !is_array($input['postal-code']))
		return $not_exists;
	list($before, $after) = $input['postal-code'];
	if (is_numeric($before) && is_numeric($after))
		return sprintf('郵便番号: %03d-%04d', $before, $after);
	return $not_exists;
}

/**
 * @param $input
 * @return string
 */
function address_of($input)
{
	if (_has(array('pref', 'city', 'town'), $input))
		return sprintf('住所: %s%s%s%s', $input['pref'], $input['city'], $input['town'],
			isset($input['bld']) ? $input['bld'] : '');
	return '';
}

/**
 * @param $input
 * @return string
 */
function phone_of($input)
{
	if (isset($input['phone']))
		return sprintf('電話番号: %s', $input['phone']);
	return '';
}

/**
 * @param $input
 * @return string
 */
function mail_of($input)
{
	return isset($input['email_adr'])
		? sprintf('メールアドレス: %s', $input['email_adr'])
		: '';
}

/**
 * @param $input
 * @return string
 */
function license_of($input)
{
	return isset($input['license'])
		? sprintf('保有資格: %s', $input['license'])
		: '';
}

/**
 * @param $input
 * @return string
 */
function history_of($input)
{
	return isset($input['history'])
		? sprintf('職務経歴: %s', $input['history'])
		: '';
}

