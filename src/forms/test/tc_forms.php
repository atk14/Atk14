<?php
/**
* Testy formularu.
*/

/**
* Definice demonstracnich formularu pro potreby testu.
*/
class Person extends Form
{
	function set_up()
	{
		$this->add_field('first_name', new CharField());
		$this->add_field('last_name', new CharField());
		$this->add_field('age', new IntegerField());
	}
}
class OptionalPersonForm extends Form
{
	function set_up()
	{
		$this->add_field('first_name', new CharField());
		$this->add_field('last_name', new CharField());
		$this->add_field('nick_name', new CharField(array('required'=>false)));
	}
}
class PersonNew extends Form
{
	function set_up()
	{
		// Trosku komplikovanejsi zapis (kvuli polim v parametrech):
		// * chceme pole typu CharField
		// * toto pole bude vykreslene s pomoci widgetu TextInput
		// * widgetu nastavime konkretni id (takze policko se vykresli s parametrem id)
		$this->add_field('first_name', new CharField(
			array(
				'widget' => new TextInput(array('attrs'=>array('id'=>'first_name_id')))
			)
		));
		$this->add_field('last_name', new CharField());
		$this->add_field('age', new IntegerField());
	}
}
class SignupForm extends Form
{
	function set_up()
	{
		$this->add_field('email', new EmailField());
		$this->add_field('get_spam', new BooleanField());
	}
}
class ContactForm extends Form
{
	function set_up()
	{
		$this->add_field('subject', new CharField());
		$this->add_field('message', new CharField(
			array(
				'widget' => new TextArea(array('attrs'=>array('rows'=>80,'cols'=>20)))
			)
		));
	}
}
class FrameworkForm extends Form
{
	function set_up()
	{
		$this->add_field('name', new CharField());
		$this->add_field('language', new ChoiceField(
			array(
				'choices' => array('P'=>'Python', 'J'=>'Java')
			)
		));
	}
}
class FrameworkForm2 extends Form
{
	function set_up()
	{
		$this->add_field('name', new CharField());
		$this->add_field('language', new ChoiceField(
			array(
				'choices' => array(''=>'------', 'P'=>'Python', 'J'=>'Java')
			)
		));
	}
}
class FrameworkForm3 extends Form
{
	function set_up()
	{
		$this->add_field('name', new CharField());
		$this->add_field('language', new ChoiceField(
			array(
				'choices' => array('P'=>'Python', 'J'=>'Java'),
				'widget' => new Select(array('attrs'=>array('class'=>'foo')))
			)
		));
	}
}
class FrameworkForm4 extends Form
{
	function set_up()
	{
		$this->add_field('name', new CharField());
		$this->add_field('language', new ChoiceField(
			array(
				'choices' => array('P'=>'Python', 'J'=>'Java'),
				'widget' => new Select(array(
					'choices'=>array('R'=>'Ruby', 'P'=>'Perl'),
					'attrs'=>array('class'=>'foo')
				))
			)
		));
	}
}
class FrameworkForm5 extends Form
{
	function set_up()
	{
		$this->add_field('name', new CharField());
		$this->add_field('language', new ChoiceField());
	}
}
class FrameworkForm6 extends Form
{
	function set_up()
	{
		$this->add_field('name', new CharField());
		$this->add_field('language', new ChoiceField(
			array(
				'choices' => array('P'=>'Python', 'J'=>'Java'),
				'widget' => new RadioSelect()
			)
		));
	}
}
class SongForm extends Form
{
	function set_up()
	{
		$this->add_field('name', new CharField());
		$this->add_field('composers', new MultipleChoiceField());
	}
}
class SongForm2 extends Form
{
	function set_up()
	{
		$this->add_field('name', new CharField());
		$this->add_field('composers', new MultipleChoiceField(
			array(
				'choices' => array('J'=>'John Lennon', 'P'=>'Paul McCartney')
			)
		));
	}
}
class SongForm3 extends Form
{
	function set_up()
	{
		$this->add_field('name', new CharField());
		$this->add_field('composers', new MultipleChoiceField(
			array(
				'choices' => array('J'=>'John Lennon', 'P'=>'Paul McCartney'),
				'widget' => new CheckboxSelectMultiple()
			)
		));
	}
}
/**
* Formulare, na kterych se testuje custom validace poli.
*/
class UserRegistration extends Form
{
	function set_up()
	{
		$this->add_field('username', new CharField(array('max_length'=>10)));
		$this->add_field('password1', new CharField(array('widget'=>new PasswordInput())));
		$this->add_field('password2', new CharField(array('widget'=>new PasswordInput())));
	}

	function clean_password2()
	{
		if (isset($this->cleaned_data['password1']) && 
			isset($this->cleaned_data['password2']) && 
			($this->cleaned_data['password1'] != $this->cleaned_data['password2'])) {

			return array('Please make sure your passwords match.', null);
		}
		return array(null, $this->cleaned_data['password2']);
	}
}
class UserRegistration2 extends Form
{
	function set_up()
	{
		$this->add_field('username', new CharField(array('max_length'=>10)));
		$this->add_field('password1', new CharField(array('widget'=>new PasswordInput())));
		$this->add_field('password2', new CharField(array('widget'=>new PasswordInput())));
	}

	function clean()
	{
		if (isset($this->cleaned_data['password1']) && 
			isset($this->cleaned_data['password2']) && 
			($this->cleaned_data['password1'] != $this->cleaned_data['password2'])) {

			return array('Please make sure your passwords match.', null);
		}
		return array(null, $this->cleaned_data);
	}
}
/**
* Formulare s "dynamickym" vytvarenim formularovych polozek.
* On je to trochu fejk, protoze narozdil od Djanga ja je vzdycky
* generuju dynamicky (metodou set_up). 
* Nicmene je zde alespon nastineno, ze do konstruktoru formulare se
* muzou natlacit extra parametry (v tomto pripade $field_list), 
* a pak s nimi podle libosti nalozit (v tomto pripade z nich poskladat
* polozky podle vnejsiho prani).
*/
class MyForm extends Form
{
	function __construct($field_list, $options=array())
	{
		$options['auto_id'] = false;
		parent::__construct($options);
		// dynamicke vytvoreni polozek formulare
		// muzu to udelat tady, ale stejne tak bych mohl *PRED* volanim
		// konstruktoru bazove tridy nastavit nejaky svuj pracovni atribut
		// (treba $this->my_field_list) a ten pak zpracovat v metode
		// set_up(). Mozna by to tak i bylo cistejsi...
		foreach ($field_list as $k => $v) {
			$this->add_field($k, $v);
		}
	}
}
class MyForm2 extends Form
{
	function __construct($field_list, $options=array())
	{
		$options['auto_id'] = false;
		parent::__construct($options);
		foreach ($field_list as $k => $v) {
			$this->add_field($k, $v);
		}
	}

	function set_up()
	{
		$this->add_field('default_field_1', new CharField());
		$this->add_field('default_field_2', new CharField());
	}
}
class Person2 extends Form
{
	function __construct($names_required=false, $options=array())
	{
		parent::__construct($options);
		if ($names_required) {
			$this->fields['first_name']->required = true;
			$this->fields['first_name']->widget->attrs['class'] = 'required';
			$this->fields['last_name']->required = true;
			$this->fields['last_name']->widget->attrs['class'] = 'required';
		}
	}

	function set_up()
	{
		$this->add_field('first_name', new CharField(array('required'=>false)));
		$this->add_field('last_name', new CharField(array('required'=>false)));
	}
}
class Person3 extends Form
{
	function __construct($name_max_length=null, $options=array())
	{
		parent::__construct($options);
		if ($name_max_length) {
			$this->fields['first_name']->max_length = $name_max_length;
			$this->fields['last_name']->max_length = $name_max_length;
		}
	}

	function set_up()
	{
		$this->add_field('first_name', new CharField(array('max_length'=>30)));
		$this->add_field('last_name', new CharField(array('max_length'=>30)));
	}
}
class Person4 extends Form
{
	function set_up()
	{
		$this->add_field('first_name', new CharField());
		$this->add_field('last_name', new CharField());
		$this->add_field('hidden_text', new CharField(array('widget'=>new HiddenInput())));
		$this->add_field('age', new IntegerField());
	}
}
class TestForm extends Form
{
	function set_up()
	{
		$this->add_field('foo', new CharField(array('widget'=>new HiddenInput())));
		$this->add_field('bar', new CharField(array('widget'=>new HiddenInput())));
	}
}
class TestForm2 extends Form
{
	function set_up()
	{
		$this->add_field('field1', new CharField());
		$this->add_field('field2', new CharField());
		$this->add_field('field3', new CharField());
		$this->add_field('field4', new CharField());
		$this->add_field('field5', new CharField());
		$this->add_field('field6', new CharField());
		$this->add_field('field7', new CharField());
		$this->add_field('field8', new CharField());
		$this->add_field('field9', new CharField());
		$this->add_field('field10', new CharField());
		$this->add_field('field11', new CharField());
		$this->add_field('field12', new CharField());
		$this->add_field('field13', new CharField());
		$this->add_field('field14', new CharField());
	}
}
class UserRegistration3 extends Form
{
	function set_up()
	{
		$this->add_field('username', new CharField(array('max_length'=>10)));
		$this->add_field('password', new CharField(array('max_length'=>10, 'widget'=>new PasswordInput())));
		$this->add_field('realname', new CharField(array('max_length'=>10, 'widget'=>new TextInput())));
		$this->add_field('address', new CharField());
	}
}
class UserRegistration4 extends Form
{
	function set_up()
	{
		$this->add_field('username', new CharField(array('max_length'=>10, 'widget'=>new TextInput(array('maxlength'=>20)))));
		$this->add_field('password', new CharField(array('max_length'=>10, 'widget'=>new PasswordInput())));
	}
}
/**
* Formulare pro otestovani chovani labels.
*/
class UserRegistration5 extends Form
{
	function set_up()
	{
		$this->add_field('username', new CharField(array('max_length'=>10, 'label'=>'Your username')));
		$this->add_field('password1', new CharField(array('widget'=>new PasswordInput())));
		$this->add_field('password2', new CharField(array('label'=>'Password (again)', 'widget'=>new PasswordInput())));
	}
}
class Questions extends Form
{
	function set_up()
	{
		$this->add_field('q1', new CharField(array('label'=>'The first question')));
		$this->add_field('q2', new CharField(array('label'=>'What is your name?')));
		$this->add_field('q3', new CharField(array('label'=>'The answer to life is:')));
		$this->add_field('q4', new CharField(array('label'=>'Answer this question!')));
		$this->add_field('q5', new CharField(array('label'=>'The last question. Period.')));
	}
}
class UserRegistration6 extends Form
{
	function set_up()
	{
		$this->add_field('username', new CharField(array('max_length'=>10, 'label'=>'')));
		$this->add_field('password', new CharField(array('widget'=>new PasswordInput())));
	}
}
class UserRegistration7 extends Form
{
	function set_up()
	{
		$this->add_field('username', new CharField(array('max_length'=>10, 'label'=>null)));
		$this->add_field('password', new CharField(array('widget'=>new PasswordInput())));
	}
}
class FavoriteForm extends Form
{
	function set_up()
	{
		$this->add_field('color', new CharField(array('label'=>'Favorite color?')));
		$this->add_field('animal', new CharField(array('label'=>'Favorite animal')));
	}
}
class UserRegistration8 extends Form
{
	function set_up()
	{
		$this->add_field('username', new CharField(array('max_length'=>10, 'initial'=>'django')));
		$this->add_field('password', new CharField(array('widget'=>new PasswordInput())));
	}
}
class UserRegistration9 extends Form
{
	function set_up()
	{
		$this->add_field('username', new CharField(array('max_length'=>10)));
		$this->add_field('password', new CharField(array('widget'=>new PasswordInput())));
	}
}
class UserRegistration10 extends Form
{
	function set_up()
	{
		$this->add_field('username', new CharField(array('max_length'=>10, 'initial'=>'django')));
		$this->add_field('password', new CharField(array('widget'=>new PasswordInput())));
	}
}
class UserRegistration11 extends Form
{
	function set_up()
	{
		$this->add_field('username', new CharField(array('max_length'=>10, 'help_text'=>'e.g., user@example.com')));
		$this->add_field('password', new CharField(array('widget'=>new PasswordInput(), 'help_text'=>'Choose wisely.')));
	}
}
class UserRegistration12 extends Form
{
	function set_up()
	{
		$this->add_field('username', new CharField(array('max_length'=>10, 'help_text'=>'e.g., user@example.com')));
		$this->add_field('password', new CharField(array('widget'=>new PasswordInput())));
		$this->add_field('next', new CharField(array('widget'=>new HiddenInput(), 'initial'=>'/', 'help_text'=>'Redirect destination')));
	}
}
class Musician extends Person
{
	function set_up()
	{
		parent::set_up();
		$this->add_field('instrument', new CharField());
	}
}
class Person5 extends Form
{
	function set_up()
	{
		$this->add_field('first_name', new CharField());
		$this->add_field('last_name', new CharField());
		$this->add_field('age', new IntegerField());
	}
	
