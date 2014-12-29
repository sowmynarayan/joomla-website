<?php

/** yvComment - A User Comments Component, developed for Joomla 1.5 
* @package yvComment 
* @(c) 2007-2009 yvolk (Yuri Volkov), http://yurivolkov.com. All rights reserved.
* @license GPL 
**/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.filter.output');
jimport('joomla.application.component.model');

/**
 * yvComments Component "comment" Model
 *
 */
class yvcommentModelcomment extends JModel {
	/**
	 * yvCommentID
	 *
	 * @var int
	 */
	var $_yvCommentID = 0;
	var $_ArticleID = 0;
	var $_task = '';
	// Article, commented by this comment
	var $_ArticleRow = null;
	// Parent article of Article, commented by this comment
	var $_parentArticleRow = null;

	/**
	 * Comments for selected ArticleID
	 *
	 * @var array
	 */
	var $_data = null;
	var $_null = null;
	// Current Comment in $_data
	var $_indCurrent = -1;

	var $_Message = array ();

	// Configuration settings
	var $_hide_title = false;
	// This is always false yet...
	var $_ini_title = false;

	function __construct() {
		parent :: __construct();
		$yvComment = & yvCommentHelper :: getInstance();

		//echo 'Constructor of yvcommentModelComment <br />';

		$this->_hide_title = (boolean) ($yvComment->getConfigValue('hide_title', '0'));
	}

	/**
	 * Method to set identifiers...
	 *
	 * @access	public
	 */
	function setParms($yvCommentID = 0, $task = 'add', $filter_state = 'P') {
		$yvComment = & yvCommentHelper :: getInstance();

		// Set yvComment ID and wipe data
		$this->_ArticleID = $yvComment->getArticleID();
		$this->_yvCommentID = $yvCommentID;
		$this->_data = null;
		$this->setTask($task);

		//echo 'setParms id=' . $this->_yvCommentID . '; ArticleID=' . $this->_ArticleID . '; task=' . $task .'<br/>';
	}

	// Current Comment
	function & getCurrentItem() {
		$Ok = false;

		if ($this->_indCurrent >= 0) {
			$Ok = true;
		} else
			if ($this->_yvCommentID != 0) {
				$this->_loadData('current');
				if ($this->_indCurrent >= 0) {
					$Ok = true;
				}
			}

		if ($Ok) {
			return $this->_data[$this->_indCurrent];
		} else {
			return $this->_null;
		}
	}

	function _loadData() {
		$yvComment = & yvCommentHelper :: getInstance();
		$Ok = false;

		// Lets load the content if it doesn't already exist
		if (empty ($this->_data) && $this->_yvCommentID != 0) {
			$this->_indCurrent = -1;

			$row = new yvCommentTable($this->_db);
			$row->id = $this->_yvCommentID;
			if ($row->load()) {
				$this->_data = array (
					$row
				);
				$Ok = true;
			} else {
				$this->appendMessage($row->getError());
			}
			//echo ' this->_yvCommentID=' . $this->_yvCommentID . ' count=' . count($this->_data);

		} else {
			$Ok = true;
		}
		if (!empty ($this->_data) and (count($this->_data) > 0)) {
			$this->_indCurrent = 0;
			if ($this->_ArticleID == 0) {
				$this->_ArticleID = $this->_data[0]->parentid;
			}
		}
		return $Ok;
	}

	// Article, commented by this comment
	function & getArticle() {
		$yvComment = & yvCommentHelper :: getInstance();

		$row = null;
		if ((!$this->_ArticleRow) and $this->_ArticleID != 0) {
			$this->_ArticleRow = & JTable :: getInstance('content');
			$this->_ArticleRow->id = $this->_ArticleID;
			if (!$this->_ArticleRow->load()) {
				$this->appendMessage(JText :: _('ROW_WAS_NOT_FOUND') . ': ArticleID=' . $this->_ArticleID);
				$this->_ArticleRow = null;
			}
		}

		return $this->_ArticleRow;
	}

	// Parent article of Article, commented by this comment
	function & getParentArticle() {
		$yvComment = & yvCommentHelper :: getInstance();

		$row = null;
		if ((!$this->_parentArticleRow) and $this->_ArticleID != 0) {
			if ($yvComment->IsCommentByID($this->_ArticleID)) {
				$CommentID = $this->_ArticleID;
				$parentid = (integer) $yvComment->DLookup('parentid', '#__content', 'id=' . $CommentID);
				//echo 'getParentArticle ' . $CommentID . '-&gt;' . $parentid . ';';
				if ($parentid == 0) {
					$message = 'This content item (id=' . $CommentID . '; title=\'' . $yvComment->DLookup('title', '#__content', 'id=' . $CommentID) . '\'' . ') is Comment (yvComment thinks so...)' . '), but it has no parent Article (its parentid=0).';
					if ($yvComment->UseDesignatedSectionForComments()) {
						$message .= ' Please ensure that there are Comments only in this \'Section for comments\': ' . 'section_id=' . $yvComment->getSectionForComments() . '; section_title=\'' . $yvComment->DLookup('title', '#__sections', 'id=' . $yvComment->getSectionForComments()) . '\'' . '.';
					}
					$this->appendMessage($message);
				} else {
					$this->_parentArticleRow = & JTable :: getInstance('content');
					$this->_parentArticleRow->id = $parentid;
					if (!$this->_parentArticleRow->load()) {
						$this->appendMessage('Article, commented by this comment, could not be loaded (' . 'ArticleID=' . $parentid . '; CommentID=' . $CommentID . ')');
						$this->_parentArticleRow = null;
					}
				}
			}
		}

		return $this->_parentArticleRow;
	}

