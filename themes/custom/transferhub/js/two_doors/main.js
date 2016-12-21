jQuery(function ($) {

    'use strict';

    // ----------------------------------------------
    // Table index
    // ----------------------------------------------

    /*-----------------------------------------------
    # Preset
    # Dropdown Menu Animation
    # Navigation Fixed
    # Search
	# Parallax Scrolling
    # Slider Height
    # Active mixitup
    # smoothScroll
    # Pretty Photo
    # Single Portfolio
    # Close Single Portfolio
    # Timer
    # Google Map Customization
    -------------------------------------------------*/



	// ----------------------------------------------
    // # Preset
    // ----------------------------------------------
	/*
	(function () {
    	$('body').append('<div class="preset-box"></div>');
    	$('.preset-box').append('<div class="preset-btn"><i class="fa fa-cog fa-spin"></i> Colors</div>');
		$('.preset-box').append('<div class="preset">\
			<div style="background:#FDC916;" data-src="FDC916"></div>\
			<div style="background:#13bbb4;" data-src="13bbb4"></div>\
			<div style="background:#f3753b;" data-src="f3753b"></div>\
			<div style="background:#ec175c;" data-src="ec175c"></div>\
			<div style="background:#37b878;" data-src="37b878"></div>\
			<div style="background:#26ace2;" data-src="26ace2"></div>\
		</div>');
		$('.preset-btn').click(function() {
			$('.preset').toggleClass('preset-show');	
		});
		$('.preset div').click(function() {
			var col=$(this).attr('data-src');
			$('.preset-css').remove();
			$('body').append('<div class="preset-css"><style>\
			.contact-wrap .btn.btn-primary::before, #comment-form .btn.btn-primary::before, .contact-wrap .btn.btn-primary::after, #comment-form .btn.btn-primary::after, .shop-carousel .price-tag, .single-table.featured-table h2, .single-table.featured-table .btn.btn-primary, .single-table:hover .btn.btn-primary:hover,\
			.widget.tag-cloud ul li a:hover, .pricing-table-one .price, .pricing-table-one .single-table h2, .pricing-table-one .single-table .btn.btn-primary, .member-filter ul li a.border::after, .powered-by::before, .powered-by::after, #feature-in .overlay-bg, #video-promotion .overlay-bg, .behind-app-img .overlay::before, .behind-app-img .overlay::after, .payment-methods .overlay-bg, .add_buttons button:hover, #onepage .contact-content .btn.btn-primary, .col-sm-3:hover .position-title:after, #onepage #fun-fact .overlay-bg, #onepage #shop .overlay-bg, #promotion-two .overlay-bg, .navbar-brand, .navbar-header::before, .section-title h2::after, #navigation .navbar-right .dropdown-menu li a:hover, #navigation .navbar-right .dropdown-menu li a:active, #promotion.parallax-section .overlay-bg\
				{background-color:#'+col+';}\
			#navigation .navbar-right .dropdown-menu-large li a:hover, #navigation .navbar-right .dropdown-menu-large li a:active, #about-video .fa-play-circle-o,#fun-fact .funs i,.slide-logo .fa-angle-down, #navigation .navbar-right li a.active,\
			.pricing-table-one .single-table.featured-table .price, .pricing-table-one .single-table.featured-table .btn.btn-primary, .team-image .overlay li a:hover, .member-filter ul li a:hover, .member-filter ul li a.active, #cat-menu ul.navbar-nav li a.active, #cat-menu ul.navbar-nav li a:hover, .read-more:hover,.entry-title a:hover,.entry-meta span a:hover, .product-image .add-to-cart .fa-shopping-cart, .add-to-cart,\
			.product-count .fa-shopping-cart, .widget-area .widget_search button.btn:hover i, .project-filter .active, #countdown ul li p, .page-breadcrumb ul li, #apps-footer a:hover, #get-touch .contact-social li a:hover, #landing-carousel .carousel-caption h3, #landing-carousel .carousel-caption .fa-angle-right, .lan-features ul li:hover h3, .lan-features ul li:hover i, .add_buttons button i, .product-info .rating span, .read-more:hover, .entry-title a:hover, .carousel-caption .post-content a:hover, .entry-meta span a:hover, .pagination > li > a:hover, .pagination > li > a.active, .pagination > li > span:hover, .pagination > li > a:focus, .pagination > li > span:focus, .widget ul li a:hover, .blog1 .post .post-content .read-more:hover, .blog1 .post h2.entry-title a:hover, .blog1 .regu-news .post .entry-meta ul li a:hover, #onepage .business-time span, #onepage .contact-content i, #onepage .contact-content a:hover, #navigation .navbar-right li a:hover, #navigation .navbar-right li.active > a, .bottom-socials ul li a:hover, #navigation .navbar-right li i:hover, #navigation .topbar-icons span i:hover, .project .overlay a, a:focus, a:hover\
				{color:#'+col+';}\
			.widget-area .widget_search .form-control:focus, #get-touch .btn.btn-primary:hover, #get-touch .form-control:focus, #apps-footer, #newsletter input:focus, #quote-carousel .item img,#screenshots-carousel .carousel-indicators .active, .add_buttons button:hover, #cat-menu ul.navbar-nav li a.active, #cat-menu ul.navbar-nav li a:hover, .widget ul li a:hover, .form-control:focus, #onepage .contact-content .form-control:focus \
				{border-color: #'+col+';}\
			#service .section-title h2::before\
				{background-image: url("images/home/icons/service-title-icon-'+col+'.png");}\
			#projects .section-title h2::before\
				{background-image: url("images/home/icons/project-title-icon-'+col+'.png");}\
			#blog .section-title h2::before\
				{background-image: url("images/home/icons/blog-title-icon-'+col+'.png");}\
			#clients .section-title h2::before\
				{background-image: url("images/home/icons/clients-title-icon-'+col+'.png");}\
			.quote-content::before\
				{background: transparent url("images/home/bg/comma-'+col+'.png");}\
			#onepage #contact-us .section-title h2::before\
    			{background-image: url("images/home/icons/contact-title-icon-'+col+'.png");}\
    		#pricing-tables .section-title h2::before\
    			{background-image: url("images/home/icons/pricing-title-icon-'+col+'.png");}\
    		#onepage #about-us .section-title h2::before\
    			{background-image: url("images/home/icons/about-title-icon-'+col+'.png");}\
			</style></div>');
			
			$('.slide-logo img').attr('src','images/home/bg/slide-logo-'+col+'.png');
			$('.coming-soon-content img').attr('src','images/home/bg/slide-logo-'+col+'.png');
			
			$('img[src^="images/home/icons/"]').each(function() {
			   var t=$(this).attr('src');
			   if(t.charAt(t.length-11)=='-') t=t.slice(0,t.length-11)+".png";
			   $(this).attr('src', t.replace(".png", "-"+col+".png"));
			});
		});
	}());
	*/
	// ----------------------------------------------
    // # Dropdown Menu Animation 
    // ----------------------------------------------
	
	(function () {
		$('.dropdown').on('show.bs.dropdown', function(e){
			$(this).find('.dropdown-menu').first().stop(true, true).slideDown(300);
		});

		$('.dropdown').on('hide.bs.dropdown', function(e){
			$(this).find('.dropdown-menu').first().stop(true, true).slideUp(300);
		});

	}());

	// ----------------------------------------------
    // # Navigation fixed  
    // ----------------------------------------------	
	
	(function () {
		$(window).on('scroll', function(){
			if( $(window).scrollTop()>650 ){
				$('#index2 #navigation .navbar-static-top').addClass('navbar-fixed-top');
			} else {
				$('#index2 #navigation .navbar-static-top').removeClass('navbar-fixed-top');
			};
			if( $(window).scrollTop()>0 ){
				$('#onepage #navigation .navbar-static-top').addClass('navbar-fixed-top');
			} else {
				$('#onepage #navigation .navbar-static-top').removeClass('navbar-fixed-top');
			};
			if( $(window).scrollTop()>0 ){
				$('#landing #navigation .navbar-static-top').addClass('navbar-fixed-top');
			} else {
				$('#landing #navigation .navbar-static-top').removeClass('navbar-fixed-top');
			}
		});
	}());
	
		
	
	// ----------------------------------------------
    // # Search
    // ----------------------------------------------

    (function () {

        $('.fa-search').on('click', function() {
            $('.search').fadeIn(500, function() {
              $(this).toggleClass('search-toggle');
            });     
        });

        $('.search-close').on('click', function() {
            $('.search').fadeOut(500, function() {
                $(this).removeClass('search-toggle');
            }); 
        });

    }());
	
	// ----------------------------------------------
    // # Parallax Scrolling
    // ----------------------------------------------
    
    (function () {
		
        function parallaxInit() {       
           $("#promotion").parallax("50%", 0.3);
			$("#twitter").parallax("50%", 0.3);
			$("#promotion-two").parallax("50%", 0.3);
			$("#about-video").parallax("50%", 0.3);
			$("#onepage #fun-fact").parallax("50%", 0.3);
			$("#onepage #shop").parallax("50%", 0.3);
			$("#landing #video-promotion").parallax("50%", 0.3);
			$("#landing #feature-in").parallax("50%", 0.3);
        }   
        parallaxInit();

    }());
	
	
		
	 // ----------------------------------------------
    // # Pretty Photo
    // ----------------------------------------------

    (function () {

        $("a[data-gallery^='prettyPhoto']").prettyPhoto({
        	social_tools: false
        });

    }());
	
	
	// ----------------------------------------------
    // # Team filter
    // ----------------------------------------------
	
	(function () {
		$(window).load(function(){
		   var $portfolio_selectors = $('.member-filter >ul>li>a');
			var $portfolio = $('.all-members');
			$portfolio.isotope({
				itemSelector : '.member',
				layoutMode : 'fitRows'
			});
			
			$portfolio_selectors.on('click', function(){
				$portfolio_selectors.removeClass('active');
				$(this).addClass('active');
				var selector = $(this).attr('data-filter');
				$portfolio.isotope({ filter: selector });
				return false;
			});
		});
    }());
	
	// ----------------------------------------------
    // # Portfolio filter
    // ----------------------------------------------
	
	(function () {
		$(window).load(function(){
		  var $portfolio_selectors = $('.project-filter >ul>li>a');
			var $portfolio = $('.all-products');
			$portfolio.isotope({
				itemSelector : '.filterable-product',
				layoutMode : 'fitRows'
			});
			
			$portfolio_selectors.on('click', function(){
				$portfolio_selectors.removeClass('active');
				$(this).addClass('active');
				var selector = $(this).attr('data-filter');
				$portfolio.isotope({ filter: selector });
				return false;
			});
		});
    }());
	
	
	// ----------------------------------------------
    // # Fun Fact Timer
    // ----------------------------------------------
	(function () {
		$('#fun-fact').bind('inview', function(event, visible, visiblePartX, visiblePartY) {
			if (visible) {
				$(this).find('.timer').each(function () {
					var $this = $(this);
					$({ Counter: 0 }).animate({ Counter: $this.text() }, {
						duration: 2000,
						easing: 'swing',
						step: function () {
							$this.text(Math.ceil(this.Counter));
						}
					});
				});
				$(this).unbind('inview');
			}
		});
	
	}());
	
	// ----------------------------------------------
    // # SmoothScroll
    // ----------------------------------------------

    (function () {

        smoothScroll.init();

    }());
	
	// ----------------------------------------------
    // # Clients Carousel Timing
    // ----------------------------------------------
	(function () {
	
		$('#client-carousel').carousel({
		  interval: 4000
		})
    }());	
	
    // ----------------------------------------------
    // # Google Map Customization
    // ----------------------------------------------
	
	(function(){

		var map;

		try {

			map = new GMaps({
				el: '#gmap',
				lat: 32.3305419,
				lng: 34.8743185,
				scrollwheel:false,
				zoom: 13,
				zoomControl : true,
				panControl : false,
				streetViewControl : false,
				mapTypeControl: false,
				overviewMapControl: false,
				clickable: false
			});


			var styles = [

			{
				"featureType": "road",
				"stylers": [
				{ "color": "#A5A5A5" }
				]
			},{
				"featureType": "water",
				"stylers": [
				{ "color": "#C9C9C9" }
				]
			},{
				"featureType": "landscape",
				"stylers": [
				{ "color": "#E9E7E3" }
				]
			},{
				"elementType": "labels.text.fill",
				"stylers": [
				{ "color": "#d3cfcf" }
				]
			},{
				"featureType": "poi",
				"stylers": [
				{ "color": "#CCCCCC" }
				]
			},{
				"elementType": "labels.text",
				"stylers": [
				{ "saturation": 1 },
				{ "weight": 0.1 },
				{ "color": "#000000" }
				]
			}

			];

			map.addStyle({
				styledMapName:"Styled Map",
				styles: styles,
				mapTypeId: "map_style"
			});

			map.setStyle("map_style");
		}
		catch (e) {
			console.log("main.js: Can't create Google Maps API object");
		}
	}());
	    	
});