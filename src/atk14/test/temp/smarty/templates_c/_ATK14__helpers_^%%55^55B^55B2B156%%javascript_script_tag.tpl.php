<?php /* Smarty version 2.6.26, created on 2011-10-19 19:47:56
         compiled from javascript_script_tag.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'javascript_script_tag', 'javascript_script_tag.tpl', 1, false),)), $this); ?>
<?php echo smarty_function_javascript_script_tag(array('file' => "site.js"), $this);?>

<?php echo smarty_function_javascript_script_tag(array('file' => "site.js",'media' => 'screen'), $this);?>

<?php echo smarty_function_javascript_script_tag(array('file' => "nonexisting.js"), $this);?>
