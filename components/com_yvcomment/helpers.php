<?php
/*
* yvComment - Commenting solution
* @version		$Id: helpers.php 2 2009-08-30 15:10:43Z yvolk $
* @package		yvCommentComponent
* @copyright	2007-2009 yvolk (Yuri Volkov), http://yurivolkov.com. All rights reserved.
* @license GPL
*/
define('JPATH_SITE_YVCOMMENT', dirname(__FILE__) );

require_once (JPATH_SITE_YVCOMMENT . DS . 'controller.php');

// Required for function ContentIDToURL	
require_once (JPATH_SITE.DS.'includes'.DS.'application.php' );
require_once (JPATH_SITE.DS.'components'.DS.'com_content'.DS.'helpers'.DS.'route.php');

// Contains global yvComment parameters etc.
class  yvCommentHelper extends JObject {
  //------------------------------------------------------------ 
  // Common attributes - the same for all instances of yvComment	

	// Main debug switch. Set it to true to debug the whole yvComment solution
	var $_debug = false;

	// static member is not compatible with PHP4 :-( 
	// Main Release Level. Extensions for the same Release are compatible
	var $_Release = '1.24';
	// Sub Release Level (Service release)
	var $_Dev_level = '0';

	var $_Ok = true; // if false - error state: try to be quiet
	var $ContentPluginsImported = false;
	// id='yvComment' . ArticleID already put to the HTML page
	var $_IdShown = array();

	var $_ShowLogo = false;
	// Is yvComment logo already put to the HTML page
	var $_IsLogoShown = false;
	
	var $_pluginData = null;
	var $_pluginParams = null;
	var $_UseContentTable = false;
	var $_UseDesignatedSectionForComments = false;
	var $_SectionForComments = 0;
	// Table name for comments
	var $_TableName = '#__content';

	// 
	var $_GuestID = null;
	// none, own, all
	var $_UserCanEdit = 'none';
	// True e.g. for User who was authenticated with OpenId, 
	//   but who wasn't added to Joomla! user's automatically after login
	var	$_UserIsTemp = false;
	var $_IsPaginationEnabled = false;
	// Stored limit value (for debug purposes...)
	var $_limit1 = 0;

	// Where to return after editing of comment?
	var $_ReturnURL = '';
	// At which Web page (Menu Itemid) we should show component?
	var $_ComponentItemid = 0;

	// Some vars from component
	var $_Comp_option = '';	
	var $_Comp_view =  '';
	var $_Comp_id	= 0;

	// Total number of instances of this class,
	//   initialized during this (Web page) request
	var $_nInd = 0;	
	// Index of attributes, that belongs to current instance
	// -1 - there is no instances yet
	// 0 - first initialized instance
	var $_Ind = -1;	

	//------------------------------------------------------------ 
	// Instance-specific attributes
	var $_IndPrev = array();  // integer, Index of previous instance
  
	// Option, that called yvComment
	var $_ParentOption = array();  // string
	var $_ParentView = array();  // string
	
	var $_InstanceId = array();  // integer
	var $_DisplayTo = array(); // string
	var $_PageParameters = array();
	var $_ViewName = array();

	var $_FilterByContext = array();
	var $_article = array();
	var $_SectionID = array();
	var $_CategoryID = array();
	var $_ArticleID = array();

	/*
	 * Initialize common attributes - the same for all instances of yvComment
	 */
	function __construct() {
		global $mainframe;
		$Ok = true;
		$message = '';	
	  $db =& JFactory::getDBO();
	  $this->_limit1 = intval(JRequest::getInt('limit', '-1')) ;

		$lang = & JFactory :: getLanguage();
		// 2007-03-23 v.1.15 - language file moved to the administrator site
		//$lang->load('com_yvcomment', JPATH_SITE);
		$lang->load('com_yvcomment', JPATH_ADMINISTRATOR);

		// Get Plugin info
		$this->_pluginData = & JPluginHelper::getPlugin('content', 'yvcomment');
		if ($this->_debug) {
			$message .= 'yvComment plugin ' . ($this->_pluginData ? 'loaded' : 'NOT loaded') . '<br/>';
			
			$session =& JFactory::getSession();
			$this->log('yvComment::__construct: ' .
				print_r($session, true) . 
				'$_SESSION: ' .
				print_r($_SESSION, true)
			);
		}
		if ($this->_pluginData) {
			$this->_pluginParams = new JParameter( $this->_pluginData->params );
		}
  	
		$this->_UseDesignatedSectionForComments = (bool) $this->getConfigValue('usedesignatedsection', '1');
		$this->_SectionForComments = $this->getConfigValue('sectionid', '0');
  	
		if ($this->getConfigValue('usecontenttable', '1')) {
			$this->_UseContentTable = true;
			$this->_TableName = $db->getPrefix() . 'content';
		} else {
			$this->_TableName = $db->getPrefix() . 'yvcomment';
		}
		$guest_username = $this->getConfigValue('guest_username', '');
		if (!empty($guest_username)) {
			jimport('joomla.user.helper');
			$this->_GuestID = JUserHelper::getUserId($guest_username);
		}
		if ($this->_GuestID == 0) {
			if ($this->getConfigValue('allow_guest_add', 0)) {
				$message .= JText::_('GUEST_USER_ACCOUNT_ERROR') . ' guest_username="' . $guest_username . '"<br />';
			}
		}

    $this->storeOptionParms();
		$this->LookupUser();
     
    $css = $this->getConfigValue('usecss','1');
		switch ($css)
		{
		  case '0' :
		    // don't use CSS
		    break;
		  case '1' :
		    $css = 'style001.css';
		  default :
				$doc =& JFactory::getDocument();
				$doc->addStyleSheet( $this->getSiteURL() . 'components/com_yvcomment/assets/' . $css, 'text/css');
	
	      // Do not add the script until it has some useful functionality to
	      // reduce the number of HTTP requests.
				// $doc->addScript( $url . 'components/com_yvcomment/assets/default.js', 'text/javascript');
		}
  	
		if (strlen($message) > 0) {
			$message .= $this->_textSignature();
			$mainframe->enqueueMessage($message, ($Ok ? 'notice' : 'error'));
		}
	}

	/**	
	 * Returns a reference to the global yvCommentHelper object, only creating it
	 * if it doesn't already exist.
	 */
	function &getInstance()
	{
		static $instance;

		if (!$instance)
		{
			$instance = new yvCommentHelper();
		}
		return $instance;
	}
	
	function LookupUser() {
		$message = '';
		$this->_UserCanEdit = 'none';
		$this->_UserIsTemp = false;
		
		// Based on the code of the 'save' function
		// of the 'components/com_content/controller.php' file
		$user	=& JFactory::getUser();
		if ($user->authorize('com_content', 'edit', 'content', 'all')) {
			$this->_UserCanEdit = 'all';
		} elseif ( $user->authorize('com_content', 'edit', 'content', 'own')) {
			$this->_UserCanEdit = 'own';
		}
    // Based on code from 'plugins/user/joomla.php', function onLoginUser
		if ($user->get('tmp_user') == 1) {
			$this->_UserIsTemp = true;
		}
    if ($this->_debug) {
			$message .= 'UserCanEdit=' . $this->_UserCanEdit . '<br />';
		  if ($this->UserIsTemp()) {
	      $message .= print_r($user, true) . '<br />';
		  }
    }
		if (strlen($message) > 0) {
			global $mainframe;
			$mainframe->enqueueMessage($message, ('notice'));
		}
	}

	function storeOptionParms() {
		global $option;
		if (empty($this->_Comp_option)) {
			if ($option != 'com_yvcomment') {
				$this->_Comp_option = $option;	
				$this->_Comp_view =  JRequest::getCmd( 'view' );
				$this->_Comp_id	= intval(JRequest::getInt('id'));
			}
		}
	}

	/*
	 * Initialize Instance-specific attributes
	 * returns: Instance index
	 */
  function BeginInstance($DisplayTo, &$params) {
		global $option;
		global $mainframe;

  	$this->_nInd += 1;
  	$InstanceInd = $this->_nInd - 1;	
  
		// push (store) previous index
  	$this->_IndPrev[$InstanceInd] = $this->_Ind;	

  	$this->_Ind = $InstanceInd;	

		//Store current global variables
  	$this->_ParentOption[$InstanceInd] = $option;
  	$this->_ParentView[$InstanceInd] = JRequest::getCmd('view');
  	$this->_ParentLayout[$InstanceInd] = JRequest::getCmd('layout');
  
		$this->_InstanceId[$InstanceInd] = rand(1000,9999);
		$this->_DisplayTo[$InstanceInd] = $DisplayTo;
  	$this->_PageParameters[$InstanceInd] = null;
		$this->_ViewName[$InstanceInd] = 'none';

		$this->_FilterByContext[$InstanceInd] = 'allcategory';
		$this->_article[$InstanceInd] = null;
  	$this->_SectionID[$InstanceInd] = 0;
  	$this->_CategoryID[$InstanceInd] = 0;
  	$this->_ArticleID[$InstanceInd] = 0;

  	$this->_setPage($params);

		$option = 'com_yvcomment';

		if ($InstanceInd == 0) {
			$this->_ReturnURL	= JRequest::getVar('url', '', '', 'STRING');
		  //echo 'url="' . $this->_ReturnURL . '"<br />';
      $this->_ComponentItemid = intval(JRequest::getInt('Itemid', 0));
			if ($this->_debug) {
				$message = 'Itemid="'	. $this->_ComponentItemid . '"; '; 
				$message = 'url="'	. base64_decode($this->getReturnURL(false)) . '"'; 
				$mainframe->enqueueMessage($message, 'notice');
			}
			
			if ($DisplayTo == 'component') {
				$this->_IsPaginationEnabled = true;
			} else {
				$PageParameters = & $mainframe->getPageParameters();
				if (!$PageParameters->get('show_pagination')) {
					// Show pagination for Comments only if Component... or something else
					// didn't show pagination	    	
					$this->_IsPaginationEnabled = true;
				}
				if ($this->_debug) {
					$message = 'PageParameters="'	. print_r($PageParameters, true) . '"'; 
					$mainframe->enqueueMessage($message, 'notice');
				}
			}
		}

		if ($DisplayTo == 'component' 
			&& $this->_ParentOption[$InstanceInd] == 'com_yvcomment') {
			if ($this->getComponentItemid() != 0) {
				// 2008-11-03, Joomla! 1.5.7
				// It looks like sometimes Joomla doesn't parse Itemid value
				// so we need to do it ourselves:  
				$router =& JSite::getRouter();
				$itemid = (integer) $router->getVar('Itemid');
				if ($itemid == 0) {
					$menu =& JSite::getMenu(true);
					//echo '<hr>menu=' . print_r($menu, true) . '<hr>';
					if ($this->_debug) {
							$menuActive = $menu->getActive();
							$message = 'Joomla! didn\'t parse Itemid (bug?):'
							. ' Parsed Itemid=' . $itemid 
							. '; in Request Itemid=' . $this->getComponentItemid() 
							. '; ActiveMenuItemID=' . ( $menuActive ? $menuActive->id : "(not set)" );
							$mainframe->enqueueMessage($message, 'notice');
					}	
					$menu->setActive($this->getComponentItemid());
				}	
			}
		}

    if ($this->_debug) {
			global $mainframe;
			$message = 'BeginInstance ind='	. $InstanceInd
				. '; IndPrev=' . $this->_IndPrev[$InstanceInd]
				. '; DisplayTo="' . $DisplayTo . '"; ParentOption="' . $this->_ParentOption[$InstanceInd] . '"; ParentView="' . $this->_ParentView[$InstanceInd] . '"; ParentLayout="' . $this->_ParentLayout[$InstanceInd] . '"';
			$mainframe->enqueueMessage($message, 'notice');
    }
  	return $this->_Ind;
  }