	function & getyvCommentID() {
		return $this->_yvCommentID;
	}

	function appendMessage($messages) {
		if (is_array($messages)) {
			foreach ($messages as $message) {
				$this->_Message[] = $message;
			}
		} else
			if (strlen(trim($messages)) > 0) {
				$this->_Message[] = $messages;
			}
		return true;
	}

	function & getMessage() {
		return $this->_Message;
	}

	function setTask($task) {
		//echo 'setTask="' . $task . '"<br/>';
		$this->_task = $task;
		if ($task == 'adddisplay' and empty ($this->_data)) {
			$this->_initData();
		}
	}

	function & getTask() {
		return $this->_task;
	}

	/**
	 * Inserts Comment to the database
	 * @return true if Ok
	 * $IsPiblished - returns true if (added and published)
	 */
	function AddOrPreview($title, $text, $created_by_alias, $created_by_link, $created_by_email, $IsPreview, & $IsPiblished) {
		global $mainframe;
		$yvComment = & yvCommentHelper :: getInstance();
		$Ok = false;
		$IsPiblished = false;
		$failed = false;
		$secretword = '';
		$username = '';

		do {
			if (!$yvComment->AddEnabledForUser()) {
				$this->appendMessage('AddEnabledForUser: ' . JText :: _('ACCESS_DENIED'));
				break;
			}
			if (!$yvComment->AddEnabledForArticle($this->_ArticleID)) {
				$this->appendMessage(JText :: _('ARTICLE_ADD_DISABLED'));
				break;
			}
			if (!$yvComment->UserIsRegistered()) {
				// Check for request forgeries
				If (!JRequest :: checkToken(null)) {
					$this->appendMessage(JText :: _('Invalid Token') . '; Token:' . JUtility :: getToken());
					break;
				}
			}

			if ($yvComment->getConfigValue('use_captcha', 0)) {
				$secretword = JRequest :: getVar('secretword', '', '', 'STRING');
			}

			if ($yvComment->UserIsGuest() && $yvComment->getConfigValue('use_openid', 0)) {
				$username = JRequest :: getVar('username', '', '', 'username');
				if (strlen($username) > 0) {
					$prefix = 'yvCommentForm.';
					// Save title and text of comment, entered by the user
					$session = & JFactory :: getSession();
					$session->set($prefix, '1');
					$session->set($prefix . 'title', $title);
					$session->set($prefix . 'text', $text);
					$session->set($prefix . 'created_by_link', $created_by_link);
					$session->set($prefix . 'created_by_email', $created_by_email);
					$session->set($prefix . 'secretword', $secretword);

					// For the reference to how Joomla! does related functions, see:
					// 1. "/components/com_user/controller.php" file,
					//		function login()
					// 2. "/libraries/joomla/application/application.php" file,
					//		function login($credentials, $options = array())
					// 3. "/plugins/authentication/openid.php" file,
					//		function onAuthenticate($credentials, $options, & $response)
					// 4. "/plugins/user/joomla.php" file,
					//		function onLoginUser($user, $options = array()) 

					// Start authentication
					jimport('joomla.user.authentication');
					$authenticate = & JAuthentication :: getInstance();
					$credentials = array ();
					$credentials['username'] = $username;
					$options = array ();
					$options['return'] = JRoute :: _('index.php?option=com_yvcomment&task=add' .
					'&view=comment&ArticleID=' . $this->_ArticleID .
					'&url=' . $yvComment->getReturnURL() .
					'&Itemid=' . $yvComment->getComponentItemid() .
					'&' . JUtility :: getToken() . '=1' .
					'&button=' . ($IsPreview ? 'preview' : 'post'));

					if ($yvComment->_debug) {
						$yvComment->log('Before authenticate: ' . print_r($session, true) .
						'$_SESSION: ' .
						print_r($_SESSION, true));
					}
					$response = $authenticate->authenticate($credentials, $options);
					if ($yvComment->_debug) {
						$yvComment->log('After authenticate: ' . print_r($session, true));
					}
					if ($response->status === JAUTHENTICATE_STATUS_SUCCESS) {
						// Did Joomla! already changed the User?
						$yvComment->LookupUser();
					} else {
						$error = '';
						if (is_object($response)) {
							if (isset ($response->error_message)) {
								$error = $response->error_message;
							}
						}
						if (empty ($error)) {
							$error = print_r($response, true);
						}
						$message = 'Auth response="' . $error . '"';
						if ($yvComment->_debug) {
							$yvComment->log($message);
						}
						$this->appendMessage($message);
					}
				}
			}

			// Should we restore data from the User's comment?
			$session = & JFactory :: getSession();
			$prefix = 'yvCommentForm.';
			if ($session->get($prefix)) {
				//$this->appendMessage('Data restored from session');
				//$this->appendMessage(print_r($session, true));

				$title = $session->get($prefix . 'title');
				$text = $session->get($prefix . 'text');
				$created_by_link = $session->get($prefix . 'created_by_link');
				$created_by_email = $session->get($prefix . 'created_by_email');
				$secretword = $session->get($prefix . 'secretword');

				$session->clear($prefix);
				$session->clear($prefix . 'text');
				$session->clear($prefix . 'title');
				$session->clear($prefix . 'created_by_link');
				$session->clear($prefix . 'created_by_email');
				$session->clear($prefix . 'secretword');
			}

			$this->_initData($title, $text, $created_by_alias, $created_by_link, $created_by_email);
			$row = & $this->getCurrentItem();

			if (!$row->check()) {
				$this->appendMessage($row->getErrors());
				break;
			}

			$min_post_period_user = $yvComment->getConfigValue('min_post_period_user', '60');
			if ($min_post_period_user > 0) {
				/* Check for minimum time between posts for the user 
				 * Guests with diffenert aliases are count as different users
				 * */
				$Criteria = '(created>' . $yvComment->SecondsFromNowToSQLDate($min_post_period_user) . ')' . ' AND created_by=' . $row->created_by;
				if ($yvComment->UserIsGuest()) {
					$Criteria .= ' AND created_by_alias=' . $this->_db->Quote($row->created_by_alias);
				}
				$CommentID_prev = $yvComment->DLookup('id', $yvComment->getTableName(), $Criteria);

				if ($CommentID_prev > 0) {
					$msg = str_replace('%1', $min_post_period_user, JText :: _('TOO_SMALL_PERIOD_BETWEEN_POSTS')) . ' ID=' . $CommentID_prev;
					$this->appendMessage($msg);
					break;
				}
			}

			// Treat temp user as Guest here
			if ($row->created_by == $yvComment->getGuestID()) {
				$min_post_period_guest = $yvComment->getConfigValue('min_post_period_guest', '60');
				if ($min_post_period_guest > 0) {
					// Second check for guest - specifically against spam
					// doesn't take alias into account'	    		
					$Criteria = '(created>' . $yvComment->SecondsFromNowToSQLDate($min_post_period_guest) . ')' . ' AND created_by=' . $row->created_by;
					$CommentID_prev = $yvComment->DLookup('id', $yvComment->getTableName(), $Criteria);
					if ($CommentID_prev > 0) {
						$msg = str_replace('%1', $min_post_period_guest, JText :: _('TOO_SMALL_PERIOD_BETWEEN_POSTS_GUEST')) . ' ID=' . $CommentID_prev;
						$this->appendMessage($msg);
						break;
					}
				}
			}

			if ($yvComment->UserIsGuest()) {
				if ($yvComment->getConfigValue('use_openid', 0)) {
					$this->appendMessage(JText :: _('AUTHENTICATION_FAILED') . ' (username' . (empty ($username) ? ' is empty' : '=\'' . $username . '\'') . ')');
					if (!$IsPreview) {
						$failed = true;
					}
				}
			} // For guests only... 

			if (!$yvComment->UserIsRegistered() && $yvComment->getConfigValue('use_captcha', 0)) {
				$OkCaptcha = false;
				if (strlen($secretword) > 0) {
					$mainframe->triggerEvent('onCaptcha_Confirm', array (
						$secretword,
						& $OkCaptcha
					));
				}
				if (!$OkCaptcha && (!$IsPreview || strlen($secretword) > 0)) {
					$this->appendMessage(JText :: _('SECRETWORD_IS_WRONG'));
					if (!$IsPreview) {
						$failed = true;
					}
				}
			} // For not registered users... 

			// Is it published?
			switch ($yvComment->getConfigValue('add_published', 'yes_for_registered_users')) {
				case 'no' :
					if ($yvComment->UserCanEdit() == 'all') {
						$row->state = 1;
					}
					break;
				case 'yes_for_registered_users' :
					if (!$yvComment->UserIsGuest()) {
						$row->state = 1;
					}
					break;
				case 'yes_for_everybody' :
					$row->state = 1;
					break;
			}

			if ($IsPreview || $failed) {
				break;
			}

			// Store the content to the database
			if (!$row->store()) {
				$this->appendMessage($yvComment->printDbErrors($this->_db));
				//$this->appendMessage('Test222: ' . $this->_db->stderr());
				break;
			}

			// Find Added Row
			$query = 'SELECT c.id' .
			' FROM ' . $yvComment->getTableName() . ' AS c' .
			' WHERE c.parentid=' . $this->_ArticleID . " AND c.created='" . $row->created . "'";
			$result = $this->_getList($query);
			if (count($result) > 0) {
				$this->_yvCommentID = $result[0]->id;
				//echo ' Found=' . $this->_yvCommentID;	  
			} else {
				$this->appendMessage(JText :: _('ROW_WAS_NOT_FOUND') . ': ArticleID=' . $this->_ArticleID . "created='" . $row->created . "'");
				break;
			}

			if ($row->state == 1) {
				$this->appendMessage(JText :: _('COMMENT_ADDED'));
				$IsPiblished = true;
			} else {
				$this->appendMessage(JText :: _('COMMENT_ADDED_UNPUBLISHED'));
			}

			$Ok = true;
		} while (0);

		if (!$IsPreview && $Ok) {
			$this->_NotifyAddEdit($row, 'add');
		}

		//echo 'Ok=' . $Ok . ' "' . $this->getMessage() . '"';

		return $Ok;
	}