	function add_prefix($field_name)
	{
		if ($this->prefix) {
			return $this->prefix.'-prefix-'.$field_name;
		}
		else {
			return $field_name;
		}
	}
}



/**
* Samotne testy.
*/
class TcForms extends TcBase
{
	/**
	* Nejprve formulari dame platna data a prozkoumame jeho reakci.
	*/
	function test_form_with_data()
	{
		// vytvorim jednoduchy formular
		$form = new Person(array(
			'data' => array(
				'first_name' => 'John',
				'last_name' => 'Lennon',
				'age' => 12
			)
		));
		// fomular je SVAZAN (bound) s daty
		$this->assertTrue($form->is_bound);
		$this->assertEquals(array(), $form->get_errors());
		// formular obsahuje validni data
		$this->assertTrue($form->is_valid());
		// formular obsahuje tato pole
		$this->assertEquals(
			array (
			  'first_name' => 'John',
			  'last_name' => 'Lennon',
			  'age' => 12,
			),
			$form->cleaned_data
		);
		// vykresleni policek jako HTML
		$field = $form->get_field('first_name');
		$this->assertEquals(
			'<input required="required" type="text" name="first_name" class="text form-control" id="id_first_name" value="John" />',
			$field->as_widget()
		);
		$this->assertEquals('First name', $field->label);
		$this->assertEquals('John', $field->data());

		$field = $form->get_field('last_name');
		$this->assertEquals(
			'<input required="required" type="text" name="last_name" class="text form-control" id="id_last_name" value="Lennon" />',
			$field->as_widget()
		);
		$this->assertEquals('Last name', $field->label);
		$this->assertEquals('Lennon', $field->data());

		$field = $form->get_field('age');
		$this->assertEquals(
			'<input required="required" type="number" name="age" class="number text form-control" id="id_age" value="12" />',
			$field->as_widget()
		);
		$this->assertEquals('Age', $field->label);
		$this->assertEquals(12, $field->data());
		// neexistujici pole (get_field vrati null)
		$field = $form->get_field('nonexistentfield');
		$this->assertNull($field);
		// kontrola generovani celeho formulare
		$this->assertEquals(
			implode(
				"\n", 
				array(
					'<tr><th><label for="id_first_name">First name:</label></th><td><input required="required" type="text" name="first_name" class="text form-control" id="id_first_name" value="John" /></td></tr>',
					'<tr><th><label for="id_last_name">Last name:</label></th><td><input required="required" type="text" name="last_name" class="text form-control" id="id_last_name" value="Lennon" /></td></tr>',
					'<tr><th><label for="id_age">Age:</label></th><td><input required="required" type="number" name="age" class="number text form-control" id="id_age" value="12" /></td></tr>'
				)
			),
			$form->as_table()
		);
	}

	/**
	* Formulari se mohou dodat prazdna data.
	*/
	function test_empty_data()
	{
		// vytvorim jednoduchy formular
		$form = new Person(array('data'=>array()));
		// fomular je SVAZAN (bound) s daty
		$this->assertTrue($form->is_bound);
		$this->assertEquals(
			array(
				'first_name' => array('This field is required.'), 
				'last_name' => array('This field is required.'), 
				'age' => array('This field is required.')
			),
			$form->get_errors()
		);
		// formular obsahuje chybna data
		$this->assertFalse($form->is_valid());
		// a protoze obsahuje chybna data, atribut cleaned_data neni nastaven
		$this->assertFalse(isset($form->cleaned_data));
		// kontrola generovani celeho formulare
		$this->assertEquals(
			implode(
				"\n", 
				array(
					'<tr><th><label for="id_first_name">First name:</label></th><td><ul class="errorlist"><li>This field is required.</li></ul><input required="required" type="text" name="first_name" class="text form-control" id="id_first_name" /></td></tr>',
					'<tr><th><label for="id_last_name">Last name:</label></th><td><ul class="errorlist"><li>This field is required.</li></ul><input required="required" type="text" name="last_name" class="text form-control" id="id_last_name" /></td></tr>',
					'<tr><th><label for="id_age">Age:</label></th><td><ul class="errorlist"><li>This field is required.</li></ul><input required="required" type="number" name="age" class="number text form-control" id="id_age" /></td></tr>'
				)
			),
			$form->as_table()
		);
		$this->assertEquals(
			implode(
				"\n", 
				array(
					'<li><ul class="errorlist"><li>This field is required.</li></ul><label for="id_first_name">First name:</label> <input required="required" type="text" name="first_name" class="text form-control" id="id_first_name" /></li>',
					'<li><ul class="errorlist"><li>This field is required.</li></ul><label for="id_last_name">Last name:</label> <input required="required" type="text" name="last_name" class="text form-control" id="id_last_name" /></li>',
					'<li><ul class="errorlist"><li>This field is required.</li></ul><label for="id_age">Age:</label> <input required="required" type="number" name="age" class="number text form-control" id="id_age" /></li>'
				)
			),
			$form->as_ul()
		);
		$this->assertEquals(
			implode(
				"\n", 
				array(
					'<ul class="errorlist"><li>This field is required.</li></ul>',
					'<p><label for="id_first_name">First name:</label> <input required="required" type="text" name="first_name" class="text form-control" id="id_first_name" /></p>',
					'<ul class="errorlist"><li>This field is required.</li></ul>',
					'<p><label for="id_last_name">Last name:</label> <input required="required" type="text" name="last_name" class="text form-control" id="id_last_name" /></p>',
					'<ul class="errorlist"><li>This field is required.</li></ul>',
					'<p><label for="id_age">Age:</label> <input required="required" type="number" name="age" class="number text form-control" id="id_age" /></p>'
				)
			),
			$form->as_p()
		);
	}

	/**
	* Formular bez dat.
	*/
	function test_empty_form()
	{
		// vytvorim jednoduchy formular
		$form = new Person();
		// fomular je SVAZAN (bound) s daty
		$this->assertFalse($form->is_bound);
		$this->assertEquals(array(), $form->get_errors());
		// formular obsahuje chybna data
		$this->assertFalse($form->is_valid());
		// a protoze obsahuje chybna data, atribut cleaned_data neni nastaven
		$this->assertFalse(isset($form->cleaned_data));
		// kontrola generovani celeho formulare
		$this->assertEquals(
			implode(
				"\n", 
				array(
					'<tr><th><label for="id_first_name">First name:</label></th><td><input required="required" type="text" name="first_name" class="text form-control" id="id_first_name" /></td></tr>',
					'<tr><th><label for="id_last_name">Last name:</label></th><td><input required="required" type="text" name="last_name" class="text form-control" id="id_last_name" /></td></tr>',
					'<tr><th><label for="id_age">Age:</label></th><td><input required="required" type="number" name="age" class="number text form-control" id="id_age" /></td></tr>',
				)
			),
			$form->as_table()
		);
		$this->assertEquals(
			implode(
				"\n", 
				array(
					'<li><label for="id_first_name">First name:</label> <input required="required" type="text" name="first_name" class="text form-control" id="id_first_name" /></li>',
					'<li><label for="id_last_name">Last name:</label> <input required="required" type="text" name="last_name" class="text form-control" id="id_last_name" /></li>',
					'<li><label for="id_age">Age:</label> <input required="required" type="number" name="age" class="number text form-control" id="id_age" /></li>',
				)
			),
			$form->as_ul()
		);
		$this->assertEquals(
			implode(
				"\n", 
				array(
					'<p><label for="id_first_name">First name:</label> <input required="required" type="text" name="first_name" class="text form-control" id="id_first_name" /></p>',
					'<p><label for="id_last_name">Last name:</label> <input required="required" type="text" name="last_name" class="text form-control" id="id_last_name" /></p>',
					'<p><label for="id_age">Age:</label> <input required="required" type="number" name="age" class="number text form-control" id="id_age" /></p>',
				)
			),
			$form->as_p()
		);
	}

	/**
	* Formular s jednim validnim polem.
	*/
	function test_form_with_one_valid_field()
	{
		// vytvorim jednoduchy formular
		$form = new Person(array('data'=>array('last_name'=>'Lennon')));
		$this->assertEquals(
			array(
				'first_name' => array('This field is required.'), 
				'age' => array('This field is required.')
			),
			$form->get_errors()
		);
		// formular obsahuje chybna data
		$this->assertFalse($form->is_valid());
		// a protoze obsahuje chybna data, atribut cleaned_data neni nastaven
		$this->assertFalse(isset($form->cleaned_data));
		// kontrola error hlasky u konkretniho pole
		$field = $form->get_field('first_name');
		$this->assertEquals(array('This field is required.'), $field->errors());
	}
	