	// This instance wan't be called, but memory is not freed 
  function EndInstance( $InstanceInd ) {
    if ($this->_debug) {
			global $mainframe;
			$message = 'EndInstance ind='	. $InstanceInd;
			$mainframe->enqueueMessage($message, ('notice'));
    }
  	if ($InstanceInd >= 0) {
	  	if ($this->_Ind != $InstanceInd) {
				global $mainframe;
				$message = 'Order of Instances is wrong: ind=' 
				. $InstanceInd . ' is being ended, but current ind="' . $this->_Ind . '"';
				$message .= $this->_textSignature();
				$mainframe->enqueueMessage($message, ('notice'));
	
				// Trying to fix...
				$this->_Ind = $InstanceInd;
	  	}	

			// Restore global values for use by other extensions
			JRequest::setVar('layout', $this->_ParentLayout[$InstanceInd]);
			JRequest::setVar('view', $this->_ParentView[$InstanceInd]);
			global $option;
  		$option = $this->_ParentOption[$InstanceInd];
	  
			// pop (restore) previous index
	  	$this->_Ind = $this->_IndPrev[$this->_Ind];
  	}	
  }

  function Ok() {
  	return $this->_Ok;
  }
  function Hide() {
  	$this->_Ok = false;
  }

  function InstanceId() {
  	return $this->_InstanceId[$this->_Ind];
  }

  function ParentView() {
  	return $this->_ParentView[$this->_Ind];
  }

  function ParentOption() {
  	return $this->_ParentOption[$this->_Ind];
  }
  function IsNested() {
  	return (
  	  $this->_IndPrev[$this->_Ind] >= 0
  	  ) ;
  }

  function DisplayTo() {
  	return $this->_DisplayTo[$this->_Ind];
  }

  function UseContentTable() {
  	return $this->_UseContentTable;
  }

  function UseDesignatedSectionForComments() {
  	return $this->_UseDesignatedSectionForComments;
  }

  function getTableName() {
  	return $this->_TableName;
  }

	// Build return URL based on current URL
  function buildReturnURL($UseExistingIfNotEmpty = false, $fragment = "") {
		if ($this->_ReturnURL && $UseExistingIfNotEmpty) {
		  $url = $this->_ReturnURL;
		} else {
			$uri =& JFactory::getURI();
			if (strlen($fragment) > 0) {
				$uri->setFragment($fragment);	
			}
			$url = base64_encode($uri->toString());	
		}
  	return $url;
  }

  function setReturnURL($url) {
	  $this->_ReturnURL = $url;
  }

  function getReturnURL($BuildIfEmpty = false, $fragment = "") {
		if (!$this->_ReturnURL && $BuildIfEmpty) {
		  $this->_ReturnURL = $this->buildReturnURL(false, $fragment);
		}
  	return $this->_ReturnURL;
  }

  function getComponentItemid() {
  	return $this->_ComponentItemid;
  }

	/*
	 * Returns value of Component/Plugin parameter
	 * from Common attributes
	 */ 
	function getConfigValue($paramName = '', $default = '') {
	  $value = $default;
	
		switch ($paramName) {
			case 'access':
				if ($this->_pluginData) {
					$value = 0;
				} else {
					// if Plugin is not loaded, then Access is denied
					$value = 0; //999; 
				}
				break;
			default:
				if ($this->_pluginParams) {
					$value = $this->_pluginParams->get($paramName, $default);
				}
		}
		//echo 'getConfigValue param="' . $paramName . '", value="' . $value . '"<br/>';
		
		return $value;
	}

  function getSectionForComments() {
  	return $this->_SectionForComments;
  }

	function IsIdShown($ArticleID, $ShowNow = false) {
		$OldValue = false;
		foreach ($this->_IdShown as $id1) {
			if ($id1 == $ArticleID) {
				$OldValue = true;
				break;
			}
		}
		if (!$OldValue && $ShowNow) {
			$this->_IdShown[] = $ArticleID;
		}	
		return $OldValue;	
	}

	// Flag to show Logo on current page
	function setShowLogo($ShowLogo = true) {
		$OldValue = $this->_ShowLogo;
		$this->_ShowLogo = $ShowLogo;
		return $OldValue;	
	}

	// Do we need to show Logo now?
	function getShowLogo($ShowNow = true) {
		$ShowLogo = false;
		if (($this->_ShowLogo) && !$this->IsNested()) {
		  if (!$this->_IsLogoShown) {
				$ShowLogo = true;
				if ($ShowNow) {
					$this->_IsLogoShown = true;
				}	
		  }
		}
		return $ShowLogo;	
	}

	function IsPaginationEnabled() {
		return $this->_IsPaginationEnabled;	
	}
	
	// Is a Comment the Content Item from Content Table with this ID?
	function IsCommentByID($id)
	{
		$Is = false;
		if ($id != 0) {
      if ($this->UseDesignatedSectionForComments()) {
			  //Unfortunately, this is not preserved by editors:
			  //   $attribs = new JParameter( $row->attribs );
			  //   if ( $attribs->get('contenttype', 'default') == 'yvcomment') {
			  //... so we use SectionID as ContentTypeID
			
				$sectionid = intval($this->DLookup('sectionid', '#__content', 'id=' . $id));
			  if (($sectionid != 0) &&
				 ($sectionid == $this->_SectionForComments)) {
					$Is = true;
				} 
      } else {
				$parentid = intval($this->DLookup('parentid', '#__content', 'id=' . $id));
			  if (($parentid != 0) ) {
					$Is = true;
				} 
      }			
		}
		//echo 'IsCommentByID=' . $Is . '; sectionid=' . $sectionid . ' (' . $this->_SectionForComments . ')<br/>';
		return $Is;
	}

  function setArticle(& $article) {
  	$this->_article[$this->_Ind] = & $article;
  	$this->_SectionID[$this->_Ind] = 0;
  	$this->_CategoryID[$this->_Ind] = 0;
  	$this->_ArticleID[$this->_Ind] = 0;
    //echo 'setArticle: ' . print_r($article, true) . '<br />';
  	
		if (is_object( $article )) {
			if (isset( $article->id )) {
				$this->_ArticleID[$this->_Ind] = intval($article->id);
			}
			if (isset( $article->sectionid )) {
				$this->_SectionID[$this->_Ind] = intval($article->sectionid);
			}
			if (isset( $article->catid )) {
				$this->_CategoryID[$this->_Ind] = intval($article->catid);
			}
		}
  }

  function &getArticle() {
  	return $this->_article[$this->_Ind];
  }

  function setViewName( $viewName ) {
  	$this->_ViewName[$this->_Ind] = $viewName;

    JRequest::setVar('view', $viewName);
		//echo 'after JRequest::setVar(view, $viewName): ' . JRequest::getVar( 'view', '(unknown)') . '<br />';
  }
  function getViewName() {
  	return $this->_ViewName[$this->_Ind];
  }

  function setFilterByContext( $FilterByContext ) {
  	$this->_FilterByContext[$this->_Ind] = $FilterByContext;
		$this->setPageValue('filterbycontext', $FilterByContext);
  }
  function getFilterByContext() {
  	return $this->_FilterByContext[$this->_Ind];
  }

  function getContextObjectName() {
  	$name = '';
  	switch ($this->getFilterByContext()) {
  		case 'article':
  		  $id = $this->_ArticleID[$this->_Ind];
  		  $name = $this->DLookup('title','#__content','id=' . $id);
  		  break;
  		case 'category':
  		  $id = $this->_CategoryID[$this->_Ind];
  		  $name = $this->DLookup('title','#__categories','id=' . $id);
  		  break;
  		case 'section':
  		  $id = $this->_SectionID[$this->_Ind];
  		  $name = $this->DLookup('title','#__sections','id=' . $id);
  		  break;
  		case 'auto':
  		case 'autocategory':
  		case 'autosection':
  		case 'all':
  		default:
  		  $name = 'none (' . $this->getFilterByContext() . ')';
  	}
  	return $name;
  }

  function setArticleID( $id ) {
  	//intval - to avoid data manipulation
  	$this->_ArticleID[$this->_Ind] = intval($id);
  }
  function getArticleID() {
  	return $this->_ArticleID[$this->_Ind];
  }

  function setSectionID( $id ) {
  	$this->_SectionID[$this->_Ind] = intval($id);
  }
  function getSectionID() {
  	return $this->_SectionID[$this->_Ind];
  }

  function setCategoryID( $id ) {
  	$this->_CategoryID[$this->_Ind] = intval($id);
  }
  function getCategoryID() {
  	return $this->_CategoryID[$this->_Ind];
  }

  function IsFilterByContextOk() {
  	$Ok = true;
  	switch ($this->_FilterByContext[$this->_Ind]) {
  		case 'article':
  		  if ($this->_ArticleID[$this->_Ind] == 0) {
  				$Ok = false;
  		  }
  		  break;
  		case 'category':
  		  if ($this->_CategoryID[$this->_Ind] == 0) {
  				$Ok = false;
  		  }
  		  break;
  		case 'section':
  		  if ($this->_SectionID[$this->_Ind] == 0) {
  				$Ok = false;
  		  }
  		  break;
  		case 'auto':
  		case 'autocategory':
  		case 'autosection':
 				$Ok = false;
  		  break;
  		case 'all':
  		  // Ok!
  		  break;
  		default:
  			$Ok = false;
  	}
  	return $Ok;
  }
  
