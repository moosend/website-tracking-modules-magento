/**
 * Copyright © Moosend. All rights reserved.
 * See COPYING.txt for license details.
 */
/* jscs:disable */
/* eslint-disable */
define([], function () {
    'use strict';

    /**
     * @param {Object} config
     */
    return function (config) {

    	(function(t, n, e, o, a) {
    		t.MooTrackerObject = a;
    		t[a] = t[a] || function() {
		        return t[a].q ? void t[a].q.push(arguments) : void(t[a].q = [arguments])
		    }
	        var l = ~~(Date.now() / 3e5),
	            i = document.createElement(e);
	        i.async = !0, i.src = o + "?ts=" + l;
	        var m = document.getElementsByTagName(e)[0];
	        m.parentNode.insertBefore(i, m);
		})(window, document, "script", "//cdn.stat-track.com/statics/moosend-tracking.min.js", "mootrack");

		var data = config.data;

		mootrack('setCookieNames', { userIdName: data.cookie_names.user_id, emailName: data.cookie_names.email });

		mootrack('init', data.current_website_id);

		if (data.order_info) {
			var email = data.order_info.email;
			var name = data.order_info.name;
			var order_data = data.order_info.order_data;
			var order_total_price = data.order_info.order_total_price;

			mootrack('identify', email, name);

			mootrack('trackOrderCompleted', order_data, order_total_price);
		}

		if (data.user_data) {
			mootrack('identify', data.user_data.email, data.user_data.name);
		}

		if (data.is_product_page) {
			mootrack('track', 'PAGE_VIEWED', data.current_product);
			return;
		}
		mootrack('trackPageView');


    };
});
