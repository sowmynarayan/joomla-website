window.addEvent('domready', function() {
		
				

	 	var list = $$('.moduletable_menu ul.menu li a');
		list.each(function(element) {
		 
			var fx = new Fx.Styles(element, {duration:550, wait:false, transition: Fx.Transitions.Expo.easeOut});
		 
			element.addEvent('mouseenter', function(){
				fx.start({
					'padding-left': 20,			        
			         'color': '#ffffff'			
				});
			});
		 
			element.addEvent('mouseleave', function(){
				fx.start({
				    'padding-left': 10,			        
			        'color': '#6EBCFF'
				});
			});
		 
		});
		
			var list = $$('ul#mainlevel-nav li a, .button, .pagenav');
		list.each(function(element) {
		 
			var fx = new Fx.Styles(element, {duration:500, wait:false, transition: Fx.Transitions.linear});
		 
			element.addEvent('mouseenter', function(){
				fx.start({							        
			         'color': '#FFAE00'			
				});
			});
		 
			element.addEvent('mouseleave', function(){
				fx.start({				    		        
			        'color': '#6EBCFF'
				});
			});
		 
		});

		
						var list = $$('a.latestnews, a.mostread, .blogsection');
		list.each(function(element) {
		 
			var fx = new Fx.Styles(element, {duration:350, wait:false, transition: Fx.Transitions.linear});
		 
			element.addEvent('mouseenter', function(){
				fx.start({							        
			         'color': '#FFAE00'			
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
			         'color': '#ff0000'			
				});
			});
		 
			element.addEvent('mouseleave', function(){
				fx.start({				    		        
			        'color': '#fff'
				});
			});
		 
		});
		
							var list = $$('.inputbox');
		list.each(function(element) {
		 
			var fx = new Fx.Styles(element, {duration:400, wait:false, transition: Fx.Transitions.linear});
		 
			element.addEvent('mouseenter', function(){
				fx.start({							        
			         'background-color': '#666666'			
				});
			});
		 
			element.addEvent('mouseleave', function(){
				fx.start({				    		        
			        'background-color': '#121212'
				});
			});
		 
		});
	
		
});


