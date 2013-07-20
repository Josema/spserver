window.alert = function(hoge){
    if(confirm(hoge)){
    }else{
	throw "";
    }
}

function check(code, string){
    var logElm = document.getElementById("log");
    if(ULZSS.decode(code) != string){
	logElm.innerHTML += string + " error!\n";
	logElm.innerHTML += ULZSS.decode(code) + "\n";
    }else{
	logElm.innerHTML += string + " ok!\n";
    }
}

function check_convert(string){
	var logElm = document.getElementById("log");
    var code = ULZSS.encode(string);
	var decode = ULZSS.decode(code);
	
	var ascii = strToAscii(code);
	var asciidecode = asciiToStr(ascii);
    
    if(decode != string){
		logElm.innerHTML += string + " error!\n";
		logElm.innerHTML += ULZSS.decode(code) + "\n";
    }else{
		logElm.innerHTML +=
			"DECODE       [" + decode.length + " " + "100%] " + decode + "\n" +
			"ENCODE       [" + code.length + " " + Math.round((code.length*100)/decode.length) + "%] " + code + "\n" +
			"ASCII        [" + ascii.length + " " + Math.round((ascii.length*100)/decode.length) + "%] " + ascii + "\n" +
			"ASCII DECODE [" + asciidecode.length + " " + Math.round((asciidecode.length*100)/decode.length) + "%] " + asciidecode + "\n" +
			"\n\n----------------------------\n\n";
    }
}




function ord (string) {
    // http://kevin.vanzonneveld.net
    // +   original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +   bugfixed by: Onno Marsman
    // +   improved by: Brett Zamir (http://brett-zamir.me)
    // +   input by: incidence
    // *     example 1: ord('K');
    // *     returns 1: 75
    // *     example 2: ord('\uD800\uDC00'); // surrogate pair to create a single Unicode character
    // *     returns 2: 65536
    var str = string + '',
        code = str.charCodeAt(0);
    if (0xD800 <= code && code <= 0xDBFF) { // High surrogate (could change last hex to 0xDB7F to treat high private surrogates as single characters)
        var hi = code;
        if (str.length === 1) {
            return code; // This is just a high surrogate with no following low surrogate, so we return its value;
            // we could also throw an error as it is not a complete character, but someone may want to know
        }
        var low = str.charCodeAt(1);
        return ((hi - 0xD800) * 0x400) + (low - 0xDC00) + 0x10000;
    }
    if (0xDC00 <= code && code <= 0xDFFF) { // Low surrogate
        return code; // This is just a low surrogate with no preceding high surrogate, so we return its value;
        // we could also throw an error as it is not a complete character, but someone may want to know
    }
    return code;
}
function chr (codePt) {
    // http://kevin.vanzonneveld.net
    // +   original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +   improved by: Brett Zamir (http://brett-zamir.me)
    // *     example 1: chr(75);
    // *     returns 1: 'K'
    // *     example 1: chr(65536) === '\uD800\uDC00';
    // *     returns 1: true
    if (codePt > 0xFFFF) { // Create a four-byte string (length 2) since this code point is high
        //   enough for the UTF-16 encoding (JavaScript internal use), to
        //   require representation with two surrogates (reserved non-characters
        //   used for building other characters; the first is "high" and the next "low")
        codePt -= 0x10000;
        return String.fromCharCode(0xD800 + (codePt >> 10), 0xDC00 + (codePt & 0x3FF));
    }
    return String.fromCharCode(codePt);
}





function strToAscii(str)
{
	var decstr = '';
 	for(k=0; k<str.length; k++)
	{
		
      decstr = decstr+rellena(ord(str[k]),3);
	}

	
	return decstr;
}
function asciiToStr(str)
{
	var decstr = '';
 	for(k=0; k<(str.length); k+=3)
	{
		var asciicode = str.substr(k,3);
		decstr += chr(asciicode);

	}

	
	return decstr;
}


function rellena(str, n, c)
{
	if (c == null) c = '0';
	for ( i=0,nstr = ''; (n-(str.toString()).length) > i; ++i)
		nstr += c;

	return nstr + str;
}





