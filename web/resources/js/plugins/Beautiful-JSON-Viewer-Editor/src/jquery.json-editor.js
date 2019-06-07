(function ($) {
    function encodeJSONStr(str) {
        var encodeMap = {
            '"': '\\"',
            '\\': '\\\\',
            '\b': '\\b',
            '\f': '\\f',
            '\n': '\\n',
            '\r': '\\r',
            '\t': '\\t'
        };

      return str.replace(/["\\\b\f\n\r\t]/g, function (match) {
          return encodeMap[match];
      });
    }

    function encodeJSON(json) {
        if (typeof json === 'string') {
            return encodeJSONStr(json);
        } else if (typeof json === 'object') {
            for (var attr in json) {
                json[attr] = encodeJSON(json[attr]);
            }
        } else if (Array.isArray(json)) {
            for (var i = 0; i < json.length; i++) {
                json[i] = encodeJSON(json[i]);
            }
        }

        return json;
    }

    function JsonEditor(container, json, options) {
        options = options || {};
        if (options.editable !== false) {
            options.editable = true;
        }

        this.$container = $(container);
        this.options = options;

        this.load(json);
    }

    JsonEditor.prototype = {
        constructor: JsonEditor,
        load: function (json) {
            this.$container.jsonViewer(encodeJSON(json), {
                collapsed: this.options.defaultCollapsed,
                withQuotes: true
            })
            .addClass('json-editor-blackbord')
            .attr('contenteditable', !!this.options.editable);
        },
        get: function () {
            try{
                this.$container.find('.collapsed').click();
                return JSON.parse(this.$container.text());
            } catch (ex) {
                throw new Error(ex);
            }
        }
    }

    window.JsonEditor = JsonEditor;
})(jQuery);