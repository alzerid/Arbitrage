/**
	@author Eric M. Janik
	@description Arbitrage2 module that is capable of registering data to a bus. This allows different javascript components/controllers
	the ability to listen for changes on published data points.  The pattern is simliar to the dbus linux pattern.
*/

//TODO: Send event type to event listeners
arbitrage2.provide('_arbitrage2.base.dbus');

/**
	@description The actual bus contents.
*/
_arbitrage2.base.dbus._bus = { };

/**
	@description DBUS object that associates with the data point. Has propertys ot set, get, etc...
	@constructor
	@param namespace The namespace to associate the data point to.
	@param data_point The data point object.
*/
_arbitrage2.base.dbus.DBusObject = function(namespace, data_point) {
	this.namespace = namespace
	this.data      = data_point;
	//this.idx       = '';
	this.listeners = [ ];
};

/**
	@description Set the value for the dbus object.
	@param val The value to set the dbus object to.
	@param opt_trigger Optional parameter to trigger liseteners. Default is true.
*/
_arbitrage2.base.dbus.DBusObject.prototype.set = function(val, opt_trigger) {
	var self = this;
	opt_trigger = ((opt_trigger === undefined)? true : opt_trigger);

	self.data = val;

	//Trigger
	if(opt_trigger)
		self.trigger();
};

/**
	@description Adds an event listener wich is called when the data object changes.
	@param cb_func The callback function to fire when the data point changes value.
*/
_arbitrage2.base.dbus.DBusObject.prototype.addListener = function(cb_func) {
	var self = this;
	self.listeners.push(cb_func);
};

/**
	@description Removes an event listener from the DBusOjbect.
	@param cb_func The callback function to remove.
*/
_arbitrage2.base.dbus.DBusObject.prototype.removeListener = function(cb_func) {
	var self = this;
	for(var i=0, listener; i<self.listeners.length, listener=self.listeners[i]; i++)
	{
		if(listener == cb_func)
		{
			self.listeners.splice(i, 1);
			return;
		}
	}
};

/**
	@description Triggers all listeners.
*/
_arbitrage2.base.dbus.DBusObject.prototype.trigger = function() {
	var self = this;

	for(var i=0, cb_func; i<self.listeners, cb_func=self.listeners[i]; i++)
		cb_func.call(self);
};

/**
	@description Publishes a datapoint to the bus.
	@param namespace The DBUS formatted namespace of the data point.
	@param data_point The data point object to associate the namespace with.
	@return Returns the new DBus object that wraps itself around the data point.
*/
_arbitrage2.base.dbus.publish = function(namespace, data_point) {

	//TODO: Check if datapoint by the namespace exists
	
	var obj = new _arbitrage2.base.dbus.DBusObject(namespace, data_point);

	//Add to dbus
	_arbitrage2.base.dbus._bus[namespace] = obj;

	return obj;
};

/**
	@description Unpublishes a datapoint from the bus.
	@param namespace The DBUS formatted namespace of the data point.
*/
_arbitrage2.base.dbus.unpublish = function(namespace) {
	if(_arbitrage2.base.dbus._bus[namespace])
		delete _arbitrage2.base.dbus._bus[namespace];
};

/**
	@description Returns the data point associated with the namespace.
	@return Returns a DBusObject or null.
*/
_arbitrage2.base.dbus.get = function(namespace) {
	return (_arbitrage2.base.dbus._bus[namespace] || null);
}

/**
	@description Adds a listener to the specific namespace data point.
	@param namespace The namespace associated with the data point to listen for.
	@param cb_func The callback function to fire when the data point value changes.
	@return Returns the DBusObject or null.
*/
_arbitrage2.base.dbus.listen = function(namespace, cb_func) {
	var self = this;
	var obj  = null;

	obj = _arbitrage2.base.dbus._bus[namespace];
	if(!obj)
		return null;
	
	obj.addListener(cb_func);
	return obj;
};

/**
	@description Removes a listner function for that data point.
	@param namespace The namespace associated with the data point.
	@param cb_func The callback function to remove.
*/
_arbitrage2.base.dbus.unlisten = function(namespace, cb_func) {
	var self = this;
	var obj  = null;

	obj = _arbitrage2.base.dbus._bus[namespace];
	if(!obj)
		return;

	obj.removeListener(cb_Func);
};



/**
	@description Grabs a DBusObject associated with the namespace and returns it.
*/

arbitrage2.exportSymbol('arbitrage2.dbus', _arbitrage2.base.dbus);
