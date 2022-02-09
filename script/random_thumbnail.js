/**
 * サムネールの表示数を指定する
 * @type {number}
 */
var THUMBNAIL_ITEM_COUNT = 3;

/**
 * サムネール画像一覧のJSONを取得するURI
 * @const
 * @type {string}
 */
var THUMBNAIL_LIST_PATH = '/images/introduction/';

Promise.all(
	[new Promise(function(resolve){ window.addEventListener('load', resolve, false); }),

	// サムネールリストの取得
	(new Promise(function(resolve,reject){
		var xhr = new XMLHttpRequest();
		xhr.addEventListener('load', resolve, false);
		xhr.addEventListener('error', reject, false);
		xhr.addEventListener('abort', reject, false);
		xhr.open('GET', THUMBNAIL_LIST_PATH, true);
		xhr.setRequestHeader('Accept', 'application/json');
		xhr.send("");
	}))

	// JSON化
	.then(function(event){
		return JSON.parse(event.target.responseText);
	})

	// 受け取ったjsonファイルリストを元に、写真をランダム配置したサムネールを設置する
	.then(function(json){
		return shuffle(json.files.filter(filename_filter))
			.slice(0, THUMBNAIL_ITEM_COUNT)
			.map(function(path){ return json.dirname + path; });

		// hatake01は縦画像
 		function filename_filter(filename) {
			return !/^hatake01/.test(filename);
		}

		// Shuffle a collection, using the modern version of the
		// [Fisher-Yates shuffle](http://en.wikipedia.org/wiki/Fisher–Yates_shuffle).
		function shuffle(data) {
			var length = data.length;
			var shuffled = Array(length);
			for(var index = 0, rand; index < length; index++) {
				rand = Math.floor(Math.random() * (index+1));
				if(rand !== index) shuffled[index] = shuffled[rand];
				shuffled[rand] = data[index];
			}
			return shuffled;
		}

	})])

	// ウィンドウロード後でかつサムネールリスト取得済みなら、ファイルリストをfigure要素に
	.then(function(values) {
		var fragment = document.createDocumentFragment();
		values[1].map(build_image_element).map(fragment.appendChild.bind(fragment));
		return build_figure_element(fragment);

		function build_image_element(src) {
			var element = document.createElement('img');
			element.setAttribute('alt', '');
			element.setAttribute('src', src);
			return element;
		}

		function build_figure_element(contents) {
			var element = document.createElement('figure');
			element.setAttribute('id', 'thumbnail');
			element.appendChild(contents);
			return element;
		}

	})

	// 生成した画像を配置対象要素に加える
	.then(function(figure){
		var target_element = document.getElementById('description');
		target_element.insertBefore(figure, target_element.firstChild);
	});
