<?php
/** This template is used to preview comment only!
 *  For the list of comments see "listofcomments" view.
**/ 
	defined('_JEXEC') or die('Restricted access'); 
  $yvComment = &yvCommentHelper::getInstance();
	if (isset($this->CurrentItem)) :	?>
	<div class="Comments">
		<?php
	  	// Create copy of the object to preserve it for use later...
			$item	= (PHP_VERSION < 5) ? $this->CurrentItem : clone($this->CurrentItem);
			$yvComment->PrepareItemForView($item);
			$i = 1;
			$even = 0;
	    ?>
		<div class='Comment<?php echo (($item->state == 1) ? '' : '_unpublished') 
		                             . (($even == 1) ? '_even' : ''); ?>'><div><div>
		  <?php
		    $even = 1 - $even; 
		  ?>
		  <div class='CommentHeader'>
		    <div class='CommentTitle'>
		      <?php
		        $ShowAvatar = isset($item->avatar);
		        $ShowControlBox = $ShowAvatar;
		        
		        if ($ShowControlBox) : 
		      ?> 
		        <div class='CommentControlBox'>
		          <table><tr>
				        <?php if ($ShowAvatar) {
				          	echo '<td>' . $item->avatar . '</td>';
				          }
								?> 
		          </tr></table>
		        </div>
		    	<?php endif; ?>
		      <?php
		        if ($yvComment->getConfigValue('hide_title', '0') == 0) {
		        	echo $this->escape($item->title); 
		        }
		      ?>
		    </div>
		    <div class='CommentDateAndAuthor'>
		      <div class='CommentDate'>
						<span class='CommentNum'><?php echo ($i); ?></span>
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
		  	if (isset($item->event)) {
		  		if (isset($item->event->afterDisplayContent)) {
						echo $item->event->afterDisplayContent;
		  		}
		  	} 
		  ?>
		</div></div></div>
	</div>
<?php endif; ?>