	/**
	 * Modifies Comment in the the database
	 * @return true if Ok
	 */
	function EditOrPreview($title, $text, $created_by_alias, $created_by_link, $created_by_email, $IsPublished, $IsPreview) {
		global $mainframe;
		$yvComment = & yvCommentHelper :: getInstance();
		$Ok = false;
		do {
			$user = & JFactory :: getUser();
			$UserID = $user->get('id');

			jimport('joomla.utilities.date');
			$jnow = new JDate();
			// Is this right?
			$jnow->setOffset(- $mainframe->getCfg('offset'));
			$now = $jnow->toMySQL();
			//echo '$now=' . $now;

			//for content: $row = & JTable::getInstance('content');
			$row = $this->getCurrentItem();
			if (!empty ($row)) {
				// Change some fields
				if ($this->_hide_title) {
					if ($this->_ini_title) {
						$a = $this->getArticle();
						if (is_object($a)) {
							$title = JText :: _('REPLY_PREFIX') . ' ' . $a->title;
						}
					} else {
						$title = $text;
					}
					$titlelen_max = 40;
					if (strlen($title) > $titlelen_max) {
						$title = substr($title, 0, $titlelen_max);
					}
				}
				if (strcmp($row->title, $title) != 0) {
					// Allow change alias, if title was changed
					$row->alias = '';
				}
				$row->title = $title;

				// Search for the {readmore} tag and split the text up accordingly.
				$pattern = '#<hr\s+id=("|\')system-readmore("|\')\s*\/*>#i';
				$tagPos = preg_match($pattern, $text);
				if ($tagPos == 0) {
					$row->introtext = $text;
					$row->fulltext = '';
				} else {
					list ($row->introtext, $row->fulltext) = preg_split($pattern, $text, 2);
				}

				if ($row->created_by == $yvComment->getGuestID()) {
					// Don't erase foreign metadata						
					$metadata = new JRegistry;
					$metadata->loadINI($row->metadata);

					$row->created_by_alias = $created_by_alias;

					// Only keep the author link if they are turned on.
					if ($yvComment->getConfigValue('allow_guest_link', '0')) {
						$link = $yvComment->strToSafeHref(trim($created_by_link));
						$metadata->setValue('created_by_link', $link);
					}
					if ($yvComment->getConfigValue('guest_email_required', '0')) {
						$metadata->setValue('created_by_email', trim($created_by_email));
					}

					$row->metadata = $metadata->toString();
				} // for Guest only
			} else {
				$this->appendMessage(JText :: _('ROW_WAS_NOT_FOUND') . ': yvCommentID=' . $this->_yvCommentID);
				break;
			}

			// Does the user have access to edit comment?
			if ($yvComment->UserCanEdit() != 'all') {
				if ($yvComment->UserCanEdit() == 'own') {
					// May edit only own comments
					if ($UserID != $row->created_by) {
						$this->appendMessage(JText :: _('ACCESS_DENIED') . ' ' . JText :: _('YOU_MAY_EDIT_YOUR_OWN'));
						// Don't show the Comment
						$row = null;
						break;
					}
				} else {
					$this->appendMessage('Edit.' . $yvComment->UserCanEdit() . ': ' . JText :: _('ACCESS_DENIED'));
					$row = null;
					break;
				}
			}

			if ($yvComment->UseContentTable()) {
				if (!$yvComment->IsCommentByID($this->_yvCommentID)) {
					$this->appendMessage(JText :: _('THIS_IS_NOT_YVCOMMENT') . ': id=' . $this->_yvCommentID);
					break;
				}
			}

			if ($yvComment->UserCanEdit() == 'all') {
				// Published
				if (strlen($IsPublished) > 0) {
					$row->state = ($IsPublished ? 1 : 0);
				}
			}

			if (!$row->check()) {
				$this->appendMessage($row->getErrors());
				break;
			}

			// Captcha is not needed here, because guests are not allowed to edit comments

			if ($IsPreview) {
				break;
			}

			$row->version += 1;
			$row->modified = $now;
			$row->modified_by = $UserID;
			// Store the content to the database
			if (!$row->store()) {
				$this->appendMessage($yvComment->printDbErrors($this->_db));
				break;
			}

			$this->appendMessage(JText :: _('COMMENT_MODIFIED'));
			$Ok = true;
		} while (0);

		if (!$IsPreview && $Ok) {
			$this->_NotifyAddEdit($row, 'edit');
		}

		//echo 'Ok=' . $Ok . ' "' . $this->getMessage() . '"';
		return $Ok;
	}

