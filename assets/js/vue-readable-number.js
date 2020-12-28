/*! Copyright (c) 2016 Naufal Rabbani (http://github.com/BosNaufal)
* Licensed Under MIT (http://opensource.org/licenses/MIT)
*
* Version 1.0.0
* - Need readable-number-js ( https://github.com/BosNaufal/vue-readable-number/vue-readable-number.js )
*
*/
Vue.filter('readable-number',{

	read: function(val) {
		return readNumber(val);
	},

	write: function(val) {
		return unReadNumber(val);
	}

});
