arbitrage2.provide('_arbitrage2.base.ajax');

(function() {

	function _normalizeParameters(params) {
		if(params === undefined)
			params = "";
		else if(typeof(params) === "object")
			params = "&" + $.param(params);
		else
			params = "&" + params;

		return "_ajax=1" + params;
	};

	function _ajax(url, type, params, cb_success) {

		//Hijack callbasks
		var _cbs = {
			error: function(jqXHR, textStatus, errorThrown) {
				var ev = { xhr: jqXHR, status: textStatus, error: errorThrown, data: undefined };
				alert('Ajax Error!');
				console.error(ev);
			},
			success: function(data, textStatus, jqXHR) {
				var ev = { xhr: jqXHR, status: textStatus, error: undefined, data: data };
				if(cb_success)
					cb_success(ev);
			}
		};

		//setup options
		var opts = {
			url: url,
			type: type.toUpperCase(),
			data: params,
			success: _cbs.success,
			error: _cbs.error
		};

		//Call ajax request
		$.ajax(opts)
	};

	/**
		@description Does an AJAX Post call
		@param url The URL to query.
		@param parameters The parameters to send with the AJAX call.
		@param cb_success The success callback function to execute upon success.
	*/
	_arbitrage2.base.ajax.post = function(url, paramaters, cb_success) { 
		//Normalize parameters
		parameters = _normalizeParameters(paramaters);

		//Call the actual ajax call
		_ajax(url, 'POST', parameters, cb_success);
	};

	/**
		@description Does an AJAX Get call
		@param url The URL to query.
		@param parameters The parameters to send with the AJAX call.
		@param cb_success The success callback function to execute upon success.
	*/
	_arbitrage2.base.ajax.get = function(url, paramaters, cb_success) { 
		//Normalize parameters
		parameters = _normalizeParameters(paramaters);

		//Call the actual ajax call
		_ajax(url, 'GET', parameters, cb_success);
	};

})();

arbitrage2.exportSymbol('arbitrage2.ajax', _arbitrage2.base.ajax);
