<?php // no direct access
defined('_JEXEC') or die('Restricted access'); ?>
<form action="index.php" method="post">
	<div class="search<?php echo $params->get('moduleclass_sfx') ?>">
		<?php
		    $output = '<input name="searchword" id="mod_search_searchword" maxlength="'.$maxlength.'" alt="'.$button_text.'" class="inputbox'.$moduleclass_sfx.'" type="text"  value="'.$text.'"  onblur="if(this.value==\'\') this.value=\''.$text.'\';" onfocus="if(this.value==\''.$text.'\') this.value=\'\';" />';

			$button = '<input id="search_button" type="image" value="'.$button_text.'" class="button'.$moduleclass_sfx.'" src="templates/b59_tpl14/images/search.gif" onclick="this.form.searchword.focus();"/>';

			echo $output.$button;
		?>
	</div>
	<input type="hidden" name="task"   value="search" />
	<input type="hidden" name="option" value="com_search" />
	<input type="hidden" name="Itemid" value=<?php echo $mitemid; ?> />
</form>
