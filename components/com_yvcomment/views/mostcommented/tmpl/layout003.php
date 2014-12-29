<?php 
/** This is simple layout for "Most commented" module,
*		that looks like standard "Latest news"   
* @version 1.20.0 
* @package yvComment 
* @(c) 2009 yvolk (Yuri Volkov), http://yurivolkov.com. All rights reserved.
* @license GPL 
**/ 
  defined('_JEXEC') or die('Restricted access'); // no direct access
 	global $mainframe;
  $yvComment = &yvCommentHelper::getInstance();

	//  echo '<p>template=mostcommented_003,  parentview=' 
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
<?php if (count($this->message)>0) : ?>
<div class="yvComment"<?php echo $this->params->get('moduleclass_sfx') ?> > 
  <div class="CommentMessage">
    <?php
      //echo $this->message . ' --- <br>';
      foreach ($this->message as $message) { 
        echo $message . '<br/>'; 
      }
    ?>
  </div>
</div>  
<?php endif; ?>

<?php if ($this->params->get('ShowComments')) : ?>
  <?php 
    $even = 0;
    $from = 0;
    $to = count($this->items);
    $limitstart = $this->params->get('yvcomment_limitstart');
  ?>
	<ul class="latestnews<?php echo $this->params->get('moduleclass_sfx'); ?>">
    <?php for ($i=$from; $i < $to; $i++) : 
	    $item = &$this->items[$i];
    ?>
			<li class="latestnews<?php echo $this->params->get('moduleclass_sfx'); ?>">
	      <?php
	        $even = 1 - $even; 
	        if (!is_object($item)) {
	          echo ' not and object, i=' . $i . ' from=' . $from . ' to=' . $to;
	        }
			$text = '';
			$href = ''; 
			$title = ''; 
			$text = $this->escape($item->title);
        	$href = $yvComment->ContentIDToURL($item->id);
			$text = $yvComment->TrimText($text, $this->params->get( 'max_characters_list_row', 0 ));
			$text .= ' (' . $item->NumComments . ')';
        	if (strlen($href) > 0) {
        		echo '<a href="' . $href . '"';
        		echo ' class="latestnews' . $this->params->get('moduleclass_sfx') . '"';
				echo ($mainframe->isAdmin() ? ' target="_blank"' : '');
				echo (empty($title) ? '' : ' title="' . $title . '"');
				echo '>' . $text . '</a>';
			} else {
        		echo $text;
        	} ?>
	    </li>
	  <?php endfor; // for every article... ?>
  </ul> 
<?php endif; // ShowComments ?>
