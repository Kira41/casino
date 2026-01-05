(function ($) {
	
	"use strict";

	// Page loading animation
	$(window).on('load', function() {

        $('#js-preloader').addClass('loaded');

    });


	$(window).scroll(function() {
	  var scroll = $(window).scrollTop();
	  var box = $('.header-text').height();
	  var header = $('header').height();

	  if (scroll >= box - header) {
	    $("header").addClass("background-header");
	  } else {
	    $("header").removeClass("background-header");
	  }
	})

	var width = $(window).width();
		$(window).resize(function() {
		if (width > 767 && $(window).width() < 767) {
			location.reload();
		}
		else if (width < 767 && $(window).width() > 767) {
			location.reload();
		}
	})

function setupIsotopeFilters() {
	document.querySelectorAll('.trending-box').forEach(function(elem) {
		var layoutMode = elem.getAttribute('data-layout-mode') || 'masonry';
		var isotopeOptions = {
			itemSelector: '.trending-items',
			layoutMode: layoutMode
		};

		if (layoutMode === 'fitRows') {
			isotopeOptions.percentPosition = true;
		}

		var filtersElem = elem.closest('.trending') ? elem.closest('.trending').querySelector('.trending-filter') : null;
		var rdn_events_list = new Isotope(elem, isotopeOptions);
		var activeClassName = 'is_active';
		var applyFilter = function(control) {
			if (!control) {
				return;
			}

			var filterValue = control.getAttribute('data-filter') || '*';
			rdn_events_list._activeFilter = filterValue;
			rdn_events_list.arrange({
				filter: filterValue
			});

			if (!filtersElem) {
				return;
			}

			filtersElem.querySelectorAll('[data-filter]').forEach(function(filterNode) {
				filterNode.classList.remove(activeClassName);
				filterNode.setAttribute('aria-pressed', 'false');
			});

			control.classList.add(activeClassName);
			control.setAttribute('aria-pressed', 'true');
		};

		elem._isotopeInstance = rdn_events_list;

		if (filtersElem) {
			var initialControl = filtersElem.querySelector('.' + activeClassName + '[data-filter]') || filtersElem.querySelector('[data-filter]');
			if (initialControl) {
				applyFilter(initialControl);
			}

			filtersElem.addEventListener('click', function(event) {
				var control = event.target.closest('[data-filter]');
				if (!control || !filtersElem.contains(control)) {
					return;
				}

				event.preventDefault();
				applyFilter(control);
			});
		} else {
			rdn_events_list._activeFilter = '*';
		}
	});
}

	document.addEventListener('DOMContentLoaded', function() {
		setupIsotopeFilters();
	});


	// Menu Dropdown Toggle
	if($('.menu-trigger').length){
		$(".menu-trigger").on('click', function() {	
			$(this).toggleClass('active');
			$('.header-area .nav').slideToggle(200);
		});
	}


	// Menu elevator animation
	$('.scroll-to-section a[href*=\\#]:not([href=\\#])').on('click', function() {
		if (location.pathname.replace(/^\//,'') == this.pathname.replace(/^\//,'') && location.hostname == this.hostname) {
			var target = $(this.hash);
			target = target.length ? target : $('[name=' + this.hash.slice(1) +']');
			if (target.length) {
				var width = $(window).width();
				if(width < 991) {
					$('.menu-trigger').removeClass('active');
					$('.header-area .nav').slideUp(200);	
				}				
				$('html,body').animate({
					scrollTop: (target.offset().top) - 80
				}, 700);
				return false;
			}
		}
	});


	// Page loading animation
	$(window).on('load', function() {
		if($('.cover').length){
			$('.cover').parallax({
				imageSrc: $('.cover').data('image'),
				zIndex: '1'
			});
		}

		$("#preloader").animate({
			'opacity': '0'
		}, 600, function(){
			setTimeout(function(){
				$("#preloader").css("visibility", "hidden").fadeOut();
			}, 300);
		});
	});
    


})(window.jQuery);
