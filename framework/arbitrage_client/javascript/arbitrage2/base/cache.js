arbitrage2.provide('_arbitrage2.base.cache');

/**
	@description Cachemanager provides an easy way to cache objects via hash keys.
*/
_arbitrage2.base.cache.CacheManager = function() {
	var self = this;
	self.cache = { };
	self.count = 0;
};

/**
	@description Adds an object into cache via key.
	@param {string} key The key to use for hashing.
	@param {Object} obj The object to cache.
*/
_arbitrage2.base.cache.CacheManager.prototype.add = function(key, obj) {
	var self = this;
	key      = self.hash(key);

	if(!self.cache[key])
		self.count++;

	self.cache[key] = obj;
};

/**
	@description Gets an object from the cash
	@param {string} key The key to use for hashing.
	$return Returns null if no object is found, else returns the object.
*/
_arbitrage2.base.cache.CacheManager.prototype.get = function(key) {
	var self = this;
	var key  = self.hash(key);

	if(self.cache[key])
		return self.cache[key];
	
	return null;
};

/**
	@description Iterates through the cache calling a callback function with key and value as parameters.
	@param cb_itr The callback function to call.
*/
_arbitrage2.base.cache.CacheManager.prototype.iterate = function(cb_itr) {
	var self = this;
	for(var key in self.cache)
		cb_itr(key, self.cache[key]);
};

/**
	@description Removes an object from cache.
	@param {string} key The key to use for hashing.
*/
_arbitrage2.base.cache.CacheManager.prototype.remove = function(key) {
	var self = this;
	var key  = self.hash(key);

	if(self.cache[key])
	{
		delete self.cache[key];
		self.count--;
	}
};

/**
	@description Method removes an item from cash by hashkey.
	@param hash The hashed key.
*/
_arbitrage2.base.cache.CacheManager.prototype.removeByHash = function(hash) {
	var self = this;

	if(self.cache[hash])
	{
		delete self.cache[hash];
		self.count--;
	}
};

/**
	@description Flushes all contents of the cache
*/
_arbitrage2.base.cache.CacheManager.prototype.flush = function() {
	var self = this;
	self.cache = { };
};

/**
	@description Method hashes a string.
	@param {string} key The key to hash.
	@return Returns a hash prepresentation of key.
*/
_arbitrage2.base.cache.CacheManager.prototype.hash = function(key) {
	var self = this;
	var hash = 0;

	if(key.length == 0)
		return hash;
	
	for(var i=0; i<key.length; i++)
	{
		var chr = key.charCodeAt(i);
		hash    = ((hash<<5)-hash)+chr;
		hash    = hash & hash;
	}
	
	return hash;
};


arbitrage2.exportSymbol('arbitrage2.cache', _arbitrage2.base.cache);