  function FindContext() {
		// Set FilterByContext
		$FilterByContext = $this->getPageValue('filterbycontext','all');
		switch ($this->getViewName()) {
    	case 'comment':
	  	  if ($FilterByContext != 'article') {
	        //echo 'FindContext: \'Filter list by\' option was set to \'article\'<br />';
	        $FilterByContext = 'article';
	  	  }
    	case 'listofcomments' :	
		    break;
			default:
		}

		$methods = array();
  	switch ($FilterByContext) {
  		case 'auto':
  		case 'autocategory':
  		case 'autosection':
				$methods[] = 'FromRequestArticle';
				$methods[] = 'FromRequest';
				//$methods[] = 'FromArticle';
				$methods[] = 'all';  // may need two passes...
				$methods[] = 'all';  // may need two passes...
				$methods[] = 'all';  // last check
  		  break;
  		case 'all':
				$methods[] = 'all';  // last check
  		  break;
  		default:
				$methods[] = 'FromArticle';
				$methods[] = 'FromRequestArticle';
				$methods[] = 'FromRequest';
				$methods[] = 'FromKnownContextVars';
  	}

		foreach ($methods as $method) {
	  	switch ($FilterByContext) {
	  		case 'auto':
	  		  if ($this->_ArticleID[$this->_Ind] != 0) {
						$FilterByContext = 'article';
	  		  } elseif ($this->_CategoryID[$this->_Ind] != 0) {
						$FilterByContext = 'category';
	  		  } elseif ($this->_SectionID[$this->_Ind] != 0) {
						$FilterByContext = 'section';
	  		  }
					break;
	  		case 'autocategory':
	  		  if ($this->_CategoryID[$this->_Ind] != 0) {
						$FilterByContext = 'category';
	  		  } elseif ($this->_ArticleID[$this->_Ind] != 0) {
						$method = 'FromKnownContextVars';
	  		  } elseif ($this->_SectionID[$this->_Ind] != 0) {
						$FilterByContext = 'section';
	  		  }
					break;
	  		case 'autosection':
	  		  if ($this->_SectionID[$this->_Ind] != 0) {
						$FilterByContext = 'section';
	  		  } elseif ($this->_CategoryID[$this->_Ind] != 0) {
						$method = 'FromKnownContextVars';
	  		  } elseif ($this->_ArticleID[$this->_Ind] != 0) {
						$method = 'FromKnownContextVars';
	  		  }
	  		  break;
	  	}
			$this->setFilterByContext($FilterByContext);
			if ($this->IsFilterByContextOk()) { 
				break; // break 'foreach' loop
			}
			
			switch ($method) {
				case 'FromArticle':
					$article = $this->getArticle();
					if (!is_object( $article )) {
						// Find first article of the component
						$article = $this->getArticleOfComponent();
						if (is_object( $article )) {
							$this->setArticle($article);
						}	
					}
					break;
				case 'FromRequestArticle':
					if ($this->getArticleID()== 0) {
						$id	= intval(JRequest::getInt( 'ArticleID', 0));	
						if ($id == 0) {
							// If this page was accessed through MenuItem link...			
							$id	= $this->getPageValue('articleid', 0);
						} 
						$this->setArticleID($id);
					}
					break;
				case 'FromRequest':
					if ($this->_debug) {
		      	echo 'FindContext-FromRequest: ' . $this->_Comp_view . '; id=' . $this->_Comp_id . '<br />';
					}	
					switch ($this->_Comp_view) {
						case 'article':
							$this->setArticleID($this->_Comp_id);
						  break;
						case 'section':
							$this->setSectionID($this->_Comp_id);
						  break;
						case 'category':
							$this->setCategoryID($this->_Comp_id);
						  break;
					}
					break;
				case 'FromKnownContextVars':
    			if ($this->_debug) {
		      	echo 'FindContext-FromKnownContextVars: ' . $FilterByContext . '; ArticleID=' . $this->getArticleID() . '<br />';
    			}	
			  	switch ($FilterByContext) {
			  		case 'article':
			  			// Nothing to do?
							$CommentID = intval(JRequest::getInt( 'yvCommentID', 0));
							if ($CommentID != 0) {
								$ArticleID = $this->DLookup('parentid', $this->getTableName(), 'id=' . $CommentID);
								$this->setArticleID($ArticleID);
							}
			  		  break;
	  				case 'autocategory':
			  		case 'category':
			  			if ($this->getArticleID() != 0) {
							  $CategoryOfArticleID = $this->DLookup('catid','#__content','id=' . $this->getArticleID());
								$this->setCategoryID($CategoryOfArticleID);
			    			if ($this->_debug) {
			      			echo 'CategoryID=' . $CategoryOfArticleID . '<br />';
			    			}	
	      				if ($FilterByContext=='autocategory') {
			      			if ($CategoryOfArticleID != '0') {
			      				$FilterByContext='category';
			      			} else {
			      				$FilterByContext='autosection';
		      				}
		      			}
			  			}
			  		  break;
	  				case 'autosection':
			  		case 'section':
			  			$SectionID = 0;
			  			if ($this->getArticleID() != 0) {
							  $SectionID = $this->DLookup('sectionid','#__content','id=' . $this->getArticleID());
			  			} 
			  			if (($SectionID == 0) && ($this->getCategoryID() != 0)) {
							  $SectionID = $this->DLookup('section','#__categories','id=' . $this->getCategoryID());
			  			} 
							$this->setSectionID($SectionID);
		    			if ($this->_debug) {
		      			echo 'SectionID=' . $SectionID . '<br />';
		    			}	
      				if ($FilterByContext=='autosection') {
		      			if ($SectionID != '0') {
		      				$FilterByContext='section';
		      			} else {
		      				$FilterByContext='all';
	      				}
	      			}
			  		  break;
			  	}
					break;
				case 'all':
					$FilterByContext = 'all';
					break;
			}
		}
		if ($this->_debug) {
	    echo 'FindContext: ' . $this->getFilterByContext() . ', ' . ($this->IsFilterByContextOk() ? 'Ok' : 'Failed') . '<br />';
		}	
  	return $this->IsFilterByContextOk();
  }

	// Number of Comments for the Current context
	function getNComments($ArticleID = 0, $filter_state = '', $authoridsfilter = '')
	{
	  $nComments = 0;
		$user		=& JFactory::getUser();
		$aid		= (int) $user->get('aid', 0);
	  
  	$From = $this->getTableName() . ' AS c';
		$From = '(' . $From . ') INNER JOIN #__content AS ar ON c.parentid=ar.id' ;

		$Where = '';    
		if ($this->UseDesignatedSectionForComments()) {
			$Where = '(c.sectionid=' . $this->getSectionForComments() . ')';    
		} else {
			$Where = '(c.parentid<>0)';    
		}
		
		$Where .= ' AND c.access <= '. (int) $aid;
		$Where .= ' AND ar.access <= '. (int) $aid;

		$nDays = $this->ResultDaysToNDays($this->getPageValue('result_days', 'all'));
		if ($nDays > 0) {
			$Where .= ' AND (c.created > ' . 
				$this->SecondsFromNowToSQLDate($this->DaysToSeconds($nDays)) . ')';
		}

		if ($ArticleID == 0) {
	  	switch ($this->getFilterByContext()) {
	  		case 'article':
			  	$Where .= ' AND ar.id=' . $this->getArticleID();
	  		  break;
	  		case 'category':
			  	$Where .= ' AND ar.catid=' . $this->getCategoryID();
	  		  break;
	  		case 'section':
			  	$Where .= ' AND ar.sectionid=' . $this->getSectionID();
	  		  break;
	  		default:
	  			// For the whole site don't show comments on comments on the first level
			  	$Where .= ' AND ar.parentid=0';
	  	}
		} else {
	  	$Where .= ' AND c.parentid=' . $ArticleID;
		}

		if (!empty($authoridsfilter)) {
		  $Where .= ' AND (c.created_by IN(' . $authoridsfilter . '))';
		}

		//Filter state of both Articles AND Comments
		//See similar 'if condition' in /components/com_content/models/frontpage.php,
		//  function _buildContentWhere
		// 1. For Articles
		$WhereState = ''; 
		if ($this->UserCanEdit() == 'all') {
			// Content state filter
			if ($filter_state) {
				if ($filter_state == 'P') {
			  	$WhereState = 'ar.state = 1';
				} else {
					if ($filter_state == 'U') {
			  		$WhereState = 'ar.state = 0';
					} else if ($filter_state == 'A') {
			  		$WhereState = 'ar.state = -1';
					}
				}
			}
		}
		else {
	  	$WhereState = 'ar.state = 1';
		}
		if (empty($WhereState)) {
			$WhereState = 'ar.state != -2';
		}
  	$Where .= ' AND ' . $WhereState;
		// 2. The same - for comments:
		$WhereState = ''; 
		if ($this->UserCanEdit() == 'all') {
			// Content state filter
			if ($filter_state) {
				if ($filter_state == 'P') {
			  	$WhereState = 'c.state = 1';
				} else {
					if ($filter_state == 'U') {
			  		$WhereState = 'c.state = 0';
					} else if ($filter_state == 'A') {
			  		$WhereState = 'c.state = -1';
					}
				}
			}
		}
		else {
	  	$WhereState = 'c.state = 1';
		}
		if (empty($WhereState)) {
			$WhereState = 'c.state != -2';
		}
  	$Where .= ' AND ' . $WhereState;

		if ($ArticleID == 0) {
			// Filter by section(s), category(ies) and even by article(s)
			$exclude = (boolean) $this->getPageValue('articlesectionids_excludefilter', '0');
			$articlesectionidsfilter = $this->getPageValue('articlesectionidsfilter', '');
			$filter = '';
			if (!empty($articlesectionidsfilter)) {
				if (!empty($filter)) {
				  $filter .= ($exclude ? ' AND ' : ' OR ');
				}
			  $filter .= 'ar.sectionid' . ($exclude ? ' NOT' : '') . ' IN(' . $articlesectionidsfilter . ')';
			}
			$articlecategoryidsfilter = $this->getPageValue('articlecategoryidsfilter', '');
			if (!empty($articlecategoryidsfilter)) {
				if (!empty($filter)) {
				  $filter .= ($exclude ? ' AND ' : ' OR ');
				}
				$filter .= 'ar.catid' . ($exclude ? ' NOT' : '') . ' IN(' . $articlecategoryidsfilter . ')';
			}
			$articleidsfilter = $this->getPageValue('articleidsfilter', '');
			if (!empty($articleidsfilter)) {
				if (!empty($filter)) {
				  $filter .= ($exclude ? ' AND ' : ' OR ');
				}
				$filter .= 'ar.id' . ($exclude ? ' NOT' : '') . ' IN(' . $articleidsfilter . ')';
			}
			if (!empty($filter)) {
			  $Where .= ' AND (' . $filter . ')';
			}
		}

		$nComments = $this->DLookup('Count(*)', $From, $Where);
	    
		return $nComments;
	}

	// Find first article of the component
  function &getArticleOfComponent() {
  	$article = null;	

		for ($i=0; $i < $this->_nInd; $i++) {
			if ($this->_DisplayTo[$i] == 'component')
			{
			  $article = & $this->_article[$i];
			  break;
			}    
			if ($this->_ParentOption[$i] != 'com_yvcomment' || $this->_DisplayTo[$i] == 'plugin') 
			{
			  $article = & $this->_article[$i];
			  break;
			}
	  }
  	return $article;
  }
  
  function getGuestID() {
  	return $this->_GuestID;
  }

	function _setPage(&$params_in) {
	  global $mainframe;
		$params = new JParameter('');
  
		switch ($this->_DisplayTo[$this->_Ind])
		{
		  case 'module' :
		  	if ($params_in->get('view_name', 'listofcomments') == 'comment') {
		  		// plugin in a module
			    // create a copy of plugin params!
			  	$params->merge($this->_pluginParams);
					//echo "Merged!" ."<br/>";
		  	}
		  	$params->merge($params_in);
		    break;
		  case 'plugin' :
		    // create a copy of plugin params!
		  	$params->merge($this->_pluginParams);
		  	$params->merge($params_in);
		    break;
		  case 'component' :
				// echo "ViewName=" . $this->ParentView() ."<br/>";
		  	if ($this->ParentView() == 'comment') {
		  		// plugin in component
			    // create a copy of plugin params!
			  	$params->merge($this->_pluginParams);
					//echo "Merged!" ."<br/>";
		  	}
				if (!$mainframe->isAdmin()) {
		    	$option = 'com_yvcomment';
					// Get the page/component configuration
					$params->merge($mainframe->getPageParameters($option));
			    //echo 'params1toString: <br/>' . $params->toString() . '<br/>';
				}  
		    break;
		  default :
				$message = 'Unknown DisplayTo="' . $this->_DisplayTo[$this->_Ind] . '"';
				$mainframe->enqueueMessage($message, 'notice');
		}

		// Set some defaults
		$params->def('yvcomment_limit',0);
		$params->def('yvcomment_limitstart',0);
		
		$this->_PageParameters[$this->_Ind] = & $params;
	}
	
	function setPagination() {
	  global $mainframe;
		$message = '';	
	  
		if ($this->_PageParameters[$this->_Ind] && $this->_IsPaginationEnabled) {
			// Only one pagination per page - this is Joomla! restriction...	
			$this->_IsPaginationEnabled = false;	
		
	    //$message .= 'limit="' . JRequest::getVar('limit', '(not set)') . '; stored=' . $this->_limit1 . '"<br/>';
			$limit = $mainframe->getUserStateFromRequest('yvcomment_limit', 'limit', $mainframe->getCfg('list_limit'));

			// ToDo; to figure out, Who's responsible for this...
			// This is very strange value at the beginning of the session, that causes wrong pagination...
			if ($limit == 9) {
				$limit = 10;
			}
			$this->_PageParameters[$this->_Ind]->set('yvcomment_limit', $limit);
			
			// Fixing Joomla pagination bug 2007-10-13
	    //$message .= 'limitstart="' . JRequest::getVar('limitstart', '(not set)') . '"<br/>';
	    //$message .= 'start="' . JRequest::getVar('start', '(not set)') . '"<br/>';
			
			$varName = 'limitstart';
			if (!$mainframe->isAdmin()) {
				if ((JRequest::getVar($varName) === null)) {
					$varName = 'start';
				}
			}
			$this->_PageParameters[$this->_Ind]->set('yvcomment_limitstart', $mainframe->getUserStateFromRequest('yvcomment_limitstart', $varName, '0'));

	    //$message .= 'getUserStateFromRequest="' 
	    //  . $mainframe->getUserStateFromRequest('yvcomment_limit', 'limit', $mainframe->getCfg('list_limit'))
	    //  . '; cfg=' . $mainframe->getCfg('list_limit') 
	    //  . '; PageValue=' . $this->getPageValue('yvcomment_limit', '0');  
		}
		if (!empty($message)) {
			$mainframe->enqueueMessage($message, 'notice');
		}
	}
	
