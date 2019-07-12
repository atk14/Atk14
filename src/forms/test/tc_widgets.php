<?php
/**
* Testy formularovych widgetu (HTML reprezentace poli).
*/


/**
* Funkce, ktera je volana z CheckboxInput. Slouzi k vyhodnoceni 
* $value a vraci true/false.
*/
function testovaci_funkce($value)
{
	return strpos($value, 'hello')===0;
}


class TcWidgets extends TcBase
{
	function test_render(){
		$w = new Input();
		$w->input_type = "text";

		$this->assertTrue($this->_compare_html('<input type="text" name="param" value="hello" />',$w->render("param","hello")));
		$this->assertTrue($this->_compare_html('<input type="text" name="param" />',$w->render("param","")));
		$this->assertTrue($this->_compare_html('<input type="text" name="param" />',$w->render("param",null)));
		$this->assertTrue($this->_compare_html('<input type="text" name="param" value="1" />',$w->render("param",true)));
		$this->assertTrue($this->_compare_html('<input type="text" name="param" value="0" />',$w->render("param",false)));
		$this->assertTrue($this->_compare_html('<input type="text" name="param" value="0" />',$w->render("param",0)));
		$this->assertTrue($this->_compare_html('<input type="text" name="param" value="0" />',$w->render("param","0")));
	}

	/**
	* Pomocna funkce, ktera overi realny vystup widgetu s ocekavanym dle
	* dodaneho pole $data.
	*/
	function _check($data)
	{
		foreach ($data as $widget_tests) {
			$widget = $widget_tests['widget'];
			foreach ($widget_tests['params'] as $test) {
				$result = $widget->render(
					$test['params']['name'],
					$test['params']['value'],
					$test['params']['options']
				);
				//$this->assertEquals($test['result'], $result);
				$this->assertTrue($this->_compare_html($test['result'],$result),"\n\n### expected ###\n$test[result]\n\n### actual ###\n$result\n\n");
			}
		}
	}

	/**
	* Overeni vykreslovani TextInput.
	*/
	function test_textinput()
	{
		$DATA = array(
			array(
				'widget' => new TextInput(),
				'params' => array(
					array(
						'params' => array('name'=>'email', 'value'=>'', 'options'=>array()),
						'result' => '<input type="text" name="email" class="text form-control" />'
					),
					array(
						'params' => array('name'=>'email', 'value'=>null, 'options'=>array()),
						'result' => '<input type="text" name="email" class="text form-control" />'
					),
					array(
						'params' => array('name'=>'email', 'value'=>'test@example.com', 'options'=>array()),
						'result' => '<input type="text" name="email" class="text form-control" value="test@example.com" />'
					),
					array(
						'params' => array('name'=>'email', 'value'=>'some "quoted" & ampersanded value', 'options'=>array()),
						'result' => '<input type="text" name="email" class="text form-control" value="some &quot;quoted&quot; &amp; ampersanded value" />'
					),
					array(
						'params' => array('name'=>'email', 'value'=>'test@example.com', 'options'=>array('attrs'=>array('class'=>'fun'))),
						'result' => '<input type="text" name="email" class="fun" value="test@example.com" />'
					),
				)
			),
			// atributy se muzou nasadit i v konstruktoru
			array(
				'widget' => new TextInput($options=array('attrs'=>array('class'=>'fun'))),
				'params' => array(
					array(
						'params' => array('name'=>'email', 'value'=>'', 'options'=>array()),
						'result' => '<input class="fun" type="text" name="email" />'
					),
					array(
						'params' => array('name'=>'email', 'value'=>'foo@example.com', 'options'=>array()),
						'result' => '<input class="fun" type="text" name="email" value="foo@example.com" />'
					),
				),
			),
			// specifictejsi options prebiji obecnejsi
			array(
				'widget' => new TextInput($options=array('attrs'=>array('class'=>'pretty'))),
				'params' => array(
					array(
						'params' => array('name'=>'email', 'value'=>'', 'options'=>array('attrs'=>array('class'=>'special'))),
						'result' => '<input class="special" type="text" name="email" />'
					),
				),
			)
		);

		$this->_check($DATA);
	}

	/**
	* Overeni vykreslovani PasswordInput.
	*/
	function test_passwordinput()
	{
		$DATA = array(
			array(
				'widget' => new PasswordInput(),
				'params' => array(
					array(
						'params' => array('name'=>'email', 'value'=>'', 'options'=>array()),
						'result' => '<input class="text" type="password" name="email" />'
					),
					array(
						'params' => array('name'=>'email', 'value'=>null, 'options'=>array()),
						'result' => '<input class="text" type="password" name="email" />'
					),
					array(
						'params' => array('name'=>'email', 'value'=>'test@example.com', 'options'=>array()),
						'result' => '<input class="text" type="password" name="email" value="test@example.com" />'
					),
					array(
						'params' => array('name'=>'email', 'value'=>'some "quoted" & ampersanded value', 'options'=>array()),
						'result' => '<input class="text" type="password" name="email" value="some &quot;quoted&quot; &amp; ampersanded value" />'
					),
					array(
						'params' => array('name'=>'email', 'value'=>'test@example.com', 'options'=>array('attrs'=>array('class'=>'fun'))),
						'result' => '<input class="fun" type="password" name="email" value="test@example.com" />'
					),
				)
			),
			// atributy se muzou nasadit i v konstruktoru
			array(
				'widget' => new PasswordInput($options=array('attrs'=>array('class'=>'fun'))),
				'params' => array(
					array(
						'params' => array('name'=>'email', 'value'=>'', 'options'=>array()),
						'result' => '<input class="fun" type="password" name="email" />'
					),
					array(
						'params' => array('name'=>'email', 'value'=>'foo@example.com', 'options'=>array()),
						'result' => '<input class="fun" type="password" name="email" value="foo@example.com" />'
					),
				),
			),
			// specifictejsi options prebiji obecnejsi
			array(
				'widget' => new PasswordInput($options=array('attrs'=>array('class'=>'pretty'))),
				'params' => array(
					array(
						'params' => array('name'=>'email', 'value'=>'', 'options'=>array('attrs'=>array('class'=>'special'))),
						'result' => '<input class="special" type="password" name="email" />'
					),
				),
			),
			// pres parametr render_value se da ovlivnit, jestli se ma vykreslovat hodnota
			array(
				'widget' => new PasswordInput($options=array('render_value'=>true)),
				'params' => array(
					array(
						'params' => array('name'=>'email', 'value'=>'secret', 'options'=>array()),
						'result' => '<input class="text" type="password" name="email" value="secret" />'
					),
				),
			),
			array(
				'widget' => new PasswordInput($options=array('render_value'=>false)),
				'params' => array(
					array(
						'params' => array('name'=>'email', 'value'=>'', 'options'=>array()),
						'result' => '<input class="text" type="password" name="email" />'
					),
					array(
						'params' => array('name'=>'email', 'value'=>null, 'options'=>array()),
						'result' => '<input class="text" type="password" name="email" />'
					),
					array(
						'params' => array('name'=>'email', 'value'=>'secret', 'options'=>array()),
						'result' => '<input class="text" type="password" name="email" />'
					),
				),
			),
			array(
				'widget' => new PasswordInput($options=array('render_value'=>false, 'attrs'=>array('class'=>'fun'))),
				'params' => array(
					array(
						'params' => array('name'=>'email', 'value'=>'secret', 'options'=>array()),
						'result' => '<input class="fun" type="password" name="email" />'
					),
				),
			)
		);

		$this->_check($DATA);
	}

