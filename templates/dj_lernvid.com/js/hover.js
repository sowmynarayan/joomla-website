window.addEvent('domready', function() {
		
				

	 	var list = $$('.module_menu ul.menu li a, a.mainlevel, a.sublevel');
		list.each(function(element) {
		 
			var fx = new Fx.Styles(element, {duration:200, wait:false, transition: Fx.Transitions.Expo.easeOut});
		 
			element.addEvent('mouseenter', function(){
				fx.start({
					'font-size': 14,			        
			         'color': '#326bdc'			
				});
			});
		 
			element.addEvent('mouseleave', function(){
				fx.start({
				    'font-size': 11,			        
			        'color': '#68c1ff'
				});
			});
		 
		});
		
			var list = $$('ul#mainlevel-nav li a, .pagenav');
		list.each(function(element) {
		 
			var fx = new Fx.Styles(element, {duration:500, wait:false, transition: Fx.Transitions.linear});
		 
			element.addEvent('mouseenter', function(){
				fx.start({							        
			         'color': '#326bdc'			
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
			         'color': '#326bdc'			
				});
			});
		 
			element.addEvent('mouseleave', function(){
				fx.start({				    		        
			        'color': '#fff'
				});
			});
		 
		});
							var list = $$('.readon');
		list.each(function(element) {
		 
			var fx = new Fx.Styles(element, {duration:350, wait:false, transition: Fx.Transitions.linear});
		 
			element.addEvent('mouseenter', function(){
				fx.start({							        
			         'color': '#326bdc'			
				});
			});
		 
			element.addEvent('mouseleave', function(){
				fx.start({				    		        
			        'color': '#fff'
				});
			});
		 
		});
});


