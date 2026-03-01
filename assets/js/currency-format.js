// The library's settings configuration object. Contains default parameters for
// currency and number formatting
const settings = {
    currency: {
        symbol : "$",		// default currency symbol is '$'
        format : "%s%v",	// controls output: %s = symbol, %v = value (can be object, see docs)
        decimal : ".",		// decimal point separator
        thousand : ",",		// thousands separator
        precision : 2,		// decimal places
        grouping : 3		// digit grouping (not implemented yet)
    },
    number: {
        precision : 0,		// default precision on numbers is 0
        grouping : 3,		// digit grouping (not implemented yet)
        thousand : ",",
        decimal : "."
    }
};

// Store reference to possibly-available ECMAScript 5 methods for later
var nativeMap = Array.prototype.map,
nativeIsArray = Array.isArray,
toString = Object.prototype.toString;

/**
 * Format a number into currency
 *
 * Usage: accounting.formatMoney(number, symbol, precision, thousandsSep, decimalSep, format)
 * defaults: (0, "$", 2, ",", ".", "%s%v")
 *
 * Localise by overriding the symbol, precision, thousand / decimal separators and format
 * Second param can be an object matching `settings.currency` which is the easiest way.
 *
 * To do: tidy up the parameters
 */
function formatMoney(number, symbol, precision, thousand, decimal, format) {
    // Resursively format arrays:
    if (isArray(number)) {
        return map(number, function(val){
            return formatMoney(val, symbol, precision, thousand, decimal, format);
        });
    }

    // Clean up number:
    number = unformat(number);

    // Build options object from second param (if object) or all params, extending defaults:
    var opts = defaults(
            (isObject(symbol) ? symbol : {
                symbol : symbol,
                precision : precision,
                thousand : thousand,
                decimal : decimal,
                format : format
            }),
            settings.currency
        ),

        // Check format (returns object with pos, neg and zero):
        formats = checkCurrencyFormat(opts.format),

        // Choose which format to use for this value:
        useFormat = number > 0 ? formats.pos : number < 0 ? formats.neg : formats.zero;

    // Return with currency symbol added:
    return useFormat.replace('%s', opts.symbol).replace('%v', formatNumber(Math.abs(number), checkPrecision(opts.precision), opts.thousand, opts.decimal));
};

/**
 * Tests whether supplied parameter is a string
 * from underscore.js, delegates to ECMA5's native Array.isArray
 */
function isArray(obj) {
    return nativeIsArray ? nativeIsArray(obj) : toString.call(obj) === '[object Array]';
}

/**
 * Check and normalise the value of precision (must be positive integer)
 */
function checkPrecision(val, base) {
    val = Math.round(Math.abs(val));
    return isNaN(val)? base : val;
}

/**
 * Implementation of toFixed() that treats floats more like decimals
 *
 * Fixes binary rounding issues (eg. (0.615).toFixed(2) === "0.61") that present
 * problems for accounting- and finance-related software.
 */
var toFixed = function(value, precision) {
    precision = checkPrecision(precision, settings.number.precision);
    var power = Math.pow(10, precision);

    // Multiply up by precision, round accurately, then divide and use native toFixed():
    return (Math.round(unformat(value) * power) / power).toFixed(precision);
};

/**
 * Tests whether supplied parameter is a string
 * from underscore.js
 */
function isString(obj) {
    return !!(obj === '' || (obj && obj.charCodeAt && obj.substr));
}

/**
 * Tests whether supplied parameter is a true object
 */
function isObject(obj) {
    return obj && toString.call(obj) === '[object Object]';
}

/**
 * Extends an object with a defaults object, similar to underscore's _.defaults
 *
 * Used for abstracting parameter handling from API methods
 */
function defaults(object, defs) {
    var key;
    object = object || {};
    defs = defs || {};
    // Iterate over object non-prototype properties:
    for (key in defs) {
        if (defs.hasOwnProperty(key)) {
            // Replace values with defaults only if undefined (allow empty/zero values):
            if (object[key] == null) object[key] = defs[key];
        }
    }
    return object;
}

/**
 * Format a number, with comma-separated thousands and custom precision/decimal places
 * Alias: `accounting.format()`
 *
 * Localise by overriding the precision and thousand / decimal separators
 * 2nd parameter `precision` can be an object matching `settings.number`
 */
