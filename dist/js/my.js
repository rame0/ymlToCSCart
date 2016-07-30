/*
 * Copyright (C) 2016 R@Me0 <r@me0.biz>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */


$(document).ready(function () {
    'use strict';
    $('.dataModifer').dataModifer();
    $('form.field-mapping select.ympOption').each(function () {
        changeSampleData(this);
    });
    $('form.field-mapping select.ympOption').change(function () {
        changeSampleData(this);
    });
    $('button#savePreset').on('click', savePreset);

    function changeSampleData(element) {
        var $element = $(element);
        var $selOpt = $element.find("option:selected");
        var $parent = $element.parent();
        $parent.find(".sampleData, .sampleData span").show();
        if ($selOpt.val() === "-1") {
            $parent.children(".sampleData").hide();
            $element.siblings('.dataModifer').dataModifer('hide');
        } else if ($selOpt.val() === "text") {
            $parent.find(".sampleData span.title").hide();
            $element.siblings('.dataModifer').dataModifer('hide');
            $parent.find(".sampleData span.data").show().text('').append(
                    $("<input/>").prop({'class': "text form-control", "placeholder": "Текст для поля " + $parent.parent().find('label div').text()})
                    );
        } else {
            $parent.find(".sampleData span.data").text(Base64.decode($selOpt.data('sample')));
            $element.siblings('.dataModifer').dataModifer('show');
        }
    }
});

function savePreset() {
    var formFields = $('form.field-mapping > .form-group,form.field-mapping > div > .form-group');
    var data = [];
    formFields.each(function () {
        var $this = $(this);
        var $element = $this.find('.ympOption');

        if ($element.length > 0 && $element.val() !== '-1') {
            var elData = {csvField: $element.attr('name'), ymlField: $element.val()};


            var $constructor = $this.find('.constructor');
            var $settingsForm = $constructor.find('.settingsform');
            var $selType = $constructor.find('.selectType');

            if ($settingsForm.length > 0 && $selType.val() !== '-1') {
                var $inputs = $settingsForm.find('input');
                var constructorFields = [];
                $inputs.each(function () {
                    constructorFields.push({name: $(this).attr('name'), val: $(this).val()});
                });
                elData.modifer = {
                    type: $selType.val(),
                    params: constructorFields
                }
            }
            data.push(elData);
        }
    });

    var presetName = prompt('Enter name for preset\r\n !!! If file with this name already exists it will be overwritten !!!');

    if (presetName) {
        $.ajax({
            url: "savePreset.php",
            method: 'POST',
            data: {"data": data, fname: presetName}
        });
    }
}

/**
 * dataModifer object.
 * Creates constructor form to deal with raw YML data
 */

+function ($) {
    'use strict';
    // PUBLIC CLASS DEFINITION
    var DataModifer = function (element, options) {
        this.$element = $(element).hide();
        this.options = $.extend({}, DataModifer.DEFAULTS, options);
        this.$switcher = $("<input/>", {class: this.options.switcherClass, type: "checkbox"});
        var label = $("<label/>");
        label.html(' Enable data modifer');
        label.prepend(this.$switcher);
        this.$formwrapper = $('<div/>', {class: this.options.formwrapperClass});
        this.$element.append(label);
        this.$element.append(this.$formwrapper);
        this.$selectType = $('<select/>', {class: this.options.selectTypeClass, name: 'modiferTypeSelector'});
        this.$selectType.append($('<option/>', {value: '-1'}).text('--'));
        for (var key in options.forms) {
            this.$selectType.append($('<option/>', {value: key}).text(options.forms[key].name));
        }

        this.$selectType.appendTo(this.$formwrapper);

        this.hide();
        this.$formwrapper.hide();
        this.$switcher.on('change', $.proxy(this.switch, this));
        this.$selectType.on('change', $.proxy(this.changeType, this));
    };
    DataModifer.DEFAULTS = {
        switcherClass: "enableConstructor",
        formwrapperClass: "constructor",
        formContainerClass: "settingsform form-inline",
        selectTypeClass: 'selectType form-control',
        forms: {
            replace: {
                name: "Replace",
                fields: [
                    {tag: 'h4', text: 'Replace text'},
                    {tag: 'div', attrs: {class: 'form-group'},
                        childs: [
                            {tag: 'label', attrs: {for : 'what'}, text: 'What:'},
                            {tag: 'input', attrs: {name: 'what', class: 'form-control'}},
                        ]
                    },
                    {tag: 'div', attrs: {class: 'form-group'},
                        childs: [
                            {tag: 'label', attrs: {for : 'with'}, text: 'With:'},
                            {tag: 'input', attrs: {name: 'with', class: 'form-control'}},
                        ]
                    }
                ]
            }
        }
    };
    DataModifer.prototype.switch = function (event) {
        var $this = $(event.target);
        if ($this.is(":checked")) {
            this.showForm();
        } else {
            this.hideForm();
        }
    }
    DataModifer.prototype.show = function (element, data) {
        this.$element.show();
    };
    DataModifer.prototype.hide = function (element, data) {
        this.$element.hide();
    };
    DataModifer.prototype.showForm = function (element, data) {
        this.$settingsForm = $("<div/>", {class: this.options.formContainerClass}).appendTo(this.$formwrapper);
        this.$formwrapper.show();
    };
    DataModifer.prototype.hideForm = function (element, data) {
        this.$settingsForm.remove();
        this.$selectType[0].selectedIndex = 0;
        this.$formwrapper.hide();
    };
    DataModifer.prototype.changeType = function (event, data) {
        var $this = $(event.target);
        var formType = $this.val();
        this.$settingsForm.html('');
        if (this.options.forms.hasOwnProperty(formType)) {
            this.constructActionInputs(this.$settingsForm, this.options.forms[formType].fields);
        }

    };
    DataModifer.prototype.fill = function (element, data) {

    };

    DataModifer.prototype.constructActionInputs = function ($parent, fields) {
        for (var i = 0; i < fields.length; i++) {
            var $fld = $("<" + fields[i].tag + "/>", fields[i].attrs);
            if (fields[i].text) {
                $fld.text(fields[i].text);
            }
            if (fields[i].childs) {
                this.constructActionInputs($fld, fields[i].childs);
            }
            $parent.append($fld);
        }
    }

// PLUGIN DEFINITION
    function Plugin(option, _args) {
        return this.each(function () {
            var $this = $(this);
            var data = $this.data('DataModifer');
            if (data && data[option]) {
                return data[option].apply(data, [Array.prototype.slice.call(arguments, 1), _args]);
            } else if (typeof option === 'object' || !option) {
                var options = $.extend({}, DataModifer.DEFAULTS, $this.data(), typeof option === 'object' && option)
                if (!data)
                    $this.data('DataModifer', (data = new DataModifer(this, options)));
            }
        });
    }

    var old = $.fn.dataModifer;
    $.fn.dataModifer = Plugin;
    $.fn.dataModifer.Constructor = DataModifer;
//NO CONFLICT

    $.fn.dataModifer.noConflict = function () {
        $.fn.dataModifer = old;
        return this;
    }

}(jQuery);
/**
 *
 * Base64 encode/decode
 * http://www.webtoolkit.info
 *
 **/

