<?php 
/** This is base (most "heavy") layout for different variants of 
* "lists of comments", including "Latest comments"...
* This layout is used by plugin to show list of comments.
* This layout uses other templates:
* 	default_owner.php -for owners reply
* 	default_reply.php -for replies "one level deep"
* @version		$Id: default.php 2 2009-08-30 15:10:43Z yvolk $
* @package yvComment 
* @(c) 2007-2009 yvolk (Yuri Volkov), http://yurivolkov.com. All rights reserved.
* @license GPL 
**/ 
  defined('_JEXEC') or die('Restricted access'); // no direct access
 	global $mainframe;
  $yvComment = &yvCommentHelper::getInstance();

	if ($yvComment->_debug) {
	  echo '<p>template=listofcomments,  parentview=' 
	  	. $yvComment->ParentView() 
	  	. ', parentoption=' . $yvComment->ParentOption() 
	  	. ', DisplayTo=' . $yvComment->DisplayTo() . '</p>';
	}  	

	// Here you may customize dynamic title of the module
	if ($this->params->get('module_title_is_dynamic', true)) {
		$module_title = $this->params->get('module_title', '');  
		$ObjectName = $yvComment->getContextObjectName();
		$msgcode = '';
  	switch ($yvComment->getFilterByContext()) {
  		case 'article':
  		  $msgcode = 'MODULE_TITLE_CONTEXT_ARTICLE';
  		  break;
  		case 'category':
  		  $msgcode = 'MODULE_TITLE_CONTEXT_CATEGORY';
  		  break;
  		case 'section':
  		  $msgcode = 'MODULE_TITLE_CONTEXT_SECTION';
  		  break;
  		case 'all':
  		  $msgcode = 'MODULE_TITLE_CONTEXT_ALL';
  		  break;
  	}
		$module_title .= str_replace( 
      array('%1'), 
      array($ObjectName), 
      JText::_($msgcode)
		);
		$this->params->set('module_title', $module_title);
	}
?>
<?php if (!$yvComment->IsNested()) : ?>
<div class="yvComment"
  <?php echo $this->params->get( 'moduleclass_sfx' ); ?>
  <?php if ($yvComment->DisplayTo() != 'module' && $this->params->get('ShowComments')) echo ' id="yvComment"'; ?>>
<?php endif; ?>
   
<?php if (count($this->message)>0) : ?>
<div class="CommentMessage">
  <?php
    //echo $this->message . ' --- <br>';
    foreach ($this->message as $message) { 
      echo $message . '<br/>'; 
    }
  ?>
</div>
<?php endif; ?>

<?php if ($this->params->get('ShowComments')) : ?>
	<?php
  $even = 0;
  $ShowNumberOfComments = true;
  $from = 0;
  $to = count($this->items);
  $ascending = (boolean)($this->params->get('orderby_pri') == 'date');

  $nCommentsTotal = $this->params->get('nCommentsTotal');
  $limitstart = $this->params->get('yvcomment_limitstart');
  $limit = $this->params->get('yvcomment_limit');
  $num1 = $nCommentsTotal - $limitstart;
  $FilterByContext = $yvComment->getFilterByContext();
  if ($yvComment->DisplayTo() == 'module' && $limit>0) {
    $ShowNumberOfComments = false;
  }
  if ($this->params->get('ShowCommentsOnComment')) {
		switch ($this->allow_comments_on_comment) {
			case 'administrators_reply_only' :
			case 'owners_reply_only' :
			case 'one_level_deep' :
				if (!$this->LoadChildren()) {
					$this->params->set('ShowCommentsOnComment', false);
				}
				break;
			case 'threaded_comments' :
				if (!$this->get('ChildrenCount')) {
					$this->params->set('ShowCommentsOnComment', false);
				}
		}
  }
	?>