var formatNumber = function(number, precision, thousand, decimal) {
    // Resursively format arrays:
    if (isArray(number)) {
        return map(number, function(val) {
            return formatNumber(val, precision, thousand, decimal);
        });
    }

    // Clean up number:
    number = unformat(number);

    // Build options object from second param (if object) or all params, extending defaults:
    var opts = defaults(
            (isObject(precision) ? precision : {
                precision : precision,
                thousand : thousand,
                decimal : decimal
            }),
            settings.number
        ),

        // Clean up precision
        usePrecision = checkPrecision(opts.precision),

        // Do some calc:
        negative = number < 0 ? "-" : "",
        base = parseInt(toFixed(Math.abs(number || 0), usePrecision), 10) + "",
        mod = base.length > 3 ? base.length % 3 : 0;

    // Format the number:
    return negative + (mod ? base.substr(0, mod) + opts.thousand : "") + base.substr(mod).replace(/(\d{3})(?=\d)/g, "$1" + opts.thousand) + (usePrecision ? opts.decimal + toFixed(Math.abs(number), usePrecision).split('.')[1] : "");
};

/**
 * Takes a string/array of strings, removes all formatting/cruft and returns the raw float value
 * Alias: `accounting.parse(string)`
 *
 * Decimal must be included in the regular expression to match floats (defaults to
 * accounting.settings.number.decimal), so if the number uses a non-standard decimal
 * separator, provide it as the second argument.
 *
 * Also matches bracketed negatives (eg. "$ (1.99)" => -1.99)
 *
 * Doesn't throw any errors (`NaN`s become 0) but this may change in future
 */
var unformat = function(value, decimal) {
    // Recursively unformat arrays:
    if (isArray(value)) {
        return map(value, function(val) {
            return unformat(val, decimal);
        });
    }

    // Fails silently (need decent errors):
    value = value || 0;

    // Return the value as-is if it's already a number:
    if (typeof value === "number") return value;

    // Default decimal point comes from settings, but could be set to eg. "," in opts:
    decimal = decimal || settings.number.decimal;

        // Build regex to strip out everything except digits, decimal point and minus sign:
    var regex = new RegExp("[^0-9-" + decimal + "]", ["g"]),
        unformatted = parseFloat(
            ("" + value)
            .replace(/\((.*)\)/, "-$1") // replace bracketed values with negatives
            .replace(regex, '')         // strip out any cruft
            .replace(decimal, '.')      // make sure decimal point is standard
        );

    // This will fail silently which may cause trouble, let's wait and see:
    return !isNaN(unformatted) ? unformatted : 0;
};

/**
 * Parses a format string or object and returns format obj for use in rendering
 *
 * `format` is either a string with the default (positive) format, or object
 * containing `pos` (required), `neg` and `zero` values (or a function returning
 * either a string or object)
 *
 * Either string or format.pos must contain "%v" (value) to be valid
 */
function checkCurrencyFormat(format) {
    var defaults = settings.currency.format;

    // Allow function as format parameter (should return string or object):
    if ( typeof format === "function" ) format = format();

    // Format can be a string, in which case `value` ("%v") must be present:
    if ( isString( format ) && format.match("%v") ) {

        // Create and return positive, negative and zero formats:
        return {
            pos : format,
            neg : format.replace("-", "").replace("%v", "-%v"),
            zero : format
        };

    // If no format, or object is missing valid positive value, use defaults:
    } else if ( !format || !format.pos || !format.pos.match("%v") ) {

        // If defaults is a string, casts it to an object for faster checking next time:
        return ( !isString( defaults ) ) ? defaults : settings.currency.format = {
            pos : defaults,
            neg : defaults.replace("%v", "-%v"),
            zero : defaults
        };

    }
    // Otherwise, assume format was fine:
    return format;
}

const formatPrice = ( price, currencySymbol = '' ) => {
    return formatMoney( price, {
        symbol:    currencySymbol ? currencySymbol : ddwcafCurrencyObject.currency_format_symbol,
        decimal:   ddwcafCurrencyObject.currency_format_decimal_sep,
        thousand:  ddwcafCurrencyObject.currency_format_thousand_sep,
        precision: ddwcafCurrencyObject.currency_format_num_decimals,
        format:    ddwcafCurrencyObject.currency_format
    } );
}

