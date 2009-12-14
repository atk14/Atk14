<?php
/**
* Fields -- jednotlive polozky formulare.
*/

require_once(dirname(__FILE__).'/widgets.php');
//require_once(dirname(__FILE__).'/../functions.inc');

/**
* Objekt pro sber pravidel pro JS validator a jedno pole.
*/
class JsValidator{
    function JsValidator(){
        $this->_messages = array();
        $this->_rules = array();
    }

    function get_messages(){ return $this->_messages; }
    function add_message($key,$message){ $this->_messages[$key] = $message; }

    function get_rules(){ return $this->_rules; }
    function add_rule($rule,$value){ $this->_rules[$rule] = $value; }

    function set_field_name($name){
        $this->_field_name = $name;
    }
}


/**
* Zakladni trida, ze ktere se odvozuji vsechny ostatni.
*/
class Field
{
    function Field($options=array())
    {
        // default data
        $options = forms_array_merge(array(
                'required'       => true, 
                'widget'         => null, 
                'label'          => null, 
                'initial'        => null,
                'help_text'      => '', // like "In this field you can write down your favourite numbers"
                'hint'           => '', // value format hint, like "1,3,7"
                'error_messages' => null,
                'disabled'       => false,
            ),
            $options
        );
        if (!isset($this->widget)) {
            $this->widget = new TextInput();
        }
        if (!isset($this->hidden_widget)) {
            $this->hidden_widget = new HiddenInput();
        }
        $this->messages = array();
        $this->update_messages(array(
            'required' => _('This field is required.'),
            'invalid' => _('Enter a valid value.'),
        ));
        
        // inicializace podle parametru konstruktoru
        $this->required = $options['required'];
        $this->label = $options['label'];
        $this->initial = $options['initial'];
        $this->help_text = $options['help_text'];
        $this->hint = $options['hint'];
        $this->disabled = $options['disabled'];
        if (is_null($options['widget'])) {
            $widget = $this->widget;
        } else {
            $widget = $options['widget'];
        }
        $extra_attrs = $this->widget_attrs($widget);
        if (count($extra_attrs) > 0) {
            $widget->attrs = forms_array_merge($widget->attrs, $extra_attrs);
        }
        $this->widget = $widget;
    }
    
    /**
    * Prida do $this->messages dalsi error hlasky.
    * NOTE: muj vymysl
    */
    function update_messages($messages)
    {
        $this->messages = forms_array_merge(
            $this->messages,
            $messages
        );
    }

    /**
    * Validace formularoveho pole.
    */
    function clean($value)
    {
        if ($this->required && $this->check_empty_value($value)) {
            return array($this->messages['required'], null);
        }
        return array(null, $value);
    }

    function widget_attrs($widget)
    {
        return array();
    }

    /**
    * Pomocna funkce, zjisti jestli je zadana hodnota "prazdna"
    * (null, prazdny retezec '', prazdne pole).
    */
    function check_empty_value($value) {
        return 
            is_null($value) ||
            (is_string($value) && $value=='') ||
            (is_array($value) && sizeof($value)==0);
    }

    function format_initial_data($data){
        return $data;
    }

    function js_validator(){
        $js_validator = new JsValidator();

        if($this->required){
            $js_validator->add_rule("required",true);
            $js_validator->add_message("required",$this->messages["required"]);
        }

        return $js_validator;
    }
}


/**
* Pole pro retezec.
*/
class CharField extends Field
{
    function CharField($options=array())
    {
        $options = forms_array_merge(array(
                'max_length' => null,
                'min_length' => null,
                'trim_value' => true,
                'null_empty_output' => false
            ),
            $options
        );
        $this->max_length = $options['max_length'];
        $this->min_length = $options['min_length'];
        parent::Field($options);
        $this->update_messages(array(
            'max_length' => _('Ensure this value has at most %max% characters (it has %length%).'),
            'min_length' => _('Ensure this value has at least %min% characters (it has %length%).'),
            'js_validator_maxlength' => _('Ensure this value has at most %max% characters.'),
            'js_validator_minlength' => _('Ensure this value has at least %min% characters.'),
            'js_validator_rangelength' => _('Ensure this value has between %min% and %max% characters.'),
        ));

        $this->trim_value = $options['trim_value'];
        $this->null_empty_output = $options['null_empty_output'];
    }