	function &PageParameters()
	{
		return $this->_PageParameters[$this->_Ind];
	}

	/*
	 * Returns value of Page/Component/Plugin parameter
	 * from Instance-specific attributes
	 */ 
	function getPageValue($paramName = '', $default = '') {
	  $value = $default;
	
		if ($this->_PageParameters[$this->_Ind]) {
			$value = $this->_PageParameters[$this->_Ind]->get($paramName, $default);
		}
		//echo 'getPageValue param="' . $paramName . '", value="' . $value . '"<br/>';
		return $value;
	}

	function setPageValue($paramName = '', $value = '') {
		if ($this->_PageParameters[$this->_Ind]) {
			$this->_PageParameters[$this->_Ind]->set($paramName, $value);
		}
	}

	// Show List of comments for the Article (inside another view...) 
	function ShowCommentsOnArticle($params_in = null) {
		$yvComment = &yvCommentHelper::getInstance();
		$Ok = true;
		$strOut = "";
		$task = 'viewdisplay';

		$params = new JParameter($params_in);
		$ArticleID = $yvComment->getArticleID();
		if ($ArticleID == '0') {
			return;
		}
		$params->set('filterbycontext','article');

  	$InstanceInd = $yvComment->BeginInstance('plugin', $params);
  	$yvComment->setArticleID($ArticleID);
		
    $viewName = $yvComment->getPageValue('view_name', 'listofcomments');
		$layoutName = $yvComment->getPageValue('layout_name', 'default');
		if ($layoutName == '0') {
			$layoutName = $yvComment->getPageValue('layout_name_custom', 'default');
		}
    JRequest::setVar('layout', $layoutName);

		$show_pagination = $yvComment->getPageValue('show_pagination', false);	
		if (!$show_pagination) {
		  // Next line doesn't work, because it doesn't really set parameter to 'false':
		  //   $yvComment->setPageValue('show_pagination', false);
		  // And this works:	
		  $yvComment->setPageValue('show_pagination', '0');	
		  // echo 'show_pagination=' . $yvComment->getPageValue('show_pagination', '???') . ';';	
			$limit = intval($yvComment->getPageValue('count', 0));
			if ($limit > 0) {
				$yvComment->setPageValue('yvcomment_limit', $limit);
			}
		}	  	

		if ($Ok) {
			$config = array ();
			$config['task'] = $task;
			$config['view'] = $viewName;
	
			// This is needed only because we can't 'undefine' this:
			//define( 'JPATH_COMPONENT',					JPATH_BASE.DS.'components'.DS.$name);
			$config['base_path'] = JPATH_SITE_YVCOMMENT;
	
			// Create the controller
			$controller = new yvcommentController($config);
	
			// Perform the Request task
			$controller->execute($task);
	
			$strOut .= $controller->getOutput();
		}

  	$yvComment->EndInstance($InstanceInd);
		return $strOut;
	}

	// Unify the introtext and fulltext fields before passing to editor...
	// for compatibility with content plugins...
	// The only difference with Joomla! core behavior is that
	//  there is no <hr> in case introtext is empty 
	//  (for compatibility with versions of yvComment prior v.1.21.0)
	function UnifyIntrotextFulltext(& $item) {
		$text = '';
		if ($item) {
			$text = $item->introtext;
			if (JString::strlen($item->fulltext) > 0) {
				if (JString::strlen($text) > 0) {
					$text = $text . "<hr id=\"system-readmore\" />";
				}
				$text = $text . $item->fulltext;
			}	
		}
		return $text;
	}
	
	function PrepareItemForView(& $item)
	{
		$yvComment = &yvCommentHelper::getInstance();
		$Ok = (boolean)($item);
		if (!$Ok) {
			echo 'Item is not an object?! "' . print_r($item, true) . '"';
		} else if (!isset($item->introtext) && !isset($item->fulltext)) {
			$Ok = false;
			echo 'Item:"' . print_r($item, true) . '"';
		}
		if ($Ok) {
			$text = '';
			$params = new JParameter($item->attribs);

			// Based on the code from the _loadArticleParams function
			//   of the "components/com_content/models/article.php" file,
			//   but not the same code...

			// Are we showing introtext with the comment
			$ShowIntro = true; 
			if (JString::strlen($item->fulltext) > 1) {
			  if (!$params->get('show_intro', true)) {
					$ShowIntro = false; 
			  }
			}	
			if ($ShowIntro) {
				$text = $item->introtext;
			}
			if (JString::strlen($item->fulltext) > 0) {
				if (JString::strlen($text) > 0) {
					// There is intro AND fulltext in the Content
					$item->readmore_link = $yvComment->ContentIDToURL($item->id);
					$item->readmore_register = false;
					// This is an "Alternative Read more text" article parameter
					$item->readmore_text = $params->get('readmore', '');
				  if (JString::strlen($item->readmore_text) < 1) {
						$item->readmore_text = JText::sprintf('Read more...');
				  }	
				} else {
					$text = $text . $item->fulltext;
				}
			}	
			$item->text = $text;

	  	if (strpos( ' ' . $item->text,'<script') < 1) {
	  		// TODO: intellectual replace: don't replace inside script...	      	
				$item->text = str_replace(chr(10), '<br />', $item->text);
	  	}
	    if ($yvComment->getConfigValue('execute_content_plugins', '0')) {
	    	// Model is the same in the case of recursion,
	    	// so don't refer to model after calling plugins!	
	    
				$item->event = new stdClass();
				$params = new JParameter('');
		
				// Disable yvComment plugin in this view
				$params->set('yvcomment_view','none');
		
		  	$dispatcher = JDispatcher::getInstance();	
		  	$dispatcher->trigger('onPrepareContent', array (&$item, &$params, 0));			
		
				$results = $dispatcher->trigger('onAfterDisplayContent', array (&$item, &$params, 0));
				$item->event->afterDisplayContent = trim(implode("\n", $results));
	    }
		}
	}  

	// May current user add comment to this Article 
	function AddEnabled($ArticleID_in) {
		$AddEnabled = $this->AddEnabledForUser();
		if ($AddEnabled) {
			$AddEnabled = $this->AddEnabledForArticle($ArticleID_in);
		}
		return $AddEnabled;
	}	

	/**
	 * Is addition of comments to this Article allowed based on:
	 * - article's Section
	 * - article's Category
	 * - article's ID
	 * - auto close comments option
	 * - ..
	 */
	function AddEnabledForArticle($ArticleID_in) {
		static $ArticleID = null;
		static $AddEnabled = null;
		// Set to true if you want to know why you see or don't see
		//   the 'Reply/Add your comment' button.
		$debugSec = false;  

		if ($debugSec) { 
			echo '<div class="Message">'; 
		}
		if ($ArticleID != $ArticleID_in) {
			$ArticleID = $ArticleID_in;
			$AddEnabled = null;
		}	
	  if 	($AddEnabled == null) {
			$AddEnabled = false;
		  if ($ArticleID != 0) {
			if ($debugSec) { echo 'ArticleID="' . $ArticleID . '"; '; }
		    // Check if we can add comments to this article
				$articlesectionids = trim($this->getConfigValue('articlesectionids', ''));
				$articlecategoryids = trim($this->getConfigValue('articlecategoryids', ''));
				$articleids = trim($this->getConfigValue('articleids', ''));
				if ((strlen($articlesectionids) == 0) 
						&& (strlen($articlecategoryids) == 0) 
						&& (strlen($articleids) == 0)) {
					$AddEnabled = true;
				} else {
				  $blnExclude = (bool) $this->getConfigValue('articlesectionids_exclude', '0');
				  $blnFound = false;

					if (strlen($articleids) > 0) {
						// Find $ArticleID in this list
						$array1 = explode(",", $articleids);
						foreach ($array1 as $articleid1) {
							if ((int)$articleid1 == $ArticleID) {
								$blnFound = true;
								break;
							}
						} 
						if ($debugSec && $blnFound) { echo 'ArticleID found in filter; '; }
					} 
					if ((!$blnFound) && strlen($articlesectionids) > 0) {
					  $ArticleSectionID = $this->DLookup('sectionid','#__content','id=' . $ArticleID);
						// Find $ArticleSectionID in this list
						$array1 = explode(",", $articlesectionids);
						foreach ($array1 as $sectionid1) {
							if ((int)$sectionid1 == $ArticleSectionID) {
								$blnFound = true;
								break;
							}
						} 
						if ($debugSec && $blnFound) { echo 'SectionID=' . $ArticleSectionID . ' found in filter; '; }
					} 
					if ((!$blnFound) && (strlen($articlecategoryids) > 0)) {
					  $ArticleCategoryID = $this->DLookup('catid','#__content','id=' . $ArticleID);
						// Find $ArticleCategoryID in this list
						$array1 = explode(",", $articlecategoryids);
						foreach ($array1 as $categoryid1) {
							if ((int)$categoryid1 == $ArticleCategoryID) {
								$blnFound = true;
								break;
							}
						} 
						if ($debugSec && $blnFound) { echo 'CategoryID=' . $ArticleCategoryID . ' found in filter; '; }
					} 
					
					if ($blnFound) {
						$AddEnabled = !$blnExclude;
					} else {
						$AddEnabled = $blnExclude;
						if ($debugSec && !$blnExclude) { echo 'Not found in filter (SectionID=' . ( isset($ArticleSectionID) ? $ArticleSectionID : '?') . '; CategoryID=' . ( isset($ArticleCategoryID) ? $ArticleCategoryID : '?') . '); '; }
					}
				}
		  }
		  if ($AddEnabled) {
				if ($this->CommentsAreClosed($ArticleID)) {
					$AddEnabled = false;
					if ($debugSec) { echo 'Comments are closed; '; }
				}
		  }
		  if ($AddEnabled) {
				$comments_on_comment = $this->getConfigValue('allow_comments_on_comment', '0');
				switch ($comments_on_comment) {
					case 'administrators_reply_only' :
					case 'owners_reply_only' :
					case 'one_level_deep' :
						if ($this->IsCommentByID($ArticleID)) {
							// 'Article' is in fact, comment, so let's rename variable
							$CommentID = $ArticleID;
							$ParentID = $this->DLookup('parentid', $this->getTableName(), 'id=' . $CommentID);
							if ($this->IsCommentByID($ParentID)) {
								// no more, than one level
								$AddEnabled = false;
								if ($debugSec) { echo 'No more, than one level; '; }
							} elseif (($comments_on_comment == 'owners_reply_only') ||
									($comments_on_comment == 'administrators_reply_only')	) {
								// Are there any comments already
								$ChildID = $this->DLookup('id', $this->getTableName(), 'parentid=' . $CommentID);
								if ($ChildID != 0) {
									// Only one 'Owners reply' (or any reply...) is allowed
									$AddEnabled = false;
									if ($debugSec) { echo 'Only one reply is allowed; '; }
								} else {
									// Is User Owner or Admin?
				          if ($this->UserCanEdit() != 'all') {
										// User is not Admin
										if ($comments_on_comment == 'administrators_reply_only') {
											$AddEnabled = false;
											if ($debugSec) { echo 'User is not Admin; '; }
										} else {
											$ArticleAuthorID = $this->DLookup('created_by', $this->getTableName(), 'id=' . $CommentID);
					  					$user		=& JFactory::getUser();
						          if ($user->get('id') != $ArticleAuthorID) {
												// User is not the Owner of the article (and is not Admin...)
												$AddEnabled = false;
												if ($debugSec) { echo 'User is not the Owner of the Article; '; }
						          }  
										}
				          }
								}
							}
						}
						break;
					case 'threaded_comments' :
						// no restrictions on level of comments...
						break;
					default : // No
						if ($this->IsCommentByID($ArticleID)) {
							$AddEnabled = false;
							if ($debugSec) { echo 'Comment on comment is disabled; '; }
						}
				}
		  }
	  }
		if ($debugSec) { 
			echo ' AddEnabledForArticle="' . $AddEnabled . '"; </div>'; 
		}
		return $AddEnabled;
	}

