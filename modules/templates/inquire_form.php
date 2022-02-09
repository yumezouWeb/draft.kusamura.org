<!DOCTYPE HTML>
<html>
	<head>
		<meta charset="UTF-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>お問合わせ - NPO法人 多摩草むらの会</title>
		<link rel="author" href="mailto:info@kusamura.org" />
		<link rel="stylesheet" type="text/css" href="/style/basic.css" />
		<style type="text/css">
			.error,
			.require
			{
				font-weight: bold;
				color: #900;
			}
			.require
			{
				margin: 0 0.25em;
			}
			form {
				margin: 1em 0;
			}
			
			label
			{
				display: block;
				padding: 0.5em;
			}
			
			fieldset.category_selection
			{
				float: left;
				width: 15em;
			}
			
			fieldset.category_selection input
			{
				margin-right: 0.25em;
			}
			
			fieldset.content_edit > label > input,
			fieldset.content_edit > label > textarea
			{
				display: block;
				width: 100%;
			}
			
			fieldset.content_edit > label > textarea
			{
				height: 10em;
			}
			
			button 
			{
				display: block;
				width: 80%;
				height: 3em;
				font-size: larger;
				margin: 0.5em auto;
			}
		</style>
		<!--[if lt IE 9]><script type="text/javascript" src="http://ie7-js.googlecode.com/svn/version/2.1(beta4)/IE9.js"></script><![endif]-->
	</head>
	<body>
		<header>
			<h1>お問合わせ</h1>
			<?= call_html_template('global-header-nav') ?>
		</header>
		<article>
			<section>
				<h2>問合わせフォームについて</h2>
				<p>当法人に対するお問合わせは以下のフォームに記入して送信をお願いいたします。なお<strong><em class="require">*</em>印は必須項目</strong>です。</p>
				<p>「送信開始」ボタンを押した後、お客様のメールを受信したことをお知らせするメールが送られてきます。このメールはシステムが自動的に送信していますので、このメールにご返信いただいてもお返事はできません。ご注意ください。なお、お問合せいただいた件に関する当方からの回答メールは、<code class="email">info@kusamura.org</code> よりお送りします。</p>
			</section>
		</article>
		<?php if(isset($errors) && count($errors)) : ?>
		<article>
			<section>
				<h2>送信失敗</h2>
				<p>エラーが発生したため、メールの送信ができませんでした。お手数ですが、以下の項目をご確認くださいませ。</p>
				<ul class="error"><?php foreach ($errors as $e) echo "<li>$e</li>"; ?></ul>
			</section>
		</article>
		<?php elseif($response->statusCode >= 500) : ?>
		<article>
			<section>
				<h2>送信失敗</h2>
				<?php if($response->statusCode === 503) : ?>
				<p class="error">前回のお問合わせから10分経過前の送信です。あと<?= ceil($response->getHeader('Retry-After') / 60) ?>分ほど間をおいてからご利用ください。</p>
				<?php else: ?>
				<p class="error">サーバーの不具合が発生しました。申し訳ありませんが、システム管理者の対応が終了するまでフォームのご利用はお待ちください。お急ぎの方は、<a href="/access">本部事務局</a>に直接ご連絡ください。</p>
				<?php endif; ?>
			</section>
		</article>
		<?php endif; ?>
		<form class="inquire" action="<?= $_SERVER['REQUEST_URI'] ?>" method="post">
			<fieldset class="category_selection">
				<legend>お問合わせの種類<em class="require">*</em></legend>
<?php
	
	// POSTで受け取った値があればdefault値としてリターンする関数
	function posted_value($name) {
		return array_key_exists($name, $_POST) ? htmlspecialchars($_POST[$name]) : '';
	}
	$selected = posted_value('category');
	
	foreach($categories as $i => $text): ?>
				<label><input type="radio" name="category" value="<?= $i+1 ?>" required="required" <?= $selected == $i+1 ? 'checked="checked"' : '' ?>/><?= $text ?></label>
<?php endforeach; ?>
			</fieldset>
			<fieldset class="content_edit">
				<legend>入力項目</legend>
				<label>お名前 <em class="require">*</em> <input id="writer" name="writer" size="32" maxlength="32" type="text" value="<?= posted_value('writer') ?>" required="required" /></label> 
				<label>会社名 <input id="company" name="company" size="32" maxlength="32" type="text" value="<?= posted_value('company') ?>" /></label> 
				<label>問合わせ内容(10000文字以内) <em class="require">*</em> <textarea id="content" name="content" rows="10" cols="40" required="required"><?= posted_value('content') ?></textarea></label> 
				<label>メールアドレス <em class="require">*</em> <input id="email" name="email" size="32" maxlength="255" type="email" pattern=".+@[a-zA-Z0-9]+\..+" value="<?= posted_value('email') ?>" required="required" /></label>
				<label>電話番号 <input id="phone" name="phone" size="32" maxlength="13" type="text" value="<?= posted_value('phone') ?>" /></label>
			</fieldset>
			<button type="submit">送信開始</button>
			<input type="hidden" name="ticket" value="<?= $ticket ?>" />
		</form>
		<footer>
			<address>2013 &copy; NPO法人 多摩草むらの会</address>
			<?= call_html_template('global-footer-nav') ?>
		</footer>
	</body>
</html>