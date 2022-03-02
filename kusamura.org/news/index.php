<?php
/**
 * ニュースディレクトリのルーティングスクリプト
 */

require_once $_SERVER['DOCUMENT_ROOT'] . '/modules/initialize.php';

// 下準備
if(!function_exists('http_response_code'))
	require_once $_SERVER['DOCUMENT_ROOT'] . '/modules/http_response_code.function.php';

// データベース設定
const DB_HOST = 'localhost';
const DB_USER = 'kusamura_www';
const DB_PASS = 'fF3nEcwEHksf';
const DB_NAME = 'kusamura_www';
const DB_CHARSET = 'utf8';

// 出力
out(http_response());

/**
 * @param array $response
 */
function out(array $response) {
	list($code, $headers, $content) = $response;
	http_response_code($code);
	if(is_array($headers))
		foreach($headers as $name => $value)
			header("$name: $value");
	die($content);
}


/**
 * @return array
 */
function http_response() {
	$conn = db_connection();
	if($conn->connect_error) return error_content(500);

	$article = article(value_of($_GET, 'datestamp'));
	if(is_null($article)) return error_content(404);

	$modified_time = db_modified_time(DB_NAME);
	$last_modified = $modified_time->format(DateTime::RFC2822);
	if(!modified_since($modified_time))
		return array(304, array('Last-Modified' => $last_modified));

	return array(200,
		array(
			'Content-Type' => 'text/html; charset=utf-8',
			'Last-Modified' => $last_modified),
		article_to_html($article)
	);
}

/**
 * @param DateTime $modified
 * @return bool
 */
function modified_since(DateTime $modified) {
	$if_modified_since = value_of($_SERVER, 'HTTP_IF_MODIFIED_SINCE');
	return !$if_modified_since
		|| DateTime::createFromFormat(DateTime::RFC2822, $if_modified_since) < $modified;
}

// タイトルと日付だけ取得するアーカイブを取得
/**
 * @return mixed
 */
function article_archive() {
	static $archive = array();
	if(!empty($archive)) return $archive;

	$conn = db_connection();
	$stmt = $conn->prepare('SELECT datestamp,title FROM news ORDER BY datestamp DESC');
	$stmt->execute();
	$stmt->bind_result($datestamp, $title);
	while($stmt->fetch()) $archive[$datestamp] = $title;
	$stmt->free_result();
	return $archive;
}


/**
 * @param $storage
 * @param $name
 * @return mixed
 */
function value_of($storage, $name) {
	return isset($storage[$name]) ? $storage[$name] : null;
}


/**
 * 更新時刻を取得
 * @param $db_name
 * @return DateTime
 */
function db_modified_time($db_name) {
	static $datetime = array();
	if(isset($datetime[$db_name])) return $datetime[$db_name];

	$query = <<<SQL
SELECT UPDATE_TIME
FROM information_schema.TABLES
WHERE TABLE_SCHEMA = ? AND TABLE_NAME = "news"
LIMIT 1
SQL;

	$conn = db_connection();
	$stmt = $conn->prepare($query);
	$stmt->bind_param('s', $db_name);
	$stmt->execute();
	$stmt->bind_result($time);
	$stmt->fetch();
	$stmt->free_result();

	$datetime[$db_name] = new DateTime($time);
	return $datetime[$db_name];
}

/**
 * @param $datestamp
 * @return object|stdClass
 */
function article($datestamp) {
	if(is_null($datestamp)) return article(latest_datestamp());
	$conn = db_connection();
	$stmt = $conn->prepare('SELECT datestamp,title,content,style FROM news WHERE datestamp=? LIMIT 1');
	$stmt->bind_param('s', $datestamp);
	$stmt->execute();

	$data = new stdClass();
	$stmt->bind_result($data->datestamp, $data->title, $data->content, $data->style);
	$stmt->fetch();
	$stmt->free_result();
	return $data;
}

/**
 * @return string
 */
function latest_datestamp() {
	$conn = db_connection();
	$stmt = $conn->prepare('SELECT MAX(datestamp) FROM news');
	$stmt->execute();
	$stmt->bind_result($datestamp);
	$stmt->fetch();
	$stmt->free_result();
	return $datestamp;
}

/**
 * @return mysqli
 */
function db_connection() {
	static $connection;
	if($connection) return $connection;

	$connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
	$connection->set_charset(DB_CHARSET);
	register_shutdown_function(array($connection, 'close'));
	return $connection;
}

/**
 * @param $code
 * @param array $headers
 * @return array
 */
function error_content($code, array $headers = array()) {
	$file = sprintf('%s/errors/%d.html', $_SERVER['DOCUMENT_ROOT'], $code);
	return file_exists($file)
		? array($code, $headers, file_get_contents($file))
		: error_content(500);
}

/**
 * @param $article
 * @return string
 */
function article_to_html($article) {
	// 日付文字列をDateTimeオブジェクトに変換
	$datestamp = new DateTime($article->datestamp);
	// アーカイブへのリンクリスト
	$archive = archive_to_html(article_archive());
	// PDFリリースのファイル名
	$alternate_pdf =
		str_replace("\\", "/", dirname(__FILE__) . '/' . $datestamp->format('Y-m-d') . '.pdf');
	// PDFへのリンクHTML
	$alternate_pdf_link =
		file_exists($alternate_pdf)
		? sprintf('<ul><li><a rel="alternate" type="application/pdf" media="print" href="%s.pdf">ニュースリリース - %s</a></li></ul>',
				$datestamp->format('Y-m-d'),
				$datestamp->format('Y年n月j日'))
		: '';

	return call_html_template('news-article-view', array(
		'datestamp' => $datestamp,
		'article' => $article,
		'archive' => $archive,
		'alternate_pdf_link' => $alternate_pdf_link
	));

}

/**
 * @param array $archive
 * @return string
 */
function archive_to_html($archive) {
	if(empty($archive)) return '';

	$format = '<li><a href="/news/%s"><time>%s</time> %s</a></li>';
	$links = array();
	foreach($archive as $date => $title) {
		$datetime = new DateTime($date);
		$links[] = sprintf($format,
			$datetime->format('Y-m-d'),
			$datetime->format('Y年n月j日'),
			$title);
	}
	$links_html = join('', $links);

	return <<<HTML
	<article>
		<section>
			<h2>その他のニュースリリース</h2>
			<ul>
				{$links_html}
			</ul>
		</section>
	</article>
HTML;


}
