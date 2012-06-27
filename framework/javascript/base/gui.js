arbitrage2.provide('_arbitrage2.base.gui');

/**
	@description A base constructor that all GUI objects should inherit.
	@constructor
	@param $obj The object to apply the GUI effects.
	@param opts The options associated with the GUI effects.
*/
_arbitrage2.base.gui.GUIObject = function($obj, opts) {
	var self  = this;
	var dopts = $.extend(true, {}, self._defaultOptions);
	self.$element = $obj;
	self.options  = $.extend(true, dopts, opts);
};

/**
	@description Static default options associated with the object
	@static
	@protected
*/
_arbitrage2.base.gui.GUIObject.prototype._defaultOptions = { };

/**
	@description Protected method that calls a callback event via the self.options.events object.
	@protected
	@params cb_name The callback to call.
	@params var_opt_params Optional variadic paramaters to take along.
	@return Returns undefined if no event was triggered, else returns the trigger return.
*/
_arbitrage2.base.gui.GUIObject.prototype._triggerEventCallBack = function(cb_name, var_opt_params) {
	var self = this;

	//Ensure event cb_name exists
	if(self.options && self.options.events && self.options.events[cb_name])
	{
		Array.prototype.splice.call(arguments, 0, 1);
		return self.options.events[cb_name].apply(self, arguments);
	}

	return undefined;
};

/**
	@description Creates a dialog that pops up within the view port.
	@constructor
	@param $obj The object to convert to a dialog.
	@param opts The options associated with the dialog.
*/
_arbitrage2.base.gui.Dialog = function($obj, opts) {
	var self = this;
	_arbitrage2.base.gui.Dialog.superproto.constructor.call(self, $obj, opts);
};
arbitrage2.inherit(_arbitrage2.base.gui.Dialog, _arbitrage2.base.gui.GUIObject);


/**
	@description Default options associated with the Dialog.
	@static
	@protected
*/
_arbitrage2.base.gui.Dialog.prototype._defaultOptions = {
	title: '',
	positioning: 'center',
	xClose: true,
	escClose: false,
	outsideClose: false,
	purgatory: false,
	events: {
		onOpen: undefined,
		onClose: undefined
	}
};

/**
	@description Static functions called for positioning.
*/
_arbitrage2.base.gui.Dialog.prototype._positioning = {
	center: function() {
		var self   = this;
		var $frame = _arbitrage2.base.gui.Dialog.prototype.$frame;
		var $port  = _arbitrage2.base.gui.Dialog.prototype.$viewPort;
		var top    = (($port.outerHeight()/2) - ($frame.outerHeight()/2));
		var left   = (($port.outerWidth()/2) - ($frame.outerWidth()/2));

		//Normalize
		top  = ((top<0)? 0 : top);
		left = ((left<0)? 0 : left);

		//Set top/left
		$frame.css('left', left).css('top', top);
	}
};

/**
	@description Background to overlay on the screen.
	@static
*/
_arbitrage2.base.gui.Dialog.prototype.$background = undefined;

/**
	@description DIV holder that contains all Dialogs that are hidden.
	@static
*/
_arbitrage2.base.gui.Dialog.prototype.$purgatory = undefined;

/**
	@description The view port to draw the Dialog.
	@static
*/
_arbitrage2.base.gui.Dialog.prototype.$viewPort = undefined;

/**
	@description The Dialog frame that is used to put content into.
	@static
*/
_arbitrage2.base.gui.Dialog.prototype.$frame = undefined;

/**
	@description The current dialog that is opened.
	@static
*/
_arbitrage2.base.gui.Dialog.prototype._current = undefined;

