
/**
 * トップページ用サムネールを生成する
 */
window.addEventListener('load', (function(){
	var image_directory, window_loaded, xhr;
	
	image_directory = '/images/introduction/';
	window_loaded = false;
	
	// images/introductionディレクトリの写真一覧を取得する。
	xhr = new XMLHttpRequest();
	xhr.addEventListener('load', ajaxDone, false);
	xhr.addEventListener('error', ajaxDone, false);
	xhr.addEventListener('abort', ajaxDone, false);
	xhr.open('GET', image_directory, true);
	xhr.setRequestHeader('Accept', 'application/json');
	xhr.send("");
	
	// xhrの共通ハンドラ。通信の後始末をする。
	function ajaxDone(event) {
		if(!window_loaded) { setTimeout(ajaxDone, 100); return; }
		xhr.removeEventListener('load', ajaxDone, false);
		xhr.removeEventListener('error', ajaxDone, false);
		xhr.removeEventListener('abort', ajaxDone, false);
		if(xhr.responseText)
			renderThumbnail(JSON.parse(xhr.responseText));
		xhr = null;
	}
	
	// 受け取ったjsonファイルリストを元に、写真をランダム配置したサムネールを設置する
	function renderThumbnail(json) {
		// hatake01は縦画像
		var names = json.files.filter(function(filename){ return filename !== 'hatake01.jpg' });
		var shuffled_names = pickupRandom(names).slice(0, 3);
		var figure_element = JSHTML.parse({
			figure: shuffled_names.map(function(path){ return { img: null, $:{ alt: "", src: json.dirname + path } }; }),
			$: { id: 'thumbnail' }
		});
		var target_element = document.getElementById('description');
		target_element.insertBefore(figure_element, target_element.firstChild);
	}
	
	// 配列をシャッフルして返す
	function pickupRandom(items) {
		var shuffled = items.slice(),
			i = items.length,
			rand, temp;
		while(i) {
			rand = Math.floor(Math.random() * i);
			temp = shuffled[--i];
			shuffled[i] = shuffled[rand];
			shuffled[rand] = temp;
		}
		return shuffled;
	}

	// フラグのセット
	return function(){ window_loaded = true; };
	
})(), false);


