// global variables
var progress,			// progress element reference
httpObject,			// request object
intervalID = false,	// interval ID
message;

function myAjax(phpFile, vars) {

    httpObject = initXMLHttpClient();
    this.response = "";
	
    var ajaxString = phpFile;

    if(vars.length > 0) {
        ajaxString += "?";
        for(var i=0; i < vars.length; i+=2) {
            if(i==0)
                ajaxString += vars[i] + "=" + vars[i+1];
            else
                ajaxString += "&" + vars[i] + "=" + vars[i+1];
        }
    }
	
    if (httpObject != null) {
        httpObject.open('GET', ajaxString, true);	// open asynchronus request
        httpObject.onreadystatechange = request_handler;		// set request handler
        httpObject.send(null); // send request
    } else {
        alert('HttpObject not created for AJAX call');
    }
}

// create XMLHttp request object in a cross-browser manner
function initXMLHttpClient() {
    
    var XMLHTTP_IDS,
    xmlhttp,
    success = false,
    i;
    // Mozilla/Chrome/Safari/IE7/IE8 (normal browsers)
    try {
        xmlhttp = new XMLHttpRequest();
    }
    // IE(?!)
    catch (e1) {
        XMLHTTP_IDS = [ 'MSXML2.XMLHTTP.5.0', 'MSXML2.XMLHTTP.4.0',
        'MSXML2.XMLHTTP.3.0', 'MSXML2.XMLHTTP', 'Microsoft.XMLHTTP' ];
        for (i = 0; i < XMLHTTP_IDS.length && !success; i++) {
            try {
                success = true;
                xmlhttp = new ActiveXObject(XMLHTTP_IDS[i]);
            }
            catch (e2) {
                alert('ERROR');
            }
        }
        if (!success) {
            alert('Unable to create XMLHttpRequest!');
        }
    }
    return xmlhttp;
}

// request handler (defined in send_request)
function request_handler() {
    if (httpObject.readyState === 4) { // if state = 4 (operation is completed)
        //alert(httpObject.responseText);
        ajax_ready(httpObject.responseText);
    }
}