var Base64 = {
    _keyStr: "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=",
    //метод для кодировки в base64 на javascript
    encode: function (input) {
        var output = "";
        var chr1, chr2, chr3, enc1, enc2, enc3, enc4;
        var i = 0
        input = Base64._utf8_encode(input);
        while (i < input.length) {
            chr1 = input.charCodeAt(i++);
            chr2 = input.charCodeAt(i++);
            chr3 = input.charCodeAt(i++);
            enc1 = chr1 >> 2;
            enc2 = ((chr1 & 3) << 4) | (chr2 >> 4);
            enc3 = ((chr2 & 15) << 2) | (chr3 >> 6);
            enc4 = chr3 & 63;
            if (isNaN(chr2)) {
                enc3 = enc4 = 64;
            } else if (isNaN(chr3)) {
                enc4 = 64;
            }
            output = output +
                    this._keyStr.charAt(enc1) + this._keyStr.charAt(enc2) +
                    this._keyStr.charAt(enc3) + this._keyStr.charAt(enc4);
        }
        return output;
    },
    //метод для раскодировки из base64
    decode: function (input) {
        var output = "";
        var chr1, chr2, chr3;
        var enc1, enc2, enc3, enc4;
        var i = 0;
        input = input.replace(/[^A-Za-z0-9\+\/\=]/g, "");
        while (i < input.length) {
            enc1 = this._keyStr.indexOf(input.charAt(i++));
            enc2 = this._keyStr.indexOf(input.charAt(i++));
            enc3 = this._keyStr.indexOf(input.charAt(i++));
            enc4 = this._keyStr.indexOf(input.charAt(i++));
            chr1 = (enc1 << 2) | (enc2 >> 4);
            chr2 = ((enc2 & 15) << 4) | (enc3 >> 2);
            chr3 = ((enc3 & 3) << 6) | enc4;
            output = output + String.fromCharCode(chr1);
            if (enc3 != 64) {
                output = output + String.fromCharCode(chr2);
            }
            if (enc4 != 64) {
                output = output + String.fromCharCode(chr3);
            }
        }
        output = Base64._utf8_decode(output);
        return output;
    },
    // метод для кодировки в utf8
    _utf8_encode: function (string) {
        string = string.replace(/\r\n/g, "\n");
        var utftext = "";
        for (var n = 0; n < string.length; n++) {
            var c = string.charCodeAt(n);
            if (c < 128) {
                utftext += String.fromCharCode(c);
            } else if ((c > 127) && (c < 2048)) {
                utftext += String.fromCharCode((c >> 6) | 192);
                utftext += String.fromCharCode((c & 63) | 128);
            } else {
                utftext += String.fromCharCode((c >> 12) | 224);
                utftext += String.fromCharCode(((c >> 6) & 63) | 128);
                utftext += String.fromCharCode((c & 63) | 128);
            }
        }
        return utftext;
    },
    //метод для раскодировки из urf8
    _utf8_decode: function (utftext) {
        var string = "";
        var i = 0;
        var c = c1 = c2 = 0;
        while (i < utftext.length) {
            c = utftext.charCodeAt(i);
            if (c < 128) {
                string += String.fromCharCode(c);
                i++;
            } else if ((c > 191) && (c < 224)) {
                c2 = utftext.charCodeAt(i + 1);
                string += String.fromCharCode(((c & 31) << 6) | (c2 & 63));
                i += 2;
            } else {
                c2 = utftext.charCodeAt(i + 1);
                c3 = utftext.charCodeAt(i + 2);
                string += String.fromCharCode(((c & 15) << 12) | ((c2 & 63) << 6) | (c3 & 63));
                i += 3;
            }
        }
        return string;
    }
}