	/**
	* Overeni vykreslovani HiddenInput.
	*/
	function test_hiddeninput()
	{
		$DATA = array(
			array(
				'widget' => new HiddenInput(),
				'params' => array(
					array(
						'params' => array('name'=>'email', 'value'=>'', 'options'=>array()),
						'result' => '<input type="hidden" name="email" />'
					),
					array(
						'params' => array('name'=>'email', 'value'=>null, 'options'=>array()),
						'result' => '<input type="hidden" name="email" />'
					),
					array(
						'params' => array('name'=>'email', 'value'=>'test@example.com', 'options'=>array()),
						'result' => '<input type="hidden" name="email" value="test@example.com" />'
					),
					array(
						'params' => array('name'=>'email', 'value'=>'some "quoted" & ampersanded value', 'options'=>array()),
						'result' => '<input type="hidden" name="email" value="some &quot;quoted&quot; &amp; ampersanded value" />'
					),
					array(
						'params' => array('name'=>'email', 'value'=>'test@example.com', 'options'=>array('attrs'=>array('class'=>'fun'))),
						'result' => '<input type="hidden" name="email" class="fun" value="test@example.com" />'
					),
				)
			),
			// atributy se muzou nasadit i v konstruktoru
			array(
				'widget' => new HiddenInput($options=array('attrs'=>array('class'=>'fun'))),
				'params' => array(
					array(
						'params' => array('name'=>'email', 'value'=>'', 'options'=>array()),
						'result' => '<input class="fun" type="hidden" name="email" />'
					),
					array(
						'params' => array('name'=>'email', 'value'=>'foo@example.com', 'options'=>array()),
						'result' => '<input class="fun" type="hidden" name="email" value="foo@example.com" />'
					),
				),
			),
			// specifictejsi options prebiji obecnejsi
			array(
				'widget' => new HiddenInput($options=array('attrs'=>array('class'=>'pretty'))),
				'params' => array(
					array(
						'params' => array('name'=>'email', 'value'=>'', 'options'=>array('attrs'=>array('class'=>'special'))),
						'result' => '<input class="special" type="hidden" name="email" />'
					),
				),
			),
			// hodnota zadana jako boolean se vykresli jako 0 nebo 1 
			// NOTE: toto je muj vymysl, Django generuje retezec True/False
			array(
				'widget' => new HiddenInput(),
				'params' => array(
					array(
						'params' => array('name'=>'get_spam', 'value'=>false, 'options'=>array()),
						'result' => '<input type="hidden" name="get_spam" value="0" />'
					),
					array(
						'params' => array('name'=>'get_spam', 'value'=>true, 'options'=>array()),
						'result' => '<input type="hidden" name="get_spam" value="1" />'
					),
				),
			),
		);

		$this->_check($DATA);
	}

	/**
	* Overeni vykreslovani MultipleHiddenInput.
	*
	* NOTE: tohle asi nepujde v PHP zrealizovat
	*/
/*
	function test_multiplehiddeninput()
	{
		$DATA = array(
			array(
				'widget' => new MultipleHiddenInput(),
				'params' => array(
					array(
						'params' => array('name'=>'email', 'value'=>array(), 'options'=>array()),
						'result' => ''
					),
					array(
						'params' => array('name'=>'email', 'value'=>null, 'options'=>array()),
						'result' => ''
					),
					array(
						'params' => array('name'=>'email', 'value'=>array('test@example.com'), 'options'=>array()),
						'result' => '<input name="email" type="hidden" value="test@example.com" />'
					),
					array(
						'params' => array('name'=>'email', 'value'=>array('some "quoted" & ampersanded value'), 'options'=>array()),
						'result' => '<input name="email" type="hidden" value="some &quot;quoted&quot; &amp; ampersanded value" />'
					),
					array(
						'params' => array('name'=>'email', 'value'=>array('test@example.com', 'foo@example.com'), 'options'=>array()),
						'result' => implode("\n", array('<input name="email" type="hidden" value="test@example.com" />', '<input name="email" type="hidden" value="foo@example.com" />'))
					),

					array(
						'params' => array('name'=>'email', 'value'=>array('test@example.com'), 'options'=>array('attrs'=>array('class'=>'fun'))),
						'result' => '<input class="fun" name="email" type="hidden" value="test@example.com" />'
					),
					array(
						'params' => array('name'=>'email', 'value'=>array('test@example.com', 'foo@example.com'), 'options'=>array('attrs'=>array('class'=>'fun'))),
						'result' => implode("\n", array('<input class="fun" name="email" type="hidden" value="test@example.com" />', '<input class="fun" name="email" type="hidden" value="foo@example.com" />'))
					),
				)
			),
			// atributy definovane v konstruktoru
			array(
				'widget' => new MultipleHiddenInput($options=array('attrs'=>array('class'=>'fun'))),
				'params' => array(
					array(
						'params' => array('name'=>'email', 'value'=>array(), 'options'=>array()),
						'result' => ''
					),
					array(
						'params' => array('name'=>'email', 'value'=>array('foo@example.com'), 'options'=>array()),
						'result' => '<input class="fun" name="email" type="hidden" value="foo@example.com" />'
					),
					array(
						'params' => array('name'=>'email', 'value'=>array('foo@example.com', 'test@example.com'), 'options'=>array()),
						'result' => implode("\n", array('<input class="fun" name="email" type="hidden" value="foo@example.com" />', '<input class="fun" name="email" type="hidden" value="test@example.com" />'))
					),
				)
			),
			// atributy definovane u metody render maji prednost
			array(
				'widget' => new MultipleHiddenInput($options=array('attrs'=>array('class'=>'pretty'))),
				'params' => array(
					array(
						'params' => array('name'=>'email', 'value'=>array('foo@example.com'), 'options'=>array('attrs'=>array('class'=>'special'))),
						'result' => '<input class="special" name="email" type="hidden" value="foo@example.com" />'
					),
				)
			),
		);

		$this->_check($DATA);
	}
*/