	// Is addition of comments for this User allowed? 
	//   (At least, to some articles...)
	function AddEnabledForUser() {
	  static $AddEnabledForUser = null;
	  $debugSec = false;
	  if 	($AddEnabledForUser == null) {
			$AddEnabledForUser = false;
			if ($this->getConfigValue('allow_guest_add', 0)) {
				$AddEnabledForUser = true;
			} elseif (!$this->UserIsGuest()) {
					$AddEnabledForUser = true;
			}
	  }
		if ($debugSec) { 
			echo '<div class="Message">AddEnabledForUser="' . $AddEnabledForUser . '"; </div>'; 
		}
		return $AddEnabledForUser;
	}

	// Is User a Guest?
	function UserIsGuest() {
	  static $UserIsGuest = null;
	  if 	($UserIsGuest == null) {
		  $user		=& JFactory::getUser();
			$aid = $user->get('aid', 0);
			if ($aid < 1) {
				$UserIsGuest = true;
			}  
	  }
		return $UserIsGuest;
	}

	function UserIsRegistered() {
		return (!$this->UserIsTemp() && !$this->UserIsGuest());
	}

	function UserIsTemp() {
		return $this->_UserIsTemp;
	}

	// 'none', 'all' , 'own'
	function UserCanEdit() {
		return	$this->_UserCanEdit;
	}

	// May current user edit this Comment
	function EditEnabled($item) {
		static $CommentID = null;
		static $EditEnabled = null;
  	$CommentID_in = 0;
	  
	  if (isset($item->id)) {
	  	$CommentID_in = (integer) $item->id;
	  } else {
	  	$CommentID_in = (integer) $item;
	  }
	  if ($CommentID_in == 0) {
	  	return false;
	  }	
		
		if ($CommentID != $CommentID_in) {
			$CommentID = $CommentID_in;
			$EditEnabled = null;
		}	
	  if 	($EditEnabled == null) {
			$EditEnabled = false;

	 		If ($this->UserCanEdit() == 'all') {
				$EditEnabled = true;
	 		} elseif ($this->UserCanEdit() == 'own') {
				$user =& JFactory::getUser();
			  if (isset($item->created_by)) {
					$created_by = $item->created_by;  
			  } else {
					$created_by = $this->DLookup('created_by', $this->getTableName(), 'id=' . $CommentID);
			  }	  
				if (($user->get('id')) == $created_by) {
					$EditEnabled = true;
				}  
	 		}
	  }
		return $EditEnabled;
	}

	// Are comments for this Article closed?
	// (If they are not closed, this yet doesn't mean, that adding comments is allowed)
	function CommentsAreClosed($ArticleID_in) {
		static $ArticleID = null;
		static $Closed = null;
		
		if ($ArticleID != $ArticleID_in) {
			$ArticleID = $ArticleID_in;
			$Closed = null;
		}	
	  if 	($Closed == null) {
			$Closed = false;
			$auto_close_days = trim($this->getConfigValue('auto_close_days', '0'));
			if ($auto_close_days > 0) {
				// auto close comments option is activated				
	  		jimport('joomla.utilities.date');
	  		$ArticleID2 = $ArticleID; // copy to work with in the loop
	  		do {
	  			// Loop to the Article itself (or first old comment...)
					$ArticleCreated = new JDate($this->DLookup('created','#__content','id=' . $ArticleID2));
					$AutoCloseTime = $ArticleCreated->toUnix() + ($auto_close_days * 24 * 60 * 60); 
					if ( $AutoCloseTime < time()) {
						$Closed = true;
						break;						
					}
					if (!$this->IsCommentByID($ArticleID2)) {
						break;
					}
					// Let's check parent
					$ArticleID2 = $this->DLookup('parentid','#__content','id=' . $ArticleID2);
	  		} while (true);
				// echo 'id="' . $ArticleID . '; "AutoCloseTime="' . $AutoCloseTime . '", time="' . time() .'"; days left="' . ($AutoCloseTime - time())/(24 * 60 * 60) . '"' . ($Closed ? ' Closed' : ' Opened') . '<br/>';
			}
	  }
		return $Closed;
	}

	function getSiteURL() {
		global $mainframe;	
	  return ($mainframe->isAdmin() ? $mainframe->getSiteURL() : JURI::base());
	}

	/**
	*	Compare version of some (yvComment) extension (plugin, module...) 
	*	with the version of the yvCommentComponent
	*/
  function VersionChecks($ExtensionName='', $ExtensionVersion='', $AlwaysWarn = false) {
		$Ok = ($this->JoomlaCoreVersionCheck($AlwaysWarn) && $this->Ok());
		if ($Ok && !empty($ExtensionVersion)) {
			$Ok = (strcmp($ExtensionVersion, $this->getCompatibleVersion()) == 0);
			if (!$Ok) {
				global $mainframe;
				$mainframe->enqueueMessage(
					'Versions of "' . $ExtensionName . '" and "yvComment Component" are not the same.<br/>' 
					. '(' . $ExtensionName . ' version="' . $ExtensionVersion . '"; Component version="' . $this->getCompatibleVersion() . '")<br/>'
					. 'Please install the same versions from <a href="http://yurivolkov.com/Joomla/yvComment/index_en.html" target="_blank">yvComment home page</a>.',
					'error');
				$this->Hide();
			}
		}
		return $Ok;
  }

	// Joomla! core version check
	// Returns Ok
  function JoomlaCoreVersionCheck($AlwaysWarn = false) {
  	static $stOk = null;  // not less, than $ver_min 
		global $mainframe;
  	if (is_null($stOk)) {
			$ver_Ok		= '1.5.12';
			$ver_min	= '1.5.6';
			$ver_min_name	= 'Joomla! version ' . $ver_min;

			$stOk = false;
  	  $message = '';
			$Warning = false;
			$Error = true;
			$ver = 0;
	
			$versionName = 'Very old (Doesn\'t have JVersion Class)';
		  $ver = $this->JoomlaShortVersion();
			if (version_compare( $ver, '1.5.1' ) < 0) {
				$Error = true;	
			} else {
			 	$versionName = 'Joomla! version ' . $ver;
			}
			if (version_compare( $ver, $ver_min ) >= 0) {
				$Error = false;	
				$stOk = true;
				if (version_compare( $ver, $ver_Ok ) < 0) {
				  if ($AlwaysWarn) {
				  	$Warning = true;
				  } else {
						$Warning = (boolean) $this->getConfigValue('joomla_version_warning', '1');
				  }
				}
			}
			if ($Error || $Warning){
				if (version_compare( $ver, '0.0.0' ) == 0) {
				  $message .= JText::_( 'JOOMLA_VERSION_IS_UNKNOWN') . '<br/>';
				  $message .= str_replace( '%1', 'Joomla! version ' . $ver_Ok, JText::_( 'JOOMLA_VERSION_WARNING_MESSAGE')) . '<br/>';
				} elseif ($Warning) {
					//Warning! Your version of Joomla is not up-to-date...
				  $message .= JText::_( 'JOOMLA_VERSION_IS_NOT_UPTODATE') . '<br/>';
				  $message .= str_replace( '%1', 'Joomla! version ' . $ver_Ok, JText::_( 'JOOMLA_VERSION_WARNING_MESSAGE')) . '<br/>';
				} else {
					$message .= str_replace( '%1', $ver_min_name, str_replace( '%2', $ver_Ok, JText::_( 'JOOMLA_VERSION_ERROR'))) . '<br/>';
				}
				if (version_compare( $ver, '0.0.0' ) > 0) {
					$message .= str_replace( '%1', $versionName, JText::_( 'CURRENT_VERSION_OF_JOOMLA')) . '<br/>';
				} 
				$message .= $this->_textSignature();
			}
			if (!$stOk) {
			  $this->_Ok = false;
			}
			if (!$stOk) {
				$mainframe->enqueueMessage($message, 'error');
				return false;
			} elseif (strlen($message) > 0) {
				$mainframe->enqueueMessage($message, 'notice');
			}
  	}
		return $stOk;
  }

	function JoomlaShortVersion(){
		jimport('joomla.version');
		$ShortVersion = '0.0.0';
		$version = new JVersion();
		if ($version) {
			$ShortVersion = $version->getShortVersion();
			//echo 'Build="' . $version->BUILD . '"<br>';
			//echo 'LongVersion="' . $version->getLongVersion() . '"<br>';
			//echo 'ShortVersion="' . $version->getShortVersion() . '"<br>';
		}  
		return $ShortVersion;
	}

	// Terminology just like in JVersion class
	function getShortVersion() {
		return $this->_Release .'.'. $this->_Dev_level;
	}
	// To compare versions of different components for compatibility
	function getCompatibleVersion() {
		return $this->_Release;
	}
	
