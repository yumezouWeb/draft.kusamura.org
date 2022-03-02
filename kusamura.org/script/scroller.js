if(!Function.prototype.bind)
	Function.prototype.bind = function(context){
		var self = this;
		if(arguments.length < 2)
			return function(){ return self.apply(context, arguments); };
		
		var bind_params = Array.prototype.slice.call(arguments, 1);
		return function(){
			return self.apply(context, arguments.length ? bind_params.concat(arguments) : bind_params);
		};
	};


/**
 * JSON表記のHTMLテンプレート
 */
var JSHTML = {
	
	/**
	 * @param {Object|Array|String} jshtml
	 * @return Node
	 */
	parse: function(jshtml){
		if(jshtml instanceof Array) {
			var node = document.createDocumentFragment();
			for(var i=0; i<jshtml.length; i++)
				node.appendChild(this.parse(jshtml[i]));
			return node;
		} else if(jshtml instanceof Object) {
			return this._parseElement(jshtml);
		}
		return document.createTextNode(jshtml);
	},
	
	_parseElement: function(jshtml){
		var tag, element, hash, i;
		for(tag in jshtml) {
			if(tag.charAt() === '$') continue;
			element = document.createElement(tag);
			hash = jshtml.$;
			if(hash) for(i in hash) {
				if(/^on/.test(i))
					element.addEventListener(i.slice(2), hash[i], false);
				else
					element.setAttribute(i, hash[i]);
			}
			hash = jshtml[tag];
			if(hash !== null) element.appendChild(this.parse(hash));
		}
		return element;
	}
	
};


/**
 * トップへのスクロールボタンを実装する。
 */
var ScrollController = {
	
	_pid: null,
	
	STEPS: 20,
	
	getCurrentPosition: function() {
		var doc = document;
		if(doc.body && doc.body.scrollTop)
			return doc.body.scrollTop;
		if(doc.documentElement && doc.documentElement.scrollTop)
			return doc.documentElement.scrollTop;
		return window.pageYOffset || null;
	},
	
	scroll: function(step) {
		var pos, pow_now;
		pos = this.getCurrentPosition();
		scrollTo(0, pos + step);
		pos_now = this.getCurrentPosition();
		if(pos_now === pos)
			clearInterval(this._pid);
	},
	
	handleEvent: function(e){
		if(this._pid) clearInterval(this._pid);
		var step_size = parseInt(-(this.getCurrentPosition() / this.STEPS));
		clearInterval(this._pid);
		this._pid = setInterval(this.scroll.bind(this, step_size), 10);
	}
	
};

// クロスブラウザ用補完処理
if(!window.addEventListener) {
	window.addEventListener = document.addEventListener = document.documentElement.addEventListener = function(event, listener, capture){
		this.attachEvent(event,
			typeof listener === 'function'
			? listener
			: listener.handleEvent._wrapper || (listener.handleEvent._wrapper = function(e){ listener.handleEvent(e); }));
	};
	window.removeEventlistener = document.removeEventListener = document.documentElement.removeEventListener = function(event, listener, capture){
		this.detachEvent(event, typeof listener === 'function' ? listener : listener.handleEvent._wrapper);
	};
	
	document.__toStandard = function(target){
		var methods = ['addEventListener', 'removeEventListener'], index = 0;
		while(index < methods.length)
			target[methods[index]] = this[methods[index++]];
		
		if(target.hasChildNodes())
			for(var item = target.firstChild; item; item = item.nextSibling)
				this.__toStandard(item);
		
		return target;
	};
	
	document._createElement = document.createElement;
	document.createElement = function(tag){ return this.__toStandard(this._createElement(tag)); };
	
	window.addEventListener('load', function(e){ document.__toStandard(document.documentElement); }, false);
}

/**
 * ロード後イベントとしてバインド
 */
window.addEventListener('load', function(){
	document.body.appendChild(JSHTML.parse({
		button: '↑',
		$: {
			'title': 'ページ先頭にスクロール',
			'class': 'scroll_trigger',
			'onclick': ScrollController
		}
	}));
}, false);