	/**
	* Overeni vykreslovani Textarea.
	*/
	function test_textarea()
	{
		$DATA = array(
			array(
				'widget' => new TextArea(),
				'params' => array(
					array(
						'params' => array('name'=>'msg', 'value'=>'', 'options'=>array()),
						'result' => '<textarea cols="40" rows="10" name="msg" class="form-control"></textarea>'
					),
					array(
						'params' => array('name'=>'msg', 'value'=>null, 'options'=>array()),
						'result' => '<textarea cols="40" rows="10" name="msg" class="form-control"></textarea>'
					),
					array(
						'params' => array('name'=>'msg', 'value'=>'value', 'options'=>array()),
						'result' => '<textarea cols="40" rows="10" name="msg" class="form-control">value</textarea>'
					),
					array(
						'params' => array('name'=>'msg', 'value'=>'some "quoted" & ampersanded value', 'options'=>array()),
						'result' => '<textarea cols="40" rows="10" name="msg" class="form-control">some &quot;quoted&quot; &amp; ampersanded value</textarea>'
					),
					array(
						'params' => array('name'=>'msg', 'value'=>'value', 'options'=>array('attrs'=>array('class'=>'pretty', 'rows'=>20))),
						'result' => '<textarea cols="40" rows="20" class="pretty" name="msg">value</textarea>'
					),
				)
			),
			// atributy se muzou nasadit i v konstruktoru
			array(
				'widget' => new TextArea($options=array('attrs'=>array('class'=>'pretty'))),
				'params' => array(
					array(
						'params' => array('name'=>'msg', 'value'=>'', 'options'=>array()),
						'result' => '<textarea cols="40" rows="10" class="pretty" name="msg"></textarea>'
					),
					array(
						'params' => array('name'=>'msg', 'value'=>'example', 'options'=>array()),
						'result' => '<textarea cols="40" rows="10" class="pretty" name="msg">example</textarea>'
					),
				),
			),
			// specifictejsi options prebiji obecnejsi
			array(
				'widget' => new TextArea($options=array('attrs'=>array('class'=>'pretty'))),
				'params' => array(
					array(
						'params' => array('name'=>'msg', 'value'=>'', 'options'=>array('attrs'=>array('class'=>'special'))),
						'result' => '<textarea cols="40" rows="10" class="special" name="msg"></textarea>'
					),
				),
			),
		);

		$this->_check($DATA);
	}

	/**
	* Overeni vykreslovani CheckboxInput.
	*/
	function test_checkboxinput()
	{
		$DATA = array(
			array(
				'widget' => new CheckboxInput(),
				'params' => array(
					array(
						'params' => array('name'=>'is_cool', 'value'=>'', 'options'=>array()),
						'result' => '<input type="checkbox" name="is_cool" />'
					),
					array(
						'params' => array('name'=>'is_cool', 'value'=>null, 'options'=>array()),
						'result' => '<input type="checkbox" name="is_cool" />'
					),
					array(
						'params' => array('name'=>'is_cool', 'value'=>false, 'options'=>array()),
						'result' => '<input type="checkbox" name="is_cool" />'
					),
					array(
						'params' => array('name'=>'is_cool', 'value'=>true, 'options'=>array()),
						'result' => '<input type="checkbox" name="is_cool" checked="checked" />'
					),
					// pouziti jakekoliv jine hodnoty nez '', null, true, false
					// vykresli widget jako checked a nastavi value na hodnotu
					array(
						'params' => array('name'=>'is_cool', 'value'=>'foo', 'options'=>array()),
						'result' => '<input type="checkbox" name="is_cool" checked="checked" value="foo" />'
					),
					array(
						'params' => array('name'=>'is_cool', 'value'=>false, 'options'=>array('attrs'=>array('class'=>'pretty'))),
						'result' => '<input class="pretty" type="checkbox" name="is_cool" />'
					),
				)
			),
			// atributy se muzou nasadit i v konstruktoru
			array(
				'widget' => new CheckboxInput($options=array('attrs'=>array('class'=>'pretty'))),
				'params' => array(
					array(
						'params' => array('name'=>'is_cool', 'value'=>'', 'options'=>array()),
						'result' => '<input class="pretty" type="checkbox" name="is_cool" />'
					),
					array(
						'params' => array('name'=>'is_cool', 'value'=>'', 'options'=>array('attrs'=>array('class'=>'special'))),
						'result' => '<input class="special" type="checkbox" name="is_cool" />'
					),
				),
			),
			// v konstruktoru se muze specifikovat parametr check_test, coz je jmeno
			// funkce, ktera provedete test, jestli zadana hodnota je true nebo false
			array(
				'widget' => new CheckboxInput($options=array('check_test'=>'testovaci_funkce')),
				'params' => array(
					array(
						'params' => array('name'=>'greeting', 'value'=>'', 'options'=>array()),
						'result' => '<input type="checkbox" name="greeting" />'
					),
					array(
						'params' => array('name'=>'greeting', 'value'=>'hello there', 'options'=>array()),
						'result' => '<input type="checkbox" name="greeting" checked="checked" value="hello there" />'
					),
					array(
						'params' => array('name'=>'greeting', 'value'=>'hello & goodbye', 'options'=>array()),
						'result' => '<input type="checkbox" name="greeting" checked="checked" value="hello &amp; goodbye" />'
					),
				),
			),

			// bootstrap4
			array(
				'widget' => new CheckboxInput($options=array('bootstrap4' => true)),
				'params' => array(
					array(
						'params' => array('name'=>'greeting', 'value'=>'hello there', 'options'=>array()),
						'result' => '<input class="form-check-input" type="checkbox" name="greeting" checked="checked" value="hello there" />'
					),
				),
			),

		);

		$this->_check($DATA);

		// pokud se v zadanem poli (prvni parametr) nenajde klic (druhy parametr)
		// pak vraci CheckboxInput false (protoze formular, ktery je odeslan s nezaskrtnutymi
		// checkboxy tyto prvky do POST dat nezaradi).
		$this->assertEquals(false, $DATA[count($DATA)-1]['widget']->value_from_datadict(array(), 'testing'));
	}

