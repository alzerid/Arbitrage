arbitrage2.provide('spa');

/**
  @description Router class that parses the URL.
	@constructor
	@params routes The routes to take into consideration.
*/
spa.Router = function(routes) {
	var self    = this;
	self.routes = routes;
};

/**
	@description 
*/
spa.Router.prototype.route = function(url) {
	var self = this;
	var ret  = null;

	//Look at the routes and see if we need to map it
	for(var idx in self.routes)
	{
		var route = self.routes[idx];
		var exp   = new RegExp(idx);

		//Check expression
		if(exp.test(url))
			return route;
	}
	
	return url;
};

/**
	@description Application container class that manages controllers, canvases, etc...
	@constructor
	@params config The configuration rules.
*/
spa.Application = function(config) {
	var self            = this;
	self.config         = config || self.config;
	self.router         = new spa.Router(self.config.mvc.routes || { });
	self.virtual_uri    = "";
	self.request_uri    = window.location.hash.replace(/^#!/, '') || "/";
	self.controllers    = { };
	self.canvases       = { };
	self.layouts        = { };
	self.loadCount      = 0;
	self.needStart      = true;
	self.cache          = new arbitrage2.cache.CacheManager();
	self.currentAction  = null;
	self.ajax           = new spa.Application.prototype.Ajax(self);

	//Setup canvases
	if(self.config.mvc.canvases)
	{
		for(var idx in self.config.mvc.canvases)
			self.canvases[idx] = new arbitrage2.mvc.Canvas($(self.config.mvc.canvases[idx]));
	}

	//Set instance to self
	spa.Application.prototype._instance = self;

	//Onhash change event
	var listen = true;
	if("onhashchange" in window)
	{
		window.addEventListener('hashchange', function() {
			//We are only concerned with #! hash changes
			if(listen && window.location.hash.search(/^#!/) == 0)
			{
				//Check if the current action unload allows us to continue
				if(self.currentAction && self.currentAction.unload() !== true)
				{
					listen = false;
					window.history.back();
					return;
				}

				self.route(window.location.hash.replace(/^#!/, ''));
			}
			else if(listen == false)
				listen = true;
		});

		//Unload event
		window.addEventListener('beforeunload', function() {
			if(self.currentAction && self.currentAction.unload)
			{
				var ret = self.currentAction.unload('beforeunload');
				if(ret !== true)
					return ret;
			}
		});
	}
	else
		alert("Your browser does not suppor the capabilities of this website!");
};

/**
	@description Default configuration
	@static
*/
spa.Application.prototype.config = arbitrage2.config;

/*arbitrage2.config['mvc'] = {
	serverCanvas: false,       //Determines if the server dictates where to draw returned HTML
	autoRequire: false,        //Determines if Arbitrage should auto require javascript
	controllerNamespace: '',   //The namespace to use when including controllers
	canvases: { },             //Key value pair, where KEY is the name and value is the CSS selector
	debug: false,              //Wether debug is toggled
	routing: { }               //Routing rules
};*/

/**
	@description Global static instance of the application.
	@static
*/
spa.Application.prototype._instance = undefined;

/**
	@description Requires a layout script.
	@param layout The layout to require.
	@param opt_cb The callback to call after the require is done.
*/
spa.Application.prototype.requireLayout = function(layout, opt_cb) {
	var self = this;

	//Get Layout
	var namespace = self.config.mvc.rootNamespace + ".layouts." + layout;
	arbitrage2.require(namespace, function() {

		//Normalize layout
		var key = layout;
		layout  = layout.replace(/_/g, ' ').toUpperCaseWords().replace(/ /g, '');

		//Add layout to layouts
		var symbol = arbitrage2.getSymbol(self.config.mvc.rootNamespace + ".layouts." + layout + "Layout");
		self.layouts[key] = new symbol();
		self.layouts[key].load();

		//Callback
		if(opt_cb)
			opt_cb();
	},
	function() {
		alert('invalid layout!');
	});
};

/**
	@description Requires a controller to this specific application instance.
	@param namespace The fully qualified namespace.
	@param opt_cb_success The optional callback to execute.
*/
spa.Application.prototype.requireController = function(namespace, opt_cb_success, opt_cb_error) {
	var self = this;
	self.loadCount++;

	//Require controller if it does not exist
	arbitrage2.require(namespace, function() {
		self.loadCount--;

		if(opt_cb_success)
			opt_cb_success(namespace);
	},
	function() {
		self.loadCount--;

		if(opt_cb_error)
			opt_cb_error(namespace);
	});
};

/**
	@description Requires a css stylesheet.
	@param css The css filename to include.
*/
spa.Application.prototype.requireCSS = function(css) {

	//Check to see if css already included, if yes, don't include
	var nodes = document.getElementsByTagName('link');
	var path  = "/stylesheets/" + css;
	if(path.search(/\.css$/i) < 0)
		path += '.css';

	for(var i=0, link; i<nodes.length, link=nodes[i]; i++)
	{
		if(link.href == path)
			return;
	}

	//Create new link element
	var node = document.createElement("link");
	$l('requiring css ' + css);
	node.setAttribute('rel', 'stylesheet');
	node.setAttribute('type', 'text/css');
	node.setAttribute('href', path);

	//Append to head
	document.getElementsByTagName('head')[0].appendChild(node);
};

/**
	@description Runs the application by calling routes etc...
*/
spa.Application.prototype.run = function() {
	var self = this;

	//Check to see if dependencis are still being loaded
	if(self.loadCount > 0)
		setTimeout(function() { self.run(); }, 100);
	else
	{
		//Grab the route
		self.virtual_uri = self.router.route(self.request_uri);

		//Load the controller
		self.loadController(self.virtual_uri, function(controller) {

			//Check if controller is already loaded
			var action = controller.loadAction(self.virtual_uri);
			if(!action)
				return;
			
			//Execute the action
			self.execute(controller, action);
		});
	}
};

/**
	@description Navigates to a url by doing a hash change.
	@param url The url to navigate to.
*/
spa.Application.prototype.navigate = function(url, opt_hard) {
	window.location.hash = "#!" + url;
};

/**
	@description Method loads the controller into memory and grabs it if need be.
	@param url The url to convert to a url to get the controller.
	@param opt_cb_loaded Optional callback function to call.
*/
spa.Application.prototype.loadController = function(url, opt_cb_loaded) {
	var self       = this;
	var namespace  = arbitrage2.convertURLNamespaceToArbitrage(url).split('.');
	var controller = namespace.slice(-2, -1).join('').toUpperCaseFirst() + "Controller";
	var action     = namespace.splice(-1).join('');
	var symbol;

	//add controllers into namespace
	namespace = [].concat(namespace.slice(0, -1), ['controllers'], namespace.slice(-1)).join('.');
	symbol    = namespace.replace(/\.[^\.]+$/, "." + controller);

	//Check if controller exists
	var rsymbol = arbitrage2.getSymbol(symbol);
	if(rsymbol)
	{
		if(opt_cb_loaded)
			opt_cb_loaded(self.controllers[symbol]);

		return;
	}

	//Require the file
	self.requireController(namespace, function() {

		//Create new controller since we never seen it
		controller = arbitrage2.getSymbol(symbol);
		self.controllers[symbol]           = new controller(self);
		self.controllers[symbol].namespace = symbol;
		self.controllers[symbol].load();

		if(opt_cb_loaded)
			opt_cb_loaded(self.controllers[symbol]);
	},
	function() {
		alert("Unable to auto require controller '" + symbol + "'.");
	});
	

	//Require namespace
	return;
};

/**
	@description Method executes the action and draws it onto the canvas.
	@param <spa.Controller> controller The controller to execute.
	@param <spa.Action> action The action to execute.
*/
spa.Application.prototype.execute = function(controller, action) {
	var self   = this;
	var params = "";

	function _renderCache(cache) {
		consoel.log("_renderCache:", cache);
	};

	//TODO: Generate Parameters
	
	//Get from cache if possible
	var key   = controller.namespace + "?" + params;
	var cache = self.cache.get(key);
	if(!cache)
	{
		self.ajax.post(self.virtual_uri, params, function(ev) {
			console.log("post", ev);

			//Create cache object


			//_renderCache(cache);

		});

	}
	else
		_renderCache(cache);

	return;


	//var canvas = controller.canvas;
	//var params = { };
	//var key    = route.url + "?";

	console.log(namespace);
	return;

	/*function _renderCache(cache) {
		//Hide current
		if(self.currentAction)
			self.currentAction.hide();

		//Print out to canvas
		for(var idx in cache.returns)
		{
			var canvas = cache.returns[idx].canvas;
			var $obj   = cache.returns[idx].$obj;
			canvas.render($obj);
			canvas.$element.trigger('arbitrage2.mvc.change');
		}

		//Call initialize 
		if(!cache.action.initialized)
			cache.action.initialize(cache.ev.data);

		//Show action
		cache.action.show();

		//Set current action
		self.currentAction = cache.action;
	};

	function _generateParams(parameters) {
		for(var idx in parameters)
		{
			params[idx] = parameters[idx];
			key = key + idx + "=" + params[idx] + "&";
		}
	};

	//Get action, controller parameters, and query parameters
	_generateParams(controller.arguments());
	_generateParams(action.arguments(route.arguments));
	_generateParams(route.parameters);

	//Check if cached
	var cache = self.cache.get(key);

	//handle cached
	if(!cache)
	{
		//Call ajax post
		self.ajax.post(route.url, params, function(ev) {

			//Check return type
			var returns = [ ];
			if(ev.data.header && ev.data.header.type == "client")
			{
				function _loadController() {
					for(var idx in ev.data.client.canvas)
					{
						var canvas = self.canvases[idx];
						if(!canvas)
						{
							alert("Unable to paint on canvas '" + idx + "'. Is it registered?");
							continue;
						}

						//Add for caching
						var data = { $obj: $(ev.data.client.canvas[idx]),  data: ev.data.client.canvas[idx], canvas: canvas };
						returns.push(data);

						//Add to cache
						self.cache.add(key, { returns: returns, url: key, controller: controller, action: action, ev: ev });
						cache = self.cache.get(key);

						//Render It
						_renderCache(cache);
					}
				};

				//Check if layout exists, if not load it!
				var layout = undefined;
				if(!self.layouts[ev.data.client.layout])
					self.requireLayout(ev.data.client.layout, _loadController);
				else
					_loadController();

			}
			else
			{
				alert('Unknown return type!');
				console.log(ev);
			}

		});
	}
	else
		_renderCache(cache);*/
};

/**
	@description Removes all cache associated with the action.
	@param {spa.Action} The action remove from cache.
*/
spa.Application.prototype.staleAction = function(action) {
	var self = this;

	//Iterate through cache and set stale
	var keys = [];
	self.cache.iterate(function(key, obj) {
		if(obj.action == action)
			keys.push(key);
	});

	//Call action free
	action.free();

	//Remove keys from cache
	for(var i=0, c; c=keys[i], i<keys.length; i++)
		self.cache.removeByHash(c);
};

/**
	@description Flushes the entire cache except for the current action.
*/
spa.Application.prototype.flush = function() {
	var self = this;
	var keys = [];

	self.cache.iterate(function(key, obj) {
		if(obj.action != self.currentAction)
		{
			obj.action.free();
			keys.push(key);
		}
	});

	//Remove keys from cache
	for(var i=0, c; i<keys.length, c=keys[i]; i++)
		self.cache.removeByHash(c);
};


/**
	@description Info bar static object.
*/
spa.Application.prototype.InfoBar = { };

/**
	@description Private variable holding a timer handle.
*/
spa.Application.prototype.InfoBar._timer = null;

/**
	@description Shows an infobar on the page.
	@param txt The text to show.
	@param type The type of info bar to show.
	@param seconds The seconds to show, 0 if infinite.
*/
spa.Application.prototype.InfoBar.show = function(txt, type, seconds) {
	if(!type)
		type = 'info';
	
	//Grab element
	var ele = document.getElementById('arbitrage2_mvc_infobar');
	if(!ele)
	{
		ele    = document.createElement('div');
		ele.id = 'arbitrage2_mvc_infobar';

		//Add child
		ele.appendChild(document.createElement('div'));
		ele.childNodes[0].className = "content";
		ele.childNodes[0].innerHTML = txt;

		//Add to DOM
		document.getElementsByTagName('body')[0].appendChild(ele);
	}

	//Add class
	ele.childNodes[0].className = type;
	ele.childNodes[0].innerHTML = txt;

	//Set timer
	if(seconds)
	{
		if(spa.Application.prototype.InfoBar._timer)
			clearTimeout(spa.Application.prototype.InfoBar._timer);

		spa.Application.prototype.InfoBar._timer = setTimeout(spa.Application.prototype.InfoBar.hide, seconds*1000);
	}
};

/**
	@description Hides the infobar on the page.
*/
spa.Application.prototype.InfoBar.hide = function() {
	var ele = document.getElementById('arbitrage2_mvc_infobar');
	if(ele)
		ele.parentElement.removeChild(ele)

	clearTimeout(spa.Application.prototype.InfoBar._timer);
	spa.Application.prototype.InfoBar._timer = null;
};


/**
	@description Ajax module.
	@constructor
*/
spa.Application.prototype.Ajax = function(application) { 
	var self = this;
	self.application = application;
	self.timer       = null;
	self.mode        = 0;
};

/**
	@description Does an AJAX Post call
	@param url The URL to query.
	@param parameters The parameters to send with the AJAX call.
	@param cb_success The success callback function to execute upon success.
*/
spa.Application.prototype.Ajax.prototype.post = function(url, parameters, cb_success) {
	var self = this;

	//Start timer, if more than 1 second, show loading bar
	self._escalate(1);
	arbitrage2.ajax.post(url, parameters, function(ev) {

		//Remove loading bar
		self._escalate(0);

		if(cb_success)
			cb_success(ev);
	});
};

/**
	@description Does an AJAX Get call
	@param url The URL to query.
	@param parameters The parameters to send with the AJAX call.
	@param cb_success The success callback function to execute upon success.
*/
spa.Application.prototype.Ajax.prototype.get = function(url, parameters, cb_success) {
	var self = this;

	//Start timer, if more than 1 second, show loading bar
	self._escalate(1);
	arbitrage2.ajax.get(url, parameters, function(ev) {

		//Remove loading bar
		self._escalate(0);

		if(cb_success)
			cb_success(ev);
	});
};

/**
	@description Method escalates the ajax loading bar text.
	@protected
*/
spa.Application.prototype.Ajax.prototype._escalate = function(mode) {
	var self = this;


	if(mode === undefined)
		mode = self.mode+1;
	
	switch(mode)
	{
		case 0:
			clearTimeout(self.timer);
			self.timer = null;
			self.mode  = 0;
			self.application.InfoBar.hide();;

			break;

		case 1:
			self.mode++;

			self.application.InfoBar.show("Loading, please wait...");
			clearTimeout(self.timer);
			self.timer = setTimeout(function() { self._escalate(); }, 10000);

			break;

		case 2:
			self.mode++;
			self.application.InfoBar.show("Wait a little longer...");
			clearTimeout(self.timer);
			self.timer = setTimeout(function() { self._escalate(); }, 20000);
			
			break;

		case 3:
			self.mode++;
			self.application.InfoBar.show("We are experiencing some major difficulties...");

			break;
	}
};



/**
	@description Controller base class. Defines an arbitrage2 JS controller.
	@constructor
*/
spa.Controller = function() {
	var self = this;
	self.currentAction = null;
	self.application   = arbitrage2.mvc.Application.prototype._instance;
	self.namespace     = "";
	self.actions       = { };
	self.loaded        = false;

	//Create actions
	/*for(var idx in self.Actions)
		self.actions[idx] = new self.Actions[idx](self);*/
};

/**
	@description Loads the controller into memeory.
	@param {string} url The url of the request.
*/
spa.Controller.prototype.loadAction = function(uri) {
	var self    = this;
	var action = uri.split('/').slice(-1)[0];

	//Check if action even exists
	if(!self.Actions[action])
	{
		alert("Action '" + action + "' does not exist in controller '" + self.namespace + "'.");
		return null;
	}

	//Get action
	if(!self.actions[action])
	{
		self.actions[action]            = new self.Actions[action]();
		self.actions[action].controller = self;
		self.actions[action].namespace  = self.namespace + "." + action;
	}
	
	return self.actions[action];
};

/**
	@description Controller load method called when the controller is loaded.
*/
spa.Controller.prototype.load = function () {
	var self = this;
	self.loaded = true;
};

/**
	@description Controller unload method. Called when the page is unloading.
*/
spa.Controller.prototype.unload = function() { };

/**
 @description Returns a list of arguments to use for the AJAX call to the server.
*/
spa.Controller.prototype.arguments = function() {
	return { };
};

/**
	@description Action base class.
	@constructor
*/
spa.Action = function() {
	//TODO: Add Caching Capabilities
	var self = this;
	self.initialized = false;
	self.controller  = null;
	self.namespace   = "";
};

/**
	@description Called when the action needs to be initialized for the first time.
	@param opt_data Optional data that is passed (depending if ajax structured response).
*/
spa.Action.prototype.initialize = function(opt_data) { 
	var self = this;
	self.initialized = true;
};

/**
	@description Called when the action is being removed from memory.
*/
spa.Action.prototype.free = function() { };

/**
	@description Called when the action is about to get unloaded from the page (prior to hide).
	@return Return true if we want to continue the unload process else false to stop it.
*/
spa.Action.prototype.unload = function() {
	return true;
};

/**
	@description Called when the action needs to be shown on the canvas.
*/
spa.Action.prototype.show = function() {
};

/**
	@description Called when the action needs to be hidden from view.
*/
spa.Action.prototype.hide = function() {
};

/**
	@description Removes any cache associated with action
*/
spa.Action.prototype.setStale = function() {
	var self = this;
	self.controller.application.staleAction(self);
};

/**
 @description Returns a list of arguments to use for the AJAX call to the server.
 @params opt_params Optional arguments to map.
*/
spa.Action.prototype.arguments = function(opt_params) {
	var self   = this;
	var params = { };

	if(!opt_params)
		opt_params = [ ];

	for(var i=0, key; i<self.args_list.length, i<opt_params.length, key=self.args_list[i]; i++)
		params[key] = opt_params[i];
	
	//Set self params
	self.args = params;

	return params;
};

/**
 @description Canvas class specifies drawing area for controllers.
 @param element The element to draw in.
*/
spa.Canvas = function($element) {
	var self      = this;
	self.$element = $element;
};

/**
	@description Renders the HTML onto the canvas.
	@param $data The $data to render.
*/
spa.Canvas.prototype.render = function($data) {
	var self = this;

	//TODO: When detaching do we ensure caching?
	self.$element.show();
	self.$element.children().detach();
	$data.appendTo(self.$element);
};


/**
  @description Layout controller
*/
spa.Layout = function() {
	var self = this;
	self.page = window.location.pathname;
};