	// Mail notification
	// based on components/com_mailto/controller.php
	function _NotifyAddEdit($row, $operation) {
		global $mainframe;
		
		$yvComment = & yvCommentHelper :: getInstance();
		$Ok = true;
		$debug = false;
		$RecipientIDs = array();
		$Recipients = array ();

		// 1. Add users from the 'List of Usernames to notify of new or updated comments' option value
		if ($yvComment->UserCanEdit() == 'all') {
			// If current user has all rights:	
			// Don't notify users from this list	
		} else {
			$str1 = trim($yvComment->getConfigValue('notify_addedit_usernames'));
			if (!empty ($str1)) {
				$RecipientUsernames = explode(',', $str1);
				foreach ($RecipientUsernames as $RecipientUsername) {
					jimport('joomla.user.helper');
					$RecipientID = JUserHelper :: getUserId(trim($RecipientUsername));
					if ($RecipientID == 0) {
						$message = 'yvComment mail notification: Username "' . $RecipientUsername . '" was not found';
						$mainframe->enqueueMessage($message, 'error');
					}
					$RecipientIDs[] = $RecipientID; 
				}
			}	
		}

		// 2. 'Notify author(s) of new or updated comments'
		// 'no' - No 
		// 'one' - Only the Author of the commented article
		// 'all' - All authors up in the thread
		$AuthorsSubscribed = $yvComment->getConfigValue('notify_addedit_authors', 'all');
		switch ($AuthorsSubscribed) {
			case 'one':
			case 'all':
				$parentid = $row->parentid;
				do {
					if ($parentid == 0) { break;}
					$RecipientIDs[] = (integer) $yvComment->DLookup('created_by', '#__content', 'id=' . $parentid);
					$parentid = (integer) $yvComment->DLookup('parentid', '#__content', 'id=' . $parentid);
				} while($AuthorsSubscribed == 'all');
		}

		$message = ' "' . count($RecipientIDs) . '" repicients found';
		if (count($RecipientIDs) > 0) {
			$user = & JFactory :: getUser();
			$UserID = $user->get('id');
			$RecipientIDs = array_unique($RecipientIDs);
			
			// Build list of Recipients
			$Recipients = array ();
			foreach ($RecipientIDs as $RecipientID) {
				$message .= '<br />Recipient: ' . $RecipientID . ' - ' ;
				if ($RecipientID == 0) {
					$message .= ' == 0';
				}
				elseif ($RecipientID == $yvComment->getGuestID()) {
					// Don't notify the Guest
					$message .= ' is guest - don\'t notify';
				}
				elseif ($RecipientID == $UserID) {
					$message .= ' is current user - don\'t notify';
					//Don't notify current user if he is in the list of recipients
				} else {
					// Is the $RecipientID User subscribed to comments on his Articles (or Comments)?
					$isSubscribed = 2; 
					// $isSubscribed - Three-state variable:
					// 2 - means 'default'; false - 'no'; true - 'yes'
	 			  	$mainframe->triggerEvent('onNotifyAuthorOfAddEditComment', array($RecipientID, &$isSubscribed) );
	 			  	if ($isSubscribed == 2) {
						// by default ALL authors are subscribed
	 			  		$isSubscribed = true;
	 			  	} else {
	 			  		$isSubscribed = (boolean) $isSubscribed;
	 			  	}
					if ($isSubscribed) { 
						// the author has subscription
         				$Recipients[] = & JUser :: getInstance($RecipientID);
						$message .= ' is subscribed';
					} else {
						$message .= ' is not subscribed';
					}
				}
			}
		}
		if ($debug) {
			$message = 'yvComment mail notification:<br/>' .
				$message ;
			$mainframe->enqueueMessage($message, 'notice');
		}
		
		if (count($Recipients) > 0) {
			$config = & JFactory :: getConfig();
			$Article = $this->getArticle();

			$sitename = $config->getValue('sitename');
			$from = $config->getValue('mailfrom');
			$fromname = $config->getValue('fromname');
			$title = $row->title;
			$text = $yvComment->UnifyIntrotextFulltext($row);

			$comment_author = $yvComment->txtAuthorName($row);
			$article_title = '';
			$article_author = '';
			if ($Article) {
				$article_title = $Article->title;
				$article_author = $yvComment->txtAuthorName($Article);
			} else {
				$article_title = 'ArticleID=' . $this->_ArticleID;
			}

			$modified_by = '';
			$subject1 = '';
			$body1 = '';
			switch ($operation) {
				case 'add' :
					$subject1 = JText :: _('MAIL_NOTIFY_ADD_YVCOMMENT_SUBJECT');
					$body1 = JText :: _('MAIL_NOTIFY_ADD_YVCOMMENT_BODY');
					break;
				default :
					$subject1 = JText :: _('MAIL_NOTIFY_EDIT_YVCOMMENT_SUBJECT');
					$body1 = JText :: _('MAIL_NOTIFY_EDIT_YVCOMMENT_BODY');
					$modified_by = $yvComment->DLookup('name', '#__users', 'id=' . $row->modified_by);
			}
			//			if ( strpos('  ' . $subject1,'%')<1) {
			//				$subject1 = '%2 - %1';
			//			}
			//			if ( strpos('  ' . $body1,'%')<1) {
			//				$body1 = '%1 \n%2 \n%3 \n%4 \n%5 \n%6 \n%7';
			//			}

			foreach ($Recipients as $Recipient) {
				$email = $Recipient->email;

				$urlArticle = $yvComment->ContentIDToURL($this->_ArticleID, false, $Recipient->id, true);
				//if (!empty($urlArticle)) {
				//	$urlArticle .= '#yvComment' . $this->_ArticleID;
				//}

				$urlComment = '';
				if ($yvComment->getConfigValue('usecontenttable', '1')) {
					$urlComment = $yvComment->ContentIDToURL($row->id, false, $Recipient->id, true);
				} else {
					$urlComment = base64_decode($yvComment->buildReturnURL(true, "yvComment" . $article->id));
				}

				// Build message
				$subject = str_replace(array (
					'%1',
					'%2',
					'%3'
				), array (
					$sitename,
					$title,
					$article_title
				), JText :: _($subject1));
				$body = str_replace(array (
					'%1',
					'%2',
					'%3',
					'%4',
					'%5',
					'%6',
					'%7',
					'%8',
					'%9'
				), array (
					$sitename,
					$title,
					$article_title,
					$text,
					$comment_author,
					$modified_by,
					$urlArticle,
					$urlComment,
					$article_author
				), JText :: _($body1));

				if ($debug) {
					$message = 'yvComment mail notification:<br/>' .
					'From: "' . $from . '"<br/>' .
					'Fromname: "' . $fromname . '"<br/>' .
					'Email: "' . $email . '"<br/>' .
					'Subject: "' . $subject . '"<br/>' .
					'Body:<hr/>' . $body . '<hr/>';
					$mainframe->enqueueMessage($message, 'notice');
				}

				if (JUtility::sendMail($from, $fromname, $email, $subject, $body) !== true) {
					$Ok = false;
					$message = 'There was an Error during yvComment mail notification:<br/>' .
					'From: "' . $from . '"<br/>' .
					'Fromname: "' . $fromname . '"<br/>' .
					'Email: "' . $email . '"<br/>' .
					'Subject: "' . $subject . '"<br/>' .
					'Body:<hr/>' . $body . '<hr/>';
					$mainframe->enqueueMessage($message, 'notice');
				}
			}
		} // there are recipients

		return $Ok;
	}

