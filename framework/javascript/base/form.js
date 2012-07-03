arbitrage2.provide('_arbitrage2.base.form');

/**
	@description A base class that defines all forms.
	@constructor
	@param $obj The form object to apply the class to.
	@param opt_options An optional parameter defining options associated with the Form
*/
_arbitrage2.base.form.Form = function($obj, opt_options) {
	var self     = this;
	var dopts    = $.extend(true, {}, self._defaultOptions);
	self.$form   = $obj;
	self.options = $.extend(true, dopts, opt_options);

	//If validator is set, set form
	if(self.options.validator)
		self.options.validator.setForm(self);

	//Hijack submit method
	self.$form.bind('submit.arbitrage2.base.form', function(ev) {

		//Add disabled class
		self.disable();

		//Call user cb
		var ret = self._triggerEventCallBack('onSubmit');
		if(ret === false)
		{
			self.enable();
			ev.stopPropagation();
			ev.preventDefault();
		}

		//Validate if validator is set
		if(self.options.validator && !self.options.validator.validate())
		{
			self.options.validator.display();
			self.enable();
			ev.stopPropagation();
			ev.stopImmediatePropagation();
			ev.preventDefault();

			return false;
		}
	});
};

/**
	@description Default options associated with the Form.
	@static
	@protected
*/
_arbitrage2.base.form.Form.prototype._defaultOptions = {
	validator: undefined,     //Validator Object
	events: {
		onSubmit: undefined     //Called when the submit event is to run
	}
};