	/**
	* Overeni vykreslovani Select.
	*/
	function test_select()
	{
		$DATA = array(
			array(
				'widget' => new Select(),
				'params' => array(
					array(
						'params' => array('name'=>'beatle', 'value'=>'J', 'options'=>array('choices'=>array('J'=>'John', 'P'=>'Paul', 'G'=>'George', 'R'=>'Ringo'))),
						'result' => implode("\n", array('<select name="beatle" class="form-control">', '<option value="J" selected="selected">John</option>', '<option value="P">Paul</option>', '<option value="G">George</option>', '<option value="R">Ringo</option>', '</select>'))
					),
					array(
						'params' => array('name'=>'beatle', 'value'=>null, 'options'=>array('choices'=>array('J'=>'John', 'P'=>'Paul', 'G'=>'George', 'R'=>'Ringo'))),
						'result' => implode("\n", array('<select name="beatle" class="form-control">', '<option value="J">John</option>', '<option value="P">Paul</option>', '<option value="G">George</option>', '<option value="R">Ringo</option>', '</select>'))
					),
					array(
						'params' => array('name'=>'beatle', 'value'=>'John', 'options'=>array('choices'=>array('J'=>'John', 'P'=>'Paul', 'G'=>'George', 'R'=>'Ringo'))),
						'result' => implode("\n", array('<select name="beatle" class="form-control">', '<option value="J">John</option>', '<option value="P">Paul</option>', '<option value="G">George</option>', '<option value="R">Ringo</option>', '</select>'))
					),
					// parametry zadane jako integer se interne prevadi na retezce
					array(
						'params' => array('name'=>'num', 'value'=>2, 'options'=>array('choices'=>array('1'=>'1', '2'=>'2', '3'=>'3'))),
						'result' => implode("\n", array('<select name="num" class="form-control">', '<option value="1">1</option>', '<option value="2" selected="selected">2</option>', '<option value="3">3</option>', '</select>'))
					),
					array(
						'params' => array('name'=>'num', 'value'=>'2', 'options'=>array('choices'=>array(1=>1, 2=>2, 3=>3))),
						'result' => implode("\n", array('<select name="num" class="form-control">', '<option value="1">1</option>', '<option value="2" selected="selected">2</option>', '<option value="3">3</option>', '</select>'))
					),
					array(
						'params' => array('name'=>'num', 'value'=>2, 'options'=>array('choices'=>array(1=>1, 2=>2, 3=>3))),
						'result' => implode("\n", array('<select name="num" class="form-control">', '<option value="1">1</option>', '<option value="2" selected="selected">2</option>', '<option value="3">3</option>', '</select>'))
					),
				)
			),
			array(
				'widget' => new Select(array('choices'=>array(1=>1, 2=>2, 3=>3))),
				'params' => array(
					array(
						'params' => array('name'=>'num', 'value'=>2, 'options'=>array()),
						'result' => implode("\n", array('<select name="num" class="form-control">', '<option value="1">1</option>', '<option value="2" selected="selected">2</option>', '<option value="3">3</option>', '</select>'))
					),
					// pokud jsou choices zadany konstruktoru i fci render, vykresli se oboje
					array(
						'params' => array('name'=>'num', 'value'=>2, 'options'=>array('choices'=>array(4=>4, 5=>5))),
						'result' => implode("\n", array('<select name="num" class="form-control">', '<option value="1">1</option>', '<option value="2" selected="selected">2</option>', '<option value="3">3</option>', '<option value="4">4</option>', '<option value="5">5</option>', '</select>'))
					),
					// escapovani hodnot i labels
					array(
						'params' => array('name'=>'num', 'value'=>2, 'options'=>array('choices'=>array('bad'=>'you & me', 'good'=>'you &gt; me'))),
						'result' => implode("\n", array('<select name="num" class="form-control">', '<option value="1">1</option>', '<option value="2" selected="selected">2</option>', '<option value="3">3</option>', '<option value="bad">you &amp; me</option>', '<option value="good">you &amp;gt; me</option>', '</select>'))
					),
				),
			)
		);

		$this->_check($DATA);
	}

