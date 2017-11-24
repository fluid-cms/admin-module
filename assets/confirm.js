/**
 * Usage:
 * Just add data attribute 'data-confirm' with confirmation message
 * If you want to ajaxify the link, just add data attribute 'data-ajax' with value 'on'
 *
 * Example:
 * <a href="http://grapesc.cz" data-confirm="Do you really want to enter Grape SC web?">Grape SC, a.s.</a>
 *
 * Example AJAX:
 * <a n:href="deleteItem! 5" data-confirm="Do you really want to delete this item?" data-ajax="on">Delete item</a>
 */
(function ($, undefined) {

	$.nette.ext({
		load: function () {
			$('[data-confirm]').click(function (event) {
				var obj = this;
				var message = $(obj).attr('data-confirm');

				event.preventDefault();
				event.stopImmediatePropagation();
				$("<div id='dConfirm' class='modal fade'></div>").appendTo('body');

				var dialog = $('#dConfirm');
				dialog.html("" +
					"<div class='modal-dialog modal-sm'>" +
					"	<div class='modal-content'>" +
					"		<div id='dConfirmHeader' class='modal-header'>" +
					" 			<button type='button' class='close' data-dismiss='modal' aria-label='Close'>" +
					"				<span aria-hidden='true'>&times;</span>" +
					"			</button>" +
					"			<h4 id='dConfirmTitle'>Potvrzení akce</h4>" +
					"		</div>" +
					"	<div id='dConfirmBody' class='modal-body'>" +
					"	</div>" +
					"	<div id='dConfirmFooter' class='modal-footer'>" +
					"		<button id='dConfirmOk' class='btn btn-primary' data-dismiss='modal' type='button'>Ano</button>" +
					"		<button id='dConfirmCancel' class='btn btn-danger' data-dismiss='modal' type='button'>Ne</button>" +
					"	</div>" +
					"</div>" +
					"");
				$('#dConfirmBody').html(message);
				$('#dConfirmOk').on('click', function () {
					var tagName = $(obj).prop("tagName");
					var callback = $(obj).attr('data-callback');

					if (callback) {
                        if (typeof window.confirm[callback] !== "undefined") {
                            window.confirm[callback](obj);
                        } else {
                        	throw "Undefined window.confirm callback: '" + callback + "'";
						}
					} else {
                        if (tagName == 'INPUT') {
                            var form = $(obj).closest('form');
                            $.nette.ajax({
                                type: 'POST',
                                url: form.attr('action'),
                                data: form.serialize()
                            }, obj, event);
                        } else {
                            if ($(obj).data('ajax') == "on") {
                                $.nette.ajax({
                                    url: obj.href
                                });
                            } else {
                                document.location = obj.href;
                            }
                        }
					}
				});

				dialog.on('shown.bs.modal', function () {
                    $('#dConfirmOk').trigger('focus');
                }).on('hidden.bs.modal', function () {
					$('#dConfirm').remove();
				});
				dialog.modal('show');
			});
		}
	});

})(jQuery);