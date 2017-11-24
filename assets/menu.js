;(function($) {

	var pluginName = "fluidSidebar",
		defaults = {
			toggle: false
		};

	function Plugin(element, options) {
		this.element = $(element);
		this.settings = $.extend({}, defaults, options);
		this.init();
	}

	Plugin.prototype = {
		init: function() {

			var $this = this.element,
				$toggle = this.settings.toggle;

			$this.find("li.active").has("ul").children("ul").addClass("collapse in");
			$this.find("li").not(".active").has("ul").children("ul").addClass("collapse");

			$this.find("li").has("ul").children("a").on("click" + "." + pluginName, function(e) {
				e.preventDefault();
				$(this).parent("li").toggleClass("active").children("ul").collapse("toggle");

				if ($toggle) {
					$(this).parent("li").siblings().removeClass("active").children("ul.in").collapse("hide");
				}

			});
		},

		remove: function() {
			this.element.off("." + pluginName);
			this.element.removeData(pluginName);
		}

	};

	$.fn[pluginName] = function(options) {
		this.each(function () {
			var el = $(this);
			if (el.data(pluginName)) {
				el.data(pluginName).remove();
			}
			el.data(pluginName, new Plugin(this, options));
			el.mCustomScrollbar({
                theme: 'minimal-dark',
                scrollInertia: 100,
                axis: 'y',
                mouseWheel: {
                    enable: true,
                    axis: 'y',
                    preventDefault: true
                }
            });
		});
		return this;
	};

})(jQuery, window, document);

$(function () {
    $('#side-menu').fluidSidebar();
});