	/**
	* Overeni vykreslovani SelectMultiple.
	*/
	function test_selectmultiple()
	{
		$DATA = array(
			array(
				'widget' => new SelectMultiple(),
				'params' => array(
					array(
						'params' => array('name'=>'beatles', 'value'=>array('J'), 'options'=>array('choices'=>array('J'=>'John', 'P'=>'Paul', 'G'=>'George', 'R'=>'Ringo'))),
						'result' => implode("\n", array('<select multiple="multiple" name="beatles[]" class="form-control">', '<option value="J" selected="selected">John</option>', '<option value="P">Paul</option>', '<option value="G">George</option>', '<option value="R">Ringo</option>', '</select>'))
					),
					array(
						'params' => array('name'=>'beatles', 'value'=>array('J', 'P'), 'options'=>array('choices'=>array('J'=>'John', 'P'=>'Paul', 'G'=>'George', 'R'=>'Ringo'))),
						'result' => implode("\n", array('<select multiple="multiple" name="beatles[]" class="form-control">', '<option value="J" selected="selected">John</option>', '<option value="P" selected="selected">Paul</option>', '<option value="G">George</option>', '<option value="R">Ringo</option>', '</select>'))
					),
					array(
						'params' => array('name'=>'beatles', 'value'=>array('J', 'P', 'R'), 'options'=>array('choices'=>array('J'=>'John', 'P'=>'Paul', 'G'=>'George', 'R'=>'Ringo'))),
						'result' => implode("\n", array('<select multiple="multiple" name="beatles[]" class="form-control">', '<option value="J" selected="selected">John</option>', '<option value="P" selected="selected">Paul</option>', '<option value="G">George</option>', '<option value="R" selected="selected">Ringo</option>', '</select>'))
					),
					// pokud je hodnota null, neoznaci se nic
					array(
						'params' => array('name'=>'beatles', 'value'=>null, 'options'=>array('choices'=>array('J'=>'John', 'P'=>'Paul', 'G'=>'George', 'R'=>'Ringo'))),
						'result' => implode("\n", array('<select multiple="multiple" name="beatles[]" class="form-control">', '<option value="J">John</option>', '<option value="P">Paul</option>', '<option value="G">George</option>', '<option value="R">Ringo</option>', '</select>'))
					),
					// pokud hodonta koresponduje s popiskem, neoznaci se nic
					array(
						'params' => array('name'=>'beatles', 'value'=>array('John'), 'options'=>array('choices'=>array('J'=>'John', 'P'=>'Paul', 'G'=>'George', 'R'=>'Ringo'))),
						'result' => implode("\n", array('<select multiple="multiple" name="beatles[]" class="form-control">', '<option value="J">John</option>', '<option value="P">Paul</option>', '<option value="G">George</option>', '<option value="R">Ringo</option>', '</select>'))
					),
					// pokud se objevi vice hodnot a nektere jsou spatne, oznaci se jen ty dobre
					array(
						'params' => array('name'=>'beatles', 'value'=>array('J', 'G', 'foo'), 'options'=>array('choices'=>array('J'=>'John', 'P'=>'Paul', 'G'=>'George', 'R'=>'Ringo'))),
						'result' => implode("\n", array('<select multiple="multiple" name="beatles[]" class="form-control">', '<option value="J" selected="selected">John</option>', '<option value="P">Paul</option>', '<option value="G" selected="selected">George</option>', '<option value="R">Ringo</option>', '</select>'))
					),
					// ne-stringove hodnoty jsou prevedeny na retezec
					array(
						'params' => array('name'=>'nums', 'value'=>array(2), 'options'=>array('choices'=>array('1'=>'1', '2'=>'2', '3'=>'3'))),
						'result' => implode("\n", array('<select multiple="multiple" name="nums[]" class="form-control">', '<option value="1">1</option>', '<option value="2" selected="selected">2</option>', '<option value="3">3</option>', '</select>'))
					),
					array(
						'params' => array('name'=>'nums', 'value'=>array('2'), 'options'=>array('choices'=>array(1=>1, 2=>2, 3=>3))),
						'result' => implode("\n", array('<select multiple="multiple" name="nums[]" class="form-control">', '<option value="1">1</option>', '<option value="2" selected="selected">2</option>', '<option value="3">3</option>', '</select>'))
					),
					array(
						'params' => array('name'=>'nums', 'value'=>array(2), 'options'=>array('choices'=>array(1=>1, 2=>2, 3=>3))),
						'result' => implode("\n", array('<select multiple="multiple" name="nums[]" class="form-control">', '<option value="1">1</option>', '<option value="2" selected="selected">2</option>', '<option value="3">3</option>', '</select>'))
					),


					array(
						'params' => array('name'=>'numbers', 'value'=>array('1'), 'options'=>array('choices'=>array('0'=>'Zero', '1'=>'One', '2'=>'Two'))),
						'result' => implode("\n", array('<select multiple="multiple" name="numbers[]" class="form-control">', '<option value="0">Zero</option>', '<option value="1" selected="selected">One</option>', '<option value="2">Two</option>', '</select>'))
					),
				)
			),
			array(
				// choices se muzou dat i do konstruktoru
				'widget' => new SelectMultiple(array('choices'=>array(1=>1, 2=>2, 3=>3))),
				'params' => array(
					array(
						'params' => array('name'=>'nums', 'value'=>array(2), 'options'=>array()),
						'result' => implode("\n", array('<select multiple="multiple" name="nums[]" class="form-control">', '<option value="1">1</option>', '<option value="2" selected="selected">2</option>', '<option value="3">3</option>', '</select>'))
					),
					// pokud se choices uvedou i u metody render, jsou spojeny s choices z konstruktoru
					array(
						'params' => array('name'=>'nums', 'value'=>array(2), 'options'=>array('choices'=>array(4=>4, 5=>5))),
						'result' => implode("\n", array('<select multiple="multiple" name="nums[]" class="form-control">', '<option value="1">1</option>', '<option value="2" selected="selected">2</option>', '<option value="3">3</option>', '<option value="4">4</option>', '<option value="5">5</option>', '</select>'))
					),
					// escapovani hodnot
					array(
						'params' => array('name'=>'nums', 'value'=>array(2), 'options'=>array('choices'=>array('bad'=>'you & me', 'good'=>'you &gt; me'))),
						'result' => implode("\n", array('<select multiple="multiple" name="nums[]" class="form-control">', '<option value="1">1</option>', '<option value="2" selected="selected">2</option>', '<option value="3">3</option>', '<option value="bad">you &amp; me</option>', '<option value="good">you &amp;gt; me</option>', '</select>'))
					),
				)
			),
		);

		$this->_check($DATA);
	}

