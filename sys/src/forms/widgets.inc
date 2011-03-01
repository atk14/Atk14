<?php
/**
 * Widgets -- HTML representation of form fields.
 *
 *
 * Each field has its own code that is rendered in HTML.
 * For example {@link CharField} is rendered as <input type="text" />
 *
 * The way the field is rendered can be changed.
 * For example a field for date can be rendered both as a text field and also as a select with 3 options.
 * In both cases they are the same field that return the same value whatever it is rendered.
 *
 * @package Atk14
 * @subpackage InternalLibraries
 * @filesource
 */


/**
 * Converts an array to HTML attributes.
 *
 * Small helper function.
 * Hodnoty jsou escapovany, klice se neescapuji.
 *
 * Example. This array
 * <code>
 *   array('src':'1.jpg', 'alt':'obrazek')
 * </code>
 *
 * will be converted to
 * <code>
 *   src="1.jpg" alt="obrazek"
 * </code>
 *
 * @param array $attrs
 * @return string
 */
function flatatt($attrs)
{
    $out = array();
    foreach ($attrs as $k => $v) {
        $out[] = ' '.$k.'="'.forms_htmlspecialchars($v).'"';
    }
    return implode('', $out);
}


/**
 * Merges arrays.
 *
 * Unlike {@link array_merge} my_array_merge first converts keys to integers.
 * If a key is available in two arrays (in first array as 1 (integer) and in the second array as "1" (string))
 * then the value from the second array overrides the value from the first array.
 *
 * <code>
 * $ary_data = array(
 *  array(
 *      "1" => "banana",
 *      "2" => "lemon",
 *      "3" => "orange"
 *      ),
 *  array(
 *      1 => "yellow banana",
 *      "4" => "pineapple",
 *      "5" => "apple"
 *      ));
 * $new_data = my_array_merge($ary_data);
 * </code>
 * 
 * Result:
 * <code>
 *  array(
 *      "1" => "yellow banana",
 *      "2" => "lemon",
 *      "3" => "orange",
 *      "4" => "pineapple",
 *      "5" => "apple"
 *      )
 * </code>
 *
 * @param array $data
 * @return array
 */
function my_array_merge($data)
{
    $output = array();
    foreach ($data as $item) {
        foreach ($item as $k => $v) {
            $output[(string)$k] = $v;
        }
    }
    return $output;
}


/**
 * Parent class for all widget types.
 *
 * This class shouldn't be used directly but through its descendant.
 *
 * @package Atk14
 * @subpackage InternalLibraries
 */
class Widget
{
    function Widget($options=array())
    {
        $options = forms_array_merge(array('attrs'=>null), $options);
        if (!isset($this->is_hidden)) {
            $this->is_hidden = false;
        }
        if (is_null($options['attrs'])) {
            $this->attrs = array();
        }
        else {
            $this->attrs = $options['attrs'];
        }
    }

    /**
     * Renders widget as a HTML element.
     *
     * @param string $name
     * @param mixed $value
     * @param array $attrs
     * @return string HTML code of the element
     * @abstract
     */
    function render($name, $value, $attrs)
    {
        return ''; // NOTE: Django v tomto miste generuje vyjimku (ktera vyvola tusim chybu 50x)
    }

    /**
    * Posklada pole atributu pro widget.
    */
    function build_attrs($attrs, $extra_attrs=array())
    {
        return forms_array_merge($this->attrs, $attrs, $extra_attrs);
    }

    /**
    * Vrati hodnotu widgetu.
    */
    function value_from_datadict($data, $name)
    {
        if (isset($data[$name])) {
            return $data[$name];
        }
        else {
            return null;
        }
    }

    /**
    * Vraci atribut id HTML prvku (pouziva se pro <label>).
    */
    function id_for_label($id_)
    {
        return $id_;
    }
}


/**
 * Base class for most widgets.
 *
 * Most <input> fields use this class.
 *
 * @package Atk14
 * @subpackage InternalLibraries
 */
class Input extends Widget
{
    var $input_type = null; // toto musi definovat konkretni odvozene tridy

    function Input($options = array()){
        parent::Widget($options);
    }

    function render($name, $value, $options=array())
    {
        if(is_bool($value)){ $value = (int)$value;}
        settype($value,"string");

        $options = forms_array_merge(array('attrs'=> null), $options);

        $final_attrs = $this->build_attrs(array(
            'type' => $this->input_type, 
            'name' => $name),
            $options['attrs']
        );
        if (strlen($value)>0) {
            $final_attrs['value'] = $value;
        }
        return '<input'.flatatt($final_attrs).' />';
    }
}


