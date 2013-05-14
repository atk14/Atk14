<?php
/**
 * Renders radio buttons as unordered list.
 *
 * <ul /><li />
 *
 * @package Atk14
 * @subpackage Forms
 */
class RadioSelect extends Select
{
	function __construct($option = array()){
		parent::__construct($option);
	}

	function _renderer($name, $value, $attrs, $choices)
	{
		$output = array();
		$i = 0;
		foreach ($choices as $k => $v) {
			$ch = new RadioInput($name, $value, $attrs, array($k=>$v), $i);
			$output[] = "<li>".$ch->render()."</li>";
			$i++;
		}
		return "<ul class=\"radios\">\n".implode("\n", $output)."\n</ul>";
	}

	function render($name, $value, $options=array())
	{
		$options = forms_array_merge(array('attrs'=>null, 'choices'=>array()), $options);
		if (is_null($value)) {
			$value = '';
		}
		$value = (string)$value;
		$final_attrs = $this->build_attrs($options['attrs']);
		$choices = my_array_merge(array($this->choices, $options['choices']));
		return $this->_renderer($name, $value, $final_attrs, $choices);
	}

	function id_for_label($id_)
	{
		if ($id_) {
			$id_ = $id_.'_0';
		}
		return $id_;
	}
}