	/**
	 * Delete/Trash the Comment
	 * @return true if Ok
	 */
	function Delete() {
		global $mainframe;
		$yvComment = & yvCommentHelper :: getInstance();
		$Ok = false;
		$row = null;
		do {
			$row = $this->getCurrentItem();
			if (empty ($row)) {
				$this->appendMessage(JText :: _('ROW_WAS_NOT_FOUND') . ': id=' . $this->_yvCommentID);
				break;
			}

			// Does the user have access to delete the Comment?
			if ($yvComment->UserCanEdit() != 'all') {
				$this->appendMessage('Delete: ' . JText :: _('ACCESS_DENIED'));
				break;
			}

			if (!$row->canDelete()) {
				$this->appendMessage($row->getError());
				break;
			}

			// Delete the content from the database
			if (!$row->delete()) {
				$this->appendMessage($row->getError());
				$this->appendMessage($yvComment->printDbErrors($this->_db));
				break;
			}

			$this->appendMessage(JText :: _('COMMENT_DELETED'));
			$Ok = true;
		} while (0);

		//echo 'Ok=' . $Ok . ' "' . $this->getMessage() . '"';
		return $Ok;
	}

	/**
	 * Publish/Unpublish the Comment
	 * @return true if Ok
	 */
	function Publish($state_new) {
		global $mainframe;
		$yvComment = & yvCommentHelper :: getInstance();
		$Ok = false;
		$row = null;
		do {

			if (!($state_new == 0 || $state_new == 1)) {
				$this->appendMessage('Publish: ' . JText :: _('ACCESS_DENIED'));
				break;
			}

			// Does the user have access to publish the Comment?
			if ($yvComment->UserCanEdit() != 'all') {
				$this->appendMessage('Publish: ' . JText :: _('ACCESS_DENIED'));
				break;
			}

			$row = $this->getCurrentItem();
			if (empty ($row)) {
				$this->appendMessage(JText :: _('ROW_WAS_NOT_FOUND') . ': yvCommentID=' . $this->_yvCommentID);
				break;
			}

			if ($row->state == $state_new) {
				$this->appendMessage('Publish.same_state: ' . JText :: _('ACCESS_DENIED'));
				break;
			}
			$row->state = $state_new;

			if (!$row->check()) {
				$this->appendMessage($row->getErrors());
				break;
			}

			// Store the content to the database
			if (!$row->store()) {
				$this->appendMessage($yvComment->printDbErrors($this->_db));
				break;
			}

			$this->appendMessage(JText :: _('COMMENT_MODIFIED'));
			$Ok = true;
		} while (0);

		if ($Ok) {
			// $this->_NotifyAddEdit($row, 'edit');
		}

		//echo 'Ok=' . $Ok . ' "' . $this->getMessage() . '"';
		return $Ok;
	}