/**
 * Widget for text input field.
 *
 * Outputs field of this type:
 * <code>
 * <input type="text" />
 * </code>
 *
 * By default the element has attribute class set to "text"
 *
 * @package Atk14
 * @subpackage InternalLibraries
 */
class TextInput extends Input
{
    var $input_type = 'text';

    function render($name, $value, $options = array()) 
    {
        if(!isset($this->attrs["class"])){ // pokud nebylo class definovano v konstruktoru
            !isset($options["attrs"]) && ($options["attrs"] = array());
            $options["attrs"] = forms_array_merge(array(
                "class" => "text"
            ),$options["attrs"]);
        }
        return parent::render($name, $value, $options);
    }
}

/**
 * Widget for password input field.
 *
 * Outputs field of this type:
 * <code>
 * <input type="password" />
 * </code>
 *
 * By default the element has attribute class set to "text"
 *
 * @package Atk14
 * @subpackage InternalLibraries
 */
class PasswordInput extends Input
{
    var $input_type = 'password';

    function PasswordInput($options=array())
    {
        if(!isset($this->attrs["class"])){ // pokud nebylo class definovano v konstruktoru
            !isset($options["attrs"]) && ($options["attrs"] = array());
            $options["attrs"] = forms_array_merge(array(
                "class" => "text"
            ),$options["attrs"]);
        } 

        $options = forms_array_merge(array('render_value'=>true), $options);
        parent::Input($options);
        $this->render_value = $options['render_value'];
    }

    function render($name, $value, $options=array())
    {
        $options = forms_array_merge(array('attrs'=>null), $options);
        if (!$this->render_value) {
            $value = null;
        }
        return parent::render($name, $value, $options);
    }
}

/**
 * Widget for hidden input field.
 *
 * Outputs field of this type:
 * <code>
 * <input type="hidden" />
 * </code>
 *
 * @package Atk14
 * @subpackage InternalLibraries
 */
class HiddenInput extends Input
{
    var $input_type = 'hidden';
    var $is_hidden = true;
}


/**
* <input type="hidden" name="pole" />
* <input type="hidden" name="pole" />
* ...
*/
/*
class MultipleHiddenInput extends HiddenInput
{
    function MultipleHiddenInput($options=array())
    {
        $options = forms_array_merge(array('attrs'=>null, 'choices'=>array()), $options);
        parent::HiddenInput($options);
        $this->choices = $options['choices'];
    }

    function render($name, $value, $options=array())
    {
        $options = forms_array_merge(array('attrs'=>null, 'choices'=>array()), $options);
        if (is_null($value)) {
            $value = array();
        }
        $final_attrs = $this->build_attrs($options['attrs'], array(
            'name' => $name,
            'type' => $this->input_type
        ));
        $out = array();
        foreach ($value as $v) {
            $_attrs = forms_array_merge($final_attrs, array('value'=>(string)$v));
            $out[] = '<input'.flatatt($_attrs).' />' ;
        }
        return implode("\n", $out);
    }

    function value_from_datadict($data, $name)
    {
        # if isinstance(data, MultiValueDict):
        #     // NOTE: tohle prdim
        #     return data.getlist(name)
        # return data.get(name, None)
        if (isset($data[$name])) {
            return $data[$name];
        }
        return null;
    }
}
*/

/**
 * Widget for text area input field.
 *
 * Outputs field of this type:
 * <code>
 * <textarea></textarea>
 * </code>
 *
 * @package Atk14
 * @subpackage InternalLibraries
 */
class Textarea extends Widget
{
    function Textarea($options=array())
    {
        $options = forms_array_merge(array('attrs'=>null), $options);
        $this->attrs = array(
            'cols' => '40',
            'rows' => '10'
        );
        if (!is_null($options['attrs'])) {
            $this->attrs = forms_array_merge($this->attrs, $options['attrs']);
        }
    }

    function render($name, $value, $options=array())
    {
        $options = forms_array_merge(array('attrs'=>null), $options);
        if (is_null($value)) {
            $value = '';
        }
        $final_attrs = $this->build_attrs($options['attrs'], array(
            'name' => $name)
        );
        return '<textarea'.flatatt($final_attrs).'>'.forms_htmlspecialchars($value).'</textarea>';
    }
}