    function clean($value)
    {
        if (is_array($value)) {
            $value = var_export($value, true);
        }
        $this->trim_value && ($value = trim($value)); // Char by se mel defaultne trimnout; pridal yarri 2008-06-25

        list($error, $value) = parent::clean($value);
        if (!is_null($error)) {
            return array($error, null);
        }

        if ($this->check_empty_value($value)) {
            $value = $this->null_empty_output ? null : '';
            return array(null, $value);
        }

        $value_length = strlen($value);
        if ((!is_null($this->max_length)) && ($value_length > $this->max_length)) {
            return array(EasyReplace($this->messages['max_length'], array('%max%'=>$this->max_length, '%length%'=>$value_length)), null);
        }
        if ((!is_null($this->min_length)) && ($value_length < $this->min_length)) {
            return array(EasyReplace($this->messages['min_length'], array('%min%'=>$this->min_length, '%length%'=>$value_length)), null);
        }
        return array(null, (string)$value);
    }

    function widget_attrs($widget)
    {
        if (!is_null($this->max_length) && in_array(strtolower(get_class($widget)), array('textinput', 'passwordinput'))) {
            return array('maxlength' => (string)$this->max_length);
        }
    }

    function js_validator(){
        $js_validator = parent::js_validator();

        if(isset($this->min_length) && ($this->max_length)){
            $js_validator->add_rule("rangelength",array($this->min_length,$this->max_length));
            $js_validator->add_message("rangelength",strtr($this->messages["js_validator_rangelength"],array("%min%" => $this->min_length,"%max%" => $this->max_length)));
        }elseif(isset($this->min_length)){
            $js_validator->add_rule("minlength",$this->min_length);
            $js_validator->add_message("minlength",str_replace("%min%",$this->min_length,$this->messages["js_validator_minlength"]));
        }elseif(isset($this->max_length)){
            $js_validator->add_rule("maxlength",$this->max_length);
            $js_validator->add_message("maxlength",str_replace("%max%",$this->max_length,$this->messages["js_validator_maxlength"]));
        }

        return $js_validator;
    }
}


/**
* Pole pro integer.
*/
class IntegerField extends Field
{
    function IntegerField($options=array())
    {
        $options = forms_array_merge(array(
                'max_value' => null,
                'min_value' => null,
            ),
            $options
        );
        $this->max_value = $options['max_value'];
        $this->min_value = $options['min_value'];
        parent::Field($options);
        $this->update_messages(array(
            'invalid' => _('Enter a whole number.'),
            'max_value' => _('Ensure this value is less than or equal to %value%.'),
            'min_value' => _('Ensure this value is greater than or equal to %value%.'),
        ));
    }

    function clean($value)
    {
        list($error, $value) = parent::clean($value);
        if (!is_null($error)) {
            return array($error, $value);
        }
        if ($this->check_empty_value($value)) {
            return array(null, null);
        }

        $value = trim((string)$value);
        if (!preg_match("/^(0|[+-]?[1-9][0-9]*)$/",$value)) {
            return array($this->messages['invalid'], null);
        }
        $value = (int)$value;

        if ((!is_null($this->max_value)) && ($value > $this->max_value)) {
            return array(EasyReplace($this->messages['max_value'], array('%value%'=>$this->max_value)), null);
        }
        if ((!is_null($this->min_value)) && ($value < $this->min_value)) {
            return array(EasyReplace($this->messages['min_value'], array('%value%'=>$this->min_value)), null);
        }
        return array(null, $value);
    }
}


/**
* Pole pro float.
*/
class FloatField extends Field
{
    function FloatField($options=array())
    {
        $options = forms_array_merge(array(
                'max_value' => null,
                'min_value' => null,
            ),
            $options
        );
        $this->max_value = $options['max_value'];
        $this->min_value = $options['min_value'];
        parent::Field($options);
        $this->update_messages(array(
            'invalid' => _('Enter a number.'),
            'max_value' => _('Ensure this value is less than or equal to %value%.'),
            'min_value' => _('Ensure this value is greater than or equal to %value%.'),
        ));
    }

    function clean($value)
    {
        list($error, $value) = parent::clean($value);
        if (!is_null($error)) {
            return array($error, $value);
        }
        if (!$this->required && $this->check_empty_value($value)) {
            return array(null, null);
        }

        $value = trim((string)$value);
        if (!is_numeric($value)) {
            return array($this->messages['invalid'], null);
        }
        $value = (float)$value;

        if ((!is_null($this->max_value)) && ($value > $this->max_value)) {
            return array(EasyReplace($this->messages['max_value'], array('%value%'=>$this->max_value)), null);
        }
        if ((!is_null($this->min_value)) && ($value < $this->min_value)) {
            return array(EasyReplace($this->messages['min_value'], array('%value%'=>$this->min_value)), null);
        }
        return array(null, $value);
    }
}