	/**
	* Overeni vykreslovani RadioSelect.
	*/
	function test_radioselect()
	{
		$DATA = array(
			array(
				'widget' => new RadioSelect(),
				'params' => array(
					array(
						'params' => array('name'=>'beatle', 'value'=>'J', 'options'=>array('choices'=>array('J'=>'John', 'P'=>'Paul', 'G'=>'George', 'R'=>'Ringo'))),
						'result' => implode("\n", array('<ul class="radios">', '<li><label><input type="radio" name="beatle" value="J" checked="checked" /> John</label></li>', '<li><label><input type="radio" name="beatle" value="P" /> Paul</label></li>', '<li><label><input type="radio" name="beatle" value="G" /> George</label></li>', '<li><label><input type="radio" name="beatle" value="R" /> Ringo</label></li>', '</ul>'))
					),
					array(
						'params' => array('name'=>'beatle', 'value'=>null, 'options'=>array('choices'=>array('J'=>'John', 'P'=>'Paul', 'G'=>'George', 'R'=>'Ringo'))),
						'result' => implode("\n", array('<ul class="radios">', '<li><label><input type="radio" name="beatle" value="J" /> John</label></li>', '<li><label><input type="radio" name="beatle" value="P" /> Paul</label></li>', '<li><label><input type="radio" name="beatle" value="G" /> George</label></li>', '<li><label><input type="radio" name="beatle" value="R" /> Ringo</label></li>', '</ul>'))
					),
					array(
						'params' => array('name'=>'beatle', 'value'=>'John', 'options'=>array('choices'=>array('J'=>'John', 'P'=>'Paul', 'G'=>'George', 'R'=>'Ringo'))),
						'result' => implode("\n", array('<ul class="radios">', '<li><label><input type="radio" name="beatle" value="J" /> John</label></li>', '<li><label><input type="radio" name="beatle" value="P" /> Paul</label></li>', '<li><label><input type="radio" name="beatle" value="G" /> George</label></li>', '<li><label><input type="radio" name="beatle" value="R" /> Ringo</label></li>', '</ul>'))
					),
					// parametry zadane jako integer se interne prevadi na retezce
					array(
						'params' => array('name'=>'num', 'value'=>2, 'options'=>array('choices'=>array('1'=>'1', '2'=>'2', '3'=>'3'))),
						'result' => implode("\n", array('<ul class="radios">', '<li><label><input type="radio" name="num" value="1" /> 1</label></li>', '<li><label><input type="radio" name="num" value="2" checked="checked" /> 2</label></li>', '<li><label><input type="radio" name="num" value="3" /> 3</label></li>', '</ul>'))
					),
					array(
						'params' => array('name'=>'num', 'value'=>'2', 'options'=>array('choices'=>array(1=>1, 2=>2, 3=>3))),
						'result' => implode("\n", array('<ul class="radios">', '<li><label><input type="radio" name="num" value="1" /> 1</label></li>', '<li><label><input type="radio" name="num" value="2" checked="checked" /> 2</label></li>', '<li><label><input type="radio" name="num" value="3" /> 3</label></li>', '</ul>'))
					),
					array(
						'params' => array('name'=>'num', 'value'=>2, 'options'=>array('choices'=>array(1=>1, 2=>2, 3=>3))),
						'result' => implode("\n", array('<ul class="radios">', '<li><label><input type="radio" name="num" value="1" /> 1</label></li>', '<li><label><input type="radio" name="num" value="2" checked="checked" /> 2</label></li>', '<li><label><input type="radio" name="num" value="3" /> 3</label></li>', '</ul>'))
					),
				)
			),
			array(
				// choices se muzou dat do konstruktoru
				'widget' => new RadioSelect(array('choices'=>array(1=>1, 2=>2, 3=>3))),
				'params' => array(
					array(
						'params' => array('name'=>'num', 'value'=>2, 'options'=>array()),
						'result' => implode("\n", array('<ul class="radios">', '<li><label><input type="radio" name="num" value="1" /> 1</label></li>', '<li><label><input type="radio" name="num" value="2" checked="checked" /> 2</label></li>', '<li><label><input type="radio" name="num" value="3" /> 3</label></li>', '</ul>'))
					),
					// pokud se choices uvedou i u render, pak se spoji s temi z konstruktoru
					array(
						'params' => array('name'=>'num', 'value'=>2, 'options'=>array('choices'=>array(4=>4, 5=>5))),
						'result' => implode("\n", array('<ul class="radios">', '<li><label><input type="radio" name="num" value="1" /> 1</label></li>', '<li><label><input type="radio" name="num" value="2" checked="checked" /> 2</label></li>', '<li><label><input type="radio" name="num" value="3" /> 3</label></li>', '<li><label><input type="radio" name="num" value="4" /> 4</label></li>', '<li><label><input type="radio" name="num" value="5" /> 5</label></li>', '</ul>'))
					),
				)
			),
			array(
				// escapovani
				'widget' => new RadioSelect(),
				'params' => array(
					array(
						'params' => array('name'=>'escape', 'value'=>null, 'options'=>array('choices'=>array('bad'=>'you & me', 'good'=>'you &gt; me'))),
						'result' => implode("\n", array('<ul class="radios">', '<li><label><input type="radio" name="escape" value="bad" /> you &amp; me</label></li>', '<li><label><input type="radio" name="escape" value="good" /> you &amp;gt; me</label></li>', '</ul>'))
					),
				)
			),
			array(
				// atributy predane v konstruktoru
				'widget' => new RadioSelect(array('attrs'=>array('id'=>'foo'))),
				'params' => array(
					array(
						'params' => array('name'=>'beatle', 'value'=>'J', 'options'=>array('choices'=>array('J'=>'John', 'P'=>'Paul', 'G'=>'George', 'R'=>'Ringo'))),
						'result' => implode("\n", array('<ul class="radios">', '<li><label><input id="foo_0" type="radio" name="beatle" value="J" checked="checked" /> John</label></li>', '<li><label><input id="foo_1" type="radio" name="beatle" value="P" /> Paul</label></li>', '<li><label><input id="foo_2" type="radio" name="beatle" value="G" /> George</label></li>', '<li><label><input id="foo_3" type="radio" name="beatle" value="R" /> Ringo</label></li>', '</ul>'))
					),
				)
			),
			array(
				// atributy predane v metode render
				'widget' => new RadioSelect(),
				'params' => array(
					array(
						'params' => array('name'=>'beatle', 'value'=>'J', 'options'=>array('choices'=>array('J'=>'John', 'P'=>'Paul', 'G'=>'George', 'R'=>'Ringo'), 'attrs'=>array('id'=>'bar'))),
						'result' => implode("\n", array('<ul class="radios">', '<li><label><input id="bar_0" type="radio" name="beatle" value="J" checked="checked" /> John</label></li>', '<li><label><input id="bar_1" type="radio" name="beatle" value="P" /> Paul</label></li>', '<li><label><input id="bar_2" type="radio" name="beatle" value="G" /> George</label></li>', '<li><label><input id="bar_3" type="radio" name="beatle" value="R" /> Ringo</label></li>', '</ul>'))
					),
				)
			),
			array(
				// markup tuned for bootstrap4
				'widget' => new RadioSelect(array('bootstrap4' => true)),
				'params' => array(
					array(
						'params' => array('name' => 'direction', 'value' => 'right', 'options' => array('choices' => array('left' => 'Left', 'right' => 'Right'), 'attrs' => array('id' => 'direction'))),
						'result' => implode("\n",array(
							'<ul class="list list--radios">',
							'<li class="list__item">',
							'<div class="form-check"><input id="direction_0" class="form-check-input" type="radio" name="direction" value="left" /> <label class="form-check-label" for="direction_0">Left</label></div>',
							'</li>',
							'<li class="list__item">',
							'<div class="form-check"><input id="direction_1" class="form-check-input" type="radio" name="direction" value="right" checked="checked" /> <label class="form-check-label" for="direction_1">Right</label></div>',
							'</li>',
							'</ul>'
						))
					),
				),
			),
		);

		$this->_check($DATA);
	}