/**
 * Widget for checkbox input field.
 *
 * Outputs field of this type:
 * <code>
 * <input type="checkbox" />
 * </code>
 *
 * @package Atk14
 * @subpackage InternalLibraries
 */
class CheckboxInput extends Widget
{
    function CheckboxInput($options=array())
    {
        $options = forms_array_merge(array('attrs'=>null, 'check_test'=>null), $options);
        parent::Widget($options);
        $this->check_test = $options['check_test'];
    }

    function render($name, $value, $options=array())
    {
        $options = forms_array_merge(array('attrs'=>null), $options);
        $final_attrs = $this->build_attrs($options['attrs'], array(
            'type' => 'checkbox', 
            'name' => $name)
        );
        if ((!is_null($this->check_test)) && ((is_array($this->check_test) && method_exists($this->check_test[0], $this->check_test[1])) || (function_exists($this->check_test)))) {
            $fn = $this->check_test;
            $result = call_user_func($fn, $value);
        }
        else {
            $result = (bool)$value;
        }
        if ($result) {
            $final_attrs['checked'] = 'checked';
        }
        if (!(is_bool($value) || (is_string($value) && ($value == '')) || is_null($value))) {
            $final_attrs['value'] = $value;
        }
        return '<input'.flatatt($final_attrs).' />';
    }

    function value_from_datadict($data, $name)
    {
        if (!isset($data[$name])) {
            // pokud hodnota v poli chybi, vratime false
            // formulare s nezaskrnutymi checkboxy se po odeslani formiku v datech neobjevuji
            return false;
        }
        return parent::value_from_datadict($data, $name);
    }
}

/**
 * Widget for select input field.
 *
 * Outputs field of this type:
 * <code>
 * <select>
 *   <option value="1">jedna</option>
 * </select>
 * </code>
 *
 * @package Atk14
 * @subpackage InternalLibraries
 */
class Select extends Widget
{
    function Select($options=array())
    {
        $options = forms_array_merge(array('attrs'=>null, 'choices'=>array()), $options);
        parent::Widget($options);
        $this->choices = $options['choices'];
    }

    function render($name, $value, $options=array())
    {
        $options = forms_array_merge(array('attrs'=>null, 'choices'=>array()), $options);
        if (is_null($value)) {
            $value = '';
        }
        $final_attrs = $this->build_attrs($options['attrs'], array(
            'name' => $name)
        );
        $output = array('<select'.flatatt($final_attrs).'>');
        // NOTE: puvodne jsem tu mel array_merge, ale ten nejde pouzit
        // protoze se chova nehezky k indexum typu integer a string
        // ('1' a 1 jsou pro nej 2 ruzne veci a v tomto KONKRETNIM miste to vadi,
        // protoze z hlediska hodnot do formularovych prvku se integer prevadi 
        // na string
        $choices = my_array_merge(array($this->choices, $options['choices']));

        foreach ($choices as $option_value => $option_label) {
            if ((string)$option_value === (string)$value) { // yarri: tady pridavam 3. rovnitko: jinak bylo "" to same jako "0"
                $selected = ' selected="selected"';
            }
            else {
                $selected = '';
            }
            $output[] = '<option value="'.forms_htmlspecialchars($option_value).'"'.$selected.'>'.forms_htmlspecialchars($option_label).'</option>';
        }
        $output[] = '</select>';
        return implode("\n", $output);
    }
}

/**
 * Widget for multiple select input field.
 *
 * Outputs field of this type:
 * <code>
 * <select multiple="multiple">
 *   <option value="1">jedna</option>
 * </select>
 * </code>
 *
 * @package Atk14
 * @subpackage InternalLibraries
 */
class SelectMultiple extends Widget
{
    function SelectMultiple($options=array())
    {
        $options = forms_array_merge(array('attrs'=>null, 'choices'=>array()), $options);
        parent::Widget($options);
        $this->choices = $options['choices'];
    }

