<?php
/**
* Formulare.
*/

require_once(dirname(__FILE__).'/fields.php');
//require_once(dirname(__FILE__).'/../functions.inc');


define('NON_FIELD_ERRORS', '__all__');

class JsFormValidator{
    function JsFormValidator(&$form){
        $this->validators = array();
        $this->_fields_html_names = array();
        foreach($form->fields as $name => $field){
            $this->validators[$name] = $field->js_validator();
            $bf = new BoundField($form, $field, $name);
            $this->_fields_html_names[$name] = $bf->html_name;
        }
    }

    function get_rules(){
        $out = array();
        foreach($this->validators as $name => $validator){
            if($rules = $validator->get_rules()){
                $key = $this->_fields_html_names[$name];
                $out[$key] = $rules;
            }
        }
        return $out;
    }

    function get_messages(){
        $out = array();
        foreach($this->validators as $name => $validator){
            if($msgs = $validator->get_messages()){
                $key = $this->_fields_html_names[$name];
                $out[$key] = $msgs;
            }
        }
        return $out;
    }
}

/**
* Converts 'first_name' to 'First name'.
*/
function pretty_name($name)
{
    return str_replace('_', ' ', ucfirst($name));
}

/**
* Funkce pro escapovani html.
*/
function forms_htmlspecialchars($string)
{
    return htmlspecialchars($string, ENT_COMPAT);

    // puvodne tady byla tato verze.
    // to ale zlobilo na webech, ktere nebezi v UTF-8...
    //return htmlspecialchars($string, ENT_COMPAT, 'UTF-8');
}

/**
* Nahrada za array_merge.
* V PHP5 se totiz u array_merge zmenilo chovani a prijima vyhradne parametry typu array.
*/
function forms_array_merge ()
{
	$merged=array ();
	for($i=0;$i<func_num_args ();$i++)
	{
		$val = func_get_arg($i);
		if(!isset($val)){ continue; }
		$tmp= is_array($val) ?  $val : array($val);
		$merged=array_merge ($merged,$tmp);
	}
	return $merged;
}

/**
* Funkce pro prevod pole s error hlaskami na HTML strukturu
* vhodnou k zobrazeni v sablonach.
*/
function format_errors($errors)
{
    if (count($errors) < 1) {
        return '';
    }
    $output = array();
    foreach ($errors as $e) {
        $output[] = '<li>'.$e.'</li>';
    }
    return '<ul class="errorlist">'.implode('', $output).'</ul>';
}


/**
* Bound field je trida pro formularove pole (field), do ktereho 
* jsou nalite data.
*/
class BoundField
{
    function BoundField($form, $field, $name)
    {
        $this->form = $form;
        $this->field = $field;
        $this->name = $name;
        $this->html_name = $form->add_prefix($name);
        if (is_null($this->field->label)) {
            $this->label = pretty_name($name);
        }
        else {
            $this->label = $this->field->label;
        }
        $this->help_text = $field->help_text;
        $this->hint = $field->hint;
        $this->required = $this->field->required;
        $this->disabled = $this->field->disabled;
    }

    /**
    * NOTE: v djangu realizovano jako property na cteni 'errors'
    */
    function errors()
    {
        if (isset($this->form->errors[$this->name])) {
            return $this->form->errors[$this->name];
        }
        else {
            return array();
        }
    }

    /**
    * Vykresli pole (field), budto s pomoci zadaneho widgetu,
    * nebo s pomoci defaultniho widgetu definovaneho u field.
    */
    function as_widget($options=array())
    {
        $options = forms_array_merge(
            array(
                'widget' => null, 
                'attrs'  => null
            ),
            $options
        );
        if (!$options['widget']) {
            $widget = $this->field->widget;
        }
        else {
            $widget = $options['widget'];
        }
        if ($options['attrs']) {
            $attrs = $options['attrs'];
        }
        else {
            $attrs = array();
        }
        $auto_id = $this->auto_id();

        if ($auto_id && !isset($attrs['id']) && !isset($widget->attrs['id'])) {
            $attrs['id'] = $auto_id;
        }
        if($this->field->disabled){
            $attrs['disabled'] = 'disabled';
        }
        if (!$this->form->is_bound || $this->field->disabled) {
            if (isset($this->form->initial[$this->name])) {
                $data = $this->field->format_initial_data($this->form->initial[$this->name]);
            }
            else {
                $data = $this->field->format_initial_data($this->field->initial);
            }
        }
        else {
            $data = $this->data();
        }

        return $widget->render($this->html_name, $data, array('attrs'=>$attrs));
    }