	/**
	 * Initialize the Comment
	 *
	 * @access	private
	 * @return	boolean	True on success
	 */
	function _initData($title = '', $text = '', $created_by_alias = '', $created_by_link = '', $created_by_email = '') {
		$yvComment = & yvCommentHelper :: getInstance();
		$created_by_username = '';

		$user = & JFactory :: getUser();
		$UserID = $user->get('id');
		//echo '$UserID=' . $UserID . '<br />';

		if ($yvComment->UserIsTemp()) {
			// We need to store User info just like for Guest
			// see 'plugins/authentication/openid.php'
			$created_by_username = $user->username;
			if (JString :: strlen($created_by_email) == 0) {
				$created_by_email = $user->email;
			}
			if (JString :: strlen($created_by_alias) == 0) {
				$created_by_alias = $user->name;
			}
		}

		jimport('joomla.utilities.date');
		$jnow = new JDate();
		// Is this right?
		//$jnow->setOffset( -$mainframe->getCfg('offset'));
		$now = $jnow->toMySQL();
		//echo '$now=' . $now;

		$row = new yvCommentTable($this->_db);
		$row->created_by = $UserID;
		$row->parentid = $this->_ArticleID;
		if (!$yvComment->UseDesignatedSectionForComments()) {
			// Section and category ids are inherited from the Article
			$row->sectionid = (integer) $yvComment->DLookup('sectionid', '#__content', 'id=' . $this->_ArticleID);
			$row->catid = (integer) $yvComment->DLookup('catid', '#__content', 'id=' . $this->_ArticleID);
		}

		if ($this->_hide_title) {
			if ($this->_ini_title) {
				$a = $this->getArticle();
				if (is_object($a)) {
					$title = JText :: _('REPLY_PREFIX') . ' ' . $a->title;
				}
			} else {
				$title = $text;
			}
			$titlelen_max = 40;
			if (strlen($title) > $titlelen_max) {
				$title = substr($title, 0, $titlelen_max);
			}
		}
		$row->title = $title;

		// Search for the {readmore} tag and split the text up accordingly.
		$pattern = '#<hr\s+id=("|\')system-readmore("|\')\s*\/*>#i';
		$tagPos = preg_match($pattern, $text);
		if ($tagPos == 0) {
			$row->introtext = $text;
			$row->fulltext = '';
		} else {
			list ($row->introtext, $row->fulltext) = preg_split($pattern, $text, 2);
		}

		$row->created = $now;
		$row->ordering = 1;
		//$row->attribs = 'contenttype=yvcomment';
		$row->version = 1;

		if ($row->created_by == 0) {
			$row->created_by = $yvComment->getGuestID();
		}
		if ($row->created_by == $yvComment->getGuestID()) {
			$metadata = new JRegistry;

			$row->created_by_alias = $created_by_alias;

			// Only keep the author link if they are turned on.
			if ($yvComment->getConfigValue('allow_guest_link', '0')) {
				if (JString :: strlen($created_by_link) > 0) {
					$link = $yvComment->strToSafeHref(trim($created_by_link));
					$metadata->setValue('created_by_link', $link);
				}
			}
			if ($yvComment->getConfigValue('guest_email_required', '0') || $yvComment->UserIsTemp()) {
				if (JString :: strlen($created_by_email) > 0) {
					$metadata->setValue('created_by_email', trim($created_by_email));
				}
			}
			if ($yvComment->UserIsTemp()) {
				$metadata->setValue('created_by_username', trim($created_by_username));
			}

			$row->metadata = $metadata->toString();
		} // for Guest only

		$rows = array ();
		$rows[0] = $row;
		$this->_data = $rows;
		$this->_indCurrent = 0;
		//echo 'DataInitialized' .  '<br/>';
		return (boolean) $this->_data;
	}
}

