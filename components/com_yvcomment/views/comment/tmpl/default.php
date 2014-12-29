<?php 
/** This template is used to show:
* - "This is comment..." link to the parent Article
* - "Add your comment" and "Comments(N)" links on frontpage/blog pages...
* - Form to add/edit Comment  
* 
* This template:
* - includes "default_item.php" to preview Comment
* - calls "listofcomments" view to show list of comments
* @version		$Id: default.php 2 2009-08-30 15:10:43Z yvolk $
* @package 		yvComment 
* @copyright	2007-2009 yvolk (Yuri Volkov), http://yurivolkov.com. All rights reserved.
* @license GPL 
**/ 
  defined('_JEXEC') or die('Restricted access'); // no direct access

  $yvComment = &yvCommentHelper::getInstance();
  $TaskTitle = "";
	$article = null;
	if ($yvComment->_debug) {
	  echo '<p>template=comment,  parentview=' 
	  	. $yvComment->ParentView() 
	  	. ', parentoption=' . $yvComment->ParentOption() 
	  	. ', DisplayTo=' . $yvComment->DisplayTo() 
			. ', addform_position=' . $this->params->get('addform_position')
	  	. '</p>';
	}
?>
<?php if (!$yvComment->IsNested()) : ?>
<div class="yvComment<?php echo $this->params->get( 'moduleclass_sfx' ); ?>"
  <?php if (!$yvComment->IsIdShown($this->ArticleID, true)) { echo " id=\"yvComment" . $this->ArticleID . "\"";} ?> >
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

<?php if ($this->params->get('ShowArticleTitle')) :
 	if (!$article) { $article = $this->get('Article'); }
 	if ($article) :
		$yvComment->PrepareItemForView($article);
?>
	<div class='Article'>
		<h2 class="contentheading"><?php
			$ArticleUrl = $yvComment->ContentIDToURL($article->id);
			if ($ArticleUrl) {
			  echo "<a href=\"" . $ArticleUrl . "\" >";
			}
		  echo $article->title; 
			if ($ArticleUrl) {
			  echo '</a>'; 
			}
		  ?></h2>
		<?php if ($this->params->get('ShowArticle')) : ?>
		<div>
			<?php 
				$text = $article->text;
				$max_length = 2000;
				if (JString::strlen($text) > $max_length) {
					// The Article text is too long to be displayed on this page.
					//$text = JFilterInput::clean($text, 'string');;
					//$text = JString::substr($text, 0, $max_length) . '...';
					$text = '...';
				}
			  echo $text;
				if (isset($article->readmore_link)) : ?>
					<a href="<?php echo $article->readmore_link; ?>" class="readon<?php echo $this->params->get('pageclass_sfx'); ?>">
						<?php echo $article->readmore_text; ?></a>
				<?php endif; ?>
		</div>
		<?php endif; // ShowArticle ?>
	</div>
<?php endif; endif; // ShowArticleTitle ?>

<?php if ($this->params->get('ShowParentArticle')) : ?>
	<div class='CommentParentArticle'>
  <?php echo JText::_('THIS_IS_COMMENT_OF'); 
	  echo ' &quot;'; 
		$ParentUrl = $yvComment->ContentIDToURL($this->ParentArticle->id);
		if ($ParentUrl) {
		  echo "<a href=\"" . $ParentUrl . "\" >";
		}
	  echo $this->ParentArticle->title; 
		if ($ParentUrl) {
		  echo '</a>'; 
		}
	  echo '&quot;'; 
  ?>
  <?php 
    ?>
	</div>
<?php endif; // ShowParentArticle ?>

<?php if ($this->params->get('ShowNumberOfCommentsAlone')) : ?>
	<div class="NumCommentsAlone">
  <?php
    if (isset($this->numcomments_link) && (!$this->print || $this->nCommentsTotal>0) ) {
        echo '<a href=\'' . $this->numcomments_link . '\'>'; 
        if ($this->nCommentsTotal > 0) {
	     		echo JText::_('COMMENTS') . ' (' . $this->nCommentsTotal . ')'; 
        } else {
	     		echo JText::_('ADD_YOUR_COMMENT'); 
        }
     	  echo '</a>'; 
    } else {
     		echo JText::_('COMMENTS') . ' (' . $this->nCommentsTotal . ')'; 
    }
  ?>
	</div>
