set_root();
var pagearSmallImg = root + "/common/images/misc/peeloff/peeloff_s.jpg";
var pagearSmallSwf = root + "/common/images/flash/peeloff/peeloff_s.swf";
var pagearBigImg = root + "/common/images/misc/peeloff/peeloff_b.jpg";
var pagearBigSwf = root + "/common/images/flash/peeloff/peeloff_b.swf";
var speedSmall = 1; 
var mirror = 'true'; 
var pageearColor = 'ffffff';  
var jumpTo = root + "/members/sign_up.php";
var openLink = 'self'; 
var openOnLoad = 9; 
var closeOnLoad = 3; 
var setDirection = 'rt'; 
var softFadeIn = 1; 
var playSound = 'false' 
var playOpenSound = 'false'; 
var playCloseSound = 'false'; 
var closeOnClick = 'false';
var closeOnClickText = 'Close';
var requiredMajorVersion = 6;
var requiredMinorVersion = 0;
var requiredRevision = 0;
var thumbWidth  = 150;
var thumbHeight = 150;
var bigWidth  = 600;
var bigHeight = 600;
var xPos = 'right';
var queryParams = 'pagearSmallImg='+escape(pagearSmallImg); 
var copyright = 'Webpicasso Media, www.webpicasso.de';
queryParams += '&pagearBigImg='+escape(pagearBigImg); 
queryParams += '&pageearColor='+pageearColor; 
queryParams += '&jumpTo='+escape(jumpTo); 
queryParams += '&openLink='+escape(openLink); 
queryParams += '&mirror='+escape(mirror); 
queryParams += '&copyright='+escape(copyright); 
queryParams += '&speedSmall='+escape(speedSmall); 
queryParams += '&openOnLoad='+escape(openOnLoad); 
queryParams += '&closeOnLoad='+escape(closeOnLoad); 
queryParams += '&setDirection='+escape(setDirection); 
queryParams += '&softFadeIn='+escape(softFadeIn); 
queryParams += '&playSound='+escape(playSound); 
queryParams += '&playOpenSound='+escape(playOpenSound); 
queryParams += '&playCloseSound='+escape(playCloseSound);  
queryParams += '&closeOnClick='+escape(closeOnClick); 
queryParams += '&closeOnClickText='+escape(utf8encode(closeOnClickText)); 
queryParams += '&lcKey='+escape(Math.random()); 
queryParams += '&bigWidth='+escape(bigWidth); 
queryParams += '&thumbWidth='+escape(thumbWidth); 

function openPeel(){
	document.getElementById('bigDiv').style.top = '0px'; 
	document.getElementById('bigDiv').style[xPos] = '0px';
	document.getElementById('thumbDiv').style.top = '-1000px';
}

function closePeel(){
	document.getElementById("thumbDiv").style.top = "0px";
	document.getElementById("bigDiv").style.top = "-1000px";
}

function writeObjects () { 
    
    var hasReqestedVersion = DetectFlashVer(requiredMajorVersion, requiredMinorVersion, requiredRevision);
    
    if(setDirection == 'lt') {
        xPosBig = 'left:-1000px';  
        xPos = 'left';   
    } else {
        xPosBig = 'right:1000px';
        xPos = 'right';              
    }
    
    document.write('<div id="bigDiv" style="position:absolute;width:'+ bigWidth +'px;height:'+ bigHeight +'px;z-index:9999;'+xPosBig+';top:-100px;">');    	
    
    if (hasReqestedVersion) {    	
    	AC_FL_RunContent(
    				"src", pagearBigSwf+'?'+ queryParams,
    				"width", bigWidth,
    				"height", bigHeight,
    				"align", "middle",
    				"id", "bigSwf",
    				"quality", "high",
    				"bgcolor", "#FFFFFF",
    				"name", "bigSwf",
    				"wmode", "transparent",
    				"scale", "noscale",
    				"salign", "tr",
    				"allowScriptAccess","always",
    				"type", "application/x-shockwave-flash",
    				'codebase', 'http://fpdownload.macromedia.com/get/flashplayer/current/swflash.cab',
    				"pluginspage", "http://www.adobe.com/go/getflashplayer"
    	);
    } else { 
    	document.write('no flash installed');
    } 
    document.write('</div>'); 
    document.write('<div id="thumbDiv" style="position:absolute;width:'+ thumbWidth +'px;height:'+ thumbHeight +'px;z-index:9999;'+xPos+':0px;top:0px;">');
    if (hasReqestedVersion) {    	
    	AC_FL_RunContent(
    				"src", pagearSmallSwf+'?'+ queryParams,
    				"width", thumbWidth,
    				"height", thumbHeight,
    				"align", "middle",
    				"id", "smallSwf",
    				"scale", "noscale",
    				"quality", "high",
    				"bgcolor", "#FFFFFF",
    				"name", "bigSwf",
    				"wmode", "transparent",
    				"allowScriptAccess","always",
    				"type", "application/x-shockwave-flash",
    				'codebase', 'http://fpdownload.macromedia.com/get/flashplayer/current/swflash.cab',
    				"pluginspage", "http://www.adobe.com/go/getflashplayer"
    	);
    } else { 
    	document.write('no flash installed');
    } 
    document.write('</div>');  
    setTimeout('document.getElementById("bigDiv").style.top = "-1000px";',100);
}

function utf8encode(txt) { 
    txt = txt.replace(/\r\n/g,"\n");
    var utf8txt = "";
    for(var i=0;i<txt.length;i++) {        
        var uc=txt.charCodeAt(i); 
        if (uc<128) {
            utf8txt += String.fromCharCode(uc);        
        } else if((uc>127) && (uc<2048)) {
            utf8txt += String.fromCharCode((uc>>6)|192);
            utf8txt += String.fromCharCode((uc&63)|128);
        } else {
            utf8txt += String.fromCharCode((uc>>12)|224);
            utf8txt += String.fromCharCode(((uc>>6)&63)|128);
            utf8txt += String.fromCharCode((uc&63)|128);
        }        
    }
    return utf8txt;
}