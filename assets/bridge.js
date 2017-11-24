/** Solving naming conflicts between jQuery UI and Bootstrap */
$.widget.bridge('uibutton', $.ui.button);
$.widget.bridge('uitooltip', $.ui.tooltip);