/**
	@author Eric M. Janik
	@date May 15 2012
	@description Javascript application object that ties into the Arbitrage2 backend Framework
*/

//Log facility
if(!window['$l'])
{
	var $l = function() {
		if($.cookie('debug') == 1 && arbitrage2.config.log && console && console.log)
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
	@description Called when the arbitrage2 application is ready.
*/
arbitrage2.ready = false;

/**
	@description Arbitrage2 config.
*/
arbitrage2.config = arbitrage2.config || {
	frameworkPath: "/framework/mvc_client/javascript",       //Javascript path to use when including files
	applicationPath: "/javascript",                          //Javascript path to the application

	includePaths: {                                          //The include paths
		'arbitrage2': '/framework/client_mvc/javascript',      //arbitrage2 namespace
		'application': '/javascript'                           //application namespace
	},

	stylesheetPath: "/stylesheets",                          //Stylesheet path to use when including css
	mvc: { }                                                 //Empty config only used when MVC pattern is used
};

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
	@description Require file class that holds relavent information and methods.
	@params opt_cb_load Optional onload callback.
	@params opt_cb_error Optional onload error callback.
*/
arbitrage2.RequiredFile = function(src, opt_cb_load, opt_cb_error) {
	var self       = this;
	self.src       = src;           //Source of the file
	self.state     = "requiring";   //The state the file is in
	self.element   = undefined;     //The DOM element associated with the RequiredFile  
	self.cb_load   = [ ];           //The array of callback functions to call when loaded
	self.cb_error  = [ ];           //The array of callback functions to call when an error occurs
	self.namespace = undefined;     //Associated namespace if relavent

	//Events setup
	if(opt_cb_load)
		self.cb_load.push(opt_cb_load);
	
	if(opt_cb_error)
		self.cb_error.push(opt_cb_error);
};

/**
	@description Calls all cb_load functions.
	@params ev The event attached to the callback.
*/
arbitrage2.RequiredFile.prototype.cbLoaded = function(ev) {
	var self   = this;
	self.state = 'loaded';
	
	for(var i=0; i<self.cb_load.length; i++)
		self.cb_load[i].call(self);
	
	//Remove callbacks
	self.cb_load  = [ ];
	self.cb_error = [ ];
};

/**
	@description Calls all cb_error functions.
	@params ev The event attached to the callback.
*/
arbitrage2.RequiredFile.prototype.cbError = function(ev) {
	var self   = this;
	self.state = 'error';
	
	for(var i=0; i<self.cb_error.length; i++)
		self.cb_error[i].call(self);
	
	alert('error');
	
	//Remove callbacks
	self.cb_load  = [ ];
	self.cb_error = [ ];
};

/**
	@description Loads the required file
*/
arbitrage2.RequiredFile.prototype.load = function() { };






/**
	@description Method appends callbacks to the callback list. If the script is already loaded, we immediately call the callback and return.
	@params opt_cb_load Optional onload callback.
	@params opt_cb_error Optional onload error callback.
	@return Returns true if a callback was immediately called, else false.
*/
arbitrage2.RequiredFile.prototype.appendCallbacks = function(opt_cb_load, opt_cb_error) {
	var self = this;

	if(script.state == "loaded" || script.state == "error")
	{
		if(opt_cb_load)
			opt_cb_load.call(self);

		return true;
	}
	else if(script.state == "error")
	{
		if(opt_cb_error)
			opt_cb_error.call(self);

		return true;
	}
	else
	{
		if(opt_cb_load)
			self.cb_load.push(opt_cb_load);

		if(opt_cb_error)
			self.cb_error.push(opt_cb_error);
	}

	return false;
};

/**
	@description Require file class specific to javascript that holds relavent information and methods.
	@params opt_cb_load Optional onload callback.
	@params opt_cb_error Optional onload error callback.
*/
arbitrage2.RequiredJavascriptFile = function(src, opt_cb_load, opt_cb_error) {
	var self = this;
	arbitrage2.RequiredJavascriptFile.superproto.constructor.call(self, src, opt_cb_load, opt_cb_error);
};
arbitrage2.inherit(arbitrage2.RequiredJavascriptFile, arbitrage2.RequiredFile);

/**
	@description An array of files associated with this RequiredFile type.
	@static
*/
arbitrage2.RequiredJavascriptFile.prototype.files = [ ];

/**
	@description Adds an SCRIPT element to the DOM.
*/
arbitrage2.RequiredJavascriptFile.prototype.load = function() {
	var self = this;

	//Add to files
	self.files.push(self);

	//Create javascript tag
	self.element = document.createElement('script');

	//setup script attributes
	self.element.type     = 'text/javascript';
	self.element.language = "JavaScript";
	self.element.src      = self.src;
	self.element.async    = false;

	//Setup state change events
	if('onreadystatechange' in self.element) //IE browsers
	{
		alert('state change browser');
		/*requiring.element.onerror = function(ev) {
			if(opt_cb_error)
				opt_cb_error();
		};

		requiring.element.onreadystatechange = function(ev) {
			if(this.readyState == "complete" || this.readyState == 'loaded')
				_checkReady();
		};*/
	}
	else
	{
		self.element.onload  = function(ev) { self.cbLoaded(ev); };
		self.element.onerror = function(ev) { self.cbError(ev); };
	}

	//Add to loading
	arbitrage2._loading_javascripts++;
	$l('requiring "' + self.src + '"');

	//Set state
	self.state = 'loading';
	
	//Add to HEAD
	document.getElementsByTagName('head').item(0).appendChild(self.element);
};




/**
	@description Requires a javascript dependency file into the DOM.
	@param file The file to include.
	@params opt_cb_load Optional onload callback.
	@params opt_cb_error Optional onload error callback.
*/
arbitrage2.requireJavascript = function(file, opt_cb_load, opt_cb_error) {

	//Include script
	var script = new arbitrage2.RequiredJavascriptFile(file, opt_cb_load, opt_cb_error);
	script.load();
};

/**
	@description Requires a dependency file.
	@params namespace The namespace to requires.
	@params opt_cb_load Optional onload callback.
	@params opt_cb_error Optional onload error callback.
*/
arbitrage2.require = function(namespace, opt_cb_load, opt_cb_error) {
	var self = this;

	//Ensure symbol does not exist
	var symbol = arbitrage2.getSymbol(namespace);

	//Require the javascript file and add to _required_javascipts
	var onamespace = namespace;
	var file       = namespace.split('.');
	var namespace  = file.splice(0, file.length-1);
	var split      = file.join('').match(/[A-Z][a-z]+/g);
	if(split)
		file = split.join('_').toLowerCase();

	//Get root path
	var path = self.config.includePaths[namespace[0]] + "/" + namespace.join('/') + "/" + file + ".js";

	//Require the JS File in a closure to retain namespacing
	(function(namespace, path, opt_cb_load, opt_cb_error) {

		function _cbLoad() {
			this.namespace = namespace;
			if(opt_cb_load)
				opt_cb_load.call(this);
		};

		function _cbError() {
			this.namespace = namespace;
			if(opt_cb_error)
				opt_cb_error.call(this);
		};

		arbitrage2.requireJavascript(path, _cbLoad, _cbError);

	})(onamespace, path, opt_cb_load, opt_cb_error);
};

/**
	@description Method requires a list files and runs a callback on complete.
*/
arbitrage2.bulkRequire = function() {
	var count = arguments.length-1;
	var cb    = arguments[count];

	function _cbReady() {
		count--;

		if(count === 0)
			cb();
	};

	//Require the files
	for(var i=0; i<count; i++)
		arbitrage2.require(arguments[i], _cbReady, _cbReady);
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
	for(var i=0; i<namespace.length && symbol; i++)
		symbol=symbol[namespace[i]];
	
	return symbol;
};

/**
	@description Method converts a URL to an arbitrage namespace.
	@param url The url to convert to an Arbitrage namespace.
	@return Returns the Arbitrage namespace.
*/
arbitrage2.convertURLNamespaceToArbitrage = function(url) {
	var ret = url;

	ret = ret.replace(/^\//, '');
	ret = ret.replace(/\//gi, '.');
	return ret;
};

/**
	@description Calls a callback method when all required js files have been included and the application is ready to run.
	@param cb_main The callback to call.
*/
arbitrage2.main = function(cb_main) {

	function _checkMainExists() {

		if(!arbitrage2.ready)
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

/**
	@description Function converts a URL namespace to an arbitrage namespace.
*/

  

//Include other base items
arbitrage2.bulkRequire('arbitrage2.base.utils', 'arbitrage2.base.ajax', 'arbitrage2.base.cache', 'arbitrage2.base.dbus', 'arbitrage2.base.mvc', 'arbitrage2.base.gui', 'arbitrage2.base.form', function() {
	arbitrage2.ready = true;
});