	// Credits for install/uninstall
	function Credits() {
?>		
		<fieldset class="adminform">
			<legend><?php echo JText::_( 'CREDITS'); ?></legend>
			<table class="admintable" width="100%">
				<tr>
					<th width="40%"><?php echo JText::_( 'CREDITS_NAMES'); ?></th>
					<th width="60%"><?php echo JText::_( 'CONTRIBUTION'); ?></th>
				</tr>
				<tr>
					<td class="name">
					  <a href="http://yurivolkov.com/index_en.html" target="_blank">Yuri Volkov</a>
					</td>
					<td>
					  <?php echo JText::_( 'FOUNDER'); ?>,
					  <?php echo JText::_( 'PROJECT_LEADER' ); ?> &amp; <?php echo JText::_( 'DEVELOPER'); ?>
					</td>
				</tr>

				<!-- v.1.24.000 2009-08-30 -->
				<tr>
					<td class="name">
						<a href="mailto:mnemonic@get2net.dk" target="_blank">Mnemonic (Brian F. Knutsson)</a>					
					</td>
					<td>
					  <?php echo JText::_( 'TRANSLATOR') . ' (Danish), v.1.23'; ?>
					</td>
				</tr>
				<tr>
					<td class="name">
						<a href="http://www.woraif.cz" target="_blank">Milan Šedý</a>
					</td>
					<td>
					  <?php echo JText::_( 'TRANSLATOR') . ' (Czech)'; ?>
					</td>
				</tr>
				<tr>
					<td class="name">
					<a href="http://www.olafdryja.de" target="_blank">Olaf Dryja</a>
					</td>
					<td>
					  <?php echo JText::_( 'TRANSLATOR') . ' (German), v.1.23.0'; ?>
					</td>
				</tr>
				<tr>
					<td class="name">
					<a href="http://www.kastenmaier.de" target="_blank">Ragnar Kastenmaier</a>
					</td>
					<td>
					  <?php echo JText::_( 'TRANSLATOR') . ' (German), v.1.23.0'; ?>
					</td>
				</tr>

				<!-- v.1.22.000 2009-05-14 -->
				<tr>
					<td class="name">
						<a href="marekal@post.pl" target="_blank">Marek Pietraszek</a>
					</td>
					<td>
					  <?php echo JText::_( 'TRANSLATOR') . ' (Polish)'; ?>
					</td>
				</tr>
				<tr>
					<td class="name">
						<a href="http://www.joomlacommunity.eu/" target="_blank">Translation team of JoomlaCommunity.eu</a>
					</td>
					<td>
					  <?php echo JText::_( 'TRANSLATOR') . ' (Dutch)'; ?>
					</td>
				</tr>
				<tr>
					<td class="name">
					<a href="http://www.szathmari.hu" target="_blank">Andor Szathmári</a>
					</td>
					<td>
					  <?php echo JText::_( 'TRANSLATOR') . ' (Hungarian)'; ?>
					</td>
				</tr>
				<tr>
					<td class="name">
						<a href="http://www.marlboroteam.eu/" target="_blank">Simone Cremonesi</a>
					</td>
					<td>
					  <?php echo JText::_( 'TRANSLATOR') . ' (Italian)'; ?>
					</td>
				</tr>

				<!-- v.1.21.000 2009-03-09 -->
				<tr>
					<td class="name">
						<a href="http://ziolczynski.pl" target="_blank">Tomasz Ziółczyński</a>
					</td>
					<td>
					  <?php echo JText::_( 'TRANSLATOR') . ' (Polish)'; ?>
					</td>
				</tr>
				<tr>
					<td class="name">
						<a href="http://befria.se" target="_blank">Markus Larsson</a>
					</td>
					<td>
					  <?php echo JText::_( 'TRANSLATOR') . ' (Swedish)'; ?>
					</td>
				</tr>
				<tr>
					<td class="name">
					<a href="http://www.igoia.info" target="_blank">Fabio Gameleira</a>
					</td>
					<td>
					  <?php echo JText::_( 'TRANSLATOR') . ' (Portuguese/Brasil)'; ?>
					</td>
				</tr>
				<tr>
					<td class="name">
					<a href="http://www.gencat.cat/ics" target="_blank">Xavier Montaña Carreras</a>
					</td>
					<td>
					  <?php echo JText::_( 'TRANSLATOR') . ' (Catalan)'; ?>
					</td>
				</tr>
				<tr>
					<td class="name">
					<a href="mailto:asut@takas.lt" target="_blank">Andrius Sutkevičius</a>
					</td>
					<td>
					  <?php echo JText::_( 'TRANSLATOR') . ' (Lithuanian)'; ?>
					</td>
				</tr>
				<tr>
					<td class="name">
					<a href="" target="_blank">FoxZilla</a>
					</td>
					<td>
					  <?php echo JText::_( 'TRANSLATOR') . ' (Finnish)'; ?>
					</td>
				</tr>
				<tr>
					<td class="name">
						<a href="http://www.dzconstantine.com" target="_blank">Saber Bousba</a>
					</td>
					<td>
					  <?php echo JText::_( 'TRANSLATOR') . ' ( Arabic (Algeria))'; ?>
					</td>
				</tr>
				<tr>
					<td class="name">
						<a href="http://www.droit-medical.com" target="_blank">Bertrand Hue</a>
					</td>
					<td>
					  <?php echo JText::_( 'TRANSLATOR') . ' (French)'; ?>
					</td>
				</tr>

				<!-- v.1.20.000 2008-01-25 -->
				<tr>
					<td class="name">
					<a href="http://webbizltd.com/" target="_blank">founder[at}webbizltd.com</a>
					</td>
					<td>
					  <?php echo JText::_( 'TRANSLATOR') . ' (English)'; ?>
					</td>
				</tr>
				<tr>
					<td class="name">
						<a href="http://www.VnEtips.com" target="_blank">VnEtips</a>
					</td>
					<td>
					  <?php echo JText::_( 'TRANSLATOR') . ' (Vietnamese)'; ?>
					</td>
				</tr>
				<tr>
					<td class="name">
						<a href="http://mslonik.pl" target="_blank">mslonik</a>
					</td>
					<td>
					  <?php echo JText::_( 'TRANSLATOR') . ' (Polish)'; ?>
					</td>
				</tr>
				<tr>
					<td class="name">
					<a href="http://www.orion.com.mk" target="_blank">ScHRiLL</a>
					</td>
					<td>
					  <?php echo JText::_( 'TRANSLATOR') . ' (Macedonian)'; ?>
					</td>
				</tr>
				<tr>
					<td class="name">
					<a href="http://www.loopgroepbedum.nl/" target="_blank">Peter v.d. Hulst</a>
					</td>
					<td>
					  <?php echo JText::_( 'DEVELOPER'); ?>
					</td>
				</tr>

				<!-- before 2008-11-30 -->
				<tr>
					<td class="name">
						<a href="http://www.rijnieks.lv" target="_blank">Krišjānis Rijnieks</a>
					</td>
					<td>
					  <?php echo JText::_( 'TRANSLATOR') . ' (Latvian)'; ?>
					</td>
				</tr>
				<tr>
					<td class="name">
						<a href="http://webdesignzone.net" target="_blank">Zoran Ilić</a>
					</td>
					<td>
					  <?php echo JText::_( 'TRANSLATOR') . ' (Serbian-latin)'; ?>
					</td>
				</tr>
				<tr>
					<td class="name">
						<a href="" target="_blank">Pawel Frankowski</a>
					</td>
					<td>
					  <?php echo JText::_( 'TRANSLATOR') . ' (Polish)'; ?>
					</td>
				</tr>
				<tr>
					<td class="name">
						<a href="" target="_blank">Arno Becht</a>
					</td>
					<td>
					  <?php echo JText::_( 'TRANSLATOR') . ' (Dutch)'; ?>
					</td>
				</tr>
				<tr>
					<td class="name">
						<a href="http://tomi.malensek.com" target="_blank">Tomi Malenšek</a>
					</td>
					<td>
					  <?php echo JText::_( 'TRANSLATOR') . ' (Slovenian)'; ?>
					</td>
				</tr>
				<tr>
					<td class="name">
						<a href="http://klug.gr" target="_blank">Ziouzios Dimitrios and Stefanidis Fotios</a>
					</td>
					<td>
					  <?php echo JText::_( 'TRANSLATOR') . ' (Greek)'; ?>
					</td>
				</tr>
				<tr>
					<td class="name">
						<a href="http://uskk.info" target="_blank">Vitalij Mojsejiv</a>
					</td>
					<td>
					  <?php echo JText::_( 'TRANSLATOR') . ' (Ukrainian)'; ?>
					</td>
				</tr>
				<tr>
					<td class="name">
						<a href="http://www.triangulodigital.com" target="_blank">Rui Braz</a>
					</td>
					<td>
					  <?php echo JText::_( 'TRANSLATOR') . ' (European Portguese)'; ?>
					</td>
				</tr>
				<tr>
					<td class="name">
						<a href="http://www.joomlagate.com/" target="_blank">baijianpeng</a>
					</td>
					<td>
					  <?php echo JText::_( 'TRANSLATOR') . ' (Chinese)'; ?>
					</td>
				</tr>
				<tr>
					<td class="name">
					<a href="http://www.layan.us" target="_blank">Massalha Shady</a>
					</td>
					<td>
					  <?php echo JText::_( 'TRANSLATOR') . ' (Arabic)'; ?>
					</td>
				</tr>

				<!-- before v.19.0 -->
				<tr>
					<td class="name">
					<a href="http://www.pleijerit.net" target="_blank">Axel Toivonen</a>
					</td>
					<td>
					  <?php echo JText::_( 'TRANSLATOR') . ' (Finnish)'; ?>
					</td>
				</tr>
				<tr>
					<td class="name">
					<a href="" target="_blank">louBaN</a>
					</td>
					<td>
					  <?php echo JText::_( 'TRANSLATOR') . ' (Galician)'; ?>
					</td>
				</tr>
				<tr>
					<td class="name">
					<a href="http://joomla.co.il" target="_blank">Yair Lahav</a>
					</td>
					<td>
					  <?php echo JText::_( 'TRANSLATOR') . ' (Hebrew)'; ?>
					</td>
				</tr>
				<tr>
					<td class="name">
					<a href="http://primavolta.hr" target="_blank">Tomislav Konestabo</a>
					</td>
					<td>
					  <?php echo JText::_( 'TRANSLATOR') . ' (Croatian)'; ?>
					</td>
				</tr>
				<tr>
					<td class="name">
					<a href="http://www.ibrary.co.kr" target="_blank">Opiokane</a>
					</td>
					<td>
					  <?php echo JText::_( 'TRANSLATOR') . ' (Korean)'; ?>
					</td>
				</tr>
				<tr>
					<td class="name">
						<a href="yvcomment@chris.misker.nl" target="_blank">Chris Misker</a>
					</td>
					<td>
					  <?php echo JText::_( 'TRANSLATOR') . ' (Dutch)'; ?>
					</td>
				</tr>

				<tr>
					<td class="name">
					<a href="" target="_blank">vbr_82</a>
					</td>
					<td>
					  <?php echo JText::_( 'TRANSLATOR') . ' (Bulgarian)'; ?>
					</td>
				</tr>
				<tr>
					<td class="name">
					<a href="http://www.arneespedal.com" target="_blank">Arne Buchholdt Espedal</a>
					</td>
					<td>
					  <?php echo JText::_( 'TRANSLATOR') . ' (Norwegian)'; ?>
					</td>
				</tr>
				<tr>
					<td class="name">
						<a href="http://www.zemj.com" target="_blank">ZemJ</a>
					</td>
					<td>
					  <?php echo JText::_( 'TRANSLATOR') . ' (Czech)'; ?>
					</td>
				</tr>
				<tr>
					<td class="name">
						<a href="http://www.ircaserta.com" target="_blank">Sergio De Falco aka SGr33n</a>
					</td>
					<td>
					  <?php echo JText::_( 'TRANSLATOR') . ' (Italian)'; ?>
					</td>
				</tr>
				<tr>
					<td class="name">
						<a href="http://www.szathmari.hu" target="_blank">Andor Szathmári</a>
					</td>
					<td>
					  <?php echo JText::_( 'TRANSLATOR') . ' (Hungarian)'; ?>
					</td>
				</tr>
				<tr>
					<td class="name">
						<a href="http://florent.nouvellon.net" target="_blank">Florent NOUVELLON</a>
					</td>
					<td>
					  <?php echo JText::_( 'TRANSLATOR') . ' (French)'; ?>
					</td>
				</tr>
				<tr>
					<td class="name">
						<a href="http://pcw.se" target="_blank">Jerry Norén</a>
					</td>
					<td>
					  <?php echo JText::_( 'TRANSLATOR') . ' (Swedish)'; ?>
					</td>
				</tr>
				<tr>
					<td class="name">
						<a href="http://www.revealmusic.se/" target="_blank">Mikael Karlsson (aka Mika3l)</a>
					</td>
					<td>
					  <?php echo JText::_( 'TRANSLATOR') . ' (Swedish)'; ?>
					</td>
				</tr>
				<tr>
					<td class="name">
						<a href="http://www.beza.com.ar" target="_blank">Enrique F. Becerra</a>
					</td>
					<td>
					  <?php echo JText::_( 'TRANSLATOR') . ' (Spanish)'; ?>
					</td>
				</tr>
				<tr>
					<td class="name">
						<a href="http://www.wsonline.nl" target="_blank">Wim Strik</a>
					</td>
					<td>
					  <?php echo JText::_( 'TRANSLATOR') . ' (Dutch)'; ?>
					</td>
				</tr>
				<tr>
					<td class="name">
						<a href="http://www.peterschluter.dk" target="_blank">Peter	Schlüter</a>					
					</td>
					<td>
					  <?php echo JText::_( 'TRANSLATOR') . ' (Danish)'; ?>
					</td>
				</tr>
				<tr>
					<td class="name">
					<a href="http://sefiroth.de/HomePage/Norman" target="_blank">Norman	Markgraf</a>
					</td>
					<td>
					  <?php echo JText::_( 'TRANSLATOR') . ' (German)'; ?>
					</td>
				</tr>
				<tr>
					<td class="name">
					  <a href="mailto:sheldon.young@gmail.com" target="_blank">Sheldon Young</a>
					</td>
					<td>
					  <?php echo JText::_( 'DEVELOPER'); ?>
					</td>
				</tr>
				<tr>
					<td class="name">
					  <a href="http://www.cmsturk.net" target="_blank">Asthon and Ercan Özkaya (CmsTürk)</a>
					</td>
					<td>
					  <?php echo JText::_( 'TRANSLATOR') . ' (Turkish)'; ?>
					</td>
				</tr>
				<tr>
					<td class="name">
					  <a href="http://joomfa.org" target="_blank">Joomfa Team</a>
					</td>
					<td>
					  <?php echo JText::_( 'TRANSLATOR') . ' (Persian/Farsi)'; ?>
					</td>
				</tr>
			</table>
		</fieldset>
<?php		
	}