	function test_inut_type(){
		$w = new TextInput();
		$this->assertEquals("text",$w->input_type);

		$w = new PasswordInput();
		$this->assertEquals("password",$w->input_type);

		$w = new CheckboxInput();
		$this->assertEquals("checkbox",$w->input_type);
	}

	/**
	* Overeni vykreslovani CheckboxSelectMultiple.
	*
	* NOTE: tohle asi nepujde v PHP zrealizovat
	*/
	function test_checkboxselectultiple()
	{
		$DATA = array(
			array(
				'widget' => new CheckboxSelectMultiple(),
				'params' => array(
					array(
						'params' => array('name'=>'beatles', 'value'=>array('J'), 'options'=>array('choices'=>array('J'=>'John', 'P'=>'Paul', 'G'=>'George', 'R'=>'Ringo'))),
						'result' => implode("\n", array('<ul class="checkboxes">', '<li class="checkbox"><label><input type="checkbox" name="beatles[]" checked="checked" value="J" /> John</label></li>', '<li class="checkbox"><label><input type="checkbox" name="beatles[]" value="P" /> Paul</label></li>', '<li class="checkbox"><label><input type="checkbox" name="beatles[]" value="G" /> George</label></li>', '<li class="checkbox"><label><input type="checkbox" name="beatles[]" value="R" /> Ringo</label></li>', '</ul>'))
					),
					array(
						'params' => array('name'=>'beatles', 'value'=>array('J', 'P'), 'options'=>array('choices'=>array('J'=>'John', 'P'=>'Paul', 'G'=>'George', 'R'=>'Ringo'))),
						'result' => implode("\n", array('<ul class="checkboxes">', '<li class="checkbox"><label><input type="checkbox" name="beatles[]" checked="checked" value="J" /> John</label></li>', '<li class="checkbox"><label><input type="checkbox" name="beatles[]" checked="checked" value="P" /> Paul</label></li>', '<li class="checkbox"><label><input type="checkbox" name="beatles[]" value="G" /> George</label></li>', '<li class="checkbox"><label><input type="checkbox" name="beatles[]" value="R" /> Ringo</label></li>', '</ul>'))
					),
					array(
						'params' => array('name'=>'beatles', 'value'=>array('J', 'P', 'R'), 'options'=>array('choices'=>array('J'=>'John', 'P'=>'Paul', 'G'=>'George', 'R'=>'Ringo'))),
						'result' => implode("\n", array('<ul class="checkboxes">', '<li class="checkbox"><label><input type="checkbox" name="beatles[]" checked="checked" value="J" /> John</label></li>', '<li class="checkbox"><label><input type="checkbox" name="beatles[]" checked="checked" value="P" /> Paul</label></li>', '<li class="checkbox"><label><input type="checkbox" name="beatles[]" value="G" /> George</label></li>', '<li class="checkbox"><label><input type="checkbox" name="beatles[]" checked="checked" value="R" /> Ringo</label></li>', '</ul>'))
					),
					// pokud je zadana hodnota null, pak se necheckne zadna z polozek seznamu
					array(
						'params' => array('name'=>'beatles', 'value'=>null, 'options'=>array('choices'=>array('J'=>'John', 'P'=>'Paul', 'G'=>'George', 'R'=>'Ringo'))),
						'result' => implode("\n", array('<ul class="checkboxes">', '<li class="checkbox"><label><input type="checkbox" name="beatles[]" value="J" /> John</label></li>', '<li class="checkbox"><label><input type="checkbox" name="beatles[]" value="P" /> Paul</label></li>', '<li class="checkbox"><label><input type="checkbox" name="beatles[]" value="G" /> George</label></li>', '<li class="checkbox"><label><input type="checkbox" name="beatles[]" value="R" /> Ringo</label></li>', '</ul>'))
					),
					// pokud je zadan popisek namisto hodnoty, nic se necheckne
					array(
						'params' => array('name'=>'beatles', 'value'=>array('John'), 'options'=>array('choices'=>array('J'=>'John', 'P'=>'Paul', 'G'=>'George', 'R'=>'Ringo'))),
						'result' => implode("\n", array('<ul class="checkboxes">', '<li class="checkbox"><label><input type="checkbox" name="beatles[]" value="J" /> John</label></li>', '<li class="checkbox"><label><input type="checkbox" name="beatles[]" value="P" /> Paul</label></li>', '<li class="checkbox"><label><input type="checkbox" name="beatles[]" value="G" /> George</label></li>', '<li class="checkbox"><label><input type="checkbox" name="beatles[]" value="R" /> Ringo</label></li>', '</ul>'))
					),
					// pokud je nektera ze zadanych hodnot neplatna, zaskrtnou se jen ty platne
					array(
						'params' => array('name'=>'beatles', 'value'=>array('J', 'G', 'foo'), 'options'=>array('choices'=>array('J'=>'John', 'P'=>'Paul', 'G'=>'George', 'R'=>'Ringo'))),
						'result' => implode("\n", array('<ul class="checkboxes">', '<li class="checkbox"><label><input type="checkbox" name="beatles[]" checked="checked" value="J" /> John</label></li>', '<li class="checkbox"><label><input type="checkbox" name="beatles[]" value="P" /> Paul</label></li>', '<li class="checkbox"><label><input type="checkbox" name="beatles[]" checked="checked" value="G" /> George</label></li>', '<li class="checkbox"><label><input type="checkbox" name="beatles[]" value="R" /> Ringo</label></li>', '</ul>'))
					),
					// zadane hodnoty jsou prevedeny na string
					array(
						'params' => array('name'=>'nums', 'value'=>array(2), 'options'=>array('choices'=>array('1'=>'1', '2'=>'2', '3'=>'3'))),
						'result' => implode("\n", array('<ul class="checkboxes">', '<li class="checkbox"><label><input type="checkbox" name="nums[]" value="1" /> 1</label></li>', '<li class="checkbox"><label><input type="checkbox" name="nums[]" checked="checked" value="2" /> 2</label></li>', '<li class="checkbox"><label><input type="checkbox" name="nums[]" value="3" /> 3</label></li>', '</ul>'))
					),
					array(
						'params' => array('name'=>'nums', 'value'=>array('2'), 'options'=>array('choices'=>array(1=>1, 2=>2, 3=>3))),
						'result' => implode("\n", array('<ul class="checkboxes">', '<li class="checkbox"><label><input type="checkbox" name="nums[]" value="1" /> 1</label></li>', '<li class="checkbox"><label><input type="checkbox" name="nums[]" checked="checked" value="2" /> 2</label></li>', '<li class="checkbox"><label><input type="checkbox" name="nums[]" value="3" /> 3</label></li>', '</ul>'))
					),
					array(
						'params' => array('name'=>'nums', 'value'=>array(2), 'options'=>array('choices'=>array(1=>1, 2=>2, 3=>3))),
						'result' => implode("\n", array('<ul class="checkboxes">', '<li class="checkbox"><label><input type="checkbox" name="nums[]" value="1" /> 1</label></li>', '<li class="checkbox"><label><input type="checkbox" name="nums[]" checked="checked" value="2" /> 2</label></li>', '<li class="checkbox"><label><input type="checkbox" name="nums[]" value="3" /> 3</label></li>', '</ul>'))
					),
				)
			),
			// volby mohou byt zadany i v konstruktoru
			array(
				'widget' => new CheckboxSelectMultiple(array('choices'=>array(1=>1, 2=>2, 3=>3))),
				'params' => array(
					array(
						'params' => array('name'=>'nums', 'value'=>array(2), 'options'=>array()),
						'result' => implode("\n", array('<ul class="checkboxes">', '<li class="checkbox"><label><input type="checkbox" name="nums[]" value="1" /> 1</label></li>', '<li class="checkbox"><label><input type="checkbox" name="nums[]" checked="checked" value="2" /> 2</label></li>', '<li class="checkbox"><label><input type="checkbox" name="nums[]" value="3" /> 3</label></li>', '</ul>'))
					),
					// volby zadane v konstruktoru i u fce render se sliji do jednoho pole
					array(
						'params' => array('name'=>'nums', 'value'=>array(2), 'options'=>array('choices'=>array(4=>4, 5=>5))),
						'result' => implode("\n", array('<ul class="checkboxes">', '<li class="checkbox"><label><input type="checkbox" name="nums[]" value="1" /> 1</label></li>', '<li class="checkbox"><label><input type="checkbox" name="nums[]" checked="checked" value="2" /> 2</label></li>', '<li class="checkbox"><label><input type="checkbox" name="nums[]" value="3" /> 3</label></li>', '<li class="checkbox"><label><input type="checkbox" name="nums[]" value="4" /> 4</label></li>', '<li class="checkbox"><label><input type="checkbox" name="nums[]" value="5" /> 5</label></li>', '</ul>'))
					),
					// escapovani
					array(
						'params' => array('name'=>'escape', 'value'=>null, 'options'=>array('choices'=>array('bad'=>'you & me', 'good'=>'you &gt; me'))),
						'result' => implode("\n", array('<ul class="checkboxes">', '<li class="checkbox"><label><input type="checkbox" name="escape[]" value="1" /> 1</label></li>', '<li class="checkbox"><label><input type="checkbox" name="escape[]" value="2" /> 2</label></li>', '<li class="checkbox"><label><input type="checkbox" name="escape[]" value="3" /> 3</label></li>', '<li class="checkbox"><label><input type="checkbox" name="escape[]" value="bad" /> you &amp; me</label></li>', '<li class="checkbox"><label><input type="checkbox" name="escape[]" value="good" /> you &amp;gt; me</label></li>', '</ul>'))
					),
				)
			),

			// bootstrap4
			array(
				'widget' => new CheckboxSelectMultiple(array('bootstrap4' => true)),
				'params' => array(
					array(
						'params' => array('name'=>'beatles', 'value'=>array('J', 'P', 'R'), 'options'=>array('choices'=>array('J'=>'John', 'P'=>'Paul', 'G'=>'George', 'R'=>'Ringo'), 'attrs' => array('id' => 'beatles'))),
						'result' => implode("\n",array(
							'<ul class="list list--checkboxes">',
							'<li class="list__item">',
							'<div class="form-check custom-control custom-checkbox"><input class="form-check-input custom-control-input" id="beatles_0" type="checkbox" name="beatles[]" checked="checked" value="J" /> <label class="form-check-label custom-control-label" for="beatles_0">John</label></div>',
							'</li>',
							'<li class="list__item">',
							'<div class="form-check custom-control custom-checkbox"><input class="form-check-input custom-control-input" id="beatles_1" type="checkbox" name="beatles[]" checked="checked" value="P" /> <label class="form-check-label custom-control-label" for="beatles_1">Paul</label></div>',
							'</li>',
							'<li class="list__item">',
							'<div class="form-check custom-control custom-checkbox"><input class="form-check-input custom-control-input" id="beatles_2" type="checkbox" name="beatles[]" value="G" /> <label class="form-check-label custom-control-label" for="beatles_2">George</label></div>',
							'</li>',
							'<li class="list__item">',
							'<div class="form-check custom-control custom-checkbox"><input class="form-check-input custom-control-input" id="beatles_3" type="checkbox" name="beatles[]" checked="checked" value="R" /> <label class="form-check-label custom-control-label" for="beatles_3">Ringo</label></div>',
							'</li>',
							'</ul>'
					)),
				)),
			),
		);

		$this->_check($DATA);
	}
}
