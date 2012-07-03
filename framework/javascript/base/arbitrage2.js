/**
	@author Eric M. Janik
	@date May 15 2012
	@description Javascript application object that ties into the Arbitrage2 backend Framework
*/

//Log facility
if(!window['$l'])
{
	var $l = function() {
		if($.cookie('debug') == 1 && console && console.log)
		{
			if(console.log.apply)
			{
				Array.prototype.unshift.call(arguments, 'arbitrage2: ');
				console.log.apply(console, arguments);
			}
			else
			{
				console.log('=== START arbitrage2:');
				for(var i=0, arg; i<arguments.length, arg=arguments[i]; i++)
					console.log(arg);

				console.log('=========================END');
				console.log('');
			}
		}
	};
}
$l('log facility on');

//Create Arbitrage2 base
if(window['arbitrage2'] == undefined)
	window['arbitrage2'] = { };

/**
	@description Adds inheritence to the class
	@param child The child subject that is going to inherit another class.
	@param parent The parent to inherit.
*/
arbitrage2.inherit = function(child, parent) {
	var F = function() { };
	F.prototype     = parent.prototype;
	child.prototype = new F();
	child.prototype.constructor = child;
	child.superproto = parent.prototype;

	return child;
};

/**
	@description Private variable counting how many modules are still being loaded.
*/
arbitrage2._loading = 0;

/**
	@description List of required files being loaded or already loaded.
	@static
*/
arbitrage2._requiring = [ ];

/**
	@description Called when the arbitrage2 application is ready.
*/
arbitrage2.ready = false;

/**
	@description Tells Arbitrage2 that the file being implemented is providng a certain namespace. Implments object into the global namespace.
	@params namespace The namespace being provided.
*/
arbitrage2.provide = function(namespace) {
	//Create namespace if not already there
	var split = namespace.split('.');
	var obj   = window;

	for(var i=0, name; name=split[i], i<split.length; i++)
	{
		if(!obj[name])
			obj[name] = { };

		obj = obj[name];
	}
};

/**
	@description Requires a dependency file.
	@params namespace The namespace to requires.
	@params opt_cb_load Optional onload callback.
*/
arbitrage2.require = function(namespace, opt_cb_load) {
	var self = this;

	function _checkReady() {

		arbitrage2._loading--;

		if(arbitrage2._loading == 0)
			arbitrage2.ready = true;

		$l('require done', arbitrage2._loading, arbitrage2.ready);

		//User callback
		if(opt_cb_load)
			opt_cb_load.call(this);
	};

	var file      = namespace.split('.');
	var namespace = file.splice(0, file.length-1);
	var split     = file.join('').match(/[A-Z][a-z]+/g);
	if(split)
		file = split.join('_').toLowerCase();

	var path = "/javascript/" + namespace.join('/') + "/" + file + ".js";

	//Check if element already exists, if so we pretend we required it again
	for(var i=0, script; i<self._requiring.length, script=self._requiring[i]; i++)
	{
		if(script == path)
		{
			$l('script already requiring or required ' + script);
			if(opt_cb_load)
				opt_cb_load.call();

			return;
		}
	}

	//Add to requiring
	self._requiring.push(path);

	//Create javascript tag
	var head   = document.getElementsByTagName('head').item(0);
	var script = document.createElement('script');

	//setup script attributes
	script.type     = 'text/javascript';
	script.language = "JavaScript";
	script.src      = path;

	if('onreadystatechange' in script)
		script.onreadystatechange = function() { if(this.readyState == "complete" || this.readyState == 'loaded') _checkReady(); };
	else
		script.onload = _checkReady;

	script.async = false;
	
	//Add to loading
	arbitrage2._loading++;
	$l('requiring "' + path + '"', arbitrage2._loading, arbitrage2.ready);
	
	//Add to dom
	head.appendChild(script);
};

/**
	@description Exports a symbold to an already created object.
	@param namespace The namespace to export he new simbol into.
	@param obj The object to export
*/
arbitrage2.exportSymbol = function(namespace, obj) {
	var last  = namespace.split('.');
	namespace = last.splice(0, last.length-1);

	var cur = window;
	for(var i=0, frag; frag=namespace[i], i<namespace.length; i++)
	{
		if(!cur[frag])
		{
			alert("Error, unable to export!");
			return false;
		}

		cur = cur[frag];
	}

	//TODO: Check if last exists already
	cur[last] = obj;
};

/**
	@description Returns the object.
	@param namespace
	@return Returns the symbol.
*/
arbitrage2.getSymbol = function(namespace) {
	namespace = namespace.split('.');
	
	var symbol = window;
	for(var i=0; symbol=symbol[namespace[i]], i<namespace.length-1; i++);

	return symbol;
};

/**
	@description Calls a callback method when all required js files have been included and the application is ready to run.
	@param cb_main The callback to call.
*/
arbitrage2.main = function(cb_main) {
	var self = this;

	function _checkMainExists() {

		if(self._loading > 0)
		{
			setTimeout(function() { _checkMainExists(cb_main); }, 300);
			return;
		}

		var namespace = cb_main.split('.');
		var method    = window;
			
		//Call method
		for(var i=0; i<namespace.length; i++)
		{
			method = method[namespace[i]];
			if(method == undefined)
			{
				setTimeout(function() { _checkMainExists(cb_main); }, 300);
				return;
			}
		}

		method();
	};

	function _checkDocumentReady() {
		if(document.readyState === "complete")
		{
			_checkMainExists();
			clearInterval(_check);
		}
	};

	var _check = setInterval(_checkDocumentReady, 10);
};

//Include other base items
arbitrage2.require('arbitrage2.base.utils');
arbitrage2.require('arbitrage2.base.ajax');
arbitrage2.require('arbitrage2.base.cache');
arbitrage2.require('arbitrage2.base.dbus');
arbitrage2.require('arbitrage2.base.mvc');
arbitrage2.require('arbitrage2.base.gui');
arbitrage2.require('arbitrage2.base.form');