    /**
    * Vrati pole jako <input type="text" />.
    */
    function as_text($attrs=null)
    {
        return $this->as_widget(array(
            'widget' => new TextInput(),
            'attrs' => $attrs
        ));
    }

    /**
    * Vrati pole jako <textarea></textarea>.
    */
    function as_textarea($attrs=null)
    {
        return $this->as_widget(array(
            'widget' => new Textarea(),
            'attrs' => $attrs
        ));
    }

    /**
    * Vrati pole jako <input type="hidden" />.
    */
    function as_hidden($attrs=null)
    {
        return $this->as_widget(array(
            'widget' => $this->field->hidden_widget,
            'attrs' => $attrs
        ));
    }

    /**
    * NOTE: pri cteni property 'data' se v Djangu vola nasledujici funkce
    */
    function data()
    {
        return $this->field->widget->value_from_datadict(
            $this->form->data,
            $this->html_name
        );
    }

    /**
    * Pokud ma field definovan atribut 'id', obali se zadany 
    * 'contents' v tagu <label> (na content se nevola htmlspecialchars).
    * Pokud 'content' zadan neni, pouzije se hodnota z $field->label
    * (escapeovana s pomoci htmlspecialchars).
    *
    * Pokud jsou v $options definovany atributy 'attrs', nalijou se
    * do tagu <label>.
    */
    function label_tag($options=array())
    {
        $options = forms_array_merge(
            array(
                'contents' => null, 
                'attrs'  => null
            ),
            $options
        );

        if ($options['contents']) {
            $contents = $options['contents'];
        }
        else {
            $contents = forms_htmlspecialchars($this->label);
        }
        if ($id_ = $this->get_id()) {
            if (is_array($options) && (count($options['attrs']) > 0)) {
                $attrs = flatatt($options['attrs']);
            }
            else {
                $attrs = '';
            }
            $widget = $this->field->widget;
            $contents = '<label for="'.$widget->id_for_label($id_).'"'.$attrs.'>'.$contents.'</label>';
        }
        return $contents;
    }

    function get_id()
    {
        $widget = $this->field->widget;
        if (isset($widget->attrs['id'])) {
             $id_ = $widget->attrs['id'];
        } else {
             $id_ = $this->auto_id();
        }
        return $id_;
    }

    function id_for_label(){ return $this->field->widget->id_for_label($this->get_id()); }

    /**
    * Vrati true, pokud je pouzity widget HiddenInput.
    * NOTE: tohle je Djangu property
    */
    function is_hidden()
    {
        return $this->field->widget->is_hidden;
    }

    /**
    * Pokud ma formular definovan parametr auto_id, vrati tato metoda
    * vypocitany atribut ID pro formularove pole.
    * NOTE: tohle je Djangu property
    */
    function auto_id()
    {
        $auto_id = $this->form->auto_id;
        if (strpos($auto_id, '%s') !== false) {
            return str_replace('%s', $this->html_name, $auto_id);
        }
        elseif ($auto_id) {
            return $this->html_name;
        }
        return '';
    }

    /**
    * Vrati initial hodnotu tohoto pole.
    */
    function get_initial()
    {
       if(isset($this->form->initial[$this->name])){ return $this->form->initial[$this->name]; }
       if(isset($this->field->initial)){ return $this->field->initial; }
       return null;
    }
}


/**
* Bazova formularova trida, ze ktere se odvozuji veskere formulare.
*/
class Form
{
    var $prefix = null;

    function Form($options=array())
    {
        $options = forms_array_merge(
            array(
                'data'          => null, 
                'auto_id'       => 'id_%s',
                'prefix'        => null, 
                'initial'       => null, 
                'label_suffix'  => ':',
                'call_set_up'   => true,
            ),
            $options
        );
        $this->is_bound = !is_null($options['data']);
        if (is_array($options['data'])) {
            $this->data = $options['data']; 
        }
        else {
            $this->data = array();
        }
        $this->auto_id = $options['auto_id'];
        if(isset($options["prefix"])){ $this->prefix = $options['prefix']; }
        if (is_array($options['initial'])) {
            $this->initial = $options['initial'];
        }
        else {
            $this->initial = array();
        }
        $this->label_suffix = $options['label_suffix'];
        $this->errors = null;
        $this->fields = array();
        $options["call_set_up"] && $this->set_up();
    }

    /**
    * Metoda pres kterou instance definuji formularova pole
    * (tj. instance tuto metodu musi pretizit).
    * NOTE: muj vymysl
    */
    function set_up()
    {
    }

