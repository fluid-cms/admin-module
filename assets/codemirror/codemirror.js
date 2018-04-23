$(function () {
    var elements = document.getElementsByClassName("form-codemirror");

    for (var i = 0; i < elements.length; i++) {
        CodeMirror.fromTextArea(elements[i], {
            lineNumbers: true,
            mode: {name: "latte", baseMode: "text/html"},
			theme: 'monokai'
        });
    }
});