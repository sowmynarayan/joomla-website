<?php // no direct access
defined('_JEXEC') or die('Restricted access'); ?>
<h1 class="componentheading">
	<?php echo $this->escape($this->message->title) ; ?>
</h1>

<div class="message">
	<?php echo $this->escape($this->message->text) ; ?>
</div>
