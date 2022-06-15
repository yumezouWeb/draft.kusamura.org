<script type="text/javascript">
	jQuery(function ($) {
		// 最初のコンテンツ以外は非表示
		$(".accordion-content:not(:first-of-type)").css("display", "none");
		// 矢印の向きを変えておく
		$(".accordion-title:first-of-type").addClass("open");
		$('.js-accordion-title').on('click', function () {
			/*クリックでコンテンツを開閉*/
			$(this).next().slideToggle(200);
			/*矢印の向きを変更*/
			$(this).toggleClass('open', 200);
		});
	});
</script>
	<script>
	jQuery(function($){
		$('.tab').click(function(){
			$('.is-active').removeClass('is-active');
			$(this).addClass('is-active');
			$('.is-show').removeClass('is-show');
			// クリックしたタブからインデックス番号を取得
			const index = $(this).index();
			// クリックしたタブと同じインデックス番号をもつコンテンツを表
			$('.panel').eq(index).addClass('is-show');
		});
	});
</script>
	<script type="text/javascript">
	$(function(){
		// モーダルウィンドウが開くときの処理    
		$(".modalOpen").click(function(){
			var navClass = $(this).attr("class"),
				href = $(this).attr("href");
			$(href).fadeIn();
			$(this).addClass("open");
			return false;
		});
		// モーダルウィンドウが閉じるときの処理    
		$(".modalClose").click(function(){
			$(this).parents(".modal").fadeOut();
			$(".modalOpen").removeClass("open");
			return false;
		});  
	});
</script>

	<!--  ツールチップによる用語説明 -->
	<script type="text/javascript">
	$(function(){
		$('a.tooltip').hover(function(){
			//マウスオーバー時
			$(this).next('span.term').css({'visibility': 'visible'});
		},
			function(){
				//マウスオーバーが外れたとき
				$(this).next('span.term').css({'visibility': 'hidden'});
			});
	});
</script>

