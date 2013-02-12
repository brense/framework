var AbstractView = Backbone.Model.extend({
	
	selector: null,
	append: false,
	template: null,
	html: null,
	params: {},
	
	__construct: function(){
		this.getTemplate();
	},
	
	render: function(params){
		var view = this;
		view.params = params;
		if(view.html != null){
			view.parseTemplate();
		} else {
			view.getTemplate(function(){
				view.parseTemplate();
			});
		}
	},
	
	getTemplate: function(callback){
		var view = this;
		$.get('templates/' + view.template + '.html', function(html){
			view.html = html;
			if(callback != null){
				callback();
			}
		});
	},
	
	parseTemplate: function(){
		var view = this;
		$.each(view.params, function(key, val){
			if(!(val instanceof Array)){
				view.html = view.html.replace('${'+ key + '}', val);
			} else {
				// TODO: handle array values
			}
		});
		if(view.append){
			view.selector.append(view.html);
		} else {
			view.selector.replaceWith(view.html);
		}
	}
	
});