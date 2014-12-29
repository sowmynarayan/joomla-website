window.addEvent('domready', function() {
		
				

	 	var list = $$('.moduletable_menu ul.menu li a, a.mainlevel, a.sublevel');
		list.each(function(element) {
		 
			var fx = new Fx.Styles(element, {duration:550, wait:false, transition: Fx.Transitions.Expo.easeOut});
		 
			element.addEvent('mouseenter', function(){
				fx.start({
					'padding-left': 27,			        
			         'color': '#c4f400'			
				});
			});
		 
			element.addEvent('mouseleave', function(){
				fx.start({
				    'padding-left': 10,			        
			        'color': '#efefef'
				});
			});
		 
		});
		
			var list = $$('ul#mainlevel-nav li a');
		list.each(function(element) {
		 
			var fx = new Fx.Styles(element, {duration:500, wait:false, transition: Fx.Transitions.linear});
		 
			element.addEvent('mouseenter', function(){
				fx.start({							        
			         'color': '#c4f400'			
				});
			});
		 
			element.addEvent('mouseleave', function(){
				fx.start({				    		        
			        'color': '#efefef'
				});
			});
		 
		});

		
			var list = $$('.pagenav');
		list.each(function(element) {
		 
			var fx = new Fx.Styles(element, {duration:500, wait:false, transition: Fx.Transitions.linear});
		 
			element.addEvent('mouseenter', function(){
				fx.start({							        
			         'color': '#c4f400'			
				});
			});
		 
			element.addEvent('mouseleave', function(){
				fx.start({				    		        
			        'color': '#fff'
				});
			});
		 
		});

		
						var list = $$('a.latestnews, a.mostread, .blogsection');
		list.each(function(element) {
		 
			var fx = new Fx.Styles(element, {duration:350, wait:false, transition: Fx.Transitions.linear});
		 
			element.addEvent('mouseenter', function(){
				fx.start({							        
			         'color': '#c4f400'			
				});
			});
		 
			element.addEvent('mouseleave', function(){
				fx.start({				    		        
			        'color': '#efefef'
				});
			});
		 
		});
});