<?php endif; // ShowNumberOfCommentsAlone ?>
	
<?php  if ($this->params->get('ShowComments') && 
	($this->params->get('addform_position') == 'below' || $this->params->get('addform_position') == 'separate_below')) {
	$parmsv = null;
	echo $yvComment->ShowCommentsOnArticle($parmsv); }  	
?>
	
<?php if ($this->params->get('ShowPreview')) {
	echo $this->loadTemplate('item'); } 
?>
	
<?php if ($this->params->get('ShowForm')) : 
	$TaskTitle = "";
	switch ($this->task_next) {
	  case 'add' :
	  	$TaskTitle = JText::_('ADD_YOUR_COMMENT');
		  break;
		case 'edit' :
		  $TaskTitle = JText::_('EDIT_COMMENT');
		  break;
		case 'delete' :
		  $TaskTitle = JText::_('DELETE_COMMENT');
		  break;
		case 'adddisplay' :
			// No title	
			break;  
		default :
		  $TaskTitle = '?? task_next="'. $this->task_next . '"<br />';
	}

	// make form name unique to allow multiple forms on one page
	$idSuffix = $yvComment->InstanceId() . '_' . rand(1000,9999); 
	$yvCommentFormName = 'yvCommentForm' . $idSuffix; 
?>
	<div class='CommentForm'>
	  <?php if (!empty($TaskTitle)) : ?>
    <h3><?php echo $TaskTitle; ?></h3>
	  <?php endif; ?>
	  <form name='<?php echo $yvCommentFormName;?>' action='<?php echo JRoute::_('index.php?option=com_yvcomment');?>' target="_top" method='post'>
	    <input type='hidden' name='Itemid' value='<?php echo $yvComment->getComponentItemid();?>' />
	    <input type='hidden' name='yvCommentID' value='<?php echo $this->get('yvCommentID'); ?>' />
	    <input type='hidden' name='ArticleID' value='<?php echo $this->ArticleID; ?>' />
	    <input type='hidden' name='task' value='<?php echo $this->task_next;?>' />
	    <input type='hidden' name='view' value='comment' />
	    <input type='hidden' name='url' value='<?php echo $this->escape($yvComment->buildReturnURL(true, "yvComment" . $this->ArticleID));?>' />
				<input type='hidden' name='button' value='' />
				<script language="javascript" type="text/javascript">
					<!--
					function submitbutton<?php echo $yvCommentFormName;?>(pressbutton) {
						var form = document.<?php echo $yvCommentFormName;?>;
						switch (pressbutton) {
							case "post":
							case "preview":
		           	<?php if (isset($this->editor)) : ?>
									<?php echo $this->editor->save( 'text' ); ?>
									
									//var text1 = <?php echo $this->editor->getContent( 'text' ); ?>
									
									//alert(text1);
								  //form.text.value = text1;	
		      			<?php endif; ?>
								// do field validation
								// TODO
							
								break;
							default:
						}
					  form.button.value = pressbutton;	
						form.submit();
					}
					//-->
		  </script>
	
	    <?php if ($this->task_next == 'delete') : ?>
	        <table width='100%'>
	        <tr>
							<td />
	          <td>
								<button type="button" onclick="submitbutton<?php echo $yvCommentFormName;?>('delete')" class='button' >
									<?php echo JText::_("DELETE"); ?>
								</button>
								<button type="button" onclick="submitbutton<?php echo $yvCommentFormName;?>('close')" class='button'>
									<?php echo JText::_("CANCEL"); ?>
								</button>
	          </td>
	        </tr>
	  		</table>
	    <?php elseif ($this->task_next == 'adddisplay') : ?>
	        <table width='100%'>
	        <tr>
	          <td>
								<button type="button" onclick="submitbutton<?php echo $yvCommentFormName;?>('adddisplay')" class='button' >
									<?php echo JText::_("ADD_YOUR_COMMENT"); ?>
								</button>
	          </td>
	        </tr>
	  		</table>
	    <?php else : 
				$item = & $this->CurrentItem;
			  $form = '';
	      if (!isset($this->editor)) {
		        if ($this->params->get('use_bbcode_form', true)) {
	 			  	$mainframe->triggerEvent('onBBCode_RenderForm', array('document.forms.' . $yvCommentFormName . '.text', &$form) );
		        }
		        if ($this->params->get('use_smiley_form', true)) {
	 			  	$mainframe->triggerEvent('onSmiley_RenderForm', array('document.forms.' . $yvCommentFormName . '.text', &$form) );
		        }
	      }  
	      if (JString::strlen($form) > 0) {
	        // didn't put this inside table, because of unbreakable lines in MSIE...            
	      	echo $form;
	      }  ?>
	    	<table width='100%'>
	        <?php if ( !$yvComment->UserIsRegistered() 
	                   || (($item) && ($item->created_by == $yvComment->getGuestID()))
	                 ) : ?>                      

		        <?php
							$created_by_username = ($item ? 
								$yvComment->getValueFromIni($item->metadata, 'created_by_username') : '');
		        	if ($created_by_username) :
		        ?>
			          <tr>
			            <td class="CommentLeftColumn">
			            	<a href='http://openid.net' target='_blank' title='<?php echo JText::_("WHAT_IS_OPENID"); 
			            		?>'><?php echo JText::_('OPENID_USERNAME'); ?></a>:
			            </td>
			            <td>
			              <?php echo $created_by_username; ?>
			            </td>  
			          </tr>
		        <?php endif; // created by temp user ?>
						<?php 
		        	if ($this->params->get('yvcomment_use_openid')) :
				        if ($yvComment->UserIsGuest()) : ?>
			          <tr>
			            <td class="CommentLeftColumn">
			            	<a href='http://openid.net' target='_blank' title='<?php echo JText::_("WHAT_IS_OPENID"); 
			            		?>'><?php echo JText::_('OPENID_USERNAME'); ?></a>:
			            </td>
			            <td>
			              <input name='username' type='text' class='com-system-openid' style='width: 95%' value='' />
			            </td>  
			          </tr>
			        <?php endif; // UserIsGuest ?>
		        <?php endif; // yvcomment_use_openid ?>

		        <?php if ( ($yvComment->UserIsGuest() && !$this->params->get('yvcomment_use_openid'))
		                   || (!$yvComment->UserIsGuest())
		                 ) : ?>                      
		          <tr>
		            <td class="CommentLeftColumn" title='<?php echo JText::_("YOURALIAS_TIP"); ?>'>
		              <?php echo JText::_("YOURALIAS"); ?>:
		            </td>
		            <td>
		              <input name='created_by_alias' type='text' class='inputbox' style='width: 98%' value="<?php echo (($item) ? $this->escape($item->created_by_alias) : '');?>" /></td>
		          </tr>
		        <?php endif; ?>
	          
	          <?php if ($this->params->get('guest_email_required')) :
								$created_by_email = ($item) ? $yvComment->getValueFromIni($item->metadata, 'created_by_email') : '';	?>
	              <tr>
	                <td class="CommentLeftColumn">
	                  <?php echo JText::_("YOUREMAIL"); ?>:
	                </td>
	                <td>
	                  <input name='created_by_email' type='text' class='inputbox' style='width: 98%' value="<?php echo (($item) ? $this->escape($created_by_email) : '');?>" /></td>
	              </tr>
	          <?php endif; ?>
	          <?php if ($this->params->get('allow_guest_link')) :
								$created_by_link = ($item) ? $yvComment->getValueFromIni($item->metadata, 'created_by_link') : '';	?>
	              <tr>
	                <td class="CommentLeftColumn" title='<?php echo JText::_("YOURLINK_TIP"); ?>'>
	                  <?php echo JText::_("YOURLINK"); ?>:
	                </td>
	                <td>
	                  <input name='created_by_link' type='text' class='inputbox' style='width: 98%' value="<?php echo (($item) ? $this->escape($created_by_link) : '');?>" /></td>
	              </tr>
	          <?php endif; ?>
	        <?php endif; // For Guests and Temp Users ?>
	        
	        <?php if ($yvComment->getConfigValue('hide_title', '0') == 0): ?>
	            <tr>
	              <td class="CommentLeftColumn" title='<?php echo JText::_("TITLE_TIP"); ?>'>
	                <?php echo JText::_("TITLE"); ?>:
	              </td>
	              <td>
	                <input name='title' type='text' class='inputbox' style='width: 98%' value="<?php echo (($item) ? $this->escape($item->title) : '');?>" /></td>
	            </tr>
	        <?php endif; ?>
	        <tr id='fulltextrow<?php echo $idSuffix; ?>'>
	          <td class="CommentLeftColumn" title='<?php echo JText::_("FULLTEXT_TIP"); ?>'>
	            <?php
	              if ($this->params->get('allow_html_edit_text') == 'no') {
	                echo JText::_("FULLTEXT");                    	
	              } else {
	              	echo JText::_("FULLTEXT_WITH_HTML");
	              }
	             ?>:
	          </td>
	          <td>
	          	<?php
	          		$textToEdit = $yvComment->UnifyIntrotextFulltext($item);
	          		if (isset($this->editor)) : ?>
	              	<?php
	              	  echo $this->editor->display('text', $this->escape($textToEdit), '98%', '400', '70', '15'); 
	              	?>
	      			<?php else : ?>
	            	<textarea rows='8' cols='40' name='text' class='inputbox' style='width: 98%'<?php
	            		if ($this->params->get('yvcomment_delay_captcha_image')
	            			&& empty($textToEdit) 
	            		  && $this->params->get('yvcomment_use_captcha')) {
	            			echo ' onfocus="document.getElementById(\'captcharow'	. $idSuffix 
	            			. '\').style.display=document.getElementById(\'fulltextrow'	. $idSuffix 
	            			. '\').style.display; ' 
	            			.	'document.getElementById(\'captcha'	.	$idSuffix 
	            			. '\').src=\'' . JRoute::_('index.php?option=com_yvcomment&task=displaycaptcha') 
	            			. '\'; document.getElementById(\'secretwordrow'	. $idSuffix 
	            			. '\').style.display=document.getElementById(\'fulltextrow'	. $idSuffix 
	            			. '\').style.display; "';
	            		} ?>><?php echo $this->escape($textToEdit);?></textarea></td>
	        		<?php endif; ?>
	        </tr>
	        
	        <?php if ($this->params->get('yvcomment_use_captcha')) : ?>
	          <tr <?php
	            		if ($this->params->get('yvcomment_delay_captcha_image')
	            			&& empty($textToEdit) 
	            		  && !isset($this->editor) ) {
	            			echo ' id=\'captcharow' . $idSuffix . '\' style=\'display:none\'';
	            		}?>>
	            <td>&nbsp;
	            </td>
	            <td>
	            	<img
	            		<?php
	            		// TODO: src attribute is required for img element
	            		// so we need some other approach here (maybe create the whole img element...) 
	            		if ($this->params->get('yvcomment_delay_captcha_image') 
	            			&& empty($textToEdit) 
	            			&& !isset($this->editor)) {
	            			echo ' id=\'captcha' . $idSuffix . '\'';
	            		} else {
	            			echo ' src=\'' . JRoute::_('index.php?option=com_yvcomment&task=displaycaptcha') . '\'' ;
	            		}?>
	            	  alt='<?php echo JText::_("SECRETWORD_IMAGE"); ?>' />
	            </td>
	          </tr>
	          <tr<?php
	            		if ($this->params->get('yvcomment_delay_captcha_image') 
	            			&& empty($textToEdit) 
	            			&& !isset($this->editor)) {
	            			echo ' id=\'secretwordrow' . $idSuffix . '\' style=\'display:none\'';
	            		}?>>
	            <td class="CommentLeftColumn" title='<?php echo JText::_("SECRETWORD_TIP"); ?>'>
	              <?php
	                echo JText::_('SECRETWORD');                    	
	             	?>:
	            </td>
	            <td>
	              <input name='secretword' type='text' class='inputbox' style='width: 98%' value='' />
	            </td>  
	          </tr>
	        <?php endif; // yvcomment_use_captcha ?>
	
	        <?php if ( ($this->task_next != 'add') 
	          & $this->user->authorize('com_content', 'edit', 'content', 'all')) : ?>
	          <tr>
	            <td class="CommentLeftColumn">
	              <?php
	                echo JText::_('PUBLISHED');                    	
	             	?>:
	            </td>
	            <td>
	            	<?php echo JHTML::_('select.booleanlist', 'state', '', $item->state); ?>
	            </td>
	          </tr>
	        <?php endif; // show published ?>
	        
	        <tr>
							<td />
	          <td>
								<button type="button" onclick="submitbutton<?php echo $yvCommentFormName;?>('post')" class='button' >
									<?php echo JText::_("POST"); ?>
								</button>
								<button type="button" onclick="submitbutton<?php echo $yvCommentFormName;?>('preview')" class='button' >
									<?php echo JText::_("PREVIEW"); ?>
								</button>
	            <?php if ($this->task != 'viewdisplay') : ?>
									<button type="button" onclick="submitbutton<?php echo $yvCommentFormName;?>('close')" class='button'>
										<?php echo JText::_("CANCEL"); ?>
									</button>
	            <?php endif; // $this->task ?>
	          </td>
	      	</tr>
	  		</table>
	   	<?php endif; // $this->task_next ?>
			<?php echo JHTML::_( 'form.token' ); ?>
	  </form>
	</div>
	<?php endif; // ShowForm ?>
	
	<?php if ($this->task == 'add' || $this->task == 'edit') : ?>
	<div class="Comment">
	  <div class='CommentForm'>
	    <h3>
	    	<?php
	        $TaskTitle = JText::_('RETURN_TO_ARTICLE');
	     		echo $TaskTitle; ?>
	    </h3>
	    <table width='100%'>
	        <tr>
							<td />
	          <td>
	            <form action='<?php echo JRoute::_('index.php?option=com_yvcomment');?>' target="_top" method='post'>
    						<input type='hidden' name='Itemid' value='<?php echo $yvComment->getComponentItemid();?>' />
    						<input type='hidden' name='ArticleID' value='<?php echo $this->ArticleID; ?>' />
				        <input type='hidden' name='task' value='add' />
				        <input type='hidden' name='view' value='comment' />
				        <input type='hidden' name='url' value='<?php echo $this->escape($yvComment->getReturnURL());?>' />
								<input type='hidden' name='button' value='close' />
							  <input type='submit' name='post' value='<?php echo JText::_("CONTINUE"); ?>' class='button' />
	            </form>
	          </td>
	        </tr>
	  	 </table>
	  </div>
	</div>
	<?php endif; // task == 'add' or 'edit' ?>

	<?php if ($this->params->get('ShowPleaseRegister')) : ?>
	<div class='PleaseRegister'>
		<?php echo JText::_('PLEASE_REGISTER_TO_ADD_COMMENTS'); ?>
	</div>
	<?php endif; // ShowPleaseRegister ?>

	<?php  if ($this->params->get('ShowComments') && 
		($this->params->get('addform_position') == 'above' || $this->params->get('addform_position') == 'separate_above')) {
		$parmsv = null;
		echo $yvComment->ShowCommentsOnArticle($parmsv); }  	
	?>

  <?php if ($yvComment->getShowLogo(true)) : ?>
	<div class='CommentPoweredBy'><a href="http://yurivolkov.com/Joomla/yvComment/index_en.html" target="_blank" rel="nofollow">yvComment v.<?php echo $yvComment->getShortVersion();?></a></div>
  <?php endif; ?>

	<?php if ( $yvComment->ParentOption() == 'com_yvcomment') {
			if ($TaskTitle == "" && $this->params->get('ShowComments') 
				&& $yvComment->getComponentItemid() == 0 ) {
				$TaskTitle = JText::_('TYPE_COMMENT_DESC');
			}
			if (strlen($TaskTitle) > 0) {
				//We need to set page title here
				$document	=& JFactory::getDocument();
				$page_title = "";
		  	if (!$article) { $article = $this->get('Article'); }
		  	if ($article) {
					$page_title = $article->title . " - ";
		  	}
				$page_title = $page_title . $TaskTitle;
				$document->setTitle($page_title);
			}
		} 
	?>

<?php if (!$yvComment->IsNested()) : ?>
<div class="CommentClr"></div></div>
<?php endif; ?>
