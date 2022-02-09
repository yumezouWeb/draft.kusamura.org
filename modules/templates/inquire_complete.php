<!DOCTYPE HTML>
<html>
	<head>
		<meta charset="UTF-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>お問合わせ完了 - NPO法人 多摩草むらの会</title>
		<link rel="author" href="mailto:info@kusamura.org" />
		<link rel="stylesheet" type="text/css" href="/style/basic.css" />
		<!--[if lt IE 9]><script type="text/javascript" src="http://ie7-js.googlecode.com/svn/version/2.1(beta4)/IE9.js"></script><![endif]-->
	</head>
	<body>
		<header>
			<h1>お問合わせ完了</h1>
			<?= call_html_template('global-header-nav') ?>
		</header>
		<article>
			<section>
				<h2>お問合わせを受け付けました</h2>
				<p>ご記入いただいた「<?= htmlspecialchars($email) ?>」宛てに「<code class="email">no-replay@kusamura.org</code>」より確認メールを送付いたしましたのでご確認ください。しばらく待っても届かない場合は、メールアドレスの記入が間違っていると思われますので、再度ご確認の上ご連絡ください。</p>
				<p>なお、返信の必要なものについては後ほど<code class="email">info@kusamura.org</code>より回答させていただきますので、お待ちくださりますようお願いいたします。</p>
				<section>
					<h3>お問合わせいただいた内容</h3>
					<dl>
						<dt>お問合わせの種類</dt>
						<dd><?= htmlspecialchars($categories[intval($category)-1]) ?></dd>
						<dt>お名前</dt>
						<dd><?= htmlspecialchars($writer) ?></dd>
						<dt>会社名</dt>
						<dd><?= preg_match("/\S/", $company) ? htmlspecialchars($company) : 'なし' ?></dd>
						<dt>問合わせ内容</dt>
						<dd><?= htmlspecialchars($content) ?></dd>
						<dt>メールアドレス</dt>
						<dd><code class="email"><?= htmlspecialchars($email) ?></code></dd>
						<dt>電話番号</dt>
						<dd><?= preg_match("/\S/", $phone) ? '<code class="phone">'.htmlspecialchars($phone).'</code>' : 'なし' ?></dd>
					</dl>
				</section>
			</section>
		</article>
		<footer>
		<address>2013 &copy; NPO法人 多摩草むらの会</address>
		<?= call_html_template('global-footer-nav') ?>
		</footer>
	</body>
</html>