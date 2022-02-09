<?php 

date_default_timezone_set('Asia/Tokyo');


// 更新時刻
$mod_time = 0;

// ディレクトリ内を検索し、エントリーリストと最終更新時刻を取得する
$directory = dir(__DIR__);
$items = array();
$ext_re = '/\.(png|jpg|jpeg)$/i';

while(($entry = $directory->read()) !== false) {
	$mod = filemtime("{$directory->path}/$entry");
	if($mod_time < $mod)
		$mod_time = $mod;
	if(preg_match($ext_re, $entry) === 1) $items[] = $entry;
}


if($mod_time !== 0) {
	header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $mod_time) . ' GMT');
	// if-modified-sinceの解釈。304を返す場合をフォロー。
	if(isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
		$mod_since = preg_replace( '/;.*/', '', $_SERVER['HTTP_IF_MODIFIED_SINCE']);
		if(!preg_match("/GMT/", $mod_since)) $mod_since .= ' GMT';
		$mod_since = strtotime($mod_since);
		if($mod_time <= $mod_since) {
			http_response_code(304);
			exit;
		}
	}
}



if(!isset($_SERVER['HTTP_ACCEPT']) || preg_match('{(?:application/json|text/plain)}', $_SERVER['HTTP_ACCEPT']) !== 1)
	http_response_code(406);

header('Content-Type: application/json; charset=utf-8');

$items = array(
	'lastModified' => date('c', $mod_time),
	'files' => $items,
	'dirname' => $_SERVER['REQUEST_URI']
);
die(json_encode($items));


?>