/**
* Pole pro logickou hodnotu.
*/
class BooleanField extends Field
{
    function BooleanField($options=array())
    {
        parent::Field($options);
        $this->widget = new CheckboxInput();
    }

    function clean($value)
    {
        list($error, $value) = parent::clean($value);
        if (!is_null($error)) {
            return array($error, $value);
        }
        if ($value === 'false') {
            return array(null, false);
        }
        return array(null, (bool)$value);
    }
}


/**
* Retezcove pole (CharField), ktere musi splnit regexp podminku.
*/
class RegexField extends CharField
{
    function RegexField($regex, $options=array())
    {
        parent::CharField($options);
        $this->update_messages(array(
            'max_length' => _('Ensure this value has at most %max% characters (it has %length%).'),
            'min_length' => _('Ensure this value has at least %min% characters (it has %length%).'),
        ));
        if (isset($options['error_message'])) {
            $this->update_messages(array(
                'invalid' => $options['error_message']
            ));
        }
        $this->regex = $regex;
    }

    function clean($value)
    {
        list($error, $value) = parent::clean($value);
        if (!is_null($error)) {
            return array($error, null);
        }
        if ($value == '') {
            return array(null, $value);
        }
        if (!preg_match($this->regex, $value)) {
            return array($this->messages['invalid'], null);
        }
        return array(null, (string)$value);
    }
}


/**
* Pole pro zadani emailu.
*/
class EmailField extends RegexField
{
    function EmailField($options=array())
    {
        $options = array_merge(array(   
            "null_empty_output" => true,
        ),$options);
        // NOTE: email_pattern je v Djangu slozen ze tri casti: dot-atom, quoted-string, domain
        $email_pattern = "/(^[-!#$%&'*+\\/=?^_`{}|~0-9A-Z]+(\.[-!#$%&'*+\\/=?^_`{}|~0-9A-Z]+)*".'|^"([\001-\010\013\014\016-\037!#-\[\]-\177]|\\[\001-011\013\014\016-\177])*"'.')@(?:[A-Z0-9-]+\.)+[A-Z]{2,6}$/i';
        parent::RegexField($email_pattern, $options);
        $this->update_messages(array(
            'invalid' => _('Enter a valid e-mail address.'),
        ));
    }

    function clean($value)
    {
        list($error, $value) = parent::clean($value);
        if (!is_null($error)) {
            return array($error, null);
        }
        if ($value == '') {
            return array(null, $value);
        }
        if (!preg_match($this->regex, $value)) {
            return array($this->messages['invalid'], null);
        }
        return array(null, (string)$value);
    }

    function js_validator(){
        $js_validator = parent::js_validator();
        $js_validator->add_rule("email",true);
        $js_validator->add_message("email",$this->messages["invalid"]);
        return $js_validator;
    }
}


/**
* Pole voleb.
*/
class ChoiceField extends Field
{
    var $choices = array();

    function ChoiceField($options=array())
    {
        if (!isset($this->widget)) {
            $this->widget = new Select();
        }
        parent::Field($options);
        $this->update_messages(array(
            'invalid_choice' => _('Select a valid choice. That choice is not one of the available choices.'),
            'required' => _('Please, choose the right option.'),
        ));
        if (isset($options['choices'])) {
            $this->set_choices($options['choices']);
        }
    }

    /**
    * Vrati seznam voleb.
    *
    * NOTE: V djangu zrealizovano pomoci property.
    */
    function get_choices()
    {
        return $this->choices;
    }

    /**
    * Nastavi seznam voleb.
    *
    * NOTE: V djangu zrealizovano pomoci property (v pripade nastaveni
    * saha i na widget)
    */
    function set_choices($value)
    {
        $this->choices = $value;
        $this->widget->choices = $value;
    }

    function clean($value)
    {
        list($error, $value) = parent::clean($value);
        if (!is_null($error)) {
            return array($error, null);
        }
        if ($this->check_empty_value($value)) {
            $value = '';
        }
        if ($value === '') {
            return array(null, null);
            //return array(null, $value);
        }
        $value = (string)$value;
        // zkontrolujeme, jestli je zadana hodnota v poli ocekavanych hodnot
        $found = false;
        foreach ($this->get_choices() as $k => $v) {
            if ((string)$k === $value) {
                $found = true;
                break;
            }
        }
        if (!$found) {
            // neni!
            return array($this->messages['invalid_choice'], null);
        }
        return array(null, (string)$value);
    }
}