    function render($name, $value, $options=array())
    {
        $options = forms_array_merge(array('attrs'=>null, 'choices'=>array()), $options);
        if (is_null($value)) {
            $value = array();
        }
        $final_attrs = $this->build_attrs($options['attrs'], array(
            'name' => $name.'[]')
        );
        $output = array('<select multiple="multiple"'.flatatt($final_attrs).'>');
        $choices = my_array_merge(array($this->choices, $options['choices']));
        $str_values = my_array_merge(array($value));

        foreach ($choices as $option_value => $option_label) {
            if (in_array($option_value, $str_values)) {
                $selected = ' selected="selected"';
            }
            else {
                $selected = '';
            }
            $output[] = '<option value="'.forms_htmlspecialchars($option_value).'"'.$selected.'>'.forms_htmlspecialchars($option_label).'</option>';
        }
        $output[] = '</select>';
        return implode("\n", $output);
    }

    function value_from_datadict($data, $name)
    {
        if (isset($data[$name])) {
            return $data[$name];
        }
        return null;
    }
}

/**
 * Widget for radio button input field.
 *
 * @package Atk14
 * @subpackage InternalLibraries
 */
class RadioInput
{
    function RadioInput($name, $value, $attrs, $choice, $index)
    {
        $this->name = $name;
        $this->value = $value;
        $this->attrs = $attrs;
        $this->index = $index;
        list($this->choice_value, $this->choice_label) = each($choice);
    }

    function is_checked()
    {
        return $this->value == $this->choice_value;
    }

    function tag()
    {
        if (isset($this->attrs['id'])) {
            $this->attrs['id'] = $this->attrs['id'].'_'.$this->index;
        }
        $final_attrs = forms_array_merge($this->attrs, array(
            'type' => 'radio',
            'name' => $this->name,
            'value' => $this->choice_value
        ));
        if ($this->is_checked()) {
            $final_attrs['checked'] = 'checked';
        }
        return '<input'.flatatt($final_attrs).' />';
    }

    function render()
    {
        return '<label>'.$this->tag().' '.forms_htmlspecialchars($this->choice_label).'</label>';
    }
}

/**
 * Renders radio buttons as unordered list.
 *
 * <ul /><li />
 *
 * @package Atk14
 * @subpackage InternalLibraries
 */
class RadioSelect extends Select
{
    function RadioSelect($option = array()){
        parent::Select($option);
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

/**
 * Renders checkboxes as unordered list.
 *
 * Each value in $choices renders as <li /> item in <ul /> list.
 *
 * @package Atk14
 * @subpackage InternalLibraries
 */
class CheckboxSelectMultiple extends SelectMultiple
{
    function my_check_test($value)
    {
        return in_array($value, $this->_my_str_values);
    }

    function render($name, $value, $options=array())
    {
        $options = forms_array_merge(array('attrs'=>null, 'choices'=>array()), $options);
        if (is_null($value)) {
            $value = array();
        }
        $has_id = is_array($options['attrs']) && isset($options['attrs']['id']);
        $final_attrs = $this->build_attrs($options['attrs']);
        $output = array('<ul class="checkboxes">');
        $choices = my_array_merge(array($this->choices, $options['choices']));
        $str_values = array();
        foreach ($value as $v) {
            if (!in_array((string)$v, $str_values)) {
                $str_values[] = (string)$v;
            }
        }
        $this->_my_str_values = $str_values;

        $i = 0;
        foreach ($choices as $option_value => $option_label) {
            if ($has_id) {
                $final_attrs['id'] = $options['attrs']['id'].'_'.$i;
            }
            $cb = new CheckboxInput(array('attrs'=>$final_attrs, 'check_test'=>array($this, 'my_check_test')));
            $option_value = (string)$option_value;
            $rendered_cb = $cb->render("{$name}[]", $option_value);
            $output[] = '<li><label>'.$rendered_cb.' '.forms_htmlspecialchars($option_label).'</label></li>';
            $i++;
        }
        $output[] = '</ul>';
        return implode("\n", $output);
    }

    function id_for_label($id_)
    {
        if ($id_) {
            $id_ = $id_.'_0';
        }
        return $id_;
    }
}

/**
 * Widget for rendering file input field.
 *
 * @package Atk14
 * @subpackage InternalLibraries
 */
class FileInput extends Input{
    var $input_type = "file";

    function render($name, $value, $options=array())
    {
        // zde je $value objekt tridy HTTPUploadedFile -> pro rendering z toho udelame prazdny string
        return parent::render($name, "", $options);
    }
    function value_from_datadict($data, $name)
    {
        global $_FILES;
        if(!isset($_FILES[$name])){ return null; }
        return HTTPUploadedFile::GetInstance($_FILES[$name],$name);
    }
}


// vim: set et ts=4 sw=4 enc=utf-8 fenc=utf-8 si: 
?>
