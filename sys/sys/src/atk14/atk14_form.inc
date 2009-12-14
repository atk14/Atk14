<?
/**
* Trida rozsiruje zakladni tridu Form z formularoveho frameworku.
*
* Atk14 rozsiruje moznosti puvodni tridy o:
*   - schopnost nastavit vychozi parametry formularovych elementu pozdeji nez v konstruktoru
*   - schopnost nastavit odeslana uzivatelova data ($_POST) pozde nez v konstruktoru
*   - moznost jednoduse doplnit hidden hodnoty
*   - moznost nastavit action formulare
*   - nabizi metody begin() a end() pro vykreslovani formulare do sablony
*   - moznost zmenit method volanim set_method()
*
* 
*
* Pouziti:
*   class LoginForm extends Atk14Form{
*       function set_up(){
*			$this->add_field("login",new CharField(array(
*				"label" => _("Login"),
*				"help_text" => _("Identifikator platce, dealera nebo kontaktu"),
*				"min_length" => 1,
*				"max_length" => 64
*			)));
*	
*			$this->add_field("password",new CharField(array(
*				"label" => _("Heslo"),
*				"min_length" => 1,
*				"max_length" => 64
*			)));
*       }
*   }
*
*   $form = new LoginForm();
*   $form->set_initial("login","prihlasovaci.jmeno");
*   $form->set_hidden_field("action","prihlaseni");
*   if($request->Post() && $form->is_valid($this->params)){
*       // data jsou ok
*       $data = $form->cleaned_data;
*   }
*
*   //ve smarty sablone potom
*   {$form->get_error_report()} // vytiskne sumarizaci chyb
*   {$form->begin()} // vytisken element <form> 
*    
*   {$form->end()} // vytiskne vsechny hidden prvky pridane volanim $form->set_hidden_field() a konec elementu </form>
*   
*/
class Atk14Form extends Form
{
    var $atk14_action = ""; // kam ma smerovat element action ve <form>
    var $atk14_data = null; // data odeslana uzivateleme
    var $atk14_hidden_fields = array();
    var $atk14_attrs = array();
    var $atk14_initial_values = null; // vychozi hodnoty; pokud se nenastavuje, musi byt null....
    var $atk14_constructor_options = array();
    var $atk14_super_constructor_called = false;

    var $atk14_error_title = "";
    var $atk14_errors = array();

    var $atk14_method = "post";

    function Atk14Form($options = array(),$controller = null)
    {
        global $HTTP_REQUEST;

        $this->atk14_constructor_options = $options;

        $this->atk14_action = str_replace("&","&amp;",$HTTP_REQUEST->getRequestURI());
        //$this->atk14_action = Atk14Url::BuildLink(array()); // tohle sestavi URL s akt. $lang, $namespace, $controller a $action

        $this->controller = $controller;

        $this->atk14_error_title = _("Formular obsahuje chyby, proto nemuze byt zpracovan.");

        $this->set_up();
    }

    /**
    *
    * $form = Atk14Form::GetInstanceByFilename("login/login_form.inc");
    * $form = Atk14Form::GetInstanceByFilename("login/login_form");
    * $form = Atk14Form::GetInstanceByFilename("login_form");
    *
    * @static
    */
    function GetInstanceByFilename($filename,$controller_obj = null)
    {
        global $ATK14_GLOBAL;
        if(!preg_match("/\\.inc$/",$filename)){ $filename .= ".inc"; }

        $controller = $ATK14_GLOBAL->getValue("controller");
        $namespace = $ATK14_GLOBAL->getValue("namespace");
        $path = $ATK14_GLOBAL->getApplicationPath()."forms";

        // toto je preferovane poradi ve vyhledavani souboru s formularem
        $files = array(
           "$path/$namespace/$filename",
           "$path/$namespace/$controller/$filename",
           "$path/$filename",
           "$filename",
        );
        foreach($files as $_file){
            if(file_exists($_file)){
                $filename = $_file;
                break;
            }
        }
        $classname = "";
        if(preg_match("/([^\\/]+)_form\\.inc$/",$filename,$matches)){
            $classname = "{$matches[1]}Form";
            $classname = str_replace("_","",$classname);
        }
        if(strlen($classname)==0 || !file_exists($filename)){
            return null;
        }
	    require_once($filename);
        $form = new $classname(array(),$controller_obj);
        return $form;
    }

