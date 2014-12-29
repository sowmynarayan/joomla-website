<?php defined('_JEXEC') or die('Restricted access'); ?>
<div class="jsn-search-form">
	<form id="searchForm" action="<?php echo JRoute::_( 'index.php?option=com_search' );?>" method="post" name="searchForm">
		<div class="jsn-search-keyword">
        	<label for="search_searchword"><?php echo JText::_( 'Search Keyword' ); ?>:</label>
			<input type="text" name="searchword" id="search_searchword" size="30" maxlength="20" value="<?php echo $this->escape($this->searchword); ?>" class="inputbox" />
            <button name="Search" onclick="this.form.submit()" class="button"><?php echo JText::_( 'Search' );?></button>
		</div>
		<div class="jsn-search-option">
			<?php echo $this->lists['searchphrase']; ?>
		</div>
		<div class="jsn-search-ordering">
			<label for="ordering"><?php echo JText::_( 'Ordering' );?>:</label>
			<?php echo $this->lists['ordering'];?>
		</div>
		<?php if ($this->params->get( 'search_areas', 1 )) : ?>
            <div class="jsn-search-category clearafter">
            	<strong><?php echo JText::_( 'Search Only' );?> :</strong>
				<?php foreach ($this->searchareas['search'] as $val => $txt) :
					$checked = is_array( $this->searchareas['active'] ) && in_array( $val, $this->searchareas['active'] ) ? 'checked="checked"' : '';
				?>
                <span>
					<input type="checkbox" name="areas[]" value="<?php echo $val;?>" id="area_<?php echo $val;?>" <?php echo $checked;?> />
					<label for="area_<?php echo $val;?>"><?php echo JText::_($txt); ?></label>
                </span>
				<?php endforeach; ?>
            </div>
		<?php endif; ?>
		<div class="searchintro">
			<p><?php echo JText::_( 'Search Keyword : ' ).'<strong> '.$this->escape($this->searchword).'</strong>'; ?></p>
       	 	<p><?php echo $this->result; ?></p>
    	</div>
	<?php if($this->total > 0) : ?>
		<div class="jsn-infofilter">
			<label for="limit">
				<?php echo JText::_( 'Display Num' ); ?>
			</label>
			<?php echo $this->pagination->getLimitBox( ); ?>
        </div>
		<?php if ($this->params->def('show_pagination_results', 1)) : ?>
			<p class="jsn-pageinfo"><?php echo $this->pagination->getPagesCounter(); ?></p>
		<?php endif; ?>
	<?php endif; ?>
	<input type="hidden" name="task"   value="search" />
</form>
</div>