	/**
	* Formular bez dodanych dat umi spravne vykreslit 
	* jednotliva policka.
	*/
	function test_void_form()
	{
		$form = new Person();
		$field = $form->get_field('first_name');
		$this->assertEquals('<input required="required" type="text" name="first_name" class="text form-control" id="id_first_name" />', $field->as_widget());
		$field = $form->get_field('last_name');
		$this->assertEquals('<input required="required" type="text" name="last_name" class="text form-control" id="id_last_name" />', $field->as_widget());
		$field = $form->get_field('age');
		$this->assertEquals('<input required="required" type="number" name="age" class="number text form-control" id="id_age" />', $field->as_widget());
	}
	
	/**
	* Pokud se do formulare poslou i nejaka extra data, ktere v definici
	* formularove tridy chybi, ve vystupu (cleaned_data) se neobjevi.
	*/
	function test_extra_data_in_form()
	{
		$data = array(
			'first_name' => 'John',
			'last_name' => 'Lennon',
			'age' => '10',
			'extra1' => 'hello',
			'extra2' => 'hello'
		);
		$form = new Person(array('data'=>$data));
		$this->assertTrue($form->is_valid());
		// formular obsahuje tato pole
		$this->assertEquals(
			array (
			  'first_name' => 'John',
			  'last_name' => 'Lennon',
			  'age' => 10,
			),
			$form->cleaned_data
		);
	}

	/**
	* Pokud ve vstupnich datech chybi nepovinna polozka, ve vystupu
	* se objevi.
	*/
	function test_missing_unrequired_field()
	{
		$data = array(
			'first_name' => 'John',
			'last_name' => 'Lennon',
		);
		$form = new OptionalPersonForm(array('data'=>$data));
		$this->assertTrue($form->is_valid());
		// formular obsahuje tato pole
		$this->assertEquals(
			array (
			  'first_name' => 'John',
			  'last_name' => 'Lennon',
			  'nick_name' => '',
			),
			$form->cleaned_data
		);
	}

	/**
	* Pokud se formular zavola s parametrem auto_id, pak se do
	* jednotlivych policek formulare a tagu <label> generuje id
	* v danem tvaru (retezec %s je nahrazen nazvem policka).
	*/
	function test_form_auto_id_with_format_string()
	{
		$form = new Person(array('auto_id'=>'%s_id'));
		// kontrola generovani celeho formulare
		$this->assertEquals(
			implode(
				"\n", 
				array(
					'<tr><th><label for="first_name_id">First name:</label></th><td><input required="required" type="text" name="first_name" class="text form-control" id="first_name_id" /></td></tr>',
					'<tr><th><label for="last_name_id">Last name:</label></th><td><input required="required" type="text" name="last_name" class="text form-control" id="last_name_id" /></td></tr>',
					'<tr><th><label for="age_id">Age:</label></th><td><input required="required" type="number" name="age" class="number text form-control" id="age_id" /></td></tr>',
				)
			),
			$form->as_table()
		);
		$this->assertEquals(
			implode(
				"\n", 
				array(
					'<li><label for="first_name_id">First name:</label> <input required="required" type="text" name="first_name" class="text form-control" id="first_name_id" /></li>',
					'<li><label for="last_name_id">Last name:</label> <input required="required" type="text" name="last_name" class="text form-control" id="last_name_id" /></li>',
					'<li><label for="age_id">Age:</label> <input required="required" type="number" name="age" class="number text form-control" id="age_id" /></li>',
				)
			),
			$form->as_ul()
		);
		$this->assertEquals(
			implode(
				"\n", 
				array(
					'<p><label for="first_name_id">First name:</label> <input required="required" type="text" name="first_name" class="text form-control" id="first_name_id" /></p>',
					'<p><label for="last_name_id">Last name:</label> <input required="required" type="text" name="last_name" class="text form-control" id="last_name_id" /></p>',
					'<p><label for="age_id">Age:</label> <input required="required" type="number" name="age" class="number text form-control" id="age_id" /></p>',
				)
			),
			$form->as_p()
		);
	}

	/**
	* Navaznost na predchozi test_form_auto_id_with_format_string -- pokud se do
	* pole auto_id da hodnota, ktera neobsahuje retezec '%s' a je 
	* vyhodnocena jako true, pak se jako id generuje primo nazev policka.
	*/
	function test_form_auto_id_without_format_string()
	{
		$form = new Person(array('auto_id'=>true));
		// kontrola generovani celeho formulare
		$this->assertEquals(
			implode(
				"\n", 
				array(
					'<li><label for="first_name">First name:</label> <input required="required" type="text" name="first_name" class="text form-control" id="first_name" /></li>',
					'<li><label for="last_name">Last name:</label> <input required="required" type="text" name="last_name" class="text form-control" id="last_name" /></li>',
					'<li><label for="age">Age:</label> <input required="required" type="number" name="age" class="number text form-control" id="age" /></li>',
				)
			),
			$form->as_ul()
		);

		$form = new Person(array('auto_id'=>'prdka'));
		// kontrola generovani celeho formulare
		$this->assertEquals(
			implode(
				"\n", 
				array(
					'<li><label for="first_name">First name:</label> <input required="required" type="text" name="first_name" class="text form-control" id="first_name" /></li>',
					'<li><label for="last_name">Last name:</label> <input required="required" type="text" name="last_name" class="text form-control" id="last_name" /></li>',
					'<li><label for="age">Age:</label> <input required="required" type="number" name="age" class="number text form-control" id="age" /></li>',
				)
			),
			$form->as_ul()
		);
	}

	/**
	* Navaznost na predchozi test_form_auto_id_without_format_string -- 
	* pokud se do pole auto_id da false hodnota, ktera neobsahuje retezec '%s',
	* pak se jako id ani <label> negeneruje.
	*/
	function test_form_auto_id_false()
	{
		$form = new Person(array('auto_id'=>false));
		// kontrola generovani celeho formulare
		$this->assertEquals(
			implode(
				"\n", 
				array(
					'<li>First name: <input required="required" type="text" name="first_name" class="text form-control" /></li>',
					'<li>Last name: <input required="required" type="text" name="last_name" class="text form-control" /></li>',
					'<li>Age: <input required="required" type="number" name="age" class="number text form-control" /></li>',
				)
			),
			$form->as_ul()
		);

		$form = new Person(array('auto_id'=>''));
		// kontrola generovani celeho formulare
		$this->assertEquals(
			implode(
				"\n", 
				array(
					'<li>First name: <input required="required" type="text" name="first_name" class="text form-control" /></li>',
					'<li>Last name: <input required="required" type="text" name="last_name" class="text form-control" /></li>',
					'<li>Age: <input required="required" type="number" name="age" class="number text form-control" /></li>',
				)
			),
			$form->as_ul()
		);
	}

	/**
	* Pokud formulari rekneme, ze NECHCEME generovat atributy id, ale
	* ve formulari je pro nejake z poli id nadefinovano, toto konkretni
	* pole se s id vykresli, zbytek poli ale zustane bez nej.
	*/
	function test_id_only_for_one_field()
	{
		$form = new PersonNew(array('auto_id'=>false));
		// kontrola generovani celeho formulare
		$this->assertEquals(
			implode(
				"\n", 
				array(
					'<li><label for="first_name_id">First name:</label> <input id="first_name_id" required="required" type="text" name="first_name" class="text form-control" /></li>',
					'<li>Last name: <input required="required" type="text" name="last_name" class="text form-control" /></li>',
					'<li>Age: <input required="required" type="number" name="age" class="number text form-control" /></li>',
				)
			),
			$form->as_ul()
		);
	}

	/**
	* Pokud formulari rekneme, ze NECHCEME generovat atributy id, ale
	* ve formulari je pro nejake z poli id nadefinovano, toto konkretni
	* pole se s id vykresli, zbytek poli ale zustane bez nej.
	*/
	function test_id_for_one_field_and_whole_form()
	{
		$form = new PersonNew(array('auto_id'=>true));
		// kontrola generovani celeho formulare
		$this->assertEquals(
			implode(
				"\n", 
				array(
					'<li><label for="first_name_id">First name:</label> <input id="first_name_id" required="required" type="text" name="first_name" class="text form-control" /></li>',
					'<li><label for="last_name">Last name:</label> <input required="required" type="text" name="last_name" class="text form-control" id="last_name" /></li>',
					'<li><label for="age">Age:</label> <input required="required" type="number" name="age" class="number text form-control" id="age" /></li>',
				)
			),
			$form->as_ul()
		);
	}

	/**
	* Overeni generovani polozek formulare s vypnutym auto_id;
	* jednou bez dat, podruhe s daty.
	*/
	function test_form_with_data_and_without_id()
	{
		// formular vypnutym generovani id
		$form = new SignupForm(array('auto_id'=>false));
		$field = $form->get_field('email');
		$this->assertEquals(
			'<input required="required" type="email" name="email" class="email text form-control" value="@" />',
			$field->as_widget()
		);
		$field = $form->get_field('get_spam');
		$this->assertEquals(
			'<input type="checkbox" name="get_spam" />',
			$field->as_widget()
		);
		// formular s daty a vypnutym generovani id
		$data = array(
			'email' => 'test@example.com', 
			'get_spam' => true
		);
		$form = new SignupForm(array('data'=>$data, 'auto_id'=>false));
		$field = $form->get_field('email');
		$this->assertEquals(
			'<input required="required" type="email" name="email" class="email text form-control" value="test@example.com" />',
			$field->as_widget()
		);
		$field = $form->get_field('get_spam');
		$this->assertEquals(
			'<input type="checkbox" name="get_spam" checked="checked" />',
			$field->as_widget()
		);
	}