    /**
    * Pokud je potreba nastavit prefix jindy nez v kontstruktoru....
    */
    function set_prefix($prefix)
    {
        $this->prefix = $prefix;
    }

    /**
    * Pomocna metoda pro definici jedne polozky formulare.
    * NOTE: muj vymysl
    */
    function add_field($name, $field)
    {
        $this->fields[$name] = $field;
    }

    /**
    * Vrati zadanou formularovou polozku nebo null.
    * NOTE: Michaluv vymysl
    */
    function get_field($name)
    {
        if (isset($this->fields[$name])) {
            return new BoundField($this, $this->fields[$name], $name);
        }
        else {
            return null;
        }
    }

    /**
    * Vrati jmena formularovych poli.
    * NOTE: Michaluv vymysl
    */
    function get_field_keys()
    {
        return array_keys($this->fields);
    }

    /**
    * TODO: Chci to volat takto:
    *   $form->list_fields("address_*");
    */
    function list_fields($wildcart = ""){
        $out = $this->get_field_keys();
        return $out;
    }

    /**
    * Vrati initial hodnotu pole daneho jmena.
    */
    function get_initial($name)
    {
        if(isset($this->initial[$name])){ return $this->initial[$name]; }
        return $this->fields[$name]->initial;
    }

    /**
    * Vrati pole s error hlaskami pro cely formular.
    * NOTE: muj vymysl, Django to ma zrealizovane pres property
    */
    function get_errors()
    {
        if (is_null($this->errors)) {
            $this->full_clean();
        }
        return $this->errors;
    }

    /**
    * Vrati true pokud formular neobsahuje zadnou chybu, jinak false.
    */
    function is_valid()
    {
        $errors = $this->get_errors();
        return ($this->is_bound && (count($errors) < 1));
    }

    /**
    * Vrati true, pokud je na danem poli chyba.
    * if($form->error_on("email")){
    *   echo "mate chybu na policku E-mail";
    * }
    */
    function error_on($field_name)
    {
        $errors = $this->get_errors();
        return ($this->is_bound && isset($errors[$field_name]) && sizeof($errors[$field_name])>0);
    }

    /**
    * Vrati jmeno policka a prefixem, pokud ma formular parametr $prefix nastaven.
    */
    function add_prefix($field_name)
    {
        if ($this->prefix) {
            return $this->prefix.'_'.$field_name;
        }
        else {
            return $field_name;
        }
    }

    /**
    * Disabluje pole podle seznamu.
    *
    * $form->disable_fields(array(
    *   "firstname",
    *   "lastname"
    * ));
    */
    function disable_fields($names){
        foreach($names as $name){
            $this->fields[$name]->disabled = true;
        }
    }

    /**
    * Pomocna funkce, ktera generuje HTML podobu formulare.
    */
    function _html_output($normal_row, $error_row, $row_ender, $help_text_html, $errors_on_separate_row)
    {
        $top_errors = $this->non_field_errors();
        $output = array();
        $hidden_fields = array();

        foreach ($this->fields as $name => $field) {
            $bf = new BoundField($this, $field, $name);
            $bf_errors = array();
            foreach ($bf->errors() as $_bf) {
                $bf_errors[] = forms_htmlspecialchars($_bf);
            }
            if ($bf->is_hidden()) {
                if ($bf_errors) {
                    foreach ($bf_errors as $e) {
                        $top_errors[] = '(Hidden field '.$name.') '.$e;
                    }
                }
                $hidden_fields[] = $bf->as_widget();
            }
            else {
                if ($errors_on_separate_row && $bf_errors) {
                    $output[] = str_replace('%s', format_errors($bf_errors), $error_row);
                }
                if ($bf->label) {
                    $label = forms_htmlspecialchars($bf->label);
                    if ($this->label_suffix) {
                        if (!in_array($label[strlen($label)-1], array(':', '?', '.', '!'))) {
                            $label .= $this->label_suffix;
                        }
                    }
                    $label = $bf->label_tag(array('contents'=>$label));
                    if (!$label) {
                        $label = '';
                    }
                }
                else {
                    $label = '';
                }
                if ($field->help_text) {
                    $help_text = str_replace('%s', $field->help_text, $help_text_html);
                }
                else {
                    $help_text = '';
                }
                if (is_array($bf_errors)) {
                    $bf_errors = format_errors($bf_errors);
                }
                $output[] = EasyReplace(
                    $normal_row, 
                    array(
                        '%(label)s' => $label,
                        '%(errors)s' => $bf_errors,
                        '%(field)s' => $bf->as_widget(),
                        '%(help_text)s' => $help_text,
                    )
                );
            }
        }
        if (count($top_errors) > 0) {
            $output = forms_array_merge(str_replace('%s', format_errors($top_errors), $error_row), $output); 
        }
        // Insert any hidden fields in the last row.
        if ($hidden_fields) { 
            $str_hidden = implode('', $hidden_fields);
            if ($output) {
                $last_row = $output[count($output)-1];
                // Chop off the trailing row_ender (e.g. '</td></tr>') and
                // insert the hidden fields.
                $output[count($output)-1] = substr($last_row, 0, strlen($last_row)-strlen($row_ender)).$str_hidden.$row_ender;
            }
            else {
                // If there aren't any rows in the output, just append the
                // hidden fields.
                $output[] = $str_hidden;
            }
        }
        return implode("\n", $output);
    }

