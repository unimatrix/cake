/**
 * Captcha widget
 *
 * @author Flavius
 * @version 0.1
 */
if(typeof Widgets === 'undefined') var Widgets = {};
Widgets.captcha = function() { 'use strict';
    var store = {
        js: {}

    // load javascript dynamically
    }, _load = function(js) {
        if(store.js[js] == true)
            return true;

        $('body').append('<script type="text/javascript" src="'+ js +'" />');
        return store.js[js] = true;

    // render each captcha
    }, render = function() {
        // go through each captcha widget
        $('div.captcha-widget').each(function() {
            let e = $(this).find('div');
            grecaptcha.render(e.get(0), e.data());
        });

    // init
    }, __construct = function() {
        _load('https://www.google.com/recaptcha/api.js?onload=GoogleRecaptchaOnLoad&render=explicit');
    };

    // public, yay
    return {
        init: __construct,
        render: render
    };
}();

// onload wrapper
var GoogleRecaptchaOnLoad = function() {
    Widgets.captcha.render();
};

// init widget on ready
$(document).ready(Widgets.captcha.init);
