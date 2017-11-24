$(function () {
    var options = {
		iconBase: 'fa fa-fw',
		tickIcon: 'fa-check',
		liveSearch: false,
		liveSearchPlaceholder: 'Hledat'
    };

	$('.form-select').each(function (i) {
    	var select = $(this);
		options.liveSearch = select.children("option").length >= 5;
		select.selectpicker(options);
	});
});
