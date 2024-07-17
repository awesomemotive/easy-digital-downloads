// Flyout Menu.
import {domReady} from 'utils/dom';

var EDD_Flyout = {
	flyoutMenu: null,
	wpfooter: null,
	overlap: null,
	init: function() {
		// Flyout Menu Elements.
		this.flyoutMenu = document.getElementById('edd-flyout');

		if (!this.flyoutMenu) {
			return;
		}

		var head = document.getElementById('edd-flyout-button'),
			edd = head.querySelector('img'),
			items = document.getElementById('edd-flyout-items'),
			menu = {
				state: 'inactive',
				srcInactive: edd.getAttribute('src'),
				srcActive: edd.dataset.active,
			};

		// Click on the menu head icon.
		head.addEventListener('click', (e) => {
			e.preventDefault();

			if (menu.state === 'active') {
				this.flyoutMenu.classList.remove('opened');
				edd.setAttribute('src', menu.srcInactive);
				menu.state = 'inactive';
				items.classList.remove('active');
			} else {
				this.flyoutMenu.classList.add('opened');
				edd.setAttribute('src', menu.srcActive);
				menu.state = 'active';
				items.classList.add('active');
			}
	  });

		// Page elements and other values.
		this.wpfooter = document.getElementById('wpfooter');

		if (!this.wpfooter) {
			return;
		}

		// If we run into pages that need to have this disabled, we can add them here.
		//this.overlap = document.querySelectorAll('#target-selector');
		this.overlap = {};

		// Hide menu if scrolled down to the bottom of the page.
		window.addEventListener('resize', this.handleScroll.bind(this));

		window.addEventListener('scroll', () => this.debounce(this.handleScroll.bind(this), 50));

		window.addEventListener('load', this.handleScroll.bind(this));

		document.addEventListener( 'edd_promo_notice_enter', () => this.flyoutMenu.classList.add('out') );
		document.addEventListener( 'edd_promo_notice_dismiss', () => setTimeout( this.flyoutMenu.classList.remove('out'), 500 ) );
	},
	handleScroll: function() {
		if ( this.overlap.length < 1 ) {
			return;
		}

		var wpfooterTop = this.wpfooter.offsetTop,
		wpfooterBottom = wpfooterTop + this.wpfooter.offsetHeight,
		overlapBottom = this.overlap.length > 0 ? this.overlap[0].offsetTop + this.overlap[0].offsetHeight + 85 : 0,
		viewTop = window.scrollY,
		viewBottom = viewTop + window.innerHeight;

		if (wpfooterBottom <= viewBottom && wpfooterTop >= viewTop && overlapBottom > viewBottom) {
			this.flyoutMenu.classList.add('out');
		  } else {
			this.flyoutMenu.classList.remove('out');
		  }
	},
	debounce: function(func, wait, immediate) {
		var timeout;
		return function () {
			var context = this,
				args = arguments;
			var later = function () {
				timeout = null;
				if (!immediate) func.apply(context, args);
			};
			var callNow = immediate && !timeout;
			clearTimeout(timeout);
			timeout = setTimeout(later, wait);
			if (callNow) func.apply(context, args);
		}
	}
}
domReady(function() {
	EDD_Flyout.init();
})