/**
	@desription Creates static divs associated with Dialog GUI Controls
	@static
*/
_arbitrage2.base.gui.Dialog.prototype._createStaticElements = function() {

	//Create HTML
	_arbitrage2.base.gui.Dialog.prototype.$purgatory  = $('<div class="arbitrage2_gui_dialog_purgatory"></div>');
	_arbitrage2.base.gui.Dialog.prototype.$background = $('<div class="arbitrage2_gui_dialog_background"></div>');
	_arbitrage2.base.gui.Dialog.prototype.$viewPort   = $('<div class="arbitrage2_gui_dialog_viewport"></div>');

	//Create frame
	var html = "";

	html += '<div class="arbitrage2_gui_dialog_frame">';

		//Close button positioning
		html += '<div class="arbitrage2_gui_dialog_close"></div>';

		/* Title Area */
		html += '<div class="arbitrage2_gui_dialog_title_wrapper">';
			
			html += '<div class="arbitrage2_gui_dialog_title_left_corner"></div>';
			html += '<div class="arbitrage2_gui_dialog_title_content"></div>';
			html += '<div class="arbitrage2_gui_dialog_title_right_corner"></div>';

		html += '</div>';
		/* End Title Area */

		/* Body Area */
		html += '<div class="arbitrage2_gui_dialog_content"></div>';
		/* End Body Area */


		/* Footer Area */
		html += '<div class="arbitrage2_gui_dialog_footer_wrapper">';
			
			html += '<div class="arbitrage2_gui_dialog_footer_left_corner"></div>';
			html += '<div class="arbitrage2_gui_dialog_footer_content"></div>';
			html += '<div class="arbitrage2_gui_dialog_footer_right_corner"></div>';

		html += '</div>';
		/* End Footer Area */

	html += '</div>';

	_arbitrage2.base.gui.Dialog.prototype.$frame = $(html);

	//Attach all to body
	_arbitrage2.base.gui.Dialog.prototype.$purgatory.hide().appendTo('body');
	_arbitrage2.base.gui.Dialog.prototype.$background.hide().appendTo('body');
	_arbitrage2.base.gui.Dialog.prototype.$viewPort.hide().appendTo('body');
	_arbitrage2.base.gui.Dialog.prototype.$frame.appendTo(_arbitrage2.base.gui.Dialog.prototype.$viewPort);

	//Static Events
	$(window).bind('resize.arbitrage2_gui_dialog', function() { 
		var dlg = _arbitrage2.base.gui.Dialog.prototype._current;
		if(dlg)
			_arbitrage2.base.gui.Dialog.prototype._positioning[dlg.options.positioning].apply(dlg);
	});

	//Key events
	$(window).bind('keydown.arbitrage2_gui_dialog', function(ev) {
		var dlg = _arbitrage2.base.gui.Dialog.prototype._current;
		if(dlg && dlg.options.escClose && ev.keyCode==27)
			dlg.close();
	});

	//Outside close
	_arbitrage2.base.gui.Dialog.prototype.$viewPort.bind('click.arbitrage2_gui_dialog', function(ev) {
		var dlg = _arbitrage2.base.gui.Dialog.prototype._current;
		if(dlg && dlg.options.outsideClose && $(ev.target).get(0) == $(this).get(0))
			dlg.close();
	});

	//X close
	_arbitrage2.base.gui.Dialog.prototype.$frame.find('div.arbitrage2_gui_dialog_close').bind('click.arbitrage2_gui_dialog', function() {
		var dlg = _arbitrage2.base.gui.Dialog.prototype._current;
		if(dlg)
			dlg.close();
	});
};

/**
	@description Opens the dialog in the view port.
*/
_arbitrage2.base.gui.Dialog.prototype.open = function() {
	var self = this;

	//Check to see if a current Dialog is opened, if so close it
	if(_arbitrage2.base.gui.Dialog.prototype._current)
		_arbitrage2.base.gui.Dialog.prototype._current.close();
	
	//Set current
	_arbitrage2.base.gui.Dialog.prototype._current = self;

	//Show background and viewport
	_arbitrage2.base.gui.Dialog.prototype.$background.show();

	//Set parent for later usage
	self.$element.data('previous-parent', self.$element.parent());

	//Check for xclose
	if(self.options.xClose)
		 _arbitrage2.base.gui.Dialog.prototype.$frame.find('div.arbitrage2_gui_dialog_close').show();
	 else
		 _arbitrage2.base.gui.Dialog.prototype.$frame.find('div.arbitrage2_gui_dialog_close').hide();

	//Constructor view port
	self.$element.show();
	self.$element.appendTo(_arbitrage2.base.gui.Dialog.prototype.$frame.find('.arbitrage2_gui_dialog_content'));

	//Set title
	_arbitrage2.base.gui.Dialog.prototype.$frame.find('div.arbitrage2_gui_dialog_title_content').html(self.options.title);

	//Show viewport
	_arbitrage2.base.gui.Dialog.prototype.$viewPort.show();

	//Position the Frame!!
	_arbitrage2.base.gui.Dialog.prototype._positioning[self.options.positioning].apply(self);

	//Call onOpen
	self._triggerEventCallBack('onOpen');
};

