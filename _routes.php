<?php
/**
 * Author: Tatsuki Osawa
 * DateTime: 2015-07-27 10:31
 * HTMLファイル呼び出しのラッパ。
 */

require_once $_SERVER['DOCUMENT_ROOT'] . '/modules/initialize.php';

header('Content-Type: text/html; charset=utf-8');
send_last_modified_header($_SERVER['DOCUMENT_ROOT'] . $_SERVER['REDIRECT_URL']);
require $_SERVER['DOCUMENT_ROOT'] . $_SERVER['REDIRECT_URL'];

/**
 * @param $filename
 */
function send_last_modified_header($filename) {
	$last_modified = new DateTime();
	$last_modified->setTimestamp(max(filemtime(__FILE__), filemtime($filename)));
	header(sprintf('Last-Modified: %s', $last_modified->format(DateTime::RFC1123)));
	if(check_if_modified_since($last_modified)) return;
	http_response_code(304);
	exit;
}

/**
 * @param DateTime $last_modified
 */
function check_if_modified_since(DateTime $last_modified) {
	$headers = array_change_key_case(apache_request_headers(), CASE_UPPER);
	if(!isset($headers['IF-MODIFIED-SINCE'])) return true;
	$if_modified_since = new DateTime($headers['IF-MODIFIED-SINCE']);
	return $last_modified > $if_modified_since;
}