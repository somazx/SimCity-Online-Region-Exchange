<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
	<title>SimCity Online Region Exchange</title>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">

	<!--[if gte IE 5.5000]>
	<script type="text/javascript">
	
	/*
	**	Slightly modified from the usual code to handle imagemaps
	**
	*/
	
	function correctPNG() // correctly handle PNG transparency in Win IE 5.5 or higher.
		{
		for(var i=0; i<document.images.length; i++)
			{
		  var img = document.images[i]
		  var imgName = img.src.toUpperCase()
		  if (imgName.substring(imgName.length-3, imgName.length) == "PNG")
			  {
			 var imgID = (img.id) ? "id='" + img.id + "' " : ""
			 var imgClass = (img.className) ? "class='" + img.className + "' " : ""
			 var imgTitle = (img.title) ? "title='" + img.title + "' " : "title='" + img.alt + "' "
			 var imgStyle = "display:inline-block;" + img.style.cssText 
			 var imgAttribs = img.attributes;
			 for (var j=0; j<imgAttribs.length; j++)
				{
					var imgAttrib = imgAttribs[j];
					if (imgAttrib.nodeName == "align")
						{		  
						if (imgAttrib.nodeValue == "left") imgStyle = "float:left;" + imgStyle
							if (imgAttrib.nodeValue == "right") imgStyle = "float:right;" + imgStyle
								break
						}
				}
			 var strNewHTML = "<img " + imgID + imgClass + imgTitle
			 strNewHTML += " border=\"none\" src=\"empty.gif\" usemap=\"#citymap\" style=\"" + "width:" + img.width + "px; height:" + img.height + "px;" + imgStyle + ";"
			 strNewHTML += "filter:progid:DXImageTransform.Microsoft.AlphaImageLoader"
			 strNewHTML += "(src=\'" + img.src + "\', sizingMethod='scale');\">"
			 img.outerHTML = strNewHTML
			 i = i-1
			  }
			}
		}
	window.attachEvent("onload", correctPNG);	
	
	</script>
	<![endif]-->

	<style type="text/css">
		body
		{
			background-image: url('iframebackground.png')
		}
	</style>
</head>

<body>
	<?php html_regionIframe() ?>
</body>
</head>