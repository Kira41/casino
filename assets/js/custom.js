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
		rdn_events_list._activeFilter = '*';
		elem._isotopeInstance = rdn_events_list;

		if (filtersElem) {
			filtersElem.addEventListener('click', function(event) {
				if (!matchesSelector(event.target, 'a')) {
					return;
				}
				event.preventDefault();
				var filterValue = event.target.getAttribute('data-filter');
				rdn_events_list._activeFilter = filterValue || '*';
				rdn_events_list.arrange({
					filter: filterValue
				});
				var activeItem = filtersElem.querySelector('.is_active');
				if (activeItem) {
					activeItem.classList.remove('is_active');
				}
				event.target.classList.add('is_active');
			});
		}

		rdn_events_list.on('arrangeComplete', function() {
			if (this._paginationUpdateInProgress) {
				return;
			}
			document.dispatchEvent(new CustomEvent('pagination:refresh', {
				detail: { container: elem }
			}));
		});
	});
}

	function PaginationController(container) {
		this.container = container;
		this.scope = container.getAttribute('data-pagination-scope') || '';
		this.items = Array.prototype.slice.call(container.querySelectorAll('[data-pagination-item]'));
		this.itemsPerPage = parseInt(container.getAttribute('data-items-per-page'), 10);
		this.itemsPerPage = isNaN(this.itemsPerPage) ? 8 : this.itemsPerPage;
		this.controls = document.querySelector('[data-pagination-controls-for="' + this.scope + '"]');
		this.currentPage = 1;
		this.isActive = !!this.controls;

		if (!this.controls) {
			return;
		}

		this.setPage(1);
	}

	PaginationController.prototype.getActiveFilter = function() {
		if (this.container._isotopeInstance && this.container._isotopeInstance._activeFilter) {
			return this.container._isotopeInstance._activeFilter;
		}

		return '*';
	};

	PaginationController.prototype.matchesCurrentFilter = function(item) {
		var filterValue = this.getActiveFilter();
		if (!filterValue || filterValue === '*') {
			return true;
		}

		return matchesSelector(item, filterValue);
	};

	PaginationController.prototype.getEligibleItems = function() {
		var self = this;
		return this.items.filter(function(item) {
			return self.matchesCurrentFilter(item);
		});
	};

	PaginationController.prototype.setPage = function(pageNumber) {
		if (!this.controls) {
			return;
		}

		var eligibleItems = this.getEligibleItems();
		var totalPages = Math.max(1, Math.ceil(eligibleItems.length / this.itemsPerPage));
		this.currentPage = Math.min(Math.max(pageNumber, 1), totalPages);
		var startIndex = (this.currentPage - 1) * this.itemsPerPage;
		var endIndex = startIndex + this.itemsPerPage;

		this.items.forEach(function(item) {
			item.classList.add('pagination-hidden');
		});

		eligibleItems.forEach(function(item, index) {
			if (index >= startIndex && index < endIndex) {
				item.classList.remove('pagination-hidden');
			}
		});

		if (this.container._isotopeInstance && typeof this.container._isotopeInstance.arrange === 'function') {
			var instance = this.container._isotopeInstance;
			instance._paginationUpdateInProgress = true;
			instance.arrange({
				filter: function(itemElem) {
					var filterValue = instance._activeFilter || '*';
					var matchesFilter = !filterValue || filterValue === '*' ? true : matchesSelector(itemElem, filterValue);
					return matchesFilter && !itemElem.classList.contains('pagination-hidden');
				}
			});
			instance._paginationUpdateInProgress = false;
		} else if (this.container._isotopeInstance && typeof this.container._isotopeInstance.layout === 'function') {
			this.container._isotopeInstance.layout();
		}

		this.renderControls(totalPages);
	};

	PaginationController.prototype.renderControls = function(totalPages) {
		if (!this.controls) {
			return;
		}

		this.controls.innerHTML = '';

		if (totalPages <= 1) {
			this.controls.setAttribute('hidden', 'hidden');
			return;
		}

		this.controls.removeAttribute('hidden');
		this.controls.appendChild(this.createControlItem('previous', this.currentPage - 1, this.currentPage === 1, '<', 'Previous page'));

		for (var page = 1; page <= totalPages; page++) {
			this.controls.appendChild(this.createControlItem('page', page, page === this.currentPage, page, 'Page ' + page));
		}

		this.controls.appendChild(this.createControlItem('next', this.currentPage + 1, this.currentPage === totalPages, '>', 'Next page'));
	};

	PaginationController.prototype.createControlItem = function(type, targetPage, isDisabled, label, ariaLabel) {
		var listItem = document.createElement('li');
		var anchor = document.createElement('a');
		anchor.href = '#';
		anchor.setAttribute('role', 'button');
		anchor.setAttribute('aria-label', ariaLabel);
		anchor.textContent = label;

		if (type === 'page' && targetPage === this.currentPage) {
			anchor.classList.add('is_active');
		}

		if (isDisabled) {
			anchor.setAttribute('aria-disabled', 'true');
		} else {
			var self = this;
			anchor.addEventListener('click', function(event) {
				event.preventDefault();
				self.setPage(targetPage);
			});
		}

		listItem.appendChild(anchor);
		return listItem;
	};

	function setupPagination() {
		document.querySelectorAll('[data-pagination-scope]').forEach(function(container) {
			var controller = new PaginationController(container);
			if (controller && controller.isActive) {
				container._paginationController = controller;
			}
		});

		document.addEventListener('pagination:refresh', function(event) {
			var container = event.detail && event.detail.container ? event.detail.container : null;
			if (container && container._paginationController) {
				container._paginationController.setPage(container._paginationController.currentPage);
			}
		});
	}

	document.addEventListener('DOMContentLoaded', function() {
		setupIsotopeFilters();
		setupPagination();
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
