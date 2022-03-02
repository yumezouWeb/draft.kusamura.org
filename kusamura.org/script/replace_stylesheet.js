[].slice.call(document.documentElement.querySelectorAll('head > link[rel|="stylesheet"]'), 0)
	.map(function(link){ link.parentNode.removeChild(link); });

document.documentElement.querySelector('head').appendChild((function(n){
	n.type = 'text/css';
	n.rel = 'stylesheet';
	n.href = 'http://draft.kusamura.org/style/basic.css';
	return n;
})(document.createElement('link')));