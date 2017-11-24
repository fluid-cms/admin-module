// CodeMirror, copyright (c) by Marijn Haverbeke and others
// Distributed under an MIT license: http://codemirror.net/LICENSE

/**
 * Latte mode (experimental)
 */

(function(mod) {
     mod(CodeMirror);
})(function(CodeMirror) {
    "use strict";

    CodeMirror.defineMode("latte", function(config, parserConf) {
        var rightDelimiter = "}";
        var leftDelimiter = "{";
        var baseMode = CodeMirror.getMode(config, parserConf.baseMode || "null");

        var regs = {
            operatorChars: /[+\-*&%=<>!?]/,
            validIdentifier: /[a-zA-Z0-9_]/,
            stringChar: /['"]/
        };

        var last;
        function cont(style, lastType) {
            last = lastType;
            return style;
        }

        function chain(stream, state, parser) {
            state.tokenize = parser;
            return parser(stream, state);
        }

        function tokenTop(stream, state) {
            var string = stream.string;
            var nextMatch = string.indexOf(leftDelimiter, stream.pos);
            if (nextMatch == stream.pos) {
                stream.match(leftDelimiter);
                if (stream.eat("*")) {
                    return chain(stream, state, tokenBlock("comment", "*" + rightDelimiter));
                } else {
                    state.depth++;
                    state.tokenize = tokenlatte;
                    last = "startTag";
                    return "tag";
                }
            }

            if (nextMatch > -1) stream.string = string.slice(0, nextMatch);
            var token = baseMode.token(stream, state.base);
            if (nextMatch > -1) stream.string = string;
            return token;
        }

        // parsing latte content
        function tokenlatte(stream, state) {
            if (stream.match(rightDelimiter, true)) {
                state.tokenize = tokenTop;
                return cont("tag", null);
            }

            if (stream.match(leftDelimiter, true)) {
                state.depth++;
                return cont("tag", "startTag");
            }

            var ch = stream.next();
            if (ch == "$") {
                stream.eatWhile(regs.validIdentifier);
                return cont("variable-2", "variable");
            } else if (ch == "|") {
                return cont("operator", "pipe");
            } else if (ch == ".") {
                return cont("operator", "property");
            } else if (ch == ":") {
                return cont("operator", "operator");
            } else if (regs.stringChar.test(ch)) {
                state.tokenize = tokenAttribute(ch);
                return cont("string", "string");
            } else if (regs.operatorChars.test(ch)) {
                stream.eatWhile(regs.operatorChars);
                return cont("operator", "operator");
            } else if (ch == "[" || ch == "]") {
                return cont("bracket", "bracket");
            } else if (ch == "(" || ch == ")") {
                return cont("bracket", "operator");
            } else if (/\d/.test(ch)) {
                stream.eatWhile(/\d/);
                return cont("number", "number");
            } else {

                if (state.last == "variable") {
                    if (ch == "|") {
                        stream.eatWhile(regs.validIdentifier);
                        return cont("qualifier", "modifier");
                    }
                } else if (state.last == "pipe") {
                    stream.eatWhile(regs.validIdentifier);
                    return cont("qualifier", "modifier");
                } else if (state.last == "modifier") {
                    stream.eatWhile(regs.validFilter);
                    return cont("keyword", "keyword");
                } else if (state.last == "startTag") {
                    stream.eatWhile(regs.validIdentifier);
                    return cont("keyword", "keyword");
                } if (state.last == "property") {
                    stream.eatWhile(regs.validIdentifier);
                    return cont("property", null);
                } else if (/\s/.test(ch)) {
                    last = "whitespace";
                    return null;
                }

                var str = "";
                if (ch != "/") {
                    str += ch;
                }
                var c = null;
                while (c = stream.eat(regs.validIdentifier)) {
                    str += c;
                }
                if (/\s/.test(ch)) {
                    return null;
                }
                return cont("attribute", "attribute");
            }
        }

        function tokenAttribute(quote) {
            return function(stream, state) {
                var prevChar = null;
                var currChar = null;
                while (!stream.eol()) {
                    currChar = stream.peek();
                    if (stream.next() == quote && prevChar !== '\\') {
                        state.tokenize = tokenlatte;
                        break;
                    }
                    prevChar = currChar;
                }
                return "string";
            };
        }

        function tokenBlock(style, terminator) {
            return function(stream, state) {
                while (!stream.eol()) {
                    if (stream.match(terminator)) {
                        state.tokenize = tokenTop;
                        break;
                    }
                    stream.next();
                }
                return style;
            };
        }

        return {
            startState: function() {
                return {
                    base: CodeMirror.startState(baseMode),
                    tokenize: tokenTop,
                    last: null,
                    depth: 0
                };
            },
            copyState: function(state) {
                return {
                    base: CodeMirror.copyState(baseMode, state.base),
                    tokenize: state.tokenize,
                    last: state.last,
                    depth: state.depth
                };
            },
            innerMode: function(state) {
                if (state.tokenize == tokenTop)
                    return {mode: baseMode, state: state.base};
            },
            token: function(stream, state) {
                var style = state.tokenize(stream, state);
                state.last = last;
                return style;
            },
            indent: function(state, text) {
                if (state.tokenize == tokenTop && baseMode.indent)
                    return baseMode.indent(state.base, text);
                else
                    return CodeMirror.Pass;
            },
            blockCommentStart: leftDelimiter + "*",
            blockCommentEnd: "*" + rightDelimiter
        };
    });

    CodeMirror.defineMIME("text/x-latte", "latte");
});