/**
	@description Closes the dialog in the view port.
*/
_arbitrage2.base.gui.Dialog.prototype.close = function() {
	var self = this;

	//Hide
	_arbitrage2.base.gui.Dialog.prototype.$background.hide();
	_arbitrage2.base.gui.Dialog.prototype.$viewPort.hide();

	//Close the dialog an put it into purgatory!
	if(self.options.purgatory)
		self.$element.appendTo(_arbitrage2.base.gui.Dialog.prototype.$purgatory);
	else
	{
		//Append back to the place it came from
		self.$element.appendTo(self.$element.data('previous-parent'));
		self.$element.removeData('previous-parent');
	}

	//Set current to undefined
	_arbitrage2.base.gui.Dialog.prototype._current = undefined;

	//Call onClose
	self._triggerEventCallBack('onClose');
};

//Create static objects
(function() {
	$(document).ready(function() {
		_arbitrage2.base.gui.Dialog.prototype._createStaticElements();
	});
})();
/** End Dialog Class **/


/** InforBar Class **/

/**
	@description InfoBar class. Shows an info bar with text and certain info bar levets, error, warning, info
	@constructor
	@param $obj The object to convert to a InfoBar.
	@param opts The options associated with the InfoBar.
*/
_arbitrage2.base.gui.InfoBar = function($obj, opts) {
	var self   = this;
	self.timer = null;
	_arbitrage2.base.gui.InfoBar.superproto.constructor.call(self, $obj, opts);

	//Now create the actual contents
	$('<div class="arbitrage2_gui_infobar_frame"></div>').appendTo($obj);
	$('<div class="arbitrage2_gui_infobar_content"</div>').appendTo($obj.find('.arbitrage2_gui_infobar_frame'))

};
arbitrage2.inherit(_arbitrage2.base.gui.InfoBar, _arbitrage2.base.gui.GUIObject);

/**
	@description Default options associated with the InfoBar.
	@static
	@protected
*/
_arbitrage2.base.gui.InfoBar.prototype._defaultOptions = {
	animation: 'none',
	events: {
		onShow: undefined,
		onHide: undefined
	}
};

/**
	@description Animation types for showing and hiding the info bar element.
*/
_arbitrage2.base.gui.InfoBar.prototype._animate = {
	none: {
		show: function(cb) {
			var self = this;
			self.$element.show();
			if(cb)
				cb();
		},
		hide: function(cb) {
			var self = this;
			self.$element.hide();
			if(cb)
				cb();
		}
	},

	fade: {
		show: function(cb) {
			var self = this;
			self.$element.fadeIn('slow', cb);
		},
		hide: function(cb) {
			var self = this;
			self.$element.fadeOut('slow', cb);
		}
	}
};

/**
	@description Shows the info bar.
	@param txt The text to write out.
	$param opt_level The level, error, warning, info. Default is info.
	@param opt_time The timeout value for hiding the InfoBar. Default is 0.
*/
_arbitrage2.base.gui.InfoBar.prototype.show = function(txt, opt_level, opt_time) {
	var self     = this;
	var $content = self.$element.find('.arbitrage2_gui_infobar_content');

	//Grab opt level
	if(opt_level === undefined)
		opt_level = "info";
	
	//Reset class
	$content.get(0).className="arbitrage2_gui_infobar_content " + opt_level;

	//Add text and show
	$content.html(decodeURIComponent(txt));
	self._animate[self.options.animation].show.call(self);

	//Setup timer
	if(opt_time !== undefined)
	{
		if(self.timer)
			clearTimeout(self.timer);

		self.timer = setTimeout(function() { self.hide(); }, opt_time*1000);
	}
};

/**
	@description Hides the InfoBar
*/
_arbitrage2.base.gui.InfoBar.prototype.hide = function() {
	var self = this;

	if(self.timer)
	{
		clearTimeout(self.timer);
		self.timer = null;
	}

	//Hide
	self._animate[self.options.animation].hide.call(self, function() {	self.$element.find('.arbitrage2_gui_infobar_content').html(''); });
};
/** End InfoBar Class **/

arbitrage2.exportSymbol('arbitrage2.gui', _arbitrage2.base.gui);