	/**
	* U kazdeho policka formulare muzeme vyspecifikovat,
	* jaky widget se ma pouzit.
	*/
	function test_fields_widgets()
	{
		// overeni zakladniho vystupu
		$form = new ContactForm(array('auto_id'=>false));
		$field = $form->get_field('subject');
		$this->assertEquals(
			'<input required="required" type="text" name="subject" class="text form-control" />',
			$field->as_widget()
		);
		$field = $form->get_field('message');
		$this->assertHtmlEquals(
			'<textarea cols="20" rows="80" name="message" class="form-control"></textarea>',
			$field->as_widget()
		);

		// podoba policek se da ovlivnit volanim metod
		// as_textarea, as_text a as_hidden
		$field = $form->get_field('subject');
		$this->assertHtmlEquals(
			'<textarea cols="40" rows="10" name="subject" class="form-control"></textarea>',
			$field->as_textarea()
		);

		// atributy definovane v instanci formulare (cols, rows)
		// se do metod as_XXX neprenasi (tj. prekresleny HTML
		// prvek cols ani rows neobsahuje)
		$field = $form->get_field('message');
		$this->assertEquals(
			'<input type="text" name="message" class="text form-control" />',
			$field->as_text()
		);
		$this->assertEquals(
			'<input type="hidden" name="message" />',
			$field->as_hidden()
		);

		// opet -- overime neprenaseni atributu, tentokrat ale dame 
		// formulari i nejaka vstupni data
		$data = array('subject'=>'Hello', 'message'=>'I love you.');
		$form = new ContactForm(array('data'=>$data, 'auto_id'=>false));
		$field = $form->get_field('subject');
		$this->assertHtmlEquals(
			'<textarea cols="40" rows="10" name="subject" class="form-control">Hello</textarea>',
			$field->as_textarea()
		);
		$field = $form->get_field('message');
		$this->assertEquals(
			'<input type="text" name="message" class="text form-control" value="I love you." />',
			$field->as_text()
		);
		$field = $form->get_field('message');
		$this->assertEquals(
			'<input type="hidden" name="message" value="I love you." />',
			$field->as_hidden()
		);
	}

	/**
	* Test formulare s ChoiceField.
	*/
	function test_form_with_choicefield()
	{
		// overeni zakladniho vystupu
		$form = new FrameworkForm(array('auto_id'=>false));
		$field = $form->get_field('language');
		$this->assertEquals(
			implode("\n", array('<select name="language" class="form-control">', '<option value="P">Python</option>', '<option value="J">Java</option>', '</select>')),
			$field->as_widget()
		);

		// test se vstupnimi daty
		$data = array('name'=>'Django', 'language'=>'P');
		$form = new FrameworkForm(array('data'=>$data, 'auto_id'=>false));
		$field = $form->get_field('language');
		$this->assertEquals(
			implode("\n", array('<select name="language" class="form-control">', '<option value="P" selected="selected">Python</option>', '<option value="J">Java</option>', '</select>')),
			$field->as_widget()
		);

		// pokud pole ChoiceField obsahuje nejakou prazdnou hodnotu, 
		// vykresli se jako zvolena
		$form = new FrameworkForm2(array('auto_id'=>false));
		$field = $form->get_field('language');
		$this->assertEquals(
			implode("\n", array('<select name="language" class="form-control">', '<option value="" selected="selected">------</option>', '<option value="P">Python</option>', '<option value="J">Java</option>', '</select>')),
			$field->as_widget()
		);

		// pri definici formulare muzu u policka vyspecifikovat 
		// dodatecne atributy
		$form = new FrameworkForm3(array('auto_id'=>false));
		$field = $form->get_field('language');
		$this->assertEquals(
			implode("\n", array('<select class="foo" name="language">', '<option value="P">Python</option>', '<option value="J">Java</option>', '</select>')),
			$field->as_widget()
		);
		$form = new FrameworkForm3(array('data'=>$data, 'auto_id'=>false));
		$field = $form->get_field('language');
		$this->assertEquals(
			implode("\n", array('<select class="foo" name="language">', '<option value="P" selected="selected">Python</option>', '<option value="J">Java</option>', '</select>')),
			$field->as_widget()
		);

		// pokud se polozky selektitka vyspecifikuji jak pro field,
		// tak pro widget, pak se pouziji jen ty pro field
		$form = new FrameworkForm4(array('auto_id'=>false));
		$field = $form->get_field('language');
		$this->assertEquals(
			implode("\n", array('<select class="foo" name="language">', '<option value="P">Python</option>', '<option value="J">Java</option>', '</select>')),
			$field->as_widget()
		);
		$form = new FrameworkForm4(array('data'=>$data, 'auto_id'=>false));
		$field = $form->get_field('language');
		$this->assertEquals(
			implode("\n", array('<select class="foo" name="language">', '<option value="P" selected="selected">Python</option>', '<option value="J">Java</option>', '</select>')),
			$field->as_widget()
		);

		// polozky selektitka se nemusi definovat ve formularove tride
		$form = new FrameworkForm5(array('auto_id'=>false));
		$field = $form->get_field('language');
		$this->assertEquals(
			implode("\n", array('<select name="language" class="form-control">', '</select>')),
			$field->as_widget()
		);
		$field->field->set_choices(array('P'=>'Python', 'J'=>'Java'));
		$this->assertEquals(
			implode("\n", array('<select name="language" class="form-control">', '<option value="P">Python</option>', '<option value="J">Java</option>', '</select>')),
			$field->as_widget()
		);
	}

	/**
	* Test formulare s ChoiceField, ktery se ma vykreslit jako RadioSelect.
	*/
	function test_form_with_radioselect()
	{
		// polozky selektitka se nemusi definovat ve formularove tride
		$form = new FrameworkForm6(array('auto_id'=>false));
		$field = $form->get_field('language');
		$this->assertTrue($this->_compare_html(
			implode("\n", array('<ul class="radios">', '<li><label><input type="radio" name="language" value="P" /> Python</label></li>', '<li><label><input type="radio" name="language" value="J" /> Java</label></li>', '</ul>')),
			$field->as_widget()
		));

		// kontrola generovani celeho formulare
		$this->assertTrue($this->_compare_html(
			implode(
				"\n", 
				array(
					'<tr><th>Name:</th><td><input required="required" type="text" name="name" class="text form-control" /></td></tr>',
					'<tr><th>Language:</th><td><ul class="radios">',
					'<li><label><input type="radio" name="language" value="P" /> Python</label></li>',
					'<li><label><input type="radio" name="language" value="J" /> Java</label></li>',
					'</ul></td></tr>'
				)
			),
			$form->as_table()
		));
		$this->assertTrue($this->_compare_html(
			implode(
				"\n", 
				array(
					'<li>Name: <input required="required" type="text" name="name" class="text form-control" /></li>',
					'<li>Language: <ul class="radios">',
					'<li><label><input type="radio" name="language" value="P" /> Python</label></li>',
					'<li><label><input type="radio" name="language" value="J" /> Java</label></li>',
					'</ul></li>'
				)
			),
			$form->as_ul()
		));

		// id u RadioSelect se generuji i s poradovym cislem
		$form = new FrameworkForm6(array('auto_id'=>'id_%s'));
		$field = $form->get_field('language');
		$this->assertTrue($this->_compare_html(
			implode("\n", array('<ul class="radios">', '<li><label><input id="id_language_0" type="radio" name="language" value="P" /> Python</label></li>', '<li><label><input id="id_language_1" type="radio" name="language" value="J" /> Java</label></li>', '</ul>')),
			$field->as_widget()
		));

		// pokud se pouzije RadioSelect s parametrem auto_id, tag <label> bude 
		// ukazovat na *prvni* z polozek radio buttonu.
		$this->assertTrue($this->_compare_html(
			implode(
				"\n", 
				array(
					'<tr><th><label for="id_name">Name:</label></th><td><input required="required" type="text" name="name" class="text form-control" id="id_name" /></td></tr>',
					'<tr><th><label for="id_language_0">Language:</label></th><td><ul class="radios">',
					'<li><label><input id="id_language_0" type="radio" name="language" value="P" /> Python</label></li>',
					'<li><label><input id="id_language_1" type="radio" name="language" value="J" /> Java</label></li>',
					'</ul></td></tr>'
				)
			),
			$form->as_table()
		));
		$this->assertTrue($this->_compare_html(
			implode(
				"\n", 
				array(
					'<li><label for="id_name">Name:</label> <input required="required" type="text" name="name" class="text form-control" id="id_name" /></li>',
					'<li><label for="id_language_0">Language:</label> <ul class="radios">',
					'<li><label><input id="id_language_0" type="radio" name="language" value="P" /> Python</label></li>',
					'<li><label><input id="id_language_1" type="radio" name="language" value="J" /> Java</label></li>',
					'</ul></li>'
				)
			),
			$form->as_ul()
		));
		$this->assertTrue($this->_compare_html(
			implode(
				"\n", 
				array(
					'<p><label for="id_name">Name:</label> <input required="required" type="text" name="name" class="text form-control" id="id_name" /></p>',
					'<p><label for="id_language_0">Language:</label> <ul class="radios">',
					'<li><label><input id="id_language_0" type="radio" name="language" value="P" /> Python</label></li>',
					'<li><label><input id="id_language_1" type="radio" name="language" value="J" /> Java</label></li>',
					'</ul></p>'
				)
			),
			$form->as_p()
		));
	}