if (!class_exists('JTableContent')) {
	//Force Joomla to find JTableContent class _properly_ 
	JTable :: getInstance('content');
}
class yvCommentTable extends JTableContent {

	// Configuration settings
	var $_IsConfigLoaded = false;
	var $_usecontenttable = false;
	var $_categoryidConfig = 0;
	var $_max_characters_fulltext = 0;
	var $_hide_title = false;

	function __construct(& $db) {
		$yvComment = & yvCommentHelper :: getInstance();
		$this->load_config();
		if ($this->_usecontenttable) {
			//$TableName = "content";
			parent :: __construct($db);
		} else {
			$TableName = "yvcomment";
			JTable :: __construct('#__' . $TableName, 'id', $db);
		}
	}

	// This function is needed, because e.g. load function erases local data :-(
	function load_config() {
		if (!$this->_IsConfigLoaded) {
			$yvComment = & yvCommentHelper :: getInstance();
			$this->_usecontenttable = $yvComment->getConfigValue('usecontenttable', '1');
			$this->_categoryidConfig = $yvComment->getConfigValue('categoryid', '0');
			$this->_max_characters_fulltext = $yvComment->getConfigValue('max_characters_fulltext', '0');
			$this->_hide_title = (boolean) ($yvComment->getConfigValue('hide_title', '0'));
			$this->_IsConfigLoaded = true;
		}
	}

	function load($oid = null) {
		$yvComment = & yvCommentHelper :: getInstance();
		$Ok = parent :: load($oid);

		if ($Ok) {
			// Ensure that:
			// 1. This is a Comment
			// 2. The User has rightd to see it

			$user = & JFactory :: getUser();
			$aid = $user->get('aid', 0);

			if ($yvComment->UseDesignatedSectionForComments()) {
				if ($this->sectionid != $yvComment->getSectionForComments()) {
					$this->setError(JText :: _('THIS_IS_NOT_YVCOMMENT') . ': id=' . $this->id);
					$Ok = false;
				}
			} else {
				if ($this->parentid == 0) {
					$this->setError(JText :: _('THIS_IS_NOT_YVCOMMENT') . ': id=' . $this->id);
					$Ok = false;
				}
			}
			if ($Ok) {
				if ($this->access > (int) $aid) {
					$this->setError('comment.access: ' . JText :: _('ACCESS_DENIED'));
					$Ok = false;
				}
			}
			if ($Ok) {
				if ($yvComment->UserCanEdit() != 'all') {
					if ($this->state != 1) {
						$this->setError(JText :: _('comment.state: ' . 'ACCESS_DENIED'));
						$Ok = false;
					}
				}
			}
			if ($Ok) {
				if ($this->state == -2) {
					$this->setError(JText :: _('comment.state-2: ' . 'ACCESS_DENIED'));
					$Ok = false;
				}
			}

		}
		If (!$Ok) {
			$this->reset();
		}
		return $Ok;
	}

