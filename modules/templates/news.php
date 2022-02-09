<!DOCTYPE HTML>
<html lang="ja">
<head>
	<meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>ニュースリリース(<?= $article->datestamp->format('Y年n月d日') ?>) - NPO法人 多摩草むらの会</title>
	<link rel="author" href="mailto:info@kusamura.org" />
	<link rel="stylesheet" type="text/css" href="/style/basic.css" />
	<style type="text/css">
	.fbpr {
		margin-top: 1em;
		border-top: 3px dotted #88F;
		padding-top: 1em;
	}
	.fbpr h2 {
		background-image: url("/images/f_logo_RGB-Blue_58.png");
	}
	</style>
	<script type="text/javascript" src="/script/step_scroll.js"></script>
	<!--[if lt IE 9]><script type="text/javascript" src="http://ie7-js.googlecode.com/svn/version/2.1(beta4)/IE9.js"></script><![endif]-->
</head>
<body class="news">
	<header>
		<h1>ニュースリリース</h1>
		<?= call_html_template('global-header-nav') ?>
	</header>
	<article>
		<section>
			<h2><time><?= $article->datestamp->format('Y年n月j日') ?></time> <?= $article->title ?></h2><?= $article->content ?>
			<ul>
				<li><a rel="alternate" type="application/pdf" media="print" href="<?= $article->datestamp->format('Y-m-d') ?>.pdf">ニュースリリース - <?= $article->datestamp->format('Y年n月j日') ?></a></li>
			</ul>
		</section>
		<section class="fbpr">
			<h2><img style="float:right;margin:0 0 1em 1em;" src="/images/fb_qr.png" alt="" /><a href="https://www.facebook.com/NPO法人-多摩草むらの会-104625144672599">多摩草むらの会 Facebook</a></h2>
			<p>多摩草むらの会のfacebook開設しました。様々な情報発信を行ってまいります。</p>
		</section>
		<?php if(count($archive)) : ?>
		<section>
			<h2>その他のニュースリリース</h2>
			<ul>
				<?php foreach($archive as $date => $title) : $date = new DateTime($date); ?>
				<li><a href="<?= '/news/'.$date->format('Y-m-d') ?>"><time><?= $date->format('Y年n月j日') ?></time> <?= $title ?></a></li>
				<?php endforeach; ?>
			</ul>
		</section>
		<?php endif; ?>
	</article>
	<footer>
		<address>2013 &copy; NPO法人 多摩草むらの会</address>
		<?= call_html_template('global-footer-nav') ?>
	</footer>
</body>
</html>