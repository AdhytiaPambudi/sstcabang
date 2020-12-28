/*! Copyright (c) 2016 Naufal Rabbani (http://github.com/BosNaufal)
* Licensed Under MIT (http://opensource.org/licenses/MIT)
*
* Version 1.0.0
* - Need readable-number-js ( https://github.com/BosNaufal/vue-readable-number/vue-readable-number.js )
* - And vue-readable-number filters ( https://github.com/BosNaufal/vue-calc-input/src/vue-readable-number.js )
*
*/
Vue.directive('calc-input',{

	bind: function(){
		var me = this;

		// make an event handler
		me.evt = function(e) {
			// get the input value
			var val = me.el.value;
			me.el.value = readNumber(val);
		};

		// Add a Event listener
		me.el.addEventListener('input',me.evt, false);
	},

	unbind: function() {
		// Remove The listener
		me.el.removeEventListener('input',me.evt, false);
	}

});