	/**
	 * Get the value from the comment metadata, which is an INI formatted string.
	 * Empty input will return null.
	 */
	function getValueFromIni($metadata_str, $key = '')
	{
		if(empty($metadata_str)) {
			return null;
		}

		$metadata_registry = new JRegistry();
		$metadata_registry->loadINI($metadata_str);
		$value = $metadata_registry->getValue($key);
		switch ($key) {
			case 'created_by_link':
			case 'created_by_email':
				$value = urldecode(html_entity_decode($value));
				break;
				
		}
		//echo '"' .$key . '"-"' . $value . '"<br />';
		return $value;
	}

  /**
   * Convert an arbitrary string into an XHTML-safe HREF that can be used as a link.
   * Used to allow users to enter a link back to their site.  No additional quoting is
   * required, it is XHTML safe.
	 *
	 * The username and password is not preserved in the URL to prevent users from  
   * inadvertently giving away a sensitive login.
   */
  function strToSafeHref($str) 
	{
		$debug = false;

		// First step is to remove any obvious attempts at cross site scripting. This will 
		// inadvertently catch some valid URLs, but it's a small price to pay for the
		// additional safety.
		$str = preg_replace('/script:/', '', $str);

		// If there is no host there may be a missing scheme.
    $uri = new JURI(trim($str));
		if(!$uri->getHost()) {
			$uri = new JURI('http://'.trim($str));
		}

		if($debug) {
			echo "URI = $str\n";
			echo "\tURI scheme = ".$uri->getScheme(), "\n";
			echo "\tURI host = ".$uri->getHost(), "\n";
			echo "\tURI port = ".$uri->getPort(), "\n";
			echo "\tURI path = ".$uri->getPath(), "\n";
			echo "\tURI query = ".$uri->getQuery(), "\n";
			echo "\tURI fragment = ".$uri->getFragment(), "\n";
		}

    // Only allow http or https as the protocol
		$scheme = 'http';
		if($uri->getScheme()) {
			$scheme = strtolower($uri->getScheme());
		}
		if($scheme != 'http' && $scheme != 'https' ) {
			$scheme = 'http';
		}	
		$result = $scheme.'://';

		// There must be a host.
		$host = $uri->getHost();
		$filter = JFilterInput::getInstance();
		if(!empty($host)) {
			$host = $filter->clean($host, 'string');
		}
		if(empty($host)) {
			return null;
		} 
		$result .= urlencode(htmlentities($host));

		// Port number must be an integer.
		$port = $uri->getPort();
		if(!empty($port)) {
			if(!is_numeric($port) || (intval($port) != $port)) {
				return null;
			} else {
				$port = intval($port);
				$result .= ':'.$port;
			}
		}

		// Path without a leading slash.
		$path = $uri->getPath();
		if(!empty($path)) {
			$path = preg_replace('/^[\\\\\/]/', '', $path);
			$components = preg_split('/^[\\\\\/]/', $path);
			foreach($components as $component) {
				$component = $filter->clean($component, 'string');
			}
			$path = join('/', array_map('urlencode', array_map('htmlentities', $components)));
			$result .= '/'.$path;
		}

		// Query.  Clean each name and value pair.
		$query = $uri->getQuery();
		if(!empty($query)) {
			$first_pair = true;
			$pairs = preg_split('/&/', $query);
			foreach($pairs as $pair) {
				$components = preg_split('/[=]/', $pair);
				foreach($components as $component) {
					$component = $filter->clean($component, 'string');
				}

				$result .= $first_pair ? '?' : '&amp;';
				$result .= join('=', array_map('urlencode', array_map('htmlentities', $components)));
				$first_pair = false;
			}
		}

		// Fragment
		$fragment = $uri->getFragment();
		$fragment = $filter->clean($fragment, 'string');
		if(!empty($fragment)) {
			$result .= '#'.urlencode(htmlentities($fragment));
		}

		if($debug) {
			echo "\tResult = $result\n";
		}
		return $result;
  }

	/*
	// Test cases for strToSafeHref.
	$list = array(
 		'http://www.example.com/valid/path/to.cgi?with&arg=42#and-fragment'
		, 'example.com'
	  , 'HTTP://www.example.com'
 	 	, 'http://foo/javascript:/inpath'
		, 'http://javascript:/gotcha'
 		, 'javascript://www.example.com'
  	, "java\nscript://www.example.com"
  	, 'http://javascript:alert("Opps")'
  	, 'http:///nohost'
  	, 'http://backslashes/c:\\autoexec.bat'
  	, 'http://host.with/42<6x9'
	);
	echo '<pre>';
	foreach( $list as $str ) {
		$safeHref = strToSafeHref($str);
	  echo '<a href="', $safeHref, '">', htmlentities($str), "</a><br />\n";
	}
	echo '</pre>';
	*/

	// TODO: Smart trim words of plain text
	function TrimText($text_in, $maxlength, $more='')
	{
		$strOut = JString::trim(strip_tags($text_in));
		if(($maxlength > 0) && (JString::strlen($strOut) > $maxlength)) {
			if ($more=='') $more = "&hellip;";
			$strOut = JString::substr($strOut, 0, $maxlength) . $more;
		} 
		return $strOut;
	}

	// Signature of this Extension
	function _textSignature() {
		$message = '<br/>-- <br/>' .
		'<a href="http://yurivolkov.com/Joomla/yvComment/index_en.html" target="_blank">' . 
		'yvComment solution, version="' . 
		$this->getShortVersion() . '"</a>'; 
		return $message;
	}
	
	function log($message) {
    error_log($message);
    //syslog(LOG_INFO, $message);

		//global $mainframe;
		//if (is_object($mainframe)) {
		//	$mainframe->enqueueMessage($message, 'notice');
		//}
	}

  function CommentIDToURL($id_in, $pathonly = true, $UserID = null, $ShowUnpublished = false) {
  	$url = null;
    if ($this->UseContentTable()) {
			$url = $this->ContentIDToURL($id_in, $pathonly, $UserID, $ShowUnpublished);     	
    }
    return $url;
  }

	 /* Build URL to the Article by it's ID, taking SEF settings, 
	 * security for UserID etc. into account 
   * @version		$Id: helpers.php 2 2009-08-30 15:10:43Z yvolk $
	 * @author	  yvolk (Yuri Volkov) <http://yurivolkov.com>
   * Based on code from getList function of 'modules/mod_latestnews/helper.php'
	 * It is very strange, that Joomla didn't have such function yet...
	 * It has now :-), but the implementation is far from ideal...
	 * 
	 * Optionally $fragment may be added to the URL  (v.0009)
	 */    
  function ContentIDToURL($id_in, $pathonly = true, $UserID = null, $ShowUnpublished = false, $fragment = "") {
    $debug = false;
    //$debug = ($id_in == 21);
    $message = '';
  	$id = (integer) $id_in;
  	$url = '';
  	if ($id > 0) {
			global $mainframe;
	
			$db			=& JFactory::getDBO();
			$user		=& JFactory::getUser($UserID);
			$userId		= (int) $user->get('id');
			$aid		= $user->get('aid', 0);
	
			$contentConfig = &JComponentHelper::getParams( 'com_content' );
			$access		= !$contentConfig->get('show_noauth');
			$where = '';
			if ($debug) {
			  $message .= 'articleid=' . $id_in . '<br/>';
			}
			if (!$ShowUnpublished) {
				$nullDate	= $db->getNullDate();
				jimport('joomla.utilities.date');
				$date = new JDate();
				$now = $date->toMySQL();
		
				// Links to the archived content are enabled, so state = -1
				$where		= 'a.state IN(-1, 1)'
					. ' AND ( a.publish_up = '.$db->Quote($nullDate).' OR a.publish_up <= '.$db->Quote($now).' )'
					. ' AND ( a.publish_down = '.$db->Quote($nullDate).' OR a.publish_down >= '.$db->Quote($now).' )'
					;
			}		

			if (!empty($where)) {
				$where .= ' AND ';
			}			
			$where .= 'a.id = ' . $id;
			if ($access) {
				$where .= ' AND a.access <= ' .(int) $aid 
				. ' AND ((cc.access <= ' .(int) $aid. ') OR cc.id IS NULL)' 
				. ' AND ((s.access <= ' .(int) $aid . ') OR s.id IS NULL)';
			}	
			if (!$ShowUnpublished) {
				$where .= ' AND ((s.published = 1) OR (s.id IS NULL))' .
					' AND ((cc.published = 1) OR (cc.id IS NULL))';
			}		
	
			// Just like it is done in 'components/com_content/models/article.php'
			$query = 'SELECT a.sectionid,' .
				' CASE WHEN CHAR_LENGTH(a.alias) THEN CONCAT_WS(":", a.id, a.alias) ELSE a.id END as slug,'.
				' CASE WHEN CHAR_LENGTH(cc.alias) THEN CONCAT_WS(":", cc.id, cc.alias) ELSE cc.id END as catslug'.
				' FROM #__content AS a' .
				' LEFT JOIN #__categories AS cc ON cc.id = a.catid' .
				' LEFT JOIN #__sections AS s ON s.id = a.sectionid' .
				' WHERE '. $where;
				
			$db->setQuery($query);
			$row = $db->loadObject();
			if (!is_null($row)) {
			  if ($debug) {
			    $message .= 'slug=' . $row->slug . '<br/>';
			  }
				// Just like it is done in 'components/com_content/views/article/view.html.php'
				$route = ContentHelperRoute::getArticleRoute($row->slug, $row->catslug, $row->sectionid); 
				if (strlen($fragment) > 0) {
					$route .= '#' . $fragment;	
				}
				$url = JRoute::_($route);
				if (strlen($fragment) > 0) {
					// 2009-07-11 There is Joomla! bug in v.1.5.12:
					// JURI class remembers the 'fragment' of the processed URI and uses it
					// in subsequent calls to build URIs...
					// So let's clear this fragment  
					$uri =& JFactory::getURI();
					$uri->setFragment('');
				}
				
			  if ($debug) {
			    $message .= 'url_1=\'' . $url . '\'<br/>';
			  }
				
				// This hack is needed for the backend.
				// Oherwise the link would lead to the Article manager...
				if ( (!$pathonly)	
					|| (substr($url, 0, 1) != '/')
						) {
					// Code from JURI::root()
					$uri =& JURI::getInstance(JURI::base());
					$root = array();
					$root['prefix'] = $uri->toString( array('scheme', 'host', 'port') );
					$root['path']   = rtrim($uri->toString( array('path') ), '/\\');

					if (substr($url, 0, 1) != '/') {
						// Maybe this will not be executed...
						// Do we need 'path' of the 'base' here?
						// $url = $root['path'] . '/' . $url;
						$url = '/' . $url;
					}
					if (!$pathonly) {
						$url = $root['prefix'] . $url;
					} 
				  if ($debug) {
				    $message .= 'prefix=\'' . $root['prefix'] . '\'<br/>';
				    $message .= 'path=\'' . $root['path'] . '\'<br/>';
				  }
				}
			}	else {
			  if ($debug) {
			    $message .= 'row is null<br/>';
					$message .= $this->printDbErrors($db);
			  }
			}
  	}
	  if ($debug) {
	    $message .= 'url_3=\'' . $url . '\'<br/>';
	  }
  	if ($message) {
  	  //global $mainframe;
			//$mainframe->enqueueMessage($message, 'notice');
			$url .= 'DEBUGMSGSTART' . $message . 'DEBUGMSGEND'; 
  	}
    return $url;
  }

