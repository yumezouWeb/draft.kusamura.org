
window.addEventListener('load', step_scroll_window.bind(null, 20), false);
window.addEventListener('load', external_anchors_set_target.bind(null, '_blank'), false);

/**
 * ステップ分割してトップへのスクロール処理を行う
 * @param {number} STEP_COUNT
 */
function step_scroll_window(STEP_COUNT) {

	// スクロール起動用ボタンの設置
	document.body.appendChild(html_trigger_button('↑'));

	/**
	 * スクロール開始用ボタンの生成
	 * @param text
	 * @return {HTMLButtonElement}
	 */
	function html_trigger_button(text) {
		var button = document.createElement('button');
		button.setAttribute('title','ページ先頭にスクロール');
		button.setAttribute('class','scroll_trigger');
		button.appendChild(document.createTextNode(text));
		button.addEventListener('click', step_scroll_trigger, false);
		return button;
	}

	/**
	 * ボタンクリックのハンドラ。
	 * ステップ分割したスクロール処理を行う。
	 */
	function step_scroll_trigger() {
		var scroll_size = parseInt(position() / STEP_COUNT);
		(function step_scroll() {
			scrollTo(0, position() - scroll_size);
			if(position()) setTimeout(step_scroll, 10);
		})();
	}

	/**
	 * 関数呼び出し時のスクロール量を整数値で返す
	 * @return {number}
	 */
	function position() {
		var doc = document;
		if(doc.body && doc.body.scrollTop)
			return doc.body.scrollTop;
		if(doc.documentElement && doc.documentElement.scrollTop)
			return doc.documentElement.scrollTop;
		return window.pageYOffset || 0;
	}

}


/**
 * 外部向けリンクにtarget属性を設定する
 * @param target_value
 */
function external_anchors_set_target(target_value) {

	[].filter.call(document.links, is_external_link)
		.forEach(set_target_blank);

	/**
	 *
	 * @param anchor
	 * @return {boolean}
	 */
	function is_external_link(anchor) {
		return /^https?:\/\//.test(anchor.getAttribute('href'));
	}

	/**
	 *
	 * @param anchor
	 */
	function set_target_blank(anchor) {
		anchor.setAttribute('target', target_value);
	}

}