class DateField extends CharField
{
    function DateField($options=array())
    {
        $options = array_merge(array(
            "null_empty_output" => true
        ),$options);
        parent::CharField($options);
        $this->update_messages(array(
            'invalid' => _('Enter a valid date.'),
        ));
        $this->_format_function = "FormatDate";
        $this->_parse_function = "ParseDate";
    }

    function clean($value)
    {
        list($error, $value) = parent::clean($value);
        if (!is_null($error)) {
            return array($error, null);
        }
        if ($value == '') {
            return array(null, $value);
        }
        eval('$value = Atk14Locale::'.$this->_parse_function.'($value);');
        if(!$value){
            return array($this->messages['invalid'], null);
        }
        return array(null, $value);
    }

    function format_initial_data($data)
    {
        eval('$out = Atk14Locale::'.$this->_format_function.'($data);');
        return $out;
    }
}

class DateTimeField extends DateField
{
    function DateTimeField($options=array())
    {
        parent::DateField($options);
        $this->update_messages(array(
            'invalid' => _('Enter a valid date, hours and minutes.')
        ));
        $this->_format_function = "FormatDateTime";
        $this->_parse_function = "ParseDateTime";
    }
}

class DateTimeWithSecondsField extends DateField
{
    function DateTimeWithSecondsField($options=array())
    {
        parent::DateField($options);
        $this->update_messages(array(
            'invalid' => _('Enter a valid date, hours, minutes and seconds.')
        ));
        $this->_format_function = "FormatDateTimeWithSeconds";
        $this->_parse_function = "ParseDateTimeWithSeconds";
    }
}


/**
* Moznost volby nekolika polozek.
*
* NOTE: tohle asi v PHP nebude fachat, protoze pokud se ve formulari objevi vice
* poli sdilejici stejny nazev, v $_POST se objevi pouze jeden z nich (posledni)
*
* NOTE: v PHP to funguje, pokude se parametr ve formulare nazve takto: <select name="choices[]" multiple="multiple">... (yarri)
*/
class MultipleChoiceField extends ChoiceField
{
    function MultipleChoiceField($options=array())
    {
        //$this->hidden_widget = new MultipleHiddenInput(); // yarri: co to je MultipleHiddenInput()?
        if (isset($options['widget'])) {
            $this->widget = $options['widget'];
        }
        else {
            $this->widget = new SelectMultiple();
        }
        parent::ChoiceField($options);
        $this->update_messages(array(
            'invalid_choice' => _('Select a valid choice. %(value)s is not one of the available choices.'),
            'invalid_list' => _('Enter a list of values.'),
        ));
    }

    function clean($value)
    {
        if ($this->required && !$value) {
            return array($this->messages['required'], null);
        }
        elseif (!$this->required && !$value) {
            return array(null, array());
        }
        if (!is_array($value)) {
            return array($this->messages['invalid_list'], null);
        }

        $new_value = array();
        foreach ($value as $k => $val) {
            $new_value[$k] = (string)$val;
        }
        $valid_values = array();
        foreach ($this->get_choices() as $k => $v) {
            if (!in_array((string)$k, $valid_values)) {
                $valid_values[] = (string)$k;
            }
        }

        foreach ($new_value as $val) {
            if (!in_array($val, $valid_values)) {
                return array(EasyReplace($this->messages['invalid_choice'], array('%(value)s'=>$val)), null);
            }
        }
        return array(null, $new_value);
    }
}

class IPAddressField extends RegexField
{
    function IPAddressField($options = array()){
        $options = array_merge(array(   
            "null_empty_output" => true,
            "ipv4_only" => false, 
            "ipv6_only" => false,
        ),$options);
        $re_ipv4 = '(25[0-5]|2[0-4]\d|[0-1]?\d?\d)(\.(25[0-5]|2[0-4]\d|[0-1]?\d?\d)){3}';
        $re_ipv6 = '[0-9a-fA-F]{0,4}(:[0-9a-fA-F]{0,4}){1,8}'; // TODO: velmi nedokonale!
        $re_exp = "/^(($re_ipv4)|($re_ipv6))$/";
        $options["ipv4_only"] && ($re_exp = "/^$re_ipv4$/");
        $options["ipv6_only"] && ($re_exp = "/^$re_ipv6$/");
        parent::RegexField($re_exp,$options);
        $this->update_messages(array(
            "invalid" => _("Enter a valid IP address."),
        ));
    }
    function clean($value){
        return parent::clean($value);
    }
}

// vim: set et ts=4 sw=4 enc=utf-8 fenc=utf-8 si: 
?>
