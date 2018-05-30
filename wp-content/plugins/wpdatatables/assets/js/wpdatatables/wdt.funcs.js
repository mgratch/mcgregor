jQuery(document).ready(function ($) {

    var wdtDateFormat = getMomentWdtDateFormat();
    var wdtTimeFormat = getMomentWdtTimeFormat();

    /**
     * Hide preloader on document ready
     */
    jQuery('.wdt-preload-layer').animateFadeOut();

    if (typeof wpdatatables_frontend_strings !== 'undefined') {
        $.fn.DataTable.defaults.oLanguage.sInfo = wpdatatables_frontend_strings.sInfo;
        $.fn.DataTable.defaults.oLanguage.sSearch = wpdatatables_frontend_strings.sSearch;
        $.fn.DataTable.defaults.oLanguage.lengthMenu = wpdatatables_frontend_strings.lengthMenu;
        $.fn.DataTable.defaults.oLanguage.sEmptyTable = wpdatatables_frontend_strings.sEmptyTable;
        $.fn.DataTable.defaults.oLanguage.sInfoEmpty = wpdatatables_frontend_strings.sInfoEmpty;
        $.fn.DataTable.defaults.oLanguage.sInfoFiltered = wpdatatables_frontend_strings.sInfoFiltered;
        $.fn.DataTable.defaults.oLanguage.sInfoPostFix = wpdatatables_frontend_strings.sInfoPostFix;
        $.fn.DataTable.defaults.oLanguage.sInfoThousands = wpdatatables_frontend_strings.sInfoThousands;
        $.fn.DataTable.defaults.oLanguage.sLengthMenu = wpdatatables_frontend_strings.sLengthMenu;
        $.fn.DataTable.defaults.oLanguage.sProcessing = wpdatatables_frontend_strings.sProcessing;
        $.fn.DataTable.defaults.oLanguage.sZeroRecords = wpdatatables_frontend_strings.sZeroRecords;
        $.fn.DataTable.defaults.oLanguage.oPaginate = wpdatatables_frontend_strings.oPaginate;
        $.fn.DataTable.defaults.oLanguage.oAria = wpdatatables_frontend_strings.oAria;
    }


    if (typeof ($.fn.dataTableExt) !== 'undefined') {

        /* Clear filters */
        $.fn.dataTableExt.oApi.fnFilterClear = function (oSettings) {
            /* Remove global filter */
            oSettings.oPreviousSearch.sSearch = "";

            /* Remove the text of the global filter in the input boxes */
            if (typeof oSettings.aanFeatures.f != 'undefined') {
                var n = oSettings.aanFeatures.f;
                for (var i = 0, iLen = n.length; i < iLen; i++) {
                    $('input', n[i]).val('');
                }
            }
            /* Remove the search text for the column filters - NOTE - if you have input boxes for these
             * filters, these will need to be reset
             */
            for
            (var i = 0, iLen = oSettings.aoPreSearchCols.length; i < iLen; i++) {
                oSettings.aoPreSearchCols[i].sSearch = "";
            }

            /* Redraw */
            oSettings.oApi._fnReDraw(oSettings);
        };

        $.extend($.fn.dataTableExt.oSort, {
            "formatted-num-pre": function (a) {
                if ($(a).text() != '') {
                    a = $(a).text();
                }
                a = (a === "-" || a === "") ? 0 : a.replace(/[^\d\-\.]/g, "");

                if (a != -1 && a != "") {
                    while (a.indexOf('.') != -1) {
                        a = a.replace(".", "");
                    }

                    a = a.replace(',', '.');

                }

                return parseFloat(a);
            },

            "formatted-num-asc": function (a, b) {
                return a - b;
            },

            "formatted-num-desc": function (a, b) {
                return b - a;
            },

            "statuscol-pre": function (a) {

                a = $(a).find('div.percents').text();
                return parseFloat(a);
            },

            "statuscol-asc": function (a, b) {
                return a - b;
            },

            "statuscol-desc": function (a, b) {
                return b - a;
            },

            "date-custom-pre": function (date) {
                return wdtPrepareDate(date, wdtDateFormat, wdtTimeFormat);
            },

            "time-custom-pre": function (time) {
                return wdtPrepareTime(time, wdtTimeFormat);
            }

        });

        $.fn.dataTableExt.oApi.fnGetColumnIndex = function (oSettings, sCol) {
            var cols = oSettings.aoColumns;
            for (var x = 0, xLen = cols.length; x < xLen; x++) {
                if ((typeof(cols[x].sTitle) == 'string') && ( cols[x].sTitle.toLowerCase() == sCol.toLowerCase() )) {
                    return x;
                }
            }
            return -1;
        };

        // This will help DataTables magic detect the "dd-MMM-yyyy" format; Unshift so that it's the first data type (so it takes priority over existing)
        $.fn.dataTableExt.aTypes.unshift(
            function (sData) {
                "use strict"; //let's avoid tom-foolery in this function
                if (/^([0-2]?\d|3[0-1])-(jan|feb|mar|apr|may|jun|jul|aug|sep|oct|nov|dec)-\d{4}/i.test(sData)) {
                    return 'date-dd-mmm-yyyy';
                }
                return null;
            }
        );

        // define the sorts
        $.fn.dataTableExt.oSort['date-dd-mmm-yyyy-asc'] = function (a, b) {
            "use strict"; //let's avoid tom-foolery in this function
            var ordA = wdtCustomDateDDMMMYYYYToOrd(a),
                ordB = wdtCustomDateDDMMMYYYYToOrd(b);
            return (ordA < ordB) ? -1 : ((ordA > ordB) ? 1 : 0);
        };

        $.fn.dataTableExt.oSort['date-dd-mmm-yyyy-desc'] = function (a, b) {
            "use strict"; //let's avoid tom-foolery in this function
            var ordA = wdtCustomDateDDMMMYYYYToOrd(a),
                ordB = wdtCustomDateDDMMMYYYYToOrd(b);
            return (ordA < ordB) ? 1 : ((ordA > ordB) ? -1 : 0);
        };

    }

    /**
     * Apply datetimepicker
     */
    // Datepicker
    $('body').on('focus', '.wdt-datepicker', function () {
        $(this).datetimepicker(
            {
                format: wdtDateFormat,
                showClear: true,
                keepOpen: true,
                useCurrent: false
            }
        )
            .off('dp.show')
            .on('dp.show', function () {
                $(this).parent().find('div.bootstrap-datetimepicker-widget').addClass('wdt-datetimepicker-modal');
                wdtAddTodayPlaceholder($(this));
            });
    });

    // Timepicker
    $('body').on('focus', '.wdt-timepicker', function () {
        $(this).datetimepicker(
            {
                format: wdtTimeFormat,
                showClear: true,
                keepOpen: true,
                useCurrent: false
            }
        )
            .off('dp.show')
            .on('dp.show', function () {
                $(this).parent().find('div.bootstrap-datetimepicker-widget').addClass('wdt-datetimepicker-modal');
            });
    });

    // Datetimepicker
    $('body').on('focus', '.wdt-datetimepicker', function () {
        $(this).datetimepicker(
            {
                format: wdtDateFormat + ' ' + wdtTimeFormat,
                showClear: true,
                keepOpen: true,
                useCurrent: false
            }
        )
            .off('dp.show')
            .on('dp.show', function () {
                $(this).parent().find('div.bootstrap-datetimepicker-widget').addClass('wdt-datetimepicker-modal');
                wdtAddTodayPlaceholder($(this));
            });
    });

});