/**
	@description Protected method that calls a callback event via the self.options.events object.
	@protected
	@params cb_name The callback to call.
	@params var_opt_params Optional variadic paramaters to take along.
	@return Returns undefined if no event was triggered, else returns the trigger return.
*/
_arbitrage2.base.form.Form.prototype._triggerEventCallBack = function(cb_name, var_opt_params) {
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
	@description Disables the form.
*/
_arbitrage2.base.form.Form.prototype.disable = function() {
	var self = this;
	self.$form.addClass('disabled');

	//Make elements readonly
	self.$form.find('input, select, button, textarea').attr('readonly', 'readonly');
};

/**
	@description Enabless the form.
*/
_arbitrage2.base.form.Form.prototype.enable = function() {
	var self = this;
	self.$form.removeClass('disabled');
	self.$form.find('input, select, button, textarea').removeAttr('readonly');
};

/**
	@description Clears the form.
*/
_arbitrage2.base.form.Form.prototype.clear = function() {
	var self = this;
	self.$form.find('input, radio, textarea').each(function() {
		$(this).val('');
	});
};

/**
	@description Clears the form. An alias to clear method.
*/
_arbitrage2.base.form.Form.prototype.reset = _arbitrage2.base.form.Form.prototype.clear;

/**
	@description Determines if the form is an arbitrage form or not.
*/
_arbitrage2.base.form.Form.prototype.isArbitrageForm = function() {
	var self = this;
	return self.$form.find('input#_form[type="hidden"]').size() > 0;
};

/**
	@description Iterates through each element and calls a callback function.
	@param cb The callback function to call.
*/
_arbitrage2.base.form.Form.prototype.iterateElements = function(cb) {
	var self = this;
	self.$form.find('input, textarea, select').each(cb);
};




/**
	@description Ajax Form class that sends data through ajax requests.
	@constructor
	@param $obj The form object to apply the class to.
	@param opt_options An optional parameter defining options associated with the Form
*/
_arbitrage2.base.form.AjaxForm = function($obj, opt_options) {
	var self = this;
	_arbitrage2.base.form.AjaxForm.superproto.constructor.call(self, $obj, opt_options);

	//Add ajax request
	self.$form.submit('submit.arbitrage2.base.form', function(ev) {

		function cb_success(ev) {

			if(ev.xhr.status != 200)
				self._triggerEventCallBack('onError');
			else
				self._triggerEventCallBack('onSuccess');

			self.enable();
		};

		//Assign variables
		var method = (self.$form.attr('method') || self.options.method).toLowerCase();
		var action = self.$form.attr('action') || self.options.action;
		var params = { };

		//Setup params
		self.$form.find('input, select, textarea').each(function() {
			params[$(this).attr('name')] = $(this).val();
		});

		//Run ajax
		var application = arbitrage2.mvc && arbitrage2.mvc.Application.prototype._instance;

		if(application)
			application.ajax[method].call(application.ajax, action, params, cb_success);
		else
			arbitrage2.ajax[method](action, params, cb_success);

		ev.stopPropagation();
		ev.preventDefault();
	});
	
};
arbitrage2.inherit(_arbitrage2.base.form.AjaxForm, _arbitrage2.base.form.Form);

/**
	@description Default options associated with the AjaxForm.
	@static
	@protected
*/
_arbitrage2.base.form.AjaxForm.prototype._defaultOptions = $.extend(true, {}, {
	action: undefined,
	method: 'POST',
	events: {
		onError: undefined        //Called when an ajax error happened
	}
}, _arbitrage2.base.form.Form.prototype._defaultOptions);



/**
	@description Validation methods that are defined.
	@static
*/
_arbitrage2.base.form.Validation = { };

/**
	@description Checks if an element is empty.
	@static
*/
_arbitrage2.base.form.Validation.EMPTY = function($element, opt_args) {
	if($.trim($element.val()).length == 0)
	{
		$element.data('arbitrage2.form.Validation', "Field '{{title}}' cannot be empty.");
		return false;
	}

	return true;
};

/**
	@description Checks if an element is an email address.
	@static
*/
_arbitrage2.base.form.Validation.EMAIL = function($element, opt_args) {
	var emailPattern = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/;
	if(!emailPattern.test($element.val()))
	{
		$element.data('arbitrage2.form.Validation', "Field '{{title}}' cannot be empty.");
		return false;
	}
	
	return true;
};


/**
	@description Display methods that are defined.
	@static
*/
_arbitrage2.base.form.Validation.Display = { };

/**
	@description Method to use an alert box to display errors.
	@static
*/
_arbitrage2.base.form.Validation.Display.ALERT = function() {
	var message = "";
	var $focus  = undefined;

	//Iterate through form
	this.iterateElements(function() {
		var msg  = $(this).data('arbitrage2.form.Validation');

		if(msg)
		{
			message += msg + "\n";
			if(!$focus)
				$focus = $(this);
		}
	});

	if(message.length > 0)
	{
		alert(message);
		$focus.focus();
	}
};




/**
	@description Validation Object defines the name of the element and rules to execute.
	@constructor
	@param field The parameter to validate
	@param validator The validator associated with this validation object.
*/
_arbitrage2.base.form.ValidationObject = function(field, validator) {
	var self       = this;
	self.field     = field;
	self.rules     = [ ];
	self.validator = validator;
};

/**
	@description Adds a rule to the Validation Object.
	@param rule The rule to add.
	@param opt_args Any additional arguments for the rule method.
*/
_arbitrage2.base.form.ValidationObject.prototype.addRule = function(rule, opt_args) {
	var self = this;
	self.rules.push({ rule: rule, args: opt_args });
};

/**
	@description Validates the element with each rule.
*/
_arbitrage2.base.form.ValidationObject.prototype.validate = function() {
	//TODO: Handle radio buttons
	var self = this;
	var $ele = self.getElement();
	var repl = { 'title' : $ele.attr('title') || $ele.attr('name')  };

	//Reset validation
	$ele.removeData('arbitrage2.form.Validation');

	//Iterate through rules
	for(var i=0, rule; i<self.rules.length, rule=self.rules[i]; i++)
	{
		var method = rule.rule;
		var args   = rule.args;
		if(!method($ele, args))
		{
			//Get message and replace strings
			var message = $ele.data('arbitrage2.form.Validation');
			for(var idx in repl)
				message = message.replace(new RegExp('{{' + idx + '}}', 'i'), repl[idx]);

			$ele.data('arbitrage2.form.Validation', message);

			return false;
		}
	}

	return true;
};

/**
	@description Returns the element in jQuery format that the validation object is associated with.
*/
_arbitrage2.base.form.ValidationObject.prototype.getElement = function() {
	var self = this;
	var pre  = ((self.validator.form.isArbitrageForm())? self.validator.form.$form.attr('name') + "_" : "");
	return self.validator.form.$form.find('[name="' + (pre+self.field) + '"]');
};


/**
	@description Validator class that defines rules to validate.
	@opt_options Optional options.
*/
_arbitrage2.base.form.Validator = function(opt_options) {
	var self = this;

	self.options    = $.extend(true, { }, opt_options, self._defaultOptions);
	self.rules      = { };
	self.displayers = [ ];
	self.form       = self.options.form;
};

/**
	@description Default options.
	@static
*/
_arbitrage2.base.form.Validator._defaultOptions = {
	form: undefined
};

/**
	@description Adds a rule to the validator object.
	@param field The name of the field.
	@param check The check to run.
	@param opt_args Additional arguments to pass to the rule.
*/
_arbitrage2.base.form.Validator.prototype.addCheck = function(field, rule, opt_args) {
	var self = this;
	var obj  = self.rules[field] || new _arbitrage2.base.form.ValidationObject(field, self);
	
	//Add rule
	obj.addRule(rule, opt_args);

	if(!self.rules[field])
		self.rules[field] = obj;
};

/**
	@description Adds a display type.
	@param method Display method to execute.
	@param opt_args Additional arguments to pass to the rule.
*/
_arbitrage2.base.form.Validator.prototype.addDisplay = function(method, opt_args) {
	var self = this;
	self.displayers.push({ method: method, args: opt_args });
};


/**
	@description Sets the form variable.
	@param form The arbitrage2.base.form.Form object.
*/
_arbitrage2.base.form.Validator.prototype.setForm = function(form) {
	var self  = this;
	self.form = form;
};

/**
	@description Validates the form.
*/
_arbitrage2.base.form.Validator.prototype.validate = function() {
	var self   = this;
	var err    = 0;

	//Iterate through each validation object
	for(var idx in self.rules)
	{
		var rule = self.rules[idx];
		err     += ((rule.validate())? 0 : 1);
	}

	return err == 0;
};

/**
	@description Displays the errors.
*/
_arbitrage2.base.form.Validator.prototype.display = function() {
	var self = this;

	for(var i=0, display; i<self.displayers.length, display = self.displayers[i]; i++)
		display.method.apply(self.form, display.args);
};

arbitrage2.exportSymbol('arbitrage2.form', _arbitrage2.base.form);