	/**
	* Test formulare s MultipleChoiceField.
	*
	* NOTE: vyrazuju to, v PHP to mozna nerozjedu
	*/
	function test_form_with_mutiplechoicefield()
	{
		// MultipleChoiceField bez choices
		$form = new SongForm(array('auto_id'=>false));
		$field = $form->get_field('composers');
		$this->assertHtmlEquals(
			implode("\n", array('<select multiple="multiple" name="composers[]" class="form-control">', '</select>')),
			$field->as_widget()
		);
		// MultipleChoiceField s choices definovanymi v konstruktoru
		$form = new SongForm2(array('auto_id'=>false));
		$field = $form->get_field('composers');
		$this->assertHtmlEquals(
			implode("\n", array('<select multiple="multiple" name="composers[]" class="form-control">', '<option value="J">John Lennon</option>', '<option value="P">Paul McCartney</option>', '</select>')),
			$field->as_widget()
		);
		// formular naplneny vstupnimi daty
		$data = array('name'=>'Yesterday', 'composers'=>array('P'));
		$form = new SongForm2(array('data'=>$data, 'auto_id'=>false));
		$field = $form->get_field('name');
		$this->assertHtmlEquals(
			'<input required="required" type="text" name="name" value="Yesterday" class="text form-control" />',
			$field->as_widget()
		);
		$field = $form->get_field('composers');
		$this->assertHtmlEquals(
			implode("\n", array('<select multiple="multiple" name="composers[]" class="form-control">', '<option value="J">John Lennon</option>', '<option value="P" selected="selected">Paul McCartney</option>', '</select>')),
			$field->as_widget()
		);
		// Pokud se MultipleChoiceField vykresluje jako hidden prvek, jde o specialni pripad:
		// pro vice hodnot se vykresli hned nekolik hidden prvku, se stejnym nazvem
		$data = array('name'=>'Yesterday', 'composers'=>array('P'));
		$form = new SongForm2(array('data'=>$data, 'auto_id'=>false));
		$field = $form->get_field('composers');
		$this->assertHtmlEquals(
			'<input name="composers[]" type="hidden" value="P" />',
			$field->as_hidden()
		);
		$data = array('name'=>'Yesterday', 'composers'=>array('P', 'J'));
		$form = new SongForm2(array('data'=>$data, 'auto_id'=>false));
		$field = $form->get_field('composers');
		$this->assertEquals(
			implode("\n", array('<input name="composers[]" type="hidden" value="P" />', '<input name="composers[]" type="hidden" value="J" />')),
			$field->as_hidden()
		);
		// Vykresleni MultipleChoiceField s pomoci widgetu CheckboxSelectMultiple
		$form = new SongForm3(array('auto_id'=>false));
		$field = $form->get_field('composers');
		$this->assertHtmlEquals(
			implode("\n", array('<ul class="checkboxes">', '<li class="checkbox"><label class="control-label"><input name="composers[]" type="checkbox" value="J" /> John Lennon</label></li>', '<li class="checkbox"><label class="control-label"><input name="composers[]" type="checkbox" value="P" /> Paul McCartney</label></li>', '</ul>')),
			$field->as_widget()
		);
		$form = new SongForm3(array('data'=>array('composers'=>array('J')), 'auto_id'=>false));
		$field = $form->get_field('composers');
		$this->assertHtmlEquals(
			implode("\n", array('<ul class="checkboxes">', '<li class="checkbox"><label class="control-label"><input name="composers[]" type="checkbox" checked="checked" value="J" /> John Lennon</label></li>', '<li class="checkbox"><label class="control-label"><input name="composers[]" type="checkbox" value="P" /> Paul McCartney</label></li>', '</ul>')),
			$field->as_widget()
		);
		$form = new SongForm3(array('data'=>array('composers'=>array('J', 'P')), 'auto_id'=>false));
		$field = $form->get_field('composers');
		$this->assertHtmlEquals(
			implode("\n", array('<ul class="checkboxes">', '<li class="checkbox"><label class="control-label"><input name="composers[]" type="checkbox" checked="checked" value="J" /> John Lennon</label></li>', '<li class="checkbox"><label class="control-label"><input name="composers[]" type="checkbox" checked="checked" value="P" /> Paul McCartney</label></li>', '</ul>')),
			$field->as_widget()
		);
		// id jednotlivych checkboxu jsou specialni pripad -- jsou k nim
		// pripojeny ciselne indexy, aby byla zachovana jedinecnost nazvu
		$form = new SongForm3(array('auto_id'=>'%s_id'));
		$field = $form->get_field('composers');
		$this->assertHtmlEquals(
			implode("\n", array('<ul class="checkboxes">', '<li class="checkbox"><label class="control-label"><input id="composers_id_0" name="composers[]" type="checkbox" value="J" /> John Lennon</label></li>', '<li class="checkbox"><label class="control-label"><input id="composers_id_1" name="composers[]" type="checkbox" value="P" /> Paul McCartney</label></li>', '</ul>')),
			$field->as_widget()
		);
		// spravne zadana data a prazdne error pole
		$data = array('name'=>'Yesterday', 'composers'=>array('J', 'P'));
		$form = new SongForm3(array('data'=>$data));
		$this->assertEquals(array(), $form->get_errors());
		$data = array('name'=>'Yesterday', 'composers'=>array('J', 'P'));

		//
		$data = array('name'=>'Yesterday');
		$form = new SongForm3(array('data'=>$data, 'auto_id'=>false));
		$this->assertEquals(
			array("composers"=>array("Please, choose the right options.")),
			$form->get_errors()
		);

		$data = array('name'=>'Yesterday', 'composers'=>array('J'));
		$form = new SongForm3(array('data'=>$data, 'auto_id'=>false));
		$this->assertEquals(array(), $form->get_errors());
		$this->assertEquals(
			array('composers'=>array('J'), 'name'=>'Yesterday'), 
			$form->cleaned_data
		);

		$data = array('name'=>'Yesterday', 'composers'=>array('J', 'P'));
		$form = new SongForm3(array('data'=>$data, 'auto_id'=>false));
		$this->assertEquals(array(), $form->get_errors());
		$this->assertEquals(
			array('composers'=>array('J', 'P'), 'name'=>'Yesterday'), 
			$form->cleaned_data
		);
	}


	// ===========================================================
	// ===========================================================
	//   Validace formularovych poli
	// ===========================================================
	// ===========================================================

	/**
	* Ve formulari lze nadefinovat custom validaci pro konkretni pole.
	* Staci napsat metodu, ktera bude mit nazev clean_XXX(), kde XXX
	* je nazev pole.
	*/
	function test_form_with_custom_field_validation()
	{
		$form = new UserRegistration(array('auto_id'=>false));
		$this->assertEquals(array(), $form->get_errors());

		$form = new UserRegistration(array('data'=>array(), 'auto_id'=>false));
		$this->assertEquals(
			array(
				'username' => array('This field is required.'),
				'password1' => array('This field is required.'), 
				'password2' => array('This field is required.')
			), 
			$form->get_errors()
		);

		$data = array('username'=>'adrian', 'password1'=>'foo', 'password2'=>'bar');
		$form = new UserRegistration(array('data'=>$data, 'auto_id'=>false));
		$this->assertEquals(
			array(
				'password2' => array('Please make sure your passwords match.')
			), 
			$form->get_errors()
		);

		$data = array('username'=>'adrian', 'password1'=>'foo', 'password2'=>'foo');
		$form = new UserRegistration(array('data'=>$data, 'auto_id'=>false));
		$this->assertEquals(array(), $form->get_errors());
		$this->assertEquals(
			array('username'=>'adrian', 'password1'=>'foo', 'password2'=>'foo'), 
			$form->cleaned_data
		);
	}

	/**
	* Formular se da otestovat taky jako celek, s pomoci metody clean().
	*/
	function test_form_with_custom_form_validation()
	{
		$form = new UserRegistration2(array('auto_id'=>false));
		$this->assertEquals(array(), $form->get_errors());

		$form = new UserRegistration2(array('data'=>array(), 'auto_id'=>false));
		$this->assertEquals(
			implode("\n", array(
				'<tr><th>Username:</th><td><ul class="errorlist"><li>This field is required.</li></ul><input maxlength="10" required="required" type="text" name="username" class="text form-control" /></td></tr>',
				'<tr><th>Password1:</th><td><ul class="errorlist"><li>This field is required.</li></ul><input class="text" required="required" type="password" name="password1" /></td></tr>',
				'<tr><th>Password2:</th><td><ul class="errorlist"><li>This field is required.</li></ul><input class="text" required="required" type="password" name="password2" /></td></tr>'
			)), 
			$form->as_table()
		);
		$this->assertEquals(
			array(
				'username' => array('This field is required.'), 
				'password1' => array('This field is required.'), 
				'password2' => array('This field is required.')
			), 
			$form->get_errors()
		);

		$data = array('username'=>'adrian', 'password1'=>'foo', 'password2'=>'bar');
		$form = new UserRegistration2(array('data'=>$data, 'auto_id'=>false));
		$this->assertEquals(
			array(
				'__all__' => array('Please make sure your passwords match.'), 
			), 
			$form->get_errors()
		);
		$this->assertEquals(
			implode("\n", array(
				'<tr><td colspan="2"><ul class="errorlist"><li>Please make sure your passwords match.</li></ul></td></tr>',
				'<tr><th>Username:</th><td><input maxlength="10" required="required" type="text" name="username" class="text form-control" value="adrian" /></td></tr>',
				'<tr><th>Password1:</th><td><input class="text" required="required" type="password" name="password1" value="foo" /></td></tr>',
				'<tr><th>Password2:</th><td><input class="text" required="required" type="password" name="password2" value="bar" /></td></tr>'
			)), 
			$form->as_table()
		);
		$this->assertEquals(
			implode("\n", array(
				'<li><ul class="errorlist"><li>Please make sure your passwords match.</li></ul></li>',
				'<li>Username: <input maxlength="10" required="required" type="text" name="username" class="text form-control" value="adrian" /></li>',
				'<li>Password1: <input class="text" required="required" type="password" name="password1" value="foo" /></li>',
				'<li>Password2: <input class="text" required="required" type="password" name="password2" value="bar" /></li>'
			)), 
			$form->as_ul()
		);
		$data = array('username'=>'adrian', 'password1'=>'foo', 'password2'=>'foo');
		$form = new UserRegistration2(array('data'=>$data, 'auto_id'=>false));
		$this->assertEquals(array(), $form->get_errors());
		$this->assertEquals(
			array('username'=>'adrian', 'password1'=>'foo', 'password2'=>'foo'), 
			$form->cleaned_data
		);
	}

