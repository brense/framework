var Server = Backbone.Model.extend({
	
	baseUrl: null,
	responseType: null,
	path: null,
	
	__construct: function(baseUrl, responseType){
		if(baseUrl == null){
			baseUrl = $('head base').attr('href');
		}
		this.baseUrl = baseUrl;
		if(responseType == null){
			responseType = 'json';
		}
		this.responseType = responseType;
		this.path = (window.location.protocol + '//' + window.location.host + window.location.pathname).replace(this.baseUrl, '');
	},
	
	initialize: function(callback){
		this.requestResource('Application/init?path=' + encodeURIComponent(this.path), callback);
	},
	
	requestResource: function(resource, callback){
		$.get(this.baseUrl + 'do/' + resource, callback, this.responseType);
	}
	
});