    /**
    *
    * $form = Atk14Form::GetInstanceByControlerAndAction("login","login");
    * 
    * @static
    */
    function GetInstanceByControlerAndAction($controller,$action,$controller_obj = null)
    {
        ($form = Atk14Form::GetInstanceByFilename("$controller/{$controller}_{$action}_form.inc",$controller_obj)) || 
        ($form = Atk14Form::GetInstanceByFilename("$controller/{$action}_form.inc",$controller_obj));
        return $form;
    }

    function GetInstanceByControler($controller){
        return Atk14Form::GetInstanceByControlerAndAction($controller->controller,$controller->action,$controller);
    }

    /**
    * V kontroleru:
    * $form = Atk14Form::GetForm("MoveForm",$this);
    */
    function GetForm($class_name,$controller = null){
        global $ATK14_GLOBAL;
        // preved z camelcase na underscore
        $filename = $class_name;
        $filename = preg_replace("/^([A-Z])/e","strtolower('\\1')",$filename);
        $filename = preg_replace("/([A-Z])/","_\\1",$filename);
        $filename = strtolower($filename);

        //echo $ATK14_GLOBAL->getValue("controller")."/$filename.inc"; exit;

        // zde se pokusime najit formik v adresari podle kontroleru a pokud nebude nalezen, zkusime o adresar vyse
        if(!$form = Atk14Form::GetInstanceByFilename($ATK14_GLOBAL->getValue("controller")."/$filename.inc",$controller)){
            $form = Atk14Form::GetInstanceByFilename("$filename.inc",$controller);
        }
        return $form;
    }

    /**
    * Returns an instance of ApplicationForm class if exists or Atk14Form.
    *
    * $form = Atk14Form::GetDefaultForm($controller);
    */
    function GetDefaultForm($controller = null){
      ($form = Atk14Form::GetForm("ApplicationForm",$controller)) || ($form = new Atk14Form(array(),$controller));
      return $form;
    }

    function validate($data)
    {
        if($this->is_valid($data)){
            return $this->cleaned_data;
        }
        return null;
    }

    /**
    * Vrati true pokud formular neobsahuje zadnou chybu, jinak false.
    *
    * if($request->Post() && $form->is_valid($_POST)){
    *
    * }
    *
    * // funguje vsak take..
    * if($request->Post() && $form->set_data($_POST) && $form->is_valid()){
    *
    * }
    *
    */
    function is_valid($data = null)
    {
        isset($data) && $this->set_data($data);

        $this->_call_super_constructor();
        
        return parent::is_valid();
    }

    function _call_super_constructor()
    {
        if(!$this->atk14_super_constructor_called){
            $options = $this->atk14_constructor_options;
            $options["call_set_up"] = false;

            if(isset($this->atk14_initial_values)){ $options["initial"] = $this->atk14_initial_values; }
            if(isset($this->atk14_data)){ $options["data"] = $this->atk14_data; }

            // v konstruktoru dojde ke smazani pole $this->fields;
            // pokud mame ve fields neco, co tam bylo dodano az v controlleru,
            // timto hackem dosahneme toho, ze o predhozi hodnoty neprijdeme
            // (a navic jsou puvodni fiedls podle ocekavani zarazena az na konec)
            if(isset($this->fields)){ $orig_fields = $this->fields; }

            parent::Form($options);

            if(isset($orig_fields)){
                $this->fields = array_merge($this->fields,$orig_fields);
            }

            $this->atk14_super_constructor_called = true;
        }
    }

