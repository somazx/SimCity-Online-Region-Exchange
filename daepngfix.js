function daebug(obj) {
	for (i in obj) {
		var daebug_obj=document.createElement('div');
		document.body.appendChild(daebug_obj);
		var v=eval("obj."+i);
		daebug_obj.innerText=i+" "+v;
	}
}

var trans=new Image();

function correctPNGforImgMap(img) {
	if (!trans.src) trans.src="empty.gif";
	img.style.width=img.width+"px";
	img.style.height=img.height+"px";
	img.style.filter="progid:DXImageTransform.Microsoft.AlphaImageLoader(src='"+img.src+"', sizingMethod='scale');";
	img.src=trans.src;
}

function correctPNGforImg(img) {
	img.outerHTML="<span style=\"width: "+img.width+"px; height: "+img.height+"px; filter: progid:DXImageTransform.Microsoft.AlphaImageLoader(src='"+img.src+"', sizingMethod='scale'); float: "+((img.align) ? img.align : img.style.styleFloat)+";\"></span>";
}

function correctPNG() {
	var r = new RegExp(".png$", "i");
	for(var i=0; i<document.images.length; i++) {
		if ((img=document.images[i]).src.match(r)) {
			(img.useMap || img.isMap) ? correctPNGforImgMap(img) : correctPNGforImg(img);
			i--;
		}
	}
}

window.attachEvent("onload", correctPNG);