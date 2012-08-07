arbitrage2.provide('arbitrage2.WYSIWYG');

/**
	@description WYSIWYG Page editor
*/
arbitrage2.WYSIWYG = function() {
	var self = this;
	self.toolbar     = new arbitrage2.WYSIWYG.ToolBar(self);
	self.$background = $('<div class="arbitrage2_wysiwyg_background"></div>');
	self.editing     = false;

	//Require CSS file
	arbitrage2.mvc.Application.prototype._instance.requireCSS('wysiwyg');

	//Create background
	self.$background.appendTo('body');

	//Events
	$('.arbitrage2_wysiwyg_toolbar_slider').click(function(ev) {
		if(self.editing)
			self.staticMode();
		else
			self.editMode();
	});
};

/**
	@description Sets the page to edit mode.
*/
arbitrage2.WYSIWYG.prototype.editMode = function() {
	var self = this;

	//Editting
	self.editing = true;

	//Show background
	//self.$background.show();

	//Find each element and set to editable
	$('*').each(function() {
		if($(this).data('arbitrage2-wysiwyg-editable') && !$(this).attr('contenteditable'))
		{
			//Set editable
			$(this).attr('contenteditable', $(this).data('arbitrage2-wysiwyg-editable')).addClass('arbitrage2_wysiwyg_editable');

			//Set icon
			var $pencil = $('<div class="arbitrage2_wysiwyg_edit_icon"></div>');
			$pencil.appendTo($(this));
		}
	});
};

/**
	@description Sets the page to static mode.
*/
arbitrage2.WYSIWYG.prototype.staticMode = function() {
	var self = this;

	//Editing
	self.editing = false;

	$('.arbitrage2_wysiwyg_editable').each(function() {
		$(this).find('.arbitrage2_wysiwyg_edit_icon').remove();
		$(this).removeAttr('contenteditable').removeClass('arbitrage2_wysiwyg_editable');
	});
};



/**
	@description Toolbar constructor for the WYSIWYG editor.
	@constructor
*/
arbitrage2.WYSIWYG.ToolBar = function(wysiwyg) {
	var self = this;
	self.wysiwyg  = wysiwyg;

	var html = '';
	html += '<div class="arbitrage2_wysiwyg_toolbar_wrapper">';
		html += '<div class="arbitrage2_wysiwyg_toolbar_buttons"></div>';
		html += '<div class="arbitrage2_wysiwyg_toolbar_slider">E</div>';
	html += '</div>';
	self.$toolbar = $(html);

	//Create toolbar and put in the DOM
	self.$toolbar.prependTo('body');
	self.$toolbar.show();
};

/**
	@description Expands the Toolbar
*/
arbitrage2.WYSIWYG.ToolBar.prototype.expand = function() {
};