    /**
    * Nastavi data formulare.
    * 
    * $form->set_data($_POST); // parametr je pole
    * $form->set_data($dictionary); // ale muze to byt i object tridy Dictionary
    *
    * Funkce vraci true, aby sla zaradit jednoduse do podminky:
    *
    *  if($this->request->Post() && $this->form->set_data($this->params) && $form->is_valid()){
    *  	$context->setValues($form->cleaned_data);
    *  	$this->flash->notice("Zaznam byl ulozen");
    *  	$this->_redirect_to_action("index");
    *  }
    */
    function set_data($data)
    {
        if(is_object($data)){ $data = $data->toArray(); }
        $this->atk14_data = $data;
        return true;
    }

    /**
    * Nastavi akci formulare.
    *
    * $form->set_action(array(
    *   "controller" => "customer",
    *   "action" => "login"
    * ));
    *
    * Defaultne je nastaveno na akt. request uri.
    */
    function set_action($url)
    {
        if(is_array($url)){
            $this->atk14_action = Atk14Url::BuildLink($url);
            return;
        }
        $this->atk14_action = (string)$url;
    }

    function set_method($method)
    {
        $this->atk14_method = (string)$method;
    }

    /**
    * Vrati initial hodnotu pole nebo vsech poli...
    *
    * $email_init = $form->get_initial("email");
    * $initials = $form->get_initial(); // zde potom je k dispozici $initials["email"]
    */
    function get_initial($name = null)
    {
        if(isset($name)){
           $out = parent::get_initial($name);
           if(isset($this->atk14_initial_values) && in_array($name,array_keys($this->atk14_initial_values))){
              $out = $this->atk14_initial_values[$name];
           }
           return $out;
        }

        $out = array();
        $keys = array_keys($this->fields);
        foreach($keys as $key){
            $out[$key] = $this->get_initial($key);
        }

        return $out;
    }

    /**
    * $form->set_initial("login","karel.kulek");
    * $form->set_initial("password","heslicko");
    * $form->set_initial(array(
    *    "login" => "karel.kulek",
    *    "password" => "heslicko"
    * ));
    * $this->set_initial($this->params); // object tridy
    */
    function set_initial($key_or_values,$value = null)
    {
        if(is_string($key_or_values)){ return $this->set_initial(array("$key_or_values" => $value)); }
        if(is_object($key_or_values)){ return $this->set_initial($key_or_values->toArray()); }

        if(!isset($this->atk14_initial_values)){ $this->atk14_initial_values = array(); }
        $this->atk14_initial_values = array_merge($key_or_values,$this->atk14_initial_values);
    }


    /**
    * Nastavi hidden pole.
    *
    * $form->set_hidden_field("step","1");
    * $form->set_hidden_field("session_id","33skls");
    * // nebo
    * $form->set_hidden_field(array(
    *   array(
    *       "step" => "1",
    *       "session_id" => "33skls"
    *   )
    * ))
    *
    */
    function set_hidden_field($key_or_values,$value = null)
    {
        if(is_string($key_or_values)){ return $this->set_hidden_field(array($key_or_values => $value)); }

        $this->atk14_hidden_fields = array_merge($this->atk14_hidden_fields,$key_or_values);
    }

    /**
    * $form->set_attr("enctype","multipart/form-data")
    */
    function set_attr($key_or_values,$value = null)
    {
        if(is_string($key_or_values)){ return $this->set_attr(array($key_or_values => $value)); }

        $this->atk14_attrs = array_merge($this->atk14_attrs,$key_or_values);
    }

    function allow_file_upload()
    {
        $this->set_attr("enctype","multipart/form-data");

    }


    /**
    * Vytiskne zacatek formulare - oteviraci tag <form>.
    *
    */
    function begin()
    {
        $this->_call_super_constructor();
        return "<form action=\"$this->atk14_action\" method=\"$this->atk14_method\"".$this->_get_attrs().">";
    }

    function begin_remote()
    {
        $this->_call_super_constructor();
        // takto je to v ROR: onsubmit="new Ajax.Request('', {asynchronous:true, evalScripts:true, parameters:Form.serialize(this)}); return false;"
        //return "<form action=\"$this->atk14_action\" method=\"$this->atk14_method\" onsubmit=\"return remote_form(this);\">";
        return "<form action=\"$this->atk14_action\" method=\"$this->atk14_method\" class=\"remote_form\"".$this->_get_attrs().">";
    }

