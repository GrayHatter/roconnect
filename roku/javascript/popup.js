/***************************/
//@Author: Adrian "yEnS" Mato Gondelle
//@website: www.yensdesign.com
//@email: yensamg@gmail.com
//@license: Feel free to use it, but keep this credits please!
/***************************/

//SETTING UP OUR POPUP
//0 means disabled; 1 means enabled;
var popupStatus = 0;

//loading popup with jQuery magic!
function loadPopup(data, page, playlistId){
	//loads popup only if it is disabled
	if(popupStatus==0){
                loadData(data, page, playlistId);
		$("#backgroundPopup").css({
			"opacity": "0.7"
		});
		$("#backgroundPopup").fadeIn("slow");
		$("#popupContact").fadeIn("slow");
		popupStatus = 1;
	}
}

function loadData(data, page, playlistId) {
    console.log("PlaylistID: " + playlistId);
    data.toString();
    var movie = String(data).split(',');
    
    $("#popupTitle").text(movie[7]);
    $("#popupSynopsis").text(String(movie[2]).replace(/&#44;/g, ','));
    $("#popupPoster").attr("src", movie[1]);
    $("#popupRank").text(((movie[11] == "") ? "0" : movie[11]) + "/100");
    $("#popupRuntime").text(movie[10]);
    $("#popupRating").text(movie[9]);
    $("#popupGenre").text(movie[8]);
    $("#popupYear").text(movie[5]);
    if (movie[12] == 'movie') {
        $("#director").show();
        $("#actors").show();
        $("#popupActors").text(String(movie[3]).replace(/&#44;/g, ','));
        $("#popupDirector").text(String(movie[4]).replace(/&#44;/g, ','));
        $("#artist").hide();
        $("#album").hide();
    } else {
        $("#artist").show();
        $("#album").show();
        $("#popupArtist").text(String(movie[3]).replace(/&#44;/g, ','));
        $("#popupAlbum").text(String(movie[4]).replace(/&#44;/g, ','));
        $("#director").hide();
        $("#actors").hide();
    }
    $("#popupFilename").text(String(movie[6]));
    $("#popupEdit").attr("href","/roku/edit.php?contentId="+movie[0]+"&playlistId="+playlistId+"&page="+page);
    $("#watchNow").attr("href", "watch.php?contentId="+movie[0]+"&playlistId="+playlistId+"&page="+page);
}

//disabling popup with jQuery magic!
function disablePopup(){
	//disables popup only if it is enabled
	if(popupStatus==1){
		$("#backgroundPopup").fadeOut("slow");
		$("#popupContact").fadeOut("slow");
		popupStatus = 0;
	}
}

//centering popup
function centerPopup(){
	//request data for centering
	var windowWidth = getWidth();
	var windowHeight = getHeight();
	var popupHeight = $("#popupContact").height();
	var popupWidth = $("#popupContact").width();
	//centering
	$("#popupContact").css({
		"position": "absolute",
		"top": windowHeight/2-popupHeight/2,
		"left": windowWidth/2-popupWidth/2
	});
	//only need force for IE6


	$("#backgroundPopup").css({
		"height": windowHeight
	});

}

function getHeight() {
  var myHeight = 0;
  if( typeof( window.innerWidth ) == 'number' ) {
    //Non-IE
    myHeight = window.innerHeight;
  } else if( document.documentElement && ( document.documentElement.clientWidth || document.documentElement.clientHeight ) ) {
    //IE 6+ in 'standards compliant mode'
    myHeight = document.documentElement.clientHeight;
  } else if( document.body && ( document.body.clientWidth || document.body.clientHeight ) ) {
    //IE 4 compatible
    myHeight = document.body.clientHeight;
  }
  return myHeight;
}

function getWidth() {
  var myWidth = 0;
  if( typeof( window.innerWidth ) == 'number' ) {
    //Non-IE
    myWidth = window.innerWidth;
  } else if( document.documentElement && ( document.documentElement.clientWidth || document.documentElement.clientHeight ) ) {
    //IE 6+ in 'standards compliant mode'
    myWidth = document.documentElement.clientWidth;
  } else if( document.body && ( document.body.clientWidth || document.body.clientHeight ) ) {
    //IE 4 compatible
    myWidth = document.body.clientWidth;
  }
  return myWidth;
}


//CONTROLLING EVENTS IN jQuery
$(document).ready(function(){

	//LOADING POPUP
	//Click the button event!
	$("#button").click(function(){
		//centering with css
		centerPopup();
		//load popup
		loadPopup();
	});

	//CLOSING POPUP
	//Click the x event!
	$("#popupContactClose").click(function(){
		disablePopup();
	});
	//Click out event!
	$("#backgroundPopup").click(function(){
		disablePopup();
	});
	//Press Escape event!
	$(document).keypress(function(e){
		if(e.keyCode==27 && popupStatus==1){
			disablePopup();
		}
	});

});