$(function () {

	var icon = $('.navbar-brand .fa');
	var brand = $('.navbar-brand');

	function setStateLoading() {
		icon.addClass('fa-spin')
			.removeClass('fa-dashboard')
			.addClass('fa-circle-o-notch');
	}

	function setStateDone() {
		icon.removeClass('fa-spin')
			.addClass('fa-dashboard')
			.removeClass('fa-circle-o-notch');

		brand.css('background', '#4caf50');

		setTimeout(function () {
			brand.css('background', '');
		}, 1000);
	}

	function setStateError() {
		icon.removeClass('fa-spin')
			.addClass('fa-dashboard')
			.removeClass('fa-circle-o-notch');
	}

	window.onbeforeunload = function() {
		setStateLoading();
	};

	window.onload = function () {
		setStateDone();
	};

	$.nette.ext('pace', {
		before: function () {
			setStateLoading();
		},
		complete: function () {
			setStateDone();
		},
		error: function () {
			setStateError();
		}
	});
});