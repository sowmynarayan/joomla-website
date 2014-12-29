<?php 
/** This is simple layout for "Latest comments" module  
* @version 1.18.0 
* @package yvComment 
* @(c) 2007-2008 yvolk (Yuri Volkov), http://yurivolkov.com. All rights reserved.
* @license GPL 
**/ 
  defined('_JEXEC') or die('Restricted access'); // no direct access
 	global $mainframe;
  $yvComment = &yvCommentHelper::getInstance();

	//  echo '<p>template=listofcomments_002,  parentview=' 
	//  	. $yvComment->ParentView() 
	//  	. ', parentoption=' . $yvComment->ParentOption() 
	//  	. ', DisplayTo=' . $yvComment->DisplayTo() . '</p>';

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
<div class="yvComment"<?php echo $this->params->get('moduleclass_sfx') ?> > 
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
      $from = 0;
      $to = count($this->items);
      $limitstart = $this->params->get('yvcomment_limitstart');
    ?>
    <div class='LatestComments'>
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
					<?php
						$text = '';
						$href = ''; 
						$title = $item->ArticleTitle; 
						if ($yvComment->getConfigValue('hide_title', '0') == 0) {
							$text = $this->escape($item->title);
						} else {
							$text = $item->text;
						}
				    if ($item->parentid > 0) {
		        	$href = $yvComment->ContentIDToURL($item->parentid, true, null, false, "yvComment" . $item->parentid);
	          }
						$text = $yvComment->TrimText($text, $this->params->get( 'max_characters_list_row', 0 ));
        		if (strlen($href) > 0) {
					?>
					<a href="<?php echo $href; ?>"<?php 
					  echo ($mainframe->isAdmin() ? ' target="_blank"' : '');
					  echo (empty($title) ? '' : ' title="' . $title . '"');
					  ?>><?php echo $text; ?></a>
					 <?php } else {
	        		echo $text;
	        	} ?>
		    </div></div></div>
		  <?php endfor; // for every comment... ?>
	  </div> 
  <?php endif; // ShowComments ?>
</div>
