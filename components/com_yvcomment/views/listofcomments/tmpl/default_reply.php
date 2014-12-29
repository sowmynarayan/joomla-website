<?php 
/** Included template for comments on Comment
* @version		$Id: default_reply.php 2 2009-08-30 15:10:43Z yvolk $
* @package yvComment 
* @(c) 2007-2008 yvolk (Yuri Volkov), http://yurivolkov.com. All rights reserved.
* @license GPL 
**/ 
  defined('_JEXEC') or die('Restricted access'); // no direct access
 	global $mainframe;
  $yvComment = &yvCommentHelper::getInstance();
	$parent = &$this->item;
  if (isset($parent->children)) {
?>
  <div class='CommentReply'>
  	<div class='CommentReply_Comments'>
<?php
  $even = 0;
	foreach ($parent->children as $item) {	  	
?>
	    	<div class='Comment<?php echo (($item->state == 1) ? '' : '_unpublished') 
	                                   . (($even == 1) ? '_even' : ''); ?>'><div><div>
		      <?php
		        $even = 1 - $even; 
		      ?>
		      <div class='CommentHeader'>
		        <div class='CommentTitle'>
			        <?php
			          $ShowEditBtn = (boolean) ( !$this->print && 
			          	$yvComment->EditEnabled($item));
				        $ShowAvatar = isset($item->avatar);
				        $ShowControlBox = $ShowEditBtn || $ShowAvatar;
				        
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
				                  <input type='image' name='editdisplay' alt='<?php echo JText::_("EDIT_COMMENT_TIP"); ?>' src='<?php echo $yvComment->getSiteURL() ?>images/M_images/<?php echo ($item->state ? 'edit.png' : 'edit_unpublished.png') ?>' />
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
					                  <input type='image' name='deletedisplay' alt='<?php echo JText::_("DELETE_COMMENT_TIP"); ?>' src='<?php echo $yvComment->getSiteURL() ?>images/M_images/icon_error.gif' />
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
			            </tr></table>
			          </div>
			        <?php endif; ?>
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
		    </div></div></div>
<?php }?>
  	</div>
  </div>
<?php }?>
