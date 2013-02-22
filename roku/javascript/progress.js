/*jslint white: true, browser: true, undef: true, nomen: true, eqeqeq: true, plusplus: false, bitwise: true, regexp: true, strict: true, newcap: true, immed: true, maxerr: 14 */
/*global window: false, ActiveXObject: false*/

/*
The onreadystatechange property is a function that receives the feedback. It is important to note that the feedback
function must be assigned before each send, because upon request completion the onreadystatechange property is reset.
This is evident in the Mozilla and Firefox source.
*/

/* enable strict mode */
"use strict";

// global variables
var progress_prog,			// progress element reference
request_prog,			// request object
intervalID_prog = false,	// interval ID
message_prog,
request_serv,			// request object
intervalID_serv = false,
serverDown = false;

// define reference to the progress bar and create XMLHttp request object
window.onload = function () {
    progress_prog = document.getElementById('progress');
    message_prog = document.getElementById('message');
    request_prog = initXMLHttpClient_prog();
}

// create XMLHttp request object in a cross-browser manner
function initXMLHttpClient_prog() {
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
            catch (e2) {}
        }
        if (!success) {
            throw new Error('Unable to create XMLHttpRequest!');
        }
    }
    return xmlhttp;
}

// send request to the server
function send_request_prog() {
    request_prog.open('GET', 'ajax-progress-bar.php', true);	// open asynchronus request
    request_prog.onreadystatechange = request_handler_prog;		// set request handler
    request_prog.send(null); // send request
}

// request handler (defined in send_request)
function request_handler_prog() {
    var level, text;
    if (request_prog.readyState === 4) { // if state = 4 (operation is completed)
        if (request_prog.status === 200) { // and the HTTP status is OK
            // get progress from the XML node and set progress bar width and innerHTML
            level = request_prog.responseXML.getElementsByTagName('progress')[0].firstChild;
            text = request_prog.responseXML.getElementsByTagName('message')[0].firstChild;
            //progress_prog.style.width = progress_prog.innerHTML = level.nodeValue + '%';
            $("#progressbar").progressbar({ value: parseInt(level.nodeValue) });
            message_prog.innerHTML = text.nodeValue;
            if (level.nodeValue == "100")
                polling_stop();
        }
        else { // if request status isn't OK
            progress_prog.style.width = '100%';
            progress_prog.innerHTML = 'Error: [' + request_prog.status + '] ' + request_prog.statusText;
        }
    }
}

// button actions (start / stop)
function polling_start() {
    if (!intervalID_prog) {
        intervalID_prog = window.setInterval('send_request_prog()', 500);
    }
}
function polling_stop() {
    window.clearInterval(intervalID_prog);
    intervalID_prog = false;
    if(navigator.platform.indexOf("Mac") == -1) {
        request_serv = initXMLHttpClient_prog();
        message_prog.innerHTML = "Server Restarting, Please wait";
        $('<style> .ui-progressbar-value { background-image: url(images/site/pbar-ani.gif); } #progressbar {  height: 22px; } </style>').appendTo("head");
        checkServer_start();
    }
}

// send request to the server
function send_request_serv() {
    request_serv.open('GET', 'ipCheck.php', true);	// open asynchronus request
    request_serv.onreadystatechange = request_handler_serv;		// set request handler
    request_serv.send(null); // send request
}

// request handler (defined in send_request)
function request_handler_serv() {
    if (request_serv.readyState === 4) { // if state = 4 (operation is completed)
        if (request_serv.status === 200) { // and the HTTP status is OK
            // get servress from the XML node and set servress bar width and innerHTML
            if(serverDown)
                checkServer_stop();
        }
        else { // if request status isn't OK
            serverDown = true;
        }
    }
}

// button actions (start / stop)
function checkServer_start() {
    if (!intervalID_serv) {
        intervalID_serv = window.setInterval('send_request_serv()', 500);
    }
}
function checkServer_stop() {
    window.clearInterval(intervalID_serv);
    intervalID_serv = false;
    $.jGrowl("Server Restart Complete");
    oTable.fnDraw(false);
    setTimeout("fadeInfo()",5000);
}
