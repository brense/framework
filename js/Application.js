var Application = Backbone.Model.extend({
	
	server: null,
	
	__construct: function(server){
		this.server = server;
	},
	
	start: function(callback){
		this.server.initialize(callback);
	}
	
});