<?php if ($ShowNumberOfComments) : ?>
<div class="NumComments">
	<?php echo JText::_('COMMENTS') . ' (' . $nCommentsTotal . ')'; ?>
	<?php if ($yvComment->getFilterByContext() == 'article') {
		if ($yvComment->CommentsAreClosed($yvComment->getArticleID())){
			echo "<img alt='" . JText::_("COMMENTS_ARE_CLOSED") . "' src='" .	$yvComment->getSiteURL() . "components/com_yvcomment/assets/checked_out.png'/>";
		}	}
	?>
</div>
<?php endif; // ShowNumberOfComments ?>
<div class="Comments">
	<div class="CommentClr"></div>
	<?php for ($i=$from; $i < $to; $i++) : 
	    $item = &$this->items[$i];
	?>
	<div class='Comment<?php echo (($item->state == 1) ? '' : '_unpublished') 
                               . (($even == 1) ? '_even' : ''); ?>'><div><div>
		<?php
		$even = 1 - $even; 
		if (!is_object($item)) {
		  echo ' not and object, i=' . $i . ' from=' . $from . ' to=' . $to;
		    }
		?>
	  <div class='CommentHeader'>
	    <div class='CommentTitle'>
	    <?php
	      $ShowEditBtn = (boolean) ( !$this->print && 
      	$yvComment->EditEnabled($item));
        $ShowAvatar = isset($item->avatar);
        
        $ShowReplyButton = false;
        $ShowOwnersReplyList = false;
        $ShowReplyList = false;
  			if ($this->params->get('ShowCommentsOnComment')) {
					switch ($this->allow_comments_on_comment) {
						case 'administrators_reply_only' :
						case 'owners_reply_only' :
							if (isset($item->children)) {
	        			$ShowOwnersReplyList = true;
							} elseif ( !$this->print 
									&& ($item->state == 1)) {
		          	$ShowReplyButton = $yvComment->AddEnabled($item->id);
							}
							break;
						case 'one_level_deep' :
							if (isset($item->children)) {
	        			$ShowReplyList = true;
							} 
							if ( !$this->print 
									&& ($yvComment->getFilterByContext() == 'article')
									&& ($item->state == 1)) {
		          	$ShowReplyButton = $yvComment->AddEnabled($item->id);
							}
							break;
					}
  			}	
        
        $ShowControlBox = $ShowEditBtn || $ShowAvatar || $ShowReplyButton;
        if ($ShowControlBox) : 
	      ?> 
	      <div class='CommentControlBox'>
	        <table><tr>
			        <?php if ($ShowAvatar) {
			          	echo '<td>' . $item->avatar . '</td>';
			          }
							?> 
	        	<?php if ($ShowEditBtn) : ?> 
	              <td>
	                <form action='<?php echo JRoute::_('index.php?option=com_yvcomment');?>' target="_top" method='post'>
								    <input type='hidden' name='Itemid' value='<?php echo $yvComment->getComponentItemid();?>' />
	                  <input type='hidden' name='yvCommentID' value='<?php echo $item->id; ?>' />
	                  <input type='hidden' name='ArticleID' value='<?php echo $item->parentid; ?>' />
	                  <input type='hidden' name='task' value='editdisplay' />
	            	    <input type='hidden' name='view' value='comment' />
	                  <input type='hidden' name='url' value='<?php echo $this->escape($yvComment->buildReturnURL(true));?>' />
	                  <input type='image' name='editdisplay' title='<?php echo JText::_("EDIT_COMMENT_TIP"); ?>' src='<?php echo $yvComment->getSiteURL() ?>images/M_images/<?php echo ($item->state ? 'edit.png' : 'edit_unpublished.png') ?>' />
	                </form>
	              </td>
		            <?php if ($yvComment->UserCanEdit() == 'all') : ?> 
		              <td>
		                <form action='<?php echo JRoute::_('index.php?option=com_yvcomment');?>' target="_top" method='post'>
									    <input type='hidden' name='Itemid' value='<?php echo $yvComment->getComponentItemid();?>' />
		                  <input type='hidden' name='yvCommentID' value='<?php echo $item->id; ?>' />
		                  <input type='hidden' name='ArticleID' value='<?php echo $item->parentid; ?>' />
		                  <input type='hidden' name='task' value='deletedisplay' />
											<input type='hidden' name='view' value='comment' />
		                  <input type='hidden' name='url' value='<?php echo $this->escape($yvComment->buildReturnURL(true));?>' />
		                  <input type='image' name='deletedisplay' title='<?php echo JText::_("DELETE_COMMENT_TIP"); ?>' src='<?php echo $yvComment->getSiteURL() ?>images/M_images/icon_error.gif' />
		                </form>
		              </td>
		              <td>
		                <form action='<?php echo JRoute::_('index.php?option=com_yvcomment');?>' target="_top" method='post'>
									    <input type='hidden' name='Itemid' value='<?php echo $yvComment->getComponentItemid();?>' />
		                  <input type='hidden' name='yvCommentID' value='<?php echo $item->id; ?>' />
		                  <input type='hidden' name='ArticleID' value='<?php echo $item->parentid; ?>' />
		                  <input type='hidden' name='task' value='publish' />
		                  <input type='hidden' name='state' value='<?php echo ($item->state == 1 ? '0' : '1'); ?>' />
											<input type='hidden' name='view' value='comment' />
		                  <input type='hidden' name='url' value='<?php echo $this->escape($yvComment->buildReturnURL(true));?>' />
		                  <input type='image' name='publish' title='<?php echo JText::_($item->state == 1 ? 'Published' : 'Unpublished'); ?>' src='<?php echo $yvComment->getSiteURL() ?>administrator/images/publish_<?php echo ($item->state==1 ? 'g' : 'x') ?>.png'; />
		                </form>
		              </td>
		            <?php endif; ?>
	        	<?php endif; ?>
				      <?php	if ($ShowReplyButton) :	?>
		              <td>
				            <form action='<?php echo JRoute::_('index.php?option=com_yvcomment');?>' target="_top" method='post'>
									    <input type='hidden' name='Itemid' value='<?php echo $yvComment->getComponentItemid();?>' />
				              <input type='hidden' name='yvCommentID' value='0' />
				              <input type='hidden' name='ArticleID' value='<?php echo $item->id; ?>' />
				              <input type='hidden' name='task' value='adddisplay' />
				        	    <input type='hidden' name='view' value='comment' />
				              <input type='hidden' name='url' value='<?php echo $this->escape($yvComment->buildReturnURL(true));?>' />
											<button type='submit' class='button' >
												<?php echo JText::_("REPLY_BUTTON"); ?>
											</button>
				            </form>
		              </td>
	        	<?php endif; ?>
	        </tr></table>
	      </div>
        <?php endif; // ControlBox ?>
	      <?php
	        if ($yvComment->getConfigValue('hide_title', '0') == 0) {
    				$text = $this->escape($item->title);
							$href = ''; 
	        	if ($this->params->get('comment_linkable') == 'comment_linkable_title') {
			        	$href = $yvComment->CommentIDToURL($item->id);
	        	}
		        if ($href) { 
							echo '<a href="' .  $href . '"' 
								. ' class="CommentTitle' . $this->params->get('moduleclass_sfx') . '"'
							  . ($mainframe->isAdmin() ? ' target="_blank"' : '')
							  . '>' . $text . '</a>';
		        } else {
			     		echo $text; 
		        }
	        }
	      ?>
	    </div>
	    <div class='CommentDateAndAuthor'>
				<div class='CommentDate'>
					<span class='CommentNum'><?php
					  echo ($ascending ? ($i + 1 + $limitstart) : ($nCommentsTotal - $i - $limitstart) );
					  ?></span>
				  <?php echo JHTML::Date($item->created, $this->params->get('date_format')); ?>
				</div>
				<div class='CommentAuthor'>
	        <?php echo $yvComment->htmlAuthorName($this, $item); ?>
				</div>
	    </div>
	    <div class="CommentClr"></div>
	  </div>
  	<div class='CommentFulltext'><?php echo $item->text;  
			if (isset($item->readmore_link)) : ?>
				<a href="<?php echo $item->readmore_link; ?>" class="readon<?php echo $this->params->get('pageclass_sfx'); ?>">
					<?php echo $item->readmore_text; ?></a>
			<?php endif; ?></div>
	  <?php
  	if ($this->params->get('ShowCommentsOnComment') 
			&& $this->allow_comments_on_comment == 'threaded_comments') :
			$ShowChildrenCount = (boolean) ($item->ChildrenCount > 0);
			if ( !$this->print && ($item->ChildrenCount == 0) 
					&& ($yvComment->getFilterByContext() == 'article')
					&& ($item->state == 1)) {
		  	if ($yvComment->AddEnabled($item->id)) {
							$ShowChildrenCount = true;	          		
		  	}
			}
			if ($ShowChildrenCount) :
        $link = $yvComment->ContentIDToURL($item->id);
        $text = '';
        if ($item->ChildrenCount > 0) {
	     		$text = JText::_('COMMENTS') . ' (' . $item->ChildrenCount . ')'; 
        } else {
	     		$text = JText::_('REPLY_LINK'); 
        }
		?>						
    <div class="CommentNumChildrenAlone">
      <a href='<?php echo $link;?>'><?php echo $text;?></a> 
    </div>
		<?php endif; endif; ?>
    <?php if (($FilterByContext != 'article') && ($item->parentid > 0)) : ?>
	  <div class='CommentParentArticle'>
	    <?php
	    	$ParentUrl = $yvComment->ContentIDToURL($item->parentid);
				if ($ParentUrl) {
		      echo '<a href="' . $ParentUrl . '" ' .
		        ($mainframe->isAdmin() ? ' target="_blank"' : '') . 
		        '>' . $this->escape($item->ArticleTitle) . '</a>';
				} else {
				  echo $this->escape($item->ArticleTitle); 
				}?>
	  , <?php
	    if ($item->ArticleAuthorID != 0) {
			echo "<span class='CommentAuthorName'>" 
			. $item->ArticleAuthorName
			. "</span>";
	    } else {
	    	echo "<span class='CommentAuthorAlias'>" 
	    	. $item->ArticleAuthorAlias
			. "</span>";
	    }  ?>
	  </div>
    <?php endif; ?>
		<div class="CommentClr"></div>
	</div></div></div>
	  <?php	if ($ShowOwnersReplyList) {
			$this->item = &$item;
			echo $this->loadTemplate('owners_reply');
	  }	?>
	  <?php	if ($ShowReplyList) {
			$this->item = &$item;
			echo $this->loadTemplate('reply');
	  }	?>
	  <?php
	  	if (isset($item->event)) {
	  		if (isset($item->event->afterDisplayContent)) {
						echo $item->event->afterDisplayContent;
	  		}
	  	}
	  ?>
  <?php endfor; // for every comment... ?>
</div>
<?php endif; // ShowComments ?>

<?php if ($this->params->get('ShowControlForm')) : ?>
<div class="CommentControlForm">
	<form action="<?php JURI::current(); ?>" method="post" name="adminForm">
	<?php 
	if ($this->params->get('ShowFilters')) {
		$lists['state'] = JHTML::_('grid.state', $this->params->get('filter_state'), 'Published', 'Unpublished', 'Archived');
		echo $lists['state'];
	}	
	
	/* TODO some other filters...
	  ...from administrator/components/com_content/admin.content.html.php
	*/		
	?>

  <?php
    if ($this->params->get('yvcomment_show_pagination')) {
      if ($this->pagination) {
    	echo $this->pagination->getListFooter();
      }	
    }
  ?>
	</form>
</div> 
<?php endif; // ShowControlForm ?>
<?php if ($yvComment->getShowLogo(true)) : ?>
<div class='CommentPoweredBy'><a href="http://yurivolkov.com/Joomla/yvComment/index_en.html" target="_blank" rel="nofollow" >yvComment v.<?php echo $yvComment->getShortVersion();?></a></div>
<?php endif; ?>
<?php if (!$yvComment->IsNested()) : ?>
<div class="CommentClr"></div></div>
<?php endif; ?>