    function _get_attrs(){
        $out = "";
        foreach($this->atk14_attrs as $key => $value){
            $out .= ' '.htmlspecialchars($key).'="'.htmlspecialchars($value).'"';
        }
        return $out;
    }

    /**
    * Vykresni konec formulare.
    *
    * Vsechny skryte prvky ($this->atk14_hidden_fields) a uzivaraci tag </form>.
    */
    function end()
    {
        $out = array();
        if(sizeof($this->atk14_hidden_fields)){
            $out[] = "<div>";
            reset($this->atk14_hidden_fields);
            while(list($_key,$_value) = each($this->atk14_hidden_fields))
            {
                $out[] = "<input type=\"hidden\" name=\"".htmlspecialchars($_key)."\" value=\"".htmlspecialchars($_value)."\" />";
            }
            $out[] = "</div>";
        }
        $out[] = "</form>";
        return join("\n",$out);
    }

    /**
    * $form->set_error("Prihlasovaci udaje nejsou spravne."); // obecna chyba bez urceni pole
    * $form->set_error("login","Takove prihlasovaci jmeno neexistuje.");
    */
    function set_error($error_message_of_field_name,$error_message = null)
    {
        if(!isset($error_message)){
            $field_name = "";
            $error_message = $error_message_of_field_name;
        }else{
            $field_name = $error_message_of_field_name;
            $error_message = $error_message;
        }

        if($field_name==""){
            $this->atk14_errors[] = $error_message;
            return;
        }

        if(!isset($this->errors)){ $this->errors = array(); }
        if(!isset($this->errors[$field_name])){ $this->errors[$field_name] = array(); }
        $this->errors[$field_name][] = $error_message;
    }

    /**
    * $error_ar = $form->get_errors(); // pole poli chyb na vsech polich
    * $error_ar = $form->get_errors("email"); // pole chyba na konkretnim poli
    */
    function get_errors($on_field = null){
         $out = parent::get_errors();
         if(!isset($out[""]) && sizeof($this->atk14_errors)>0){
             $out[""] = array();
         }
         if(sizeof($this->atk14_errors)>0){
             $out[""] = array_merge($out[""],$this->atk14_errors);
         }
         if(isset($on_field)){
            if(!isset($out[$on_field])){ $on_field[$on_field] = array(); }
            return $out[$on_field];
         }
         return $out;
    }

    function non_field_errors(){
        $errors = parent::non_field_errors();
        foreach($this->atk14_errors as $e){ $errors[] = $e; }
        return $errors;
    }

    function set_error_title($title){
        $this->atk14_error_title = $title;
    }

    /**
    * Priznak, zda tento formular obsahuje chyby.
    *
    * @return bool
    */
    function has_errors()
    {
        return (sizeof($this->get_errors())>0);
    }

    function get_error_report()
    {
		if(!$this->has_errors()){ return ""; }
		$out = array();
		$out[] = "<div class=\"errorExplanation\">";
		$out[] = "<h3>$this->atk14_error_title</h3>";
		$out[] = "<ul>";
        $errors = $this->get_errors();
		reset($errors);
		while(list($_key,$_messages) = each($errors)){
            if(sizeof($_messages)==0){ continue; }
            $_prefix = "";
            if(isset($this->fields[$_key])){
              $_prefix = $this->fields[$_key]->label.": ";
            }
			$out[] = "<li>$_prefix".join("</li>\n<li>$_prefix",$_messages)."</li>";
		}
		$out[] = "</ul>";
		$out[] = "</div>";
		return join("\n",$out);
    }

    // !!! je dulezite pred volanim get_field() volat konstruktor rodice.
    // !!! jinak by nebyl formular ($this) zinicialozovan (chybela by napr vlastnost $this->auto_id)
    // !!! a te je dulezita pri volani:
    // !!!   $field = $form->get_field("name");
    // !!!   echo $field->label_tag();
    function get_field($name){
        $this->_call_super_constructor();
        return parent::get_field($name);
    }
}
// vim: set et ts=4 sw=4 enc=utf-8 fenc=utf-8 si: 
?>