    /**
    * Generuje formular jako HTML tabulku.
    */
    function as_table()
    {
        return $this->_html_output(
            '<tr><th>%(label)s</th><td>%(errors)s%(field)s%(help_text)s</td></tr>',
            '<tr><td colspan="2">%s</td></tr>',
            '</td></tr>',
            '<br />%s',
            False
        );
    }

    /**
    * Generuje formular jako HTML <ul><li> seznam.
    */
    function as_ul()
    {
        return $this->_html_output(
            '<li>%(errors)s%(label)s %(field)s%(help_text)s</li>',
            '<li>%s</li>',
            '</li>',
            ' %s',
            False
        );
    }

    /**
    * Generuje formular jako HTML odstavce <p>.
    */
    function as_p()
    {
        return $this->_html_output(
            '<p>%(label)s %(field)s%(help_text)s</p>',
            '%s',
            '</p>',
            ' %s',
            True
        );
    }

    /**
    * Vrati chyby, ktere nejsou primo spojene s nejakym konkretnim polickem, 
    * ale s formularem jako celkem.
    */
    function non_field_errors()
    {
        $errors = $this->get_errors();
        if (isset($errors[NON_FIELD_ERRORS])) {
            return $errors[NON_FIELD_ERRORS];
        }
        else {
            return array();
        }
    }

    /**
    * Zkontroluje vsechny prvky formulare a vytvori z nich pole $this->errors a 
    * $this->cleaned_data.
    */
    function full_clean()
    {
        $this->errors = array();
        if (!$this->is_bound) {
            return;
        }
        $this->cleaned_data = array();
        // pro kazde policko formulare zavolame clean metody
        foreach (array_keys($this->fields) as $name) {
            $field = &$this->fields[$name];
            // pokud je toto policko disablovane, bude v cleaned_data vracena initial hodnota
            // podle nebude validovano...
            if($field->disabled){
                $this->cleaned_data[$name] = $this->get_initial($name);
                continue;
            }
            $value = $field->widget->value_from_datadict(
                $this->data, 
                $this->add_prefix($name)
            );

            list($error, $value) = $field->clean($value);
            if (is_null($error)) {
                $this->cleaned_data[$name] = $value;
                if (method_exists($this, 'clean_'.$name)) {
                    list($error, $value) = call_user_func(array($this, 'clean_'.$name));
                    if (is_null($error)) {
                        $this->cleaned_data[$name] = $value;
                    }
                }
            }
            if (!is_null($error)) {
                if (isset($this->errors[$name])) {
                    $this->errors[$name][] = $error;
                }
                else {
                    $this->errors[$name] = array($error);
                }
                if (isset($this->cleaned_data[$name])) {
                    unset($this->cleaned_data[$name]);
                }
            }
        }
        // globalni clean pro cely formular
        list($error, $this->cleaned_data) = $this->clean();
        if (!is_null($error)) {
            if (isset($this->errors[NON_FIELD_ERRORS])) {
                $this->errors[NON_FIELD_ERRORS][] = $error;
            }
            else {
                $this->errors[NON_FIELD_ERRORS] = array($error);
            }
        }
        // pokud se vyskytla nejaka chyba, pak musime dat pryc cele pole cleaned_data
        if (count($this->errors) > 0) {
            unset($this->cleaned_data);
        }
    }

    /**
    * Kontrola kompletniho formulare. Vola se az potom, co se zavolala metoda clean
    * pro kazde policko formulare.
    * Toto je hook pro situace, kdy je treba proverit formular jako celek.
    *
    * Vola se i v pripade, kdy jsou nektera pole invalidni.
    */
    function clean()
    {
        return array(null, $this->cleaned_data);
    }

    function js_validator(){
        return new JsFormValidator($this);
    }
}

// vim: set et ts=4 sw=4 enc=utf-8 fenc=utf-8 si: 
?>