	/**
	* Testy dynamickeho vytvareni formularu a zmeny
	* atributu jiz zadefinovanych polozek.
	*/
	function test_dynamic_form_creation_and_changes()
	{
		// V Djangu se prvky formulare definji jako atributy tridy.
		// Nektere z testu se tykaji toho, ze je mozne polozky formulare
		// generovat i dynamicky v jeho konstruktoru.
		// Ja to v podstate delam jenom takto -- v metode set_up()
		// volam metodu add_field a tou definuji jednotlive polozky.
		
		// Instance formularu se navzajem neovlivnuji
		// (stejna trida, podle parametru $field_list se pokazde 
		// vygeneruji jina pole)
		$field_list = array(
			'field1' => new CharField(), 
			'field2' => new CharField()
		);
		$form = new MyForm($field_list);
		$this->assertEquals(
			implode("\n", array(
				'<tr><th>Field1:</th><td><input required="required" type="text" name="field1" class="text form-control" /></td></tr>',
				'<tr><th>Field2:</th><td><input required="required" type="text" name="field2" class="text form-control" /></td></tr>'
			)), 
			$form->as_table()
		);

		$field_list = array(
			'field3' => new CharField(), 
			'field4' => new CharField()
		);
		$form = new MyForm($field_list);
		$this->assertEquals(
			implode("\n", array(
				'<tr><th>Field3:</th><td><input required="required" type="text" name="field3" class="text form-control" /></td></tr>',
				'<tr><th>Field4:</th><td><input required="required" type="text" name="field4" class="text form-control" /></td></tr>'
			)), 
			$form->as_table()
		);

		$field_list = array(
			'field5' => new CharField(), 
			'field6' => new CharField()
		);
		$form2 = new MyForm($field_list);
		$this->assertEquals(
			implode("\n", array(
				'<tr><th>Field5:</th><td><input required="required" type="text" name="field5" class="text form-control" /></td></tr>',
				'<tr><th>Field6:</th><td><input required="required" type="text" name="field6" class="text form-control" /></td></tr>'
			)), 
			$form2->as_table()
		);

		// jeste jeden test na neovlivnovani...
		$field_list = array(
			'field1' => new CharField(), 
			'field2' => new CharField()
		);
		$form = new MyForm2($field_list);
		$this->assertEquals(
			implode("\n", array(
				'<tr><th>Default field 1:</th><td><input required="required" type="text" name="default_field_1" class="text form-control" /></td></tr>',
				'<tr><th>Default field 2:</th><td><input required="required" type="text" name="default_field_2" class="text form-control" /></td></tr>',
				'<tr><th>Field1:</th><td><input required="required" type="text" name="field1" class="text form-control" /></td></tr>',
				'<tr><th>Field2:</th><td><input required="required" type="text" name="field2" class="text form-control" /></td></tr>'
			)), 
			$form->as_table()
		);

		$field_list = array(
			'field3' => new CharField(), 
			'field4' => new CharField()
		);
		$form = new MyForm2($field_list);
		$this->assertEquals(
			implode("\n", array(
				'<tr><th>Default field 1:</th><td><input required="required" type="text" name="default_field_1" class="text form-control" /></td></tr>',
				'<tr><th>Default field 2:</th><td><input required="required" type="text" name="default_field_2" class="text form-control" /></td></tr>',
				'<tr><th>Field3:</th><td><input required="required" type="text" name="field3" class="text form-control" /></td></tr>',
				'<tr><th>Field4:</th><td><input required="required" type="text" name="field4" class="text form-control" /></td></tr>'
			)), 
			$form->as_table()
		);

		// Prime zmeny provedene nad konkretnimi poli se rovnez
		// navzajem neovlivnji
		$form = new Person2($names_required=false);
		$field = $form->get_field('first_name');
		$this->assertFalse($field->field->required);
		$this->assertEquals(array(), $field->field->widget->attrs);
		$field = $form->get_field('last_name');
		$this->assertFalse($field->field->required);
		$this->assertEquals(array(), $field->field->widget->attrs);

		$form = new Person2($names_required=true);
		$field = $form->get_field('first_name');
		$this->assertTrue($field->field->required);
		$this->assertEquals(array('class'=>'required'), $field->field->widget->attrs);
		$field = $form->get_field('last_name');
		$this->assertTrue($field->field->required);
		$this->assertEquals(array('class'=>'required'), $field->field->widget->attrs);

		$form2 = new Person2($names_required=false);
		$field = $form2->get_field('first_name');
		$this->assertFalse($field->field->required);
		$this->assertEquals(array(), $field->field->widget->attrs);
		$field = $form2->get_field('last_name');
		$this->assertFalse($field->field->required);
		$this->assertEquals(array(), $field->field->widget->attrs);

		// ...a jeste jeden test s nastavenim jinych parametru
		$form = new Person3($name_max_length=null);
		$field = $form->get_field('first_name');
		$this->assertEquals(30, $field->field->max_length);
		$field = $form->get_field('last_name');
		$this->assertEquals(30, $field->field->max_length);

		$form = new Person3($name_max_length=20);
		$field = $form->get_field('first_name');
		$this->assertEquals(20, $field->field->max_length);
		$field = $form->get_field('last_name');
		$this->assertEquals(20, $field->field->max_length);

		$form2 = new Person3($name_max_length=null);
		$field = $form2->get_field('first_name');
		$this->assertEquals(30, $field->field->max_length);
		$field = $form2->get_field('last_name');
		$this->assertEquals(30, $field->field->max_length);
	}

	/**
	* Testy chovani hidden prvku ve formulari.
	*/
	function test_hidden_fields()
	{
		// Pro hidden polozky se ve formulari nevykresluje label,
		// a jsou prilepeny k posledni polozce formulare.
		$form = new Person4(array('auto_id'=>false));
		$this->assertEquals(
			implode("\n", array(
				'<tr><th>First name:</th><td><input required="required" type="text" name="first_name" class="text form-control" /></td></tr>',
				'<tr><th>Last name:</th><td><input required="required" type="text" name="last_name" class="text form-control" /></td></tr>',
				'<tr><th>Age:</th><td><input required="required" type="number" name="age" class="number text form-control" /><input type="hidden" name="hidden_text" /></td></tr>'
			)), 
			$form->as_table()
		);
		$this->assertEquals(
			implode("\n", array(
				'<li>First name: <input required="required" type="text" name="first_name" class="text form-control" /></li>',
				'<li>Last name: <input required="required" type="text" name="last_name" class="text form-control" /></li>',
				'<li>Age: <input required="required" type="number" name="age" class="number text form-control" /><input type="hidden" name="hidden_text" /></li>'
			)), 
			$form->as_ul()
		);
		$this->assertEquals(
			implode("\n", array(
				'<p>First name: <input required="required" type="text" name="first_name" class="text form-control" /></p>',
				'<p>Last name: <input required="required" type="text" name="last_name" class="text form-control" /></p>',
				'<p>Age: <input required="required" type="number" name="age" class="number text form-control" /><input type="hidden" name="hidden_text" /></p>'
			)), 
			$form->as_p()
		);

		// pokud se nastavi auto_id, hidden prvky jej taky obdrzi, ale label se stale nezobrazi
		$form = new Person4(array('auto_id'=>'id_%s'));
		$this->assertEquals(
			implode("\n", array(
				'<tr><th><label for="id_first_name">First name:</label></th><td><input required="required" type="text" name="first_name" class="text form-control" id="id_first_name" /></td></tr>',
				'<tr><th><label for="id_last_name">Last name:</label></th><td><input required="required" type="text" name="last_name" class="text form-control" id="id_last_name" /></td></tr>',
				'<tr><th><label for="id_age">Age:</label></th><td><input required="required" type="number" name="age" class="number text form-control" id="id_age" /><input type="hidden" name="hidden_text" id="id_hidden_text" /></td></tr>'
			)), 
			$form->as_table()
		);
		$this->assertEquals(
			implode("\n", array(
				'<li><label for="id_first_name">First name:</label> <input required="required" type="text" name="first_name" class="text form-control" id="id_first_name" /></li>',
				'<li><label for="id_last_name">Last name:</label> <input required="required" type="text" name="last_name" class="text form-control" id="id_last_name" /></li>',
				'<li><label for="id_age">Age:</label> <input required="required" type="number" name="age" class="number text form-control" id="id_age" /><input type="hidden" name="hidden_text" id="id_hidden_text" /></li>'
			)), 
			$form->as_ul()
		);
		$this->assertEquals(
			implode("\n", array(
				'<p><label for="id_first_name">First name:</label> <input required="required" type="text" name="first_name" class="text form-control" id="id_first_name" /></p>',
				'<p><label for="id_last_name">Last name:</label> <input required="required" type="text" name="last_name" class="text form-control" id="id_last_name" /></p>',
				'<p><label for="id_age">Age:</label> <input required="required" type="number" name="age" class="number text form-control" id="id_age" /><input type="hidden" name="hidden_text" id="id_hidden_text" /></p>'
			)), 
			$form->as_p()
		);

		// Pokud ma HiddenInput chybu, zobrazi se hlaska nad formularem.
		$data = array('first_name'=>'John', 'last_name'=>'Lennon', 'age'=>'12');
		$form = new Person4(array('data'=>$data, 'auto_id'=>false));
		$this->assertEquals(
			implode("\n", array(
				'<tr><td colspan="2"><ul class="errorlist"><li>(Hidden field hidden_text) This field is required.</li></ul></td></tr>',
				'<tr><th>First name:</th><td><input required="required" type="text" name="first_name" class="text form-control" value="John" /></td></tr>',
				'<tr><th>Last name:</th><td><input required="required" type="text" name="last_name" class="text form-control" value="Lennon" /></td></tr>',
				'<tr><th>Age:</th><td><input required="required" type="number" name="age" class="number text form-control" value="12" /><input type="hidden" name="hidden_text" /></td></tr>'
			)), 
			$form->as_table()
		);
		$this->assertEquals(
			implode("\n", array(
				'<li><ul class="errorlist"><li>(Hidden field hidden_text) This field is required.</li></ul></li>',
				'<li>First name: <input required="required" type="text" name="first_name" class="text form-control" value="John" /></li>',
				'<li>Last name: <input required="required" type="text" name="last_name" class="text form-control" value="Lennon" /></li>',
				'<li>Age: <input required="required" type="number" name="age" class="number text form-control" value="12" /><input type="hidden" name="hidden_text" /></li>'
			)), 
			$form->as_ul()
		);
		$this->assertEquals(
			implode("\n", array(
				'<ul class="errorlist"><li>(Hidden field hidden_text) This field is required.</li></ul>',
				'<p>First name: <input required="required" type="text" name="first_name" class="text form-control" value="John" /></p>',
				'<p>Last name: <input required="required" type="text" name="last_name" class="text form-control" value="Lennon" /></p>',
				'<p>Age: <input required="required" type="number" name="age" class="number text form-control" value="12" /><input type="hidden" name="hidden_text" /></p>'
			)), 
			$form->as_p()
		);

		// Samozrejme je mozne mit formular slozeny pouze s hidden policek
		$form = new TestForm(array('auto_id'=>false));
		$this->assertEquals(
			'<input type="hidden" name="foo" /><input type="hidden" name="bar" />',
			$form->as_table()
		);
		$this->assertEquals(
			'<input type="hidden" name="foo" /><input type="hidden" name="bar" />',
			$form->as_ul()
		);
		$this->assertEquals(
			'<input type="hidden" name="foo" /><input type="hidden" name="bar" />',
			$form->as_p()
		);
	}

	/**
	* Overeni poradi vykreslovani policek formulare.
	*/
	function test_fields_order()
	{
		// Polozky formulare jsou zobrazeny ve stejnem poradi,
		// jak byly definovany
		$form = new TestForm2(array('auto_id'=>false));
		$this->assertEquals(
			implode("\n", array(
				'<tr><th>Field1:</th><td><input required="required" type="text" name="field1" class="text form-control" /></td></tr>',
				'<tr><th>Field2:</th><td><input required="required" type="text" name="field2" class="text form-control" /></td></tr>',
				'<tr><th>Field3:</th><td><input required="required" type="text" name="field3" class="text form-control" /></td></tr>',
				'<tr><th>Field4:</th><td><input required="required" type="text" name="field4" class="text form-control" /></td></tr>',
				'<tr><th>Field5:</th><td><input required="required" type="text" name="field5" class="text form-control" /></td></tr>',
				'<tr><th>Field6:</th><td><input required="required" type="text" name="field6" class="text form-control" /></td></tr>',
				'<tr><th>Field7:</th><td><input required="required" type="text" name="field7" class="text form-control" /></td></tr>',
				'<tr><th>Field8:</th><td><input required="required" type="text" name="field8" class="text form-control" /></td></tr>',
				'<tr><th>Field9:</th><td><input required="required" type="text" name="field9" class="text form-control" /></td></tr>',
				'<tr><th>Field10:</th><td><input required="required" type="text" name="field10" class="text form-control" /></td></tr>',
				'<tr><th>Field11:</th><td><input required="required" type="text" name="field11" class="text form-control" /></td></tr>',
				'<tr><th>Field12:</th><td><input required="required" type="text" name="field12" class="text form-control" /></td></tr>',
				'<tr><th>Field13:</th><td><input required="required" type="text" name="field13" class="text form-control" /></td></tr>',
				'<tr><th>Field14:</th><td><input required="required" type="text" name="field14" class="text form-control" /></td></tr>'
			)), 
			$form->as_table()
		);
	}

