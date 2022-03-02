function OfficePoint(source, x, y, options) {
	this.sourceId = source;
	this.x = x;
	this.y = y;
	if(!options) return;
	
	if(options.radius)
		this.radius = options.radius;
	if(options.fillColor)
		this.fillColor = options.fillColor;
	if(options.lineColor)
		this.lineColor = options.lineColor;
	if(options.onshow)
		this.onshow = options.onshow;
	if(options.onhide)
		this.onhide = options.onhide;
}

OfficePoint.prototype.radius = 10;
OfficePoint.prototype.fillColor = [255, 50, 0];
OfficePoint.prototype.lineColor = [160, 25, 0];

/**
 * 拡大縮小等の影響を考慮した座標を取得する
 */
OfficePoint.prototype.getRevisedPoint = function(){
	var 
		img = document.getElementById(this.sourceId),
		ratio = img.width / parseFloat(img.getAttribute('width')),
		style = getComputedStyle(img),
		x = this.x * ratio,
		y = this.y * ratio,
		values, i;
	
	values = [parseFloat(style.left), parseFloat(style.paddingLeft), parseFloat(style.marginLeft)];
	i = values.length;
	while(i) if(!isNaN(values[--i])) x += values[i];
	
	values = [parseFloat(style.top), parseFloat(style.paddingTop), parseFloat(style.marginTop)];
	i = values.length;
	while(i) if(!isNaN(values[--i])) y += values[i];
	
	return { x: x, y: y };
};


/**
 * 指定ポイントから地図上座標へのラインを引く
 */
OfficePoint.prototype.draw = function(canvas, from_x, from_y){
	var
		context = canvas.getContext('2d'),
		to = this.getRevisedPoint();
	
	context.clearRect(0, 0, canvas.width, canvas.height);
	context.beginPath();
	context.fillStyle = 'rgb(' + this.fillColor + ')';
	context.strokeStyle = 'rgb(' + this.lineColor + ')';
	
	context.arc(from_x, from_y, this.radius, 0, Math.PI * 2);
	context.fill();
	context.moveTo(from_x, from_y);
	context.lineTo(to.x, to.y);
	context.stroke();
	context.closePath();
	
	context.beginPath();
	context.arc(to.x, to.y, this.radius, 0, Math.PI * 2);
	context.fill();
	context.closePath();
	
};


OfficePoint.prototype.showText = function(canvas, target_x, target_y, text){
	var context = canvas.getContext('2d');
	context.fillStyle = 'white';
	context.fillRect(target_x, target_y, this.radius * 32, this.radius * 3);
	context.strokeStyle = 'black';
	context.rect(target_x, target_y, this.radius * 32, this.radius * 3);
	context.stroke();
	context.font = this.radius * 1.5 + "px monospace";
	context.fillStyle = "red";
	context.fillText(text, target_x + this.radius / 2, target_y + this.radius * 2, this.radius * 30);
};

/**
 * 広域マップの座標を強調する。
 */
window.addEventListener('load', 
{
	radius: 10,
	fillColor: [255,50,0],
	lineColor: [160,25,0],
	
	canvasId: 'maphint',
	imgId: 'tama_hachioji_map',
	indexId: 'office_index',
	otherMapId: 'heart_of_tokyo_map',
	
	routes: {
		"manju-yumu": new OfficePoint('tama_hachioji_map', 70, 783),
		"time": new OfficePoint('tama_hachioji_map', 100, 783),
		"muku": new OfficePoint('tama_hachioji_map', 268, 800),
		"hatake": new OfficePoint('tama_hachioji_map', 412, 86),
		"somu": new OfficePoint('tama_hachioji_map', 630, 382),
		"yumezou": new OfficePoint('tama_hachioji_map', 660, 382),
		"yumu#matsugaya": new OfficePoint('tama_hachioji_map', 690, 382),
		"access": new OfficePoint('tama_hachioji_map', 834, 700),
		"yumeusagi": new OfficePoint('tama_hachioji_map', 1132, 885),
		"kitchen": new OfficePoint('tama_hachioji_map', 1132, 885),
		"yumu": new OfficePoint('tama_hachioji_map', 1349, 1082),
		"yumu#yumenu": new OfficePoint('tama_hachioji_map', 1629, 952),
		
		"yumu#baba": new OfficePoint('heart_of_tokyo_map',464,474, 
		{
			onshow: function(){
				document.getElementById(this.sourceId).className = 'show';
			},
			onhide: function(){
				document.getElementById(this.sourceId).className = '';
			}
		})
	},
	
	handleEvent: function(e) {
		switch(e.type) {
			case 'focus':
			case 'mouseover':
				this.show(e); break;
			case 'blur':
			case 'mouseout':
				this.hide(e); break;
			case 'load':
				this.init(e); break;
		}
	},
	
	init: function(e){
		var canvas = document.createElement('canvas');
		if(!canvas.getContext) return null;
		
		// マップ画像からキャンバスを生成する
		var img = document.getElementById(this.imgId);
		canvas.id = this.canvasId;
		canvas.setAttribute('width', img.getAttribute('width'));
		canvas.setAttribute('height', img.getAttribute('height'));
		img.parentNode.insertBefore(canvas, img);
		
		// 事業所リストにリスナーをセットする
		var index = document.getElementById(this.indexId);
		index.addEventListener('focus', this, true);
		index.addEventListener('blur', this, true);
		index.addEventListener('mouseover', this, false);
		index.addEventListener('mouseout', this, false);
		
		this._image = img;
		this._index = index;
		this._canvas = canvas;
	},
	
	getRatio: function(img){
		return img.width / parseFloat(img.getAttribute('width'));
	},
	
	getSharedParent: function(a,b){
		var lineage = [a];
		while(a.parentNode) lineage.push((a = a.parentNode));
		while(b.parentNode) {
			if(lineage.indexOf(b) !== -1) return b;
			b = b.parentNode;
		}
		return null;
	},
	
	getNodePosition: function(target, root){
		var 
			result = { x: 0, y: 0 },
			style = getComputedStyle(target);
		while(target && target != root) {
			result.x += target.offsetLeft;
			result.y += target.offsetTop;
			target = target.offsetParent;
		}
		return result;
	},
	
	getBindedPoint: function(anchor){
		var href = anchor.href;
		if(!href) return null;
		var path = href.slice(href.lastIndexOf('/')+1);
		return this.routes.hasOwnProperty(path) ? this.routes[path] : null;
	},
	
	show: function(e){
		var target = e.target;
		if(target.nodeName !== 'A') return;
		
		var point = this.getBindedPoint(target);
		if(!point) return;
		
		var canvas, context, style, from;
		canvas = this._canvas;
		canvas.className = 'appeal';
		
		point.draw(canvas, target);
		
		// indexに用いている背景画像の人参に位置合わせを行う
		style = getComputedStyle(target.parentNode);
		from = this.getNodePosition(target, this.getSharedParent(target, canvas));
		from.x -= parseFloat(style.fontSize) / 2;
		from.y += parseFloat(style.height) / 2;
		from.y -= this._index.scrollTop;
		
		
		var text = target.parentNode;
		while(text.nodeName !== 'DD') text = text.nextSibling;
		text = text.textContent || text.lastChild.data;
		
		// 補助線の描画
		point.draw(canvas, from.x, from.y);
		point.showText(canvas, 10, 10, text);
		if(point.onshow) point.onshow();
	},
	
	hide: function(e){
		var point = this.getBindedPoint(e.target);
		if(!point) return;
		this._canvas.className = '';
		if(point.onhide) point.onhide();
	}
	
}, false);
