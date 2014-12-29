/**
* @author    JoomlaShine.com http://www.joomlashine.com
* @copyright Copyright (C) 2008 - 2009 JoomlaShine.com. All rights reserved.
* @license   Copyrighted Commercial Software
* This file may not be redistributed in whole or significant part.
*/

/* Menu */

function jsnSetMenuFX(menuId, menuFX, options)
{
	switch(menuFX)
	{
		case 0:
			JSNUtils.sfHover(menuId);
			break;

		case 1:
			if (typeof(MooTools) != 'undefined') {
				new MooMenu($(menuId), options)
			} else {
				jsnSetMenuFX(menuId, 0, options);
			}
			break;
	}
}

function jsnInitTreemenu(menuClass)
{
	var treemenus, treemenu, menuId;

	// Set ids for all side menus base on class
	treemenus = JSNUtils.getElementsByClass(document, "UL", menuClass);
	if (treemenus == undefined) return;

	for(var i=0;i<treemenus.length;i++){
		menuId = "base-treemenu-" + (i+1);
		treemenu = treemenus[i];
		treemenu.id = menuId;

		// Set fx
		jsnSetMenuFX(menuId, 0, {});
	}
}

function jsnResizeTemplate()
{
	if(isFloat == true){
		JSNUtils.setEqualHeight(["jsn-maincontent", "jsn-leftsidecontent", "jsn-rightsidecontent"]);
	}
}
function jsnInitTemplate()
{
	JSNUtils.sfHover("base-mainmenu");

	jsnInitTreemenu("menu-treemenu");

	JSNUtils.setStickPosition("jsn-pstickleft", lspAlignment);
	JSNUtils.setStickPosition("jsn-pstickright", rspAlignment);
}

// Call on document load
JSNUtils.addEvent(window, 'load', jsnInitTemplate);
JSNUtils.addEvent(window, 'resize', jsnResizeTemplate);