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
 * @namespace
 */
var Kusamura = {};


Kusamura.HeaderNavigation = 
{ ul: [
	{ li: [
		{ a: 'トップ', $: { href: '/' } },
		{ ul: [
			{ li: { a: '沿革', $: { href: '/history' } } },
			{ li: { a: '組織体制', $: { href: '/structure' } } }
		]}
	]},
	{ li: { a: '最新情報', $: { href: './' } } },
	{ li: { a: 'ご利用案内', $: { href: '/guidance' } } },
	{ li: [
		{ a: '事業所紹介', $: { href: '/introduction/' } },
		{ ul: [
			{ li: { a: '遊夢', $: { href: '/introduction/yumu' } } },
			{ li: { a: 'まんじゅう屋', $: { href: '/introduction/manju-yumu' } } },
			{ li: { a: '夢畑', $: { href: '/introduction/hatake' } } },
			{ li: { a: '草夢', $: { href: '/introduction/somu' } } },
			{ li: { a: '夢うさぎ', $: { href: '/introduction/yumeusagi' } } },
			{ li: { a: '夢像', $: { href: '/introduction/yumezou' } } },
			{ li: { a: '夢来', $: { href: '/introduction/muku' } } },
			{ li: { a: 'グループホーム', $: { href: '/introduction/gh' } } },
			{ li: { a: '待夢', $: { href: '/introduction/time' } } }
		]}
	]},
	{ li: { a: 'アクセス', $: { href: '/access' } } },
	{ li: { a: 'お問合わせ', $: { href: '/inquire' } } }
]};

window.addEventListener('load', function(){
	
	document.body.appendChild(JSHTML.parse(Kusamura.HeaderNavigation));
	
}, false);



Kusamura.ready = function(target, callback){
	if(typeof target === 'function')
		callback = target, target = window;
	target.addEventListener('load', callback, false);
	target.addEventListener('abort', callback, false);
};

Kusamura.Model = function(defaults){
	this.defaults = defaults;
	this.changed = {};
};

Kusamura.Model.prototype.set = function(){};

Kusamura.Model.prototype.get = function(){};


Kusamura.View = function(){
	
};

Kusamura.View.prototype.render = function(){
	
};

Kusamura.Controller = function(){
	
};

Kusamura.Controller.prototype.handleEvent = function(){
	
};

window.addEventListener('load', function(event){
	
	
	
}, false);







