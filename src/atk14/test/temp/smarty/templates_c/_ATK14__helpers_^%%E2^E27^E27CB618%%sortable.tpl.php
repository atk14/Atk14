<?php /* Smarty version 2.6.26, created on 2011-10-19 19:47:56
         compiled from sortable.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('block', 'sortable', 'sortable.tpl', 4, false),)), $this); ?>
<table>
	<thead>
		<tr>
			<?php $this->_tag_stack[] = array('sortable', array('key' => 'date')); $_block_repeat=true;smarty_block_sortable($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?><th>Date</th><?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_sortable($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>
			<?php $this->_tag_stack[] = array('sortable', array('key' => 'name')); $_block_repeat=true;smarty_block_sortable($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?><th class="name">Name</th><?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_sortable($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?>
		</tr>
	</thead>
</table>