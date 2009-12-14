<?php
/**
* Widgets -- HTML reprezentace formularovych poli.
*
* Kazde pole ma defaultne nastaveno, jakym zpusobem se ma 
* vykreslovat v HTML strance. Napr. CharField se generuje
* jako <input type="text" />. Defaultni nastaveni lze ale
* prebit a konkretni formularova pole mohou mit vice podob
* (napr. pole pro datum muze byt jako jedno pole pro zadani
* retezce s datumem, ale stejne tak to muze byt sada 3 selectu,
* ve kterych se datum naklika; v obou pripadech pujde ale o 
* stejne formularove pole, ktere bude vracet stejnou hodnotu
* at uz je vykresleno jakkoliv).
*/


/**
* Pomocna funkce, ktera prevadi pole parametru na HTML atributy.
* tj. 
*   array('src':'1.jpg', 'alt':'obrazek')
* na 
*   src="1.jpg" alt="obrazek"
* Hodnoty jsou escapovany, klice se neescapuji.
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
* Pomocna funkce pro spojeni poli.
*
* Narozdil od merge_array tato funkce prevadi klice na retezce
* a teprve potom pole spoji. Tj. pokud se ve 2 polich objevi
* polozka pod indexem 1 (integer), pak hodnota z druheho pole
* prepise puvodne definovanou.
* V tomto konkretnim pripade se nativni merge_array chova jinak.
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
* Bazova trida, ze ktere jsou odvozeny vsechny widgety.
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
    * Vykresli widget jako HTML prvek formulare.
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
* Zakladni trida pro vetsinu <input> widgetu.
*/
class Input extends Widget
{
    var $input_type = null; // toto musi definovat konkretni odvozene tridy

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
* <input type="text" />
*
* 2008-07-10: Horka novinka - defaultne to bude mit atribut class="text"
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
* <input type="password" />
*
* 2008-07-10: Horka novinka - defaultne to bude mit atribut class="text"
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
* <input type="hidden" />
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
* <textarea></textarea>
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
* <input type="checkbox" />
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
* <select>
*   <option value="1">jedna</option>
* </select>
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
* <select multiple="multiple">
*   <option value="1">1</option>
* </select>
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
* Pomocna trida na vykresleni jednoho radio buttonu.
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
* Vykresli radio buttony podle pole $choices jako <ul><li> seznam.
*/
class RadioSelect extends Select
{
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
* Vykresli check buttony podle pole $choices jako <ul class="checkboxes"><li> seznam.
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


// vim: set et ts=4 sw=4 enc=utf-8 fenc=utf-8 si: 
?>
