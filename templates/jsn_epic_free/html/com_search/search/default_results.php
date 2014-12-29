<?php defined('_JEXEC') or die('Restricted access'); ?>
<div class="jsn-search-result">
	<?php foreach( $this->results as $result ) : ?>
		<fieldset>
			<p>
				<span class="small">
					<?php echo $this->pagination->limitstart + $result->count.'. ';?>
				</span>
				<?php if ( $result->href ) :
					if ($result->browsernav == 1 ) : ?>
						<a href="<?php echo JRoute::_($result->href); ?>" target="_blank">
					<?php else : ?>
						<a href="<?php echo JRoute::_($result->href); ?>">
                    <?php endif;
						echo $this->escape($result->title);
						if ( $result->href ) : ?>
						</a>
					<?php endif;
					if ( $result->section ) : ?>
						<span class="small">
								(<?php echo $this->escape($result->section); ?>)
						</span>
					<?php endif; ?>
				<?php endif; ?>
			</p>
			<p><?php echo $result->text; ?></p>
			<?php if ( $this->params->get( 'show_date' )) : ?>
				<p class="small"><?php echo $result->created; ?></p>
			<?php endif; ?>
		</fieldset>
	<?php endforeach; ?>
	<?php if ($this->params->def('show_pagination', 2) == 1  || ($this->params->get('show_pagination') == 2 && $this->pagination->get('pages.total') > 1)) : ?>
		<div class="jsn-pagination">
			<?php echo $this->pagination->getPagesLinks(); ?>
		</div>
	<?php endif; ?>
</div>