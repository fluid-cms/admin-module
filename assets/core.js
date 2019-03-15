$(function () {
	function initTooltips() {
        $('body').tooltip({
            selector: '[data-toggle="tooltip"]',
            container: 'body',
            delay: {show: 500, hide: 100}
        });
    }

    initTooltips();
	$.nette.ext('tooltips', {
        success: function () {
            $('.tooltip').remove();
            initTooltips();
        }
	});

	$(".fluid-select-buttons").click(function () {
		$('[data-control="buttons"]').val($(this).data("icon"));
		$(".fluid-select-buttons").removeClass('active');
		$(this).addClass('active');
	});

	$.nette.ext('category-buttons', {
		success: function () {
			$(".fluid-select-buttons").click(function () {
				$('[data-control="buttons"]').val($(this).data("icon"));
				$(".fluid-select-buttons").removeClass('active');
				$(this).addClass('active');
			});
		}
	});

	$(".time-before").html(function (index, value) {
		$(this).attr('title', moment(value, "YYYY-MM-DD HH:mm:ss").format('D.MMMM YYYY HH:mm'));
		return moment(value, "YYYY-MM-DD HH:mm:ss").from();
	});
});
