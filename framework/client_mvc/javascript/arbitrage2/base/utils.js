arbitrage2.provide('_arbitrage2.base.utils');

_arbitrage2.base.utils.ImplodeParams = function(str) {
	str = str.replace(/^(#|\?)/, '');
	str = str.split('&');

	var ret = { };
	for(var i=0, entry; i<str.length, entry=str[i]; i++)
	{
		var split = entry.split('=');
		var key   = split[0] || entry || '';
		var val   = split[1] || undefined;

		//set return
		ret[key] = val;
	}
	
	return ret;
};

/**
  @description Method parses the url.
  @url The string url to parse.
  @return Returns an object with possible keys: { href, protocol, host, hostname, pathname, port, hash }
*/
_arbitrage2.base.utils.parseURL = function(url) {
	var ret = {
		href: url,
		protocol: '',
		hostname: '',
		host: '', 
		port: '',
		pathname: '',
		query: '',
		hash: ''
	};

	//Parse the protocol
	ret.protocol = url.replace(/^(\w+):\/\/.*$/, '$1');
	url = url.replace(ret.protocol + "://", '');

	//parse out host
	ret.hostname = url.replace(/^([\w\._-]+)(\/|:)?.*$/, '$1');
	ret.host     = ret.hostname;
	url = url.replace(ret.hostname, '');

	//Parse out port if exists
	if(url.match(/^:\d+/))
	{
		ret.port = url.replace(/^:(\d+).*$/, '$1');
		url = url.replace(':' + ret.port, '');
	}

	//parse out path
	ret.pathname = url.replace(/^(\/?[^\?#]+)(\?|$)?.*$/, '$1');
	url = url.replace(ret.pathname, '');

	//Parse out query string
	if(url.match(/^\?/))
	{
		ret.query = url.replace(/^\?([^#]+).*$/, '$1');
		url = url.replace('?' + ret.query, '');
	}

	//Parse out hash string
	if(url.match(/^#/))
		ret.hash = url.replace(/^#(.*)$/, '$1');

	return ret;
};

arbitrage2.exportSymbol('arbitrage2.utils', _arbitrage2.base.utils);
