;(function($) {

	var name = "fluidNote";

	function FluidNote(element, options) {
		this.$this = $(element);
		this.id = element.id;
		this._changed = false;
		this._pasteFormatted = false;
		this.$recoverLabel = null;
		this.$recoverButton = null;
		this.settings = $.extend({}, {
			title: 'Nespecifikovaný titulek', // Title for navbar
			basePath: null, // URL base path
			editLink: null, // Link to backend record edit (administration)
			editSignal: null, // edit signal - if null, Save icon in summernote will not appear (works only in inline)
			uploadSignal: null, // upload signal
			inline: false, // Air mode - klasický inline edit v popoveru
			unique: null, // Pokud je potřeba označit unikátnost requestu (ID boxu, či čehokoliv)
			storage: true, // Local Storage - lze vypnout ukladani změn do local storage, (pokud prohlizec uzivatele nepodporuje localStorage - automaticky false)
			custom: {
				toolbar: [],
				buttons: {}
			}
		}, options);
	}

	FluidNote.prototype = {
		init: function () {
			var plugin = this;

			if (plugin.settings.inline) {
				plugin.$this.popover({
					html: true,
					container: 'body',
					placement: function (context, source) {
						if (($(source).offset().top - $(window).scrollTop()) < 150) {
							return "bottom";
						}
						return "top";
					},
					title: plugin.settings.title,
					trigger: 'manual',
					animation: false,
					content: '<div id="content-edit" class="btn btn-primary btn-block btn-inline-edit" onclick="$(\'#' + plugin.id + '\').fluidNote(\'initInlineSummernote\')">Upravit</div>',
					template: '<div class="popover popover-inline" role="tooltip"><div class="arrow"></div><h3 class="popover-title"></h3><div class="popover-content"></div></div>'
				}).on("mouseenter", function () {
					plugin.$this.popover('show');
					plugin.$this.addClass('hover');
					$(".popover").on('mouseleave', function () {
						if (!plugin.$this.is(':hover')) {
							plugin.$this.popover('hide');
							plugin.$this.removeClass('hover');
						}
					});
				}).on('mouseleave', function () {
					setTimeout(function () {
						if (!$(".popover:hover").length) {
							plugin.$this.popover('hide');
							plugin.$this.removeClass('hover');
						}
					}, 0);
				}).on('dblclick', function () {
					plugin.initInlineSummernote();
				});
			} else {
				plugin.initBasicSummernote();
			}
		},
		initBasicSummernote: function () {
			var plugin = this;

			var toolbar = [
				['style', ['style', 'bold', 'italic', 'underline', 'clear', 'paste']],
				['font', ['strikethrough', 'superscript', 'subscript']],
				['fontsize', ['fontsize', "height"]],
				['color', ['color']],
				['table', ['table']],
				['para', ['ul', 'ol', 'paragraph']],
				['insert', ['link', 'picture', 'video']],
				['view', ['fullscreen', 'codeview']]
			].concat(plugin.settings.custom.toolbar);

			var buttons = {
				paste: function () {
					var ui = $.summernote.ui;
					var button = ui.button({
						contents: '<i class="fa fa-paint-brush fa-fw"/>',
						tooltip: 'Vkládat formátované',
						click: function () {
							plugin._pasteFormatted = !plugin._pasteFormatted;
							$(this).toggleClass('active', plugin._pasteFormatted);
						}
					});
					return button.render();
				}
			};

			var settings = $.extend({}, {
				height: 400,
				lang: 'cs-CZ',
				callbacks: {
					onImageUpload: function(files) {
						plugin.upload(files[0], this);
					},
					onPaste: function (e) {
						if (!plugin._pasteFormatted) {
							var bufferText = ((e.originalEvent || e).clipboardData || window.clipboardData).getData('Text');
							e.preventDefault();
							document.execCommand('insertText', false, bufferText);
						}
					}
				}
			}, plugin.settings.custom);

			for (var buttonName in plugin.settings.custom.buttons) {
				if (plugin.settings.custom.buttons.hasOwnProperty(buttonName)) {
					buttons[buttonName] = plugin.settings.custom.buttons[buttonName];
				}
			}

			settings.toolbar = toolbar;
			settings.buttons = buttons;

			plugin.$this.summernote(settings);

			$('.note-codable').on('blur', function () {
				if (plugin.$this.summernote('codeview.isActivated')) {
					plugin.$this.val(plugin.$this.summernote('code'));
				}
			});

			plugin.checkUploadAvailable();
		},
		initInlineSummernote: function () {
			var plugin = this;

			plugin.$this.popover('hide');
			plugin.$this.removeClass('hover');

			if (plugin.settings.editSignal) {
				var SaveButton = function (context) {
					var ui = $.summernote.ui;

					var button = ui.button({
						contents: '<i class="fa fa-save fa-fw"/>',
						tooltip: 'Uložit změny',
						click: function () {
							$.nette.ajax({
								type: "POST",
								dataType: "json",
								url: plugin.settings.editSignal,
								data: { unique: plugin.settings.unique, content: context.invoke('code')}
							});
						}
					});

					return button.render();
				};
			}

			plugin.$this.summernote({
				popover: {
					image: [
						['imagesize', ['imageSize100', 'imageSize50', 'imageSize25']],
						['float', ['floatLeft', 'floatRight', 'floatNone']],
						['remove', ['removeMedia']]
					],
					link: [
						['link', ['linkDialogShow', 'unlink']]
					],
					air: [
						['color', ['color']],
						['style', ['style']],
						['font', ['fontsize', 'bold', 'italic', 'underline', 'clear']],
						['para', ['ul', 'paragraph']],
						['table', ['table']],
						['insert', ['link', 'picture']],
						(plugin.settings.editSignal ? ['customtoolbar', ['savebutton']] : ['customtoolbar', []])
					]
				},
				buttons: {
					savebutton: SaveButton
				},
				airMode: true,
				lang: 'cs-CZ',
				callbacks: {
					onImageUpload: function(files) {
						plugin.upload(files[0], this);
					},
					onChange: function () {
						plugin.onChange();
					},
					onFocus: function () {
						plugin.onFocus();
					},
					onInit: function () {
						plugin.onInitInline();
					}
				}
			});
		},
		checkUploadAvailable: function () {
			var plugin = this;
			if (plugin.settings.uploadSignal === null || plugin.settings.basePath === null) {
				this.$this.siblings(".note-editor").find('.note-group-select-from-files').remove();
			}
		},
		upload: function (file, editor) {
			var plugin = this;

			data = new FormData();
			data.append("file", file);
			$.nette.ajax({
				type: "POST",
				url: plugin.settings.uploadSignal,
				cache: false,
				contentType: false,
				processData: false,
				data: data,
				success: function(payload) {
					$(editor).summernote('insertImage', plugin.settings.basePath + '/' + payload['path']);
				}
			});
		},
		createOverlay: function () {
			var overlay = document.createElement("div");
			overlay.id = "inline-edit-overlay";
			document.body.appendChild(overlay);
		},
		createNavBar: function () {
			var plugin = this;

			var editButton = !plugin.settings.editLink ? "" :
				"<li class=\"nav-item\">\n" +
				"    <a href=\"" + plugin.settings.editLink + "\" target=\"_blank\" class=\"nav-link\"><i class=\"fa fa-pencil\"></i> Upravit v administraci</a>\n" +
				"</li>\n";

			var recoverBlock = !plugin.isStorageEnabled() ? "" :
				"<br />Poslední změna: " +
				"<span id='inline-edit-recover-label'>nikdy</span> " +
				"<a id='inline-edit-recover-button' style='display: none' class='btn btn-xs btn-primary' onclick=\"$('#" + plugin.id + "').fluidNote('recover')\">Obnovit</a>\n";

			var navbar = "" +
				"<div class=\"container\">\n" +
				"    <span class=\"navbar-brand f-14 mr-auto\">" + plugin.settings.title +
					 recoverBlock +
				"    </span>\n" +
				"    <ul class=\"nav navbar-nav navbar-right\">\n" +
						editButton +
				"        <li class=\"nav-item\">\n" +
				"            <a onclick=\"$('#" + plugin.id + "').fluidNote('save')\" style=\"cursor: pointer;\" class=\"nav-link\"><i class=\"fa fa-save\"></i> Uložit změny</a>\n" +
				"        </li>\n" +
				"        <li class=\"nav-item\">\n" +
				"            <a onclick=\"$('#" + plugin.id + "').fluidNote('confirmAndDestroy')\" style=\"cursor: pointer;\" class=\"nav-link\"><i class=\"fa fa-pencil\"></i> Ukončit úpravy</a>\n" +
				"        </li>\n" +
				"    </ul>\n" +
				"</div>\n";

			var element = document.createElement("nav");
			element.id = "navbar-inline";
			element.className = "navbar navbar-inverse navbar-fixed-bottom navbar-inline bg-inverse fixed-bottom navbar-toggleable-md";
			element.innerHTML = navbar;

			document.body.appendChild(element);

			plugin.$recoverLabel = $('#inline-edit-recover-label');
			plugin.$recoverButton = $('#inline-edit-recover-button');
		},
		confirmAndDestroy: function () {
			if (this._changed) {
				if (confirm("Provedené změny nebyly uloženy a po aktualizaci stránky zmizí.")) {
					this.destroy();
				}
			} else {
				this.destroy();
			}
		},
		destroy: function () {
			this.destroyOverlay();
			this.destroyNavbar();
			this.$this.summernote('destroy');
		},
		destroyOverlay: function () {
			document.body.removeChild(document.getElementById("inline-edit-overlay"));
		},
		destroyNavbar: function () {
			document.body.removeChild(document.getElementById("navbar-inline"));
		},
		onFocus: function () {

		},
		onChange: function () {
			var plugin = this;
			plugin._changed = true;

			if (plugin.isStorageEnabled()) {
				var date = moment().format('dddd D:M:YYYY, HH:mm:ss');
				var code = plugin.$this.summernote('code');

				plugin.$recoverLabel.removeClass().addClass('text-warning').html(date + " (uloženo lokálně)");
				plugin.$recoverButton.hide();

				localStorage.setItem(plugin.settings.unique, JSON.stringify({ code: code, date: date }));
			}
		},
		onInitInline: function () {
			var plugin = this;

			plugin.createOverlay();
			plugin.createNavBar();
			plugin.checkUploadAvailable();

			if (plugin.hasBackup() && plugin.isStorageEnabled()) {
				plugin.$recoverLabel.removeClass().addClass('text-danger');
				plugin.$recoverLabel.html(plugin.getBackup().date + " (záloha)");
				plugin.$recoverButton.show();
			}
		},
		recover: function () {
			var plugin = this;

			if (plugin.hasBackup() && plugin.isStorageEnabled()) {
				plugin.$this.summernote('code', plugin.getBackup().code);
				plugin.$recoverLabel.removeClass().addClass('text-success');
				plugin.$recoverLabel.html(plugin.getBackup().date + " (obnoveno)");
			}
		},
		hasBackup: function () {
			return (localStorage.getItem(this.settings.unique) !== null);
		},
		getBackup: function () { // return JSON with code & date
			return JSON.parse(localStorage.getItem(this.settings.unique));
		},
		isStorageEnabled: function () {
			return (typeof(Storage) !== "undefined") && this.settings.storage;
		},
		save: function () {
			var plugin = this;
			plugin._changed = false;

			$.nette.ajax({
				type: "POST",
				dataType: "json",
				url: plugin.settings.editSignal,
				data: { unique: plugin.settings.unique, content: plugin.$this.summernote('code') }
			});

			plugin.$recoverLabel.removeClass().addClass('text-success').html('uloženo');

			if (plugin.isStorageEnabled()) {
				localStorage.removeItem(plugin.settings.unique);
			}
		}
	};

	function Plugin(option) {
		var options = typeof option === "object" && option;

		return this.each(function () {
			var $this   = $(this);
			var $plugin = $this.data(name);

			if (!$plugin) {
				$plugin = new FluidNote(this, options);
				$this.data(name, $plugin);
			}

			if (typeof option === "string") {
				$plugin[option]();
			} else {
				$plugin.init();
			}
		})
	}

	$.fn.fluidNote             = Plugin;
	$.fn.fluidNote.Constructor = FluidNote;

})(jQuery, window, document);