var wdtCustomDateDDMMMYYYYToOrd = function (date) {
    "use strict"; //let's avoid tom-foolery in this function
    // Convert to a number YYYYMMDD which we can use to order
    var dateParts = date.split(/-/);
    return (dateParts[2] * 10000) + (jQuery.inArray(dateParts[1].toUpperCase(), ["JAN", "FEB", "MAR", "APR", "MAY", "JUN", "JUL", "AUG", "SEP", "OCT", "NOV", "DEC"]) * 100) + dateParts[0];
};

function wdtValidateURL(textval) {
    var regex = /^([a-z]([a-z]|\d|\+|-|\.)*):(\/\/(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?((\[(|(v[\da-f]{1,}\.(([a-z]|\d|-|\.|_|~)|[!\$&'\(\)\*\+,;=]|:)+))\])|((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=])*)(:\d*)?)(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*|(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)|((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)|((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)){0})(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(\#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?(||.*)?$/i;
    return regex.test(textval);
}

function wdtPrepareDate(date, wdtDateFormat, wdtTimeFormat) {
    var format = wdtDateFormat + ' ' + wdtTimeFormat;
    return moment(date, format).valueOf();
}

function wdtPrepareTime(time, wdtTimeFormat) {
    return moment(time, wdtTimeFormat).valueOf();
}

function wdtValidateEmail(email) {
    var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+(||.*)?$/;
    return regex.test(email);
}

function wdtRandString(n) {
    if (!n) {
        n = 5;
    }

    var text = '';
    var possible = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';

    for (var i = 0; i < n; i++) {
        text += possible.charAt(Math.floor(Math.random() * possible.length));
    }

    return text;
}

function wdtFormatNumber(n, c, d, t) {
    c = isNaN(c = Math.abs(c)) ? 2 : c,
        d = d == undefined ? "." : d,
        t = t == undefined ? "," : t,
        s = n < 0 ? "-" : "",
        i = parseInt(n = Math.abs(+n || 0).toFixed(c)) + "",
        j = (j = i.length) > 3 ? j % 3 : 0;
    return s + (j ? i.substr(0, j) + t : "") + i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + t) + (c ? d + Math.abs(n - i).toFixed(c).slice(2) : "");
}

function wdtUnformatNumber(number, thousandsSeparator, decimalsSeparator, isFloat) {
    if (typeof isFloat == 'undefined') {
        isFloat = false;
    }

    var return_string = String(number).replace(new RegExp('\\' + thousandsSeparator, 'g'), '');

    if (isFloat && decimalsSeparator == ',') {
        return_string = return_string.replace(new RegExp('\\' + decimalsSeparator), '.');
    }
    return return_string;
}

function wdtCalculateColumnSum(columnData, thousandsSeparator) {
    if (columnData.length > 0) {
        if (thousandsSeparator == '.') {
            var sum = columnData.reduce(function (a, b) {
                return parseFloat(a) + parseFloat(b.replace(/\./g, ''));
            }, 0);
        } else {
            sum = columnData.reduce(function (a, b) {
                return parseFloat(a) + parseFloat(b.replace(/\,/g, ''));
            }, 0);
        }
    } else {
        sum = 0;
    }

    return parseFloat(sum);
}

function wdtCalculateColumnMin(columnData, thousandsSeparator) {
    if (columnData.length > 0) {
        if (thousandsSeparator == '.') {
            var min = columnData.reduce(function (a, b) {
                return parseInt(typeof(a) == 'number' ? a : a.replace(/\./g, '')) <= parseInt(b.replace(/\./g, '')) ?
                    parseFloat(typeof(a) == 'number' ? a : a.replace(/\./g, '')) :
                    parseFloat(b.replace(/\./g, ''));
            });
        } else {
            min = columnData.reduce(function (a, b) {
                return parseInt(typeof(a) == 'number' ? a : a.replace(/\,/g, '')) <= parseInt(b.replace(/\,/g, '')) ?
                    parseFloat(typeof(a) == 'number' ? a : a.replace(/\,/g, '')) :
                    parseFloat(b.replace(/\,/g, ''));
            });
        }
    } else {
        min = 0;
    }

    return min;
}

function wdtCalculateColumnMax(columnData, thousandsSeparator) {
    if (columnData.length > 0) {
        if (thousandsSeparator == '.') {
            var max = columnData.reduce(function (a, b) {
                return parseInt(typeof(a) == 'number' ? a : a.replace(/\./g, '')) >= parseInt(b.replace(/\./g, '')) ?
                    parseFloat(typeof(a) == 'number' ? a : a.replace(/\./g, '')) :
                    parseFloat(b.replace(/\./g, ''));
            });
        } else {
            max = columnData.reduce(function (a, b) {
                return parseInt(typeof(a) == 'number' ? a : a.replace(/\,/g, '')) >= parseInt(b.replace(/\,/g, '')) ?
                    parseFloat(typeof(a) == 'number' ? a : a.replace(/\,/g, '')) :
                    parseFloat(b.replace(/\,/g, ''));
            });
        }
    } else {
        max = 0;
    }

    return max;
}

function wdtFormatNumberByColumnType(number, columnType, columnDecimalPlaces, generalDecimalPlaces, decimalSeparator, thousandsSeparator) {
    var numStr = '';
    if (columnType == 'int') {
        numStr = wdtFormatNumber(number, 0, decimalSeparator, thousandsSeparator);
    } else {
        if (columnDecimalPlaces != -1) {
            numStr = wdtFormatNumber(number, columnDecimalPlaces, decimalSeparator, thousandsSeparator);
        } else {
            numStr = wdtFormatNumber(number, generalDecimalPlaces, decimalSeparator, thousandsSeparator);
        }
    }
    return numStr;
}

function wdtFillPossibleValuesList(distValues) {
    if (jQuery('#wdt-column-values-list').tagsinput('items').length > 0) {
        jQuery('#wdt-possible-values-merge-list-modal').modal('show');

        jQuery('.wdt-merge-possible-values').click(function (e) {
            e.preventDefault();
            e.stopImmediatePropagation();

            var oldListValues = jQuery.extend([], jQuery('#wdt-column-values-list').tagsinput('items'));
            var mergedValues = jQuery.merge(oldListValues, distValues);

            jQuery('#wdt-column-values-list').tagsinput('removeAll');
            mergedValues.sort();
            mergedValues = jQuery.unique(mergedValues);
            jQuery('#wdt-column-values-list').tagsinput('add', mergedValues.join('|'));
            jQuery('#wdt-possible-values-merge-list-modal').modal('hide');
        });

        jQuery('.wdt-replace-possible-values').click(function (e) {
            e.preventDefault();
            e.stopImmediatePropagation();

            jQuery('#wdt-column-values-list').tagsinput('removeAll');
            distValues.sort();
            jQuery('#wdt-column-values-list').tagsinput('add', distValues.join('|'));
            jQuery('#wdt-possible-values-merge-list-modal').modal('hide');
        });
    } else {
        distValues.sort();
        jQuery('#wdt-column-values-list').tagsinput('add', distValues.join('|'));
    }
}

/**
 * Add today button to date/datetime picker for conditional formatting
 */
function wdtAddTodayPlaceholder(input) {
    // Add %TODAY% to datepicker
    if (input.hasClass("formatting-rule-cell-value")) {
        var todayPlaceholder =
            '<div class="col-sm-12 text-center p-b-15">' +
            '<button class="btn btn-primary p-5 btn-xs today-placeholder" data-toggle="tooltip" data-placement="top" title="By settings %TODAY% placeholder, cell value will be compared with today\'s date.">%TODAY%</button>' +
            '</div>';

        jQuery('.datepicker').closest("ul.list-unstyled").append(todayPlaceholder);
        jQuery('.today-placeholder').tooltip();

        // Set %TODAY% as conditional formatting rule value
        jQuery('.today-placeholder').click(function () {
            jQuery('.formatting-rule-cell-value').val('%TODAY%');
            jQuery(this).closest('.form-group').find('.formatting-rule-cell-value').data("DateTimePicker").hide();
        });
    }
}

function getMomentWdtDateFormat () {
    return wpdatatables_settings.wdtDateFormat.replace('d', 'DD').replace('M', 'MMM').replace('m', 'MM').replace('y', 'YY');
}

function getMomentWdtTimeFormat () {
    return wpdatatables_settings.wdtTimeFormat.replace('H', 'HH').replace('i', 'mm');
}