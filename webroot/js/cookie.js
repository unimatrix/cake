/**
 * Cookie Policy
 * Will create the notice html for your cookie policy
 *
 * @author Flavius
 * @version 0.4
 */
Cookies.policy = function() { 'use strict';
	var store = {
		message: 'Acest site folosește cookies pentru a-ți oferi o experiență cât mai plăcută. Continuarea navigării implică acceptarea lor.',
		accept: 'Sunt de acord',
		page: '/cookies',
		details: 'Mai multe detalii',
		parent: $('body')

	// html5 sticky footer
	}, _build = function() {
		// do html
		var html = '<div class="cookie-policy">' +
	        '<div>' +
	            '<span>' + store.message + '</span>' +
	            '<div>' +
	            	'<a href="javascript:void(0);">' + store.accept + '</a>' +
	            	'<a href="' + store.page + '" target="_blank" rel="nofollow">' + store.details + '</a>' +
	            '</div>' +
	        '</div>' +
	    '</div>';

		// append to parent
		store.parent.append(html);

		// define policy and spacer
		var policy = $('div.cookie-policy', store.parent);

		// setup the spacer
		$('body').css({marginBottom: parseInt($('body').css('marginBottom').replace('px', '')) + policy.outerHeight()});
		if($('body > footer').length > 0)
			$('body > footer').css({marginBottom: parseInt($('body > footer').css('marginBottom').replace('px', '')) + policy.outerHeight()});

		// on i agree click
		$('div.cookie-policy > div > div > a:first-child', store.parent).on('click', function() {
			// hide element
            policy.fadeOut('fast', function() {
        		$('body').removeAttr('style');
        		$('body > footer').removeAttr('style');
            });

			// set cookie
			Cookies.set('cookie_accept', '1', {
				expires: 30,
				path: '/'
			});
		});

	// init
	}, __construct = function(cfg) {
		// set configuration
		store = $.extend({}, store, cfg);

		// build cookies
		if(Cookies.get('cookie_accept') !== '1')
			_build();
	};

	// public, yay
	return {
	    init: __construct
	};
}();
