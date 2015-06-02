
function toggle_node(x, untoggle_elements) {
	
	if(untoggle_elements)
	{
		node = document.getElementById(untoggle_elements)
		cnodes = node.childNodes

		for(i=0; i < cnodes.length; i++)
		{
			node = cnodes.item(i)
			if(node.nodeType == 1)
				node.style.display = 'none'
		}
	}

	var node = document.getElementById('city_info_' + x)
	var img = document.getElementById('city_image_' + x)	
	
	// now that we know what the file name is
	/*
	if(img.getAttribute("loaded") == "false")
	{
	*/
		LoadImage = new Image()
		LoadImage.src = img.getAttribute("url")
		img.src = LoadImage.src + '?nocache=' + Number.random
	
	//	img.setAttribute("loaded", 'true')
	//} 


if (node.style.display == '') {
		node.style.display = 'none'
	} else {
		node.style.display = ''
	}
}

function js_confirm($message) {
	return (confirm($message));
}


function refresh_city_img(id)
{
	
}


function refresh_region_img()
{

}