	// Template helper function to create HTML elements to mention Author
	// and, optionally, link to the Author  
  function htmlAuthorName(&$ViewObj, &$item) {
  	//echo print_r($ViewObj, true);
  	$link = '';

		$SpanClass = '';
		$AuthorName = '';
  	$url = '';
  	$IsExternalLink = false;

		if ($item->created_by == $this->getGuestID()) {
			$created_by_username = $this->getValueFromIni($item->metadata, 'created_by_username');
			if ($created_by_username) {
				$SpanClass = 'CommentAuthorOpenID';
				$AuthorName = $item->created_by_alias;
			} else {
				$SpanClass = 'CommentAuthorAlias';
				$AuthorName = $item->created_by_alias;
			}
    } else {
			$SpanClass = 'CommentAuthorName';
    	if (isset($item->AuthorName)) {
    		$AuthorName = $item->AuthorName;
    	} else {
				$author_mentioned_by = $this->getConfigValue('author_mentioned_by', 'name');	
				$AuthorName = $this->DLookup($author_mentioned_by, '#__users', 'id=' . $item->created_by);
    	}
    }
				
		if ($ViewObj->params->get('author_linkable')) {
			// If Author is Guest
			if ($item->created_by == $this->getGuestID()) {
				// For Guests only: 	
				$url = $this->getValueFromIni($item->metadata, 'created_by_link');
				if(empty($url)) {
					$url = $this->getValueFromIni($item->metadata, 'created_by_username');
				}
				if(empty($url)) {
					$url = $this->getValueFromIni($item->metadata, 'created_by_email');
					if(!empty($url)) {
						$url = 'mailto:' . $url;
					}
				}
				if(!empty($url)) {
					$IsExternalLink = true; 
				}
			} else switch ($ViewObj->params->get('author_linkable')){
				case 'link_to_the_cb_profile':
					$url = JRoute::_('index.php?option=com_comprofiler&task=userprofile&user=' . $item->created_by);
					$pathonly = false;
					// This hack is needed for the backend.
					// Oherwise the link would lead to the CB Admin root (Article manager for articles)...
					if ( (!$pathonly)	
						|| (substr($url, 0, 1) != '/')
							) {
						if (substr($url, 0, 1) != '/') {
							// Maybe this will not be executed...
							// Do we need 'path' of the 'base' here?
							// $url = $root['path'] . '/' . $url;
							$url = '/' . $url;
						}
						if (!$pathonly) {
							// Code from JURI::root()
							$uri =& JURI::getInstance(JURI::base());
							$root['prefix'] = $uri->toString( array('scheme', 'host', 'port') );
							$url = $root['prefix'] . $url;
						} 
					}
					break;
				default:
					//echo print_r($item, true);
					if (isset($item->webpage)) {	
						$url = $item->webpage;
					}
					if(!empty($url)) {
						$IsExternalLink = true; 
					}
			}
		}

		// Everything is ready, so let's build the link		
		$link = $AuthorName;
		if(!empty($link)) {
			if(!empty($url)) {
				$link = '<a href="' . $url . '"' . ($IsExternalLink ? ' target="_blank"' : '') . '>'
				. $link
				. '</a>';
			}
			if(!empty($SpanClass)) {
				$link = '<span class="' . $SpanClass . '">'
				. $link
				. '</span>';
			}
    }
		return $link;
  }

	// Build text 'to mention Author' for this content table row (item...)
  function txtAuthorName(&$item) {
		$AuthorName = '';
		if (is_object($item) && isset($item->created_by)) {
			if ($item->created_by == $this->getGuestID()) {
				$created_by_username = $this->getValueFromIni($item->metadata, 'created_by_username');
				if ($created_by_username) {
					$AuthorName = $item->created_by_alias;
				} else {
					$AuthorName = $item->created_by_alias;
				}

				$details = '';
				$part = $this->getValueFromIni($item->metadata, 'created_by_username');
				if (!empty ($part) && (!JString::stristr($AuthorName . $details, $part))) {
					if (!empty ($details)) {	
						$details .= ' , '; 
					}
					$details .= $part;
				}
				$part = $this->getValueFromIni($item->metadata, 'created_by_link');
				if (!empty ($part) && (!JString::stristr($AuthorName . $details, $part))) {
					if (!empty ($details)) {	
						$details .= ' , '; 
					}
					$details .= $part;
				}
				$part = $this->getValueFromIni($item->metadata, 'created_by_email');
				if (!empty ($part) && (!JString::stristr($AuthorName . $details, $part))) {
					if (!empty ($details)) {	
						$details .= ' , '; 
					}
					$details .= $part;
				}
				if (!empty ($details)) {
					//$AuthorName .= ' ( ' . $details . ' )';
					$AuthorName .= ' , ' . $details;
				}
	    } else {
	    	// Not guest
	    	if (isset($item->AuthorName)) {
	    		$AuthorName = $item->AuthorName;
	    	} else {
					$author_mentioned_by = $this->getConfigValue('author_mentioned_by', 'name');	
					$AuthorName = $this->DLookup($author_mentioned_by, '#__users', 'id=' . $item->created_by);
	    	}
	    }
		}
		return $AuthorName;
  }
	
	/**
	 *  Database helper functions
	 *  I didn't put them inside yvCommentHelper to reduce calling code  
	 */
	function DLookup($Expression, $Domain, $Criteria) {
	  $db =& JFactory::getDBO();
	  return $this->DLookup_db($db, $Expression, $Domain, $Criteria);
	}
	
	function DLookup_db(&$db, $Expression, $Domain, $Criteria = '') {
	  $debug = false;
	  $sOut = null;
	  $query = '';
	  if (strpos($Expression, 'SELECT') === 0) {
	  	// this is already SQL statement, just execute it
		  $query = $Expression;
	  } else {
		  $query = 'SELECT';
		  $query .=  ' (' . $Expression . ') As Expr1';
		  $query .= ' FROM ' . $Domain;
		  if ($Criteria) {
		    $query .= ' WHERE (' . $Criteria . ')';
		  }
	  }
		$row = null;
		$db->setQuery($query);
	  $row = $db->loadObject();
		if ($db->getErrorNum() > 0) {
		  echo $this->printDbErrors($db);
		}
	  
		if (is_object($row))	{
		  if ($debug) {
		    echo get_class($row). '<br />';
		    echo 'object_vars=' . '<br />';
	      foreach(get_object_vars($row) as $key=>$val) {
	        echo $key . '="' . $val . '"<br />';
	      }
		    echo 'query="' . $query . '"<br />';
		  }  
			$sOut = $row->Expr1;
		}
	  return $sOut;
	}

	function TableExists($pattern, $prefix='#__') {
	  $db =& JFactory::getDBO();
	  return $this->TableExists_db($db, $pattern, $prefix);
	}

	function TableExists_db(&$db, $pattern, $prefix='#__') {
	  $Exists = false;
	  $debug = false;
	  // JDatabase class doesn't substitute prefix if table name is in single (or double) quotes
	  $pattern2 = str_replace( $prefix, $db->getPrefix(), $pattern);
	  // '_' is a literal and not part of the pattern
	  $pattern2 = str_replace( '_', '\_', $pattern2);
	  $query = 'SHOW TABLES LIKE \'' . $pattern2 . '\'';
	  if ($debug) echo 'query="' . $query . '"<br />';;
		$db->setQuery($query);
	  $row = $db->loadObject();
		if ($db->getErrorNum() == 0) {
			if (is_object($row))	{
			  if ($debug) {
			  	$ind1=1;	  
		      foreach(get_object_vars($row) as $key=>$val) {
		        echo $ind1 . '. "'. $key . '"="' . $val . '"<br />';
		        $ind1 +=1;
		      }
			  }  
				$Exists = true;
			}
		} elseif ($debug) echo $this->printDbErrors($db);  
	  if ($debug) echo ($Exists? 'true' : 'false') . '<br/>';
	  return $Exists;
	}
	
	function printDbErrors($db) {
	  $out = null;
		if ($db->getErrorNum() > 0)	{
		  $out = '<p>Database errors:' . '<br />';
			$errors[] = array('msg' => $db->getErrorMsg(), 'sql' => $db->_sql);
			foreach( $errors as $error) {
			  $out .= 'Error message: "' . $error['msg'] . '"<br />';
			  $out .= '  SQL="' . $error['sql'] . '"<br /><hr />';
			}
			$out .= '</p>';
		}
	  return $out;
	}
	
	// Return number of days, 0 - all
	function ResultDaysToNDays($result_days = 'all') {
		$nDays = 0;
		// These numbers were taken from phpbb3 code (for consistency...)
    //$limit_days	= array(0 => $user->lang['ALL_RESULTS'], 1 => $user->lang['1_DAY'],
    //  7 => $user->lang['7_DAYS'], 14 => $user->lang['2_WEEKS'], 30 => $user->lang['1_MONTH'],
    //  90 => $user->lang['3_MONTHS'], 180 => $user->lang['6_MONTHS'], 365 => $user->lang['1_YEAR']); 		
  	switch ($result_days) {
  		case '1day':
				$nDays = 1;
  		  break;
  		case '7days':
				$nDays = 7;
  		  break;
  		case '2weeks':
				$nDays = 14;
  		  break;
  		case '1month':
				$nDays = 30;
  		  break;
  		case '3months':
				$nDays = 90;
  		  break;
  		case '6months':
				$nDays = 180;
  		  break;
  		case '1year':
				$nDays = 365;
  		  break;
  	}
		return $nDays;
	}

	function DaysToSeconds($nDays = 0) {
  	$nSeconds =  $nDays * 24 * 60 * 60;
  	return $nSeconds;
	}

	// Calculate date and time $nSeconds to the past
	// Returns quoted date to be used in SQL satements
	function SecondsFromNowToSQLDate($nSeconds = 0) {
  	$sqlDateQuoted = '';
	  $db =& JFactory::getDBO();

		jimport('joomla.utilities.date');
		$date1 = new JDate();
		$date1->_date -= $nSeconds;
		$date1Sql = $date1->toMysql();
		$sqlDateQuoted = $db->Quote($date1Sql);
  	
  	return $sqlDateQuoted;
	}
}   
// End of yvCommentHelper class
	
// Fixing Joomla pagination bug 2007-10-13
jimport('joomla.html.pagination');
class yvCommentJPagination extends JPagination {
	function _buildDataObject()
	{
		$data =	parent::_buildDataObject();

		global $mainframe;
		if (!$mainframe->isAdmin()) {
			$data->all->link	= JRoute::_("&limitstart=00");
			$data->start->link	= JRoute::_("&limitstart=00");
			if ($data->previous->base	== 0) {
				$data->previous->link = JRoute::_("&limitstart=00");
			}
			if (count($data->pages) > 0) {
				// $message = print_r($data->pages, true) . '"<br/>';
				//$mainframe->enqueueMessage($message, 'notice');
			
				$data->pages[1]->link = JRoute::_("&limitstart=00"); 
			}
		}
		return $data;
	}
}
?>