	/**
	* Nektere atributy poli (fields) maji vliv na vykreslovani widgetu
	* (na jejich HTML atributy).
	*/
	function test_fields_attributes()
	{
		// Pokud se nastavi pro CharField atribut max_length,
		// a vykresli se pres widget TextInput nebo PasswordInput,
		// pak widget bude obsahovat HTML atribut maxlength.
		$form = new UserRegistration3(array('auto_id'=>false));
		$this->assertEquals(
			implode("\n", array(
				'<li>Username: <input maxlength="10" required="required" type="text" name="username" class="text form-control" /></li>',
				'<li>Password: <input class="text" maxlength="10" required="required" type="password" name="password" /></li>',
				'<li>Realname: <input maxlength="10" required="required" type="text" name="realname" class="text form-control" /></li>',
				'<li>Address: <input required="required" type="text" name="address" class="text form-control" /></li>'
			)), 
			$form->as_ul()
		);

		// Pokud si v nejakem widgetu zadefinuju parametr maxlength,
		// pak bude prebity stejnym parametrem z definice pole (field)
		$form = new UserRegistration4(array('auto_id'=>false));
		$this->assertEquals(
			implode("\n", array(
				'<li>Username: <input maxlength="10" required="required" type="text" name="username" class="text form-control" /></li>',
				'<li>Password: <input class="text" maxlength="10" required="required" type="password" name="password" /></li>',
			)), 
			$form->as_ul()
		);
	}

	/**
	* Test chovani labels.
	*/
	function test_form_labels()
	{
		// Policko (field) muze mit nadefinovany label.
		// Pokud jej nema, je automaticky vytvoren z jeho nazvu.
		$form = new UserRegistration5(array('auto_id'=>false));
		$this->assertEquals(
			implode("\n", array(
				'<li>Your username: <input maxlength="10" required="required" type="text" name="username" class="text form-control" /></li>',
				'<li>Password1: <input class="text" required="required" type="password" name="password1" /></li>',
				'<li>Password (again): <input class="text" required="required" type="password" name="password2" /></li>'
			)),
			$form->as_ul()
		);

		// Labely budou koncit dvouteckou jen v tom pripade, 
		// ze posledni znak neni :?.!
		$form = new Questions(array('auto_id'=>false));
		$this->assertEquals(
			implode("\n", array(
				'<p>The first question: <input required="required" type="text" name="q1" class="text form-control" /></p>',
				'<p>What is your name? <input required="required" type="text" name="q2" class="text form-control" /></p>',
				'<p>The answer to life is: <input required="required" type="text" name="q3" class="text form-control" /></p>',
				'<p>Answer this question! <input required="required" type="text" name="q4" class="text form-control" /></p>',
				'<p>The last question. Period. <input required="required" type="text" name="q5" class="text form-control" /></p>'
			)),
			$form->as_p()
		);
		$form = new Questions();
		$this->assertEquals(
			implode("\n", array(
				'<p><label for="id_q1">The first question:</label> <input required="required" type="text" name="q1" class="text form-control" id="id_q1" /></p>',
				'<p><label for="id_q2">What is your name?</label> <input required="required" type="text" name="q2" class="text form-control" id="id_q2" /></p>',
				'<p><label for="id_q3">The answer to life is:</label> <input required="required" type="text" name="q3" class="text form-control" id="id_q3" /></p>',
				'<p><label for="id_q4">Answer this question!</label> <input required="required" type="text" name="q4" class="text form-control" id="id_q4" /></p>',
				'<p><label for="id_q5">The last question. Period.</label> <input required="required" type="text" name="q5" class="text form-control" id="id_q5" /></p>'
			)),
			$form->as_p()
		);
		
		// Pokud se pro nejake pole nastavi label na prazdny retezec,
		// pro dane pole se <label> nevykresli.
		$form = new UserRegistration6(array('auto_id'=>false));
		$this->assertEquals(
			implode("\n", array(
				'<li> <input maxlength="10" required="required" type="text" name="username" class="text form-control" /></li>',
				'<li>Password: <input class="text" required="required" type="password" name="password" /></li>'
			)),
			$form->as_ul()
		);
		$form = new UserRegistration6(array('auto_id'=>'id_%s'));
		$this->assertEquals(
			implode("\n", array(
				'<li> <input maxlength="10" required="required" type="text" name="username" class="text form-control" id="id_username" /></li>',
				'<li><label for="id_password">Password:</label> <input class="text" required="required" type="password" name="password" id="id_password" /></li>'
			)),
			$form->as_ul()
		);
		
		// Pokud je label nadefinovan jako null, je odvozen z nazvu pole.
		$form = new UserRegistration7(array('auto_id'=>false));
		$this->assertEquals(
			implode("\n", array(
				'<li>Username: <input maxlength="10" required="required" type="text" name="username" class="text form-control" /></li>',
				'<li>Password: <input class="text" required="required" type="password" name="password" /></li>'
			)),
			$form->as_ul()
		);
		$form = new UserRegistration7(array('auto_id'=>'id_%s'));
		$this->assertEquals(
			implode("\n", array(
				'<li><label for="id_username">Username:</label> <input maxlength="10" required="required" type="text" name="username" class="text form-control" id="id_username" /></li>',
				'<li><label for="id_password">Password:</label> <input class="text" required="required" type="password" name="password" id="id_password" /></li>'
			)),
			$form->as_ul()
		);
	}

	/**
	* V konstruktoru se da zadat tzv. label_suffix, ktery zadefinuje posledni
	* znak v labelu.
	*/
	function test_label_suffix()
	{
		$form = new FavoriteForm(array('auto_id'=>false));
		$this->assertEquals(
			implode("\n", array(
				'<li>Favorite color? <input required="required" type="text" name="color" class="text form-control" /></li>',
				'<li>Favorite animal: <input required="required" type="text" name="animal" class="text form-control" /></li>'
			)),
			$form->as_ul()
		);

		$form = new FavoriteForm(array('auto_id'=>false, 'label_suffix'=>'?'));
		$this->assertEquals(
			implode("\n", array(
				'<li>Favorite color? <input required="required" type="text" name="color" class="text form-control" /></li>',
				'<li>Favorite animal? <input required="required" type="text" name="animal" class="text form-control" /></li>'
			)),
			$form->as_ul()
		);

		$form = new FavoriteForm(array('auto_id'=>false, 'label_suffix'=>''));
		$this->assertEquals(
			implode("\n", array(
				'<li>Favorite color? <input required="required" type="text" name="color" class="text form-control" /></li>',
				'<li>Favorite animal <input required="required" type="text" name="animal" class="text form-control" /></li>'
			)),
			$form->as_ul()
		);
	}

	/**
	* U kazdeho pole ve formulari se mohou nadefinovat tzv. initial_data, coz
	* je hodnota, kterou se pole predvyplni *pouze v pripade*, ze do formulari
	* nejsou nalite (POST) data.
	*/
	function test_initial_data()
	{
		// V tomto pripade formular neplnime POST daty, pole se vykresli s initial hodnotou
		$form = new UserRegistration8(array('auto_id'=>false));
		$this->assertEquals(
			implode("\n", array(
				'<li>Username: <input maxlength="10" required="required" type="text" name="username" class="text form-control" value="django" /></li>',
				'<li>Password: <input class="text" required="required" type="password" name="password" /></li>'
			)),
			$form->as_ul()
		);

		// V tomto pripade formular *plnime* POST daty, initial data se *nezobrazi*
		$form = new UserRegistration8(array('data'=>array(), 'auto_id'=>false));
		$this->assertEquals(
			implode("\n", array(
				'<li><ul class="errorlist"><li>This field is required.</li></ul>Username: <input maxlength="10" required="required" type="text" name="username" class="text form-control" /></li>',
				'<li><ul class="errorlist"><li>This field is required.</li></ul>Password: <input class="text" required="required" type="password" name="password" /></li>'
			)),
			$form->as_ul()
		);
		$form = new UserRegistration8(array('data'=>array('username'=>''), 'auto_id'=>false));
		$this->assertEquals(
			implode("\n", array(
				'<li><ul class="errorlist"><li>This field is required.</li></ul>Username: <input maxlength="10" required="required" type="text" name="username" class="text form-control" /></li>',
				'<li><ul class="errorlist"><li>This field is required.</li></ul>Password: <input class="text" required="required" type="password" name="password" /></li>'
			)),
			$form->as_ul()
		);
		$form = new UserRegistration8(array('data'=>array('username'=>'foo'), 'auto_id'=>false));
		$this->assertEquals(
			implode("\n", array(
				'<li>Username: <input maxlength="10" required="required" type="text" name="username" class="text form-control" value="foo" /></li>',
				'<li><ul class="errorlist"><li>This field is required.</li></ul>Password: <input class="text" required="required" type="password" name="password" /></li>'
			)),
			$form->as_ul()
		);

		// Pokud formular naplnim POST daty, a v tech nektera pole chybi, 
		// initial_data se nepouziji na jejich defaultni predvyplneni.
		// initial_data slouzi pouze na predvyplnovani formularu, do kterych
		// se POST data neposlou...
		$form = new UserRegistration8(array('data'=>array('password'=>'secret')));
		$this->assertEquals(
			array('username' => array('This field is required.')),
			$form->get_errors()
		);
	}

