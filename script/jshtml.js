/**
 * Created by tatsu_000 on 2015/10/12.
 */


/**
 *
 * @param data
 * @return {HTMLElement|Text|DocumentFragment|Node}
 */
function jshtml(data) {
	if(data.nodeType)
		return data.cloneNode(true);
	if(data instanceof Array)
		return jshtml.fragment(data);
	if(data instanceof Object)
		return jshtml.element(data);
	return jshtml.text(data);
}

/**
 *
 * @param data
 * @return {HTMLElement}
 */
jshtml.element = function(data){
	var tag, element, attributes, name, contents;
	for(tag in data) {
		if(tag === '$') continue;
		element = document.createElement(tag);
		attributes = data.$;
		if(attributes)
			for(name in attributes)
				jshtml.attribute(element, name, attributes[name]);
		contents = data[tag];
		if(contents !== null)
			element.appendChild(jshtml(contents));
		break;
	}
	return element;
};

/**
 *
 * @param {HTMLElement} element
 * @param name
 * @param value
 * @return {HTMLElement}
 */
jshtml.attribute = function(element, name, value){
	if(name === 'style' && typeof value === 'object') {
		for(var key in value) element.style[key] = value[key];
	}
	else if(/^on/.test(name) && typeof value === 'function') {
		element.addEventListener(name, value, false);
	}
	else if(value === null) {
		element.removeAttribute(name);
	}
	else {
		element.setAttribute(name, value);
	}
	return element;
};

/**
 *
 * @param data
 * @return {Text}
 */
jshtml.text = function(data){
	return document.createTextNode(data);
};

/**
 *
 * @param data
 * @return {DocumentFragment}
 */
jshtml.fragment = function(data){
	var fragment = document.createDocumentFragment();
	for(var i=0,len=data.length; i < len; i++)
		fragment.appendChild(jshtml(data[i]));
	return fragment;
};