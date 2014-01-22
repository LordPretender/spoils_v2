$(document).ready(function() {

	//Call the Drop-down Menu
	$('ul.sf-menu').superfish(); 

	//Call the Testimonial Slider
	$('#testimonial-slider').cycle({
	
		fx: 'fade',					//Choose from different transition effects. See Documentation for more info.
		speed: 1000,				//Speed in milliseconds for each slide.
		sync: 1,					//States whether or not the transitions happen at the same time or not. 0 = False, 1 = True.
		slideResize: true,			//-----------------------------
		containerResize: false,		//THESE FOUR FIELDS SHOULD NOT BE ALTERED IN ORDER
		width: '100%',				//FOR THE SLIDER TO BE RESPONSIVE.
		fit: 1,						//-----------------------------
		next:   '#next-test', 		//Anchor tags for the navigation buttons
		prev:   '#prev-test' 		//
		
	});
	
	//Twitter Feed Setup
	$(".tweet").tweet({
	
		username: "yourTwitterUsername",		//Your Twitter Username
		join_text: "auto",
		count: 2,								//How many tweets to show in the feed
		loading_text: "loading tweets..."		//The text displayed while the tweets are loading
		
	});
	
	//Flickr Feed Setup
	$('#flickr').jflickrfeed({
	
		limit: 6,								//How many images to display (I think the limit is 20)
		qstrings: {
			id: 'putYourIDNumberHere'			//This is your unique Flickr account reference. See documentation.
		},
		itemTemplate: '<li>'+
			'<a rel="colorbox" href="{{image}}" title="{{title}}">' +
				'<img src="{{image_s}}" alt="{{title}}" />' +
			'</a>' +
		'</li>'
		}, function(data) {
			$('#flickr a').colorbox();			//Opens Flickr image in colorbox
	});
	
	//This is a stylesheet switcher for use in the live preview
	$(function()
		{
			// Call stylesheet init so that all stylesheet changing functions 
			// will work.
			$.stylesheetInit();
			
			// This code loops through the stylesheets when you click the link with 
			// an ID of "toggler" below.
			$('#toggler').bind(
				'click',
				function(e)
				{
					$.stylesheetToggle();
					return false;
				}
			);
			
			// When one of the styleswitch links is clicked then switch the stylesheet to
			// the one matching the value of that links rel attribute.
			$('.styleswitch').bind(
				'click',
				function(e)
				{
					$.stylesheetSwitch(this.getAttribute('rel'));
					return false;
				}
			);
		}
	);
	
});