	/**
	* initial_data se daji definovat i v konstruktoru formulare.
	*/
	function test_dynamic_initial_data()
	{
		$form = new UserRegistration9(array('initial'=>array('username'=>'django'), 'auto_id'=>false));
		$this->assertEquals(
			implode("\n", array(
				'<li>Username: <input maxlength="10" required="required" type="text" name="username" class="text form-control" value="django" /></li>',
				'<li>Password: <input class="text" required="required" type="password" name="password" /></li>'
			)),
			$form->as_ul()
		);
		$form = new UserRegistration9(array('initial'=>array('username'=>'stephane'), 'auto_id'=>false));
		$this->assertEquals(
			implode("\n", array(
				'<li>Username: <input maxlength="10" required="required" type="text" name="username" class="text form-control" value="stephane" /></li>',
				'<li>Password: <input class="text" required="required" type="password" name="password" /></li>'
			)),
			$form->as_ul()
		);

		// Pokud se do formiku nalijou data (POST), stavaji se initial_data nepodstatne.
		$form = new UserRegistration9(array('data'=>array(), 'initial'=>array('username'=>'django'), 'auto_id'=>false));
		$this->assertEquals(
			implode("\n", array(
				'<li><ul class="errorlist"><li>This field is required.</li></ul>Username: <input maxlength="10" required="required" type="text" name="username" class="text form-control" /></li>',
				'<li><ul class="errorlist"><li>This field is required.</li></ul>Password: <input class="text" required="required" type="password" name="password" /></li>'
			)),
			$form->as_ul()
		);
		$form = new UserRegistration9(array('data'=>array('username'=>''), 'initial'=>array('username'=>'django'), 'auto_id'=>false));
		$this->assertEquals(
			implode("\n", array(
				'<li><ul class="errorlist"><li>This field is required.</li></ul>Username: <input maxlength="10" required="required" type="text" name="username" class="text form-control" /></li>',
				'<li><ul class="errorlist"><li>This field is required.</li></ul>Password: <input class="text" required="required" type="password" name="password" /></li>'
			)),
			$form->as_ul()
		);
		$form = new UserRegistration9(array('data'=>array('username'=>'foo'), 'initial'=>array('username'=>'django'), 'auto_id'=>false));
		$this->assertEquals(
			implode("\n", array(
				'<li>Username: <input maxlength="10" required="required" type="text" name="username" class="text form-control" value="foo" /></li>',
				'<li><ul class="errorlist"><li>This field is required.</li></ul>Password: <input class="text" required="required" type="password" name="password" /></li>'
			)),
			$form->as_ul()
		);

		// Pokud ve vstupnich (POST) datech nejake udaje chybi, z initial_dat se nepouziji.
		$form = new UserRegistration9(array('data'=>array('password'=>'secret'), 'initial'=>array('username'=>'django')));
		$this->assertEquals(
			array('username'=>array('This field is required.')),
			$form->get_errors()
		);
		$this->assertFalse($form->is_valid());

		// Pokud jsou initial_data definovana u pole ve formularove tride i ve 
		// volani konstruktoru, pouziji se ty z konstruktoru
		$form = new UserRegistration10(array('initial'=>array('username'=>'babik'), 'auto_id'=>false));
		$this->assertEquals(
			implode("\n", array(
				'<li>Username: <input maxlength="10" required="required" type="text" name="username" class="text form-control" value="babik" /></li>',
				'<li>Password: <input class="text" required="required" type="password" name="password" /></li>'
			)),
			$form->as_ul()
		);
		$this->assertFalse($form->is_valid());
	}

	/**
	* Testy napoved u formularovych poli.
	*/
	function test_help_text()
	{
		$form = new UserRegistration11(array('auto_id'=>false));
		$this->assertEquals(
			implode("\n", array(
				'<li>Username: <input maxlength="10" required="required" type="text" name="username" class="text form-control" /> e.g., user@example.com</li>',
				'<li>Password: <input class="text" required="required" type="password" name="password" /> Choose wisely.</li>'
			)),
			$form->as_ul()
		);
		$this->assertEquals(
			implode("\n", array(
				'<p>Username: <input maxlength="10" required="required" type="text" name="username" class="text form-control" /> e.g., user@example.com</p>',
				'<p>Password: <input class="text" required="required" type="password" name="password" /> Choose wisely.</p>'
			)),
			$form->as_p()
		);
		$this->assertEquals(
			implode("\n", array(
				'<tr><th>Username:</th><td><input maxlength="10" required="required" type="text" name="username" class="text form-control" /><br />e.g., user@example.com</td></tr>',
				'<tr><th>Password:</th><td><input class="text" required="required" type="password" name="password" /><br />Choose wisely.</td></tr>'
			)),
			$form->as_table()
		);

		// help_text se zobrazi nezavisle na tom, jestli je formular nality daty
		$form = new UserRegistration11(array('data'=>array('username'=>'foo'), 'auto_id'=>false));
		$this->assertEquals(
			implode("\n", array(
				'<li>Username: <input maxlength="10" required="required" type="text" name="username" class="text form-control" value="foo" /> e.g., user@example.com</li>',
				'<li><ul class="errorlist"><li>This field is required.</li></ul>Password: <input class="text" required="required" type="password" name="password" /> Choose wisely.</li>'
			)),
			$form->as_ul()
		);

		// help_text se pro hidden pole nezobrazuje
		$form = new UserRegistration12(array('auto_id'=>false));
		$this->assertEquals(
			implode("\n", array(
				'<li>Username: <input maxlength="10" required="required" type="text" name="username" class="text form-control" /> e.g., user@example.com</li>',
				'<li>Password: <input class="text" required="required" type="password" name="password" /><input type="hidden" name="next" value="/" /></li>'
			)),
			$form->as_ul()
		);
	}

	/**
	* Testy dedicnosti.
	*/
	function test_subclassing()
	{
		$form = new Person(array('auto_id'=>false));
		$this->assertEquals(
			implode("\n", array(
				'<li>First name: <input required="required" type="text" name="first_name" class="text form-control" /></li>',
				'<li>Last name: <input required="required" type="text" name="last_name" class="text form-control" /></li>',
				'<li>Age: <input required="required" type="number" name="age" class="number text form-control" /></li>'
			)),
			$form->as_ul()
		);
		$form = new Musician(array('auto_id'=>false));
		$this->assertEquals(
			implode("\n", array(
				'<li>First name: <input required="required" type="text" name="first_name" class="text form-control" /></li>',
				'<li>Last name: <input required="required" type="text" name="last_name" class="text form-control" /></li>',
				'<li>Age: <input required="required" type="number" name="age" class="number text form-control" /></li>',
				'<li>Instrument: <input required="required" type="text" name="instrument" class="text form-control" /></li>'
			)),
			$form->as_ul()
		);

		// NOTE: vicenasobna dedicnost v php4 nefunguje, tj. testy na ni vynechavam
	}

	/**
	* Otestuje chovani formularovych prefixu.
	*/
	function test_forms_prefixes()
	{
		$data = array(
			'person1_first_name' => 'John',
			'person1_last_name' => 'Lennon',
			'person1_age' => '12'
		);
		$form = new Person(array('data'=>$data, 'prefix'=>'person1'));
		$this->assertEquals(
			implode("\n", array(
				'<li><label for="id_person1_first_name">First name:</label> <input required="required" type="text" name="person1_first_name" class="text form-control" id="id_person1_first_name" value="John" /></li>',
				'<li><label for="id_person1_last_name">Last name:</label> <input required="required" type="text" name="person1_last_name" class="text form-control" id="id_person1_last_name" value="Lennon" /></li>',
				'<li><label for="id_person1_age">Age:</label> <input required="required" type="number" name="person1_age" class="number text form-control" id="id_person1_age" value="12" /></li>'
			)),
			$form->as_ul()
		);

		$field = $form->get_field('first_name');
		$this->assertEquals(
			'<input required="required" type="text" name="person1_first_name" class="text form-control" id="id_person1_first_name" value="John" />',
			$field->as_widget()
		);
		$field = $form->get_field('last_name');
		$this->assertEquals(
			'<input required="required" type="text" name="person1_last_name" class="text form-control" id="id_person1_last_name" value="Lennon" />',
			$field->as_widget()
		);
		$field = $form->get_field('age');
		$this->assertEquals(
			'<input required="required" type="number" name="person1_age" class="number text form-control" id="id_person1_age" value="12" />',
			$field->as_widget()
		);

		$this->assertEquals(array(), $form->get_errors());
		$this->assertTrue($form->is_valid());
		$this->assertEquals(
			array('first_name'=>'John', 'last_name'=>'Lennon', 'age'=>12),
			$form->cleaned_data
		);

		// nalijeme formular spatymi daty a otestujeme chovani error poli
		$data = array(
			'person1_first_name' => '',
			'person1_last_name' => '',
			'person1_age' => ''
		);
		$form = new Person(array('data'=>$data, 'prefix'=>'person1'));
		$this->assertEquals(
			array(
				'first_name' => array('This field is required.'),
				'last_name' => array('This field is required.'),
				'age' => array('This field is required.')

			), 
			$form->get_errors()
		);
		$field = $form->get_field('first_name');
		$this->assertEquals(
			array('This field is required.'),
			$field->errors()
		);
		$field = $form->get_field('person1_first_name');
		$this->assertNull($field);

		// V tomto priklade data nemaji prefix, ale formular jej vyzaduje
		// (takze formular tyto data nevidi).
		$form = new Person(array('data'=>$data, 'prefix'=>'person1'));
		$data = array(
			'first_name' => 'John',
			'last_name' => 'Lennon',
			'age' => '12'
		);
		$form = new Person(array('data'=>$data, 'prefix'=>'person1'));
		$this->assertEquals(
			array(
				'first_name' => array('This field is required.'),
				'last_name' => array('This field is required.'),
				'age' => array('This field is required.')

			), 
			$form->get_errors()
		);

		// Vstupni data (POST) muhou diky ruznym prefixum obsahovat 
		// nekolik ruznych dat pro totozny formular.
		$data = array(
			'person1_first_name' => 'John',
			'person1_last_name' => 'Lennon',
			'person1_age' => '12',
			'person2_first_name' => 'Jim',
			'person2_last_name' => 'Morrison',
			'person2_age' => '32'
		);
		$form1 = new Person(array('data'=>$data, 'prefix'=>'person1'));
		$this->assertTrue($form1->is_valid());
		$this->assertEquals(
			array(
				'first_name' => 'John', 
				'last_name' => 'Lennon',
				'age' => 12
			),
			$form1->cleaned_data
		);
		$form2 = new Person(array('data'=>$data, 'prefix'=>'person2'));
		$this->assertTrue($form2->is_valid());
		$this->assertEquals(
			array(
				'first_name' => 'Jim', 
				'last_name' => 'Morrison',
				'age' => 32
			),
			$form2->cleaned_data
		);

		// defaultne se mezi prefix a jmeno pole dava znak '-'. 
		// Jde to ale prevalit metodou add_prefix definovanou na urovni
		// formularove tridy.
		$form = new Person5(array('prefix'=>'foo'));
		$this->assertTrue($form2->is_valid());
		$this->assertEquals(
			implode("\n", array(
				'<li><label for="id_foo-prefix-first_name">First name:</label> <input required="required" type="text" name="foo-prefix-first_name" class="text form-control" id="id_foo-prefix-first_name" /></li>',
				'<li><label for="id_foo-prefix-last_name">Last name:</label> <input required="required" type="text" name="foo-prefix-last_name" class="text form-control" id="id_foo-prefix-last_name" /></li>',
				'<li><label for="id_foo-prefix-age">Age:</label> <input required="required" type="number" name="foo-prefix-age" class="number text form-control" id="id_foo-prefix-age" /></li>'
			)),
			$form->as_ul()
		);
		$data = array(
			'foo-prefix-first_name' => 'John',
			'foo-prefix-last_name' => 'Lennon',
			'foo-prefix-age' => '12'
		);
		$form = new Person5(array('data'=>$data, 'prefix'=>'foo'));
		$this->assertTrue($form->is_valid());
		$this->assertEquals(
			array(
				'first_name' => 'John', 
				'last_name' => 'Lennon',
				'age' => 12
			),
			$form->cleaned_data
		);
	}
}