	function canDelete($oid = null, $joins = null) {
		$yvComment = & yvCommentHelper :: getInstance();
		$CanDelete = false;
		$k = $this->_tbl_key;
		if ($oid) {
			$this-> $k = intval($oid);
		}
		$nChildren = 0;
		if ($yvComment->UseContentTable()) {
			$nChildren = $yvComment->DLookup_db($this->_db, 'COUNT(*)', $this->_tbl, "parentid=" . $this-> $k);
		}
		if ($nChildren > 0) {
			$this->setError(JText :: _('DELETE_CHILD_COMMENTS_PRESENT'));
		} else {
			$CanDelete = true;
		}
		if ($CanDelete) {
			$CanDelete = parent :: canDelete($oid, $joins);
		}
		return $CanDelete;
	}

	/**
	 * Overloaded check method to ensure data integrity
	 */
	function check() {
		$yvComment = & yvCommentHelper :: getInstance();
		$this->load_config();

		$Ok = true;
		/** check for valid name */
		if (trim($this->title) == '') {
			$Ok = false;
			if ($this->_hide_title) {
				$this->setError(JText :: _('FULLTEXT_TIP'));
			} else {
				$this->setError(JText :: _('TITLE_IS_REQUIRED'));
			}
		}
		if ($this->_max_characters_fulltext > 0) {
			$len1 = strlen(trim($this->introtext)) + strlen(trim($this->fulltext));
			if ($len1 > $this->_max_characters_fulltext) {
				$Ok = false;
				$this->setError(JText :: _('FULLTEXT_IS_TOO_LONG') . ' (' . $len1 . ' > ' . $this->_max_characters_fulltext . ')');
			}
		}
		if ($this->created_by == 0) {
			$Ok = false;
			$this->setError(JText :: _('GUEST_USER_ACCOUNT_ERROR'));
		}
		if ($this->parentid == 0) {
			$Ok = false;
			$this->setError(JText :: _('NO_ARTICLE'));
		} else {
			// Ensure access level of comment is the same as of parent Article
			$ArticleAccess = (integer) $yvComment->DLookup('access', '#__content', 'id=' . $this->parentid);
			if ($this->access != $ArticleAccess) {
				$this->access = $ArticleAccess;
			}
		}
		if ($this->sectionid == 0 && $yvComment->UseDesignatedSectionForComments()) {
			$this->sectionid = $yvComment->getSectionForComments();
			if ($this->sectionid == 0) {
				$Ok = false;
				$this->setError(JText :: _('SECTIONID_NOT_SET'));
			}
		}
		if ($this->catid == 0 && $yvComment->UseDesignatedSectionForComments()) {
			$this->catid = $this->_categoryidConfig;
			if ($this->catid == 0) {
				$Ok = false;
				$this->setError(JText :: _('CATEGORYID_NOT_SET'));
			}
		}

		if ($this->created_by == $yvComment->getGuestID()) {
			// Guest	
			$metadata = new JRegistry;
			$metadata->loadINI($this->metadata);

			if (trim($this->created_by_alias) == '') {
				$Ok = false;
				$this->setError(JText :: _('ALIAS_IS_REQUIRED'));
			}
			elseif ($yvComment->getConfigValue('check_guest_alias', 1)) {
				// Guest's alias shouldn't be like any user's name
				$UserID2 = $yvComment->DLookup('id', '#__users', 'name LIKE ' . $this->_db->Quote('%' . $this->created_by_alias . '%'));
				if ($UserID2 > 0) {
					$Ok = false;
					$this->setError(JText :: _('ALIAS_IS_IN_USE') . ': \'' . $this->created_by_alias . '\'');
				}
			}

			if ($yvComment->getConfigValue('guest_email_required', '0')) {
				$email = $metadata->getValue('created_by_email');
				if (!empty ($email)) {
					jimport('joomla.mail.helper');
					// Make sure the e-mail address is valid
					if (!JMailHelper :: isEmailAddress($email)) {
						$Ok = false;
						$this->setError(JText :: sprintf('EMAIL_INVALID', $email));
					}
				}
				if ($email == '') {
					$Ok = false;
					$this->setError(JText :: _('EMAIL_IS_REQUIRED'));
				}
			}
		} else {
			// Not guest	
			if (trim($this->created_by_alias) == '') {
				$this->created_by_alias = $yvComment->DLookup('name', '#__users', 'id=' . $this->created_by);
			}
		}
		if (!$Ok) {
			return false;
		}

		return parent :: check();
	}

	// Physically delete or move to Trash
	function delete($oid = null) {
		$yvComment = & yvCommentHelper :: getInstance();
		//$this->setError('test delete'); return false;

		$blnTrash = (boolean) $yvComment->getConfigValue('delete_to_trash', '1');
		if ($blnTrash) {
			$blnTrash = $this->_usecontenttable;
		}
		if ($blnTrash) {
			// Delete, if this comment is in Trash already
			$blnTrash = ($this->state != '-2');
		}
		if (!$blnTrash) {
			return parent :: delete($oid);
		}
		//Delete to "Trash"
		//Let's do this exactly like Joomla! does it
		//  in ContentController::removeContent(), 
		//  see 'administrator/components/com_content/controller.php'
		$this->state = '-2';
		$this->ordering = '0';
		$this->checked_out = '0';
		$this->checked_out_time = $this->_db->getNullDate();

		return $this->store();
	}

	/* There is incosistency in JTable with JObject 
	 * So I have to restore JObject behaviour here to store all error and the last one
	 * */
	function setError($error) {
		$this->_error = $error;
		array_push($this->_errors, $error);
	}
}
?>
