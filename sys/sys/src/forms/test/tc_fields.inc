<?php
/**
* Testy formularovych poli.
*/


require_once('../fields.php');


class tc_fields extends tc_base
{
    // provereni funkcnosti HTML kompraratu
    // nema primou souvislot s polickama
    function test_html_comparation(){
        $this->assertTrue($this->_compare_html('<a href="http://www.domenka.cz/" title="Home" />','<a title="Home" href="http://www.domenka.cz/" />'));
        $this->assertFalse($this->_compare_html('<a href="http://www.domenka.cz/" title="Home" />','<a title="Ooops" href="http://www.domenka.cz/" />'));

        $this->assertTrue($this->_compare_html('<p><a href="http://www.domenka.cz/" title="Home" /></p>','<p><a title="Home" href="http://www.domenka.cz/" /></p>'));
        $this->assertFalse($this->_compare_html('<p><a href="http://www.domenka.cz/" title="Home" /></p>','<p><a title="Ooops" href="http://www.domenka.cz/" /></p>'));
    }

    function test_check_empty_value(){
        $f = new Field();

        $this->assertTrue($f->check_empty_value(null));
        $this->assertTrue($f->check_empty_value(""));
        $this->assertTrue($f->check_empty_value(array()));

        $this->assertFalse($f->check_empty_value(0));
        $this->assertFalse($f->check_empty_value(-1));
        $this->assertFalse($f->check_empty_value("value"));
        $this->assertFalse($f->check_empty_value(" "));
        $this->assertFalse($f->check_empty_value(array(0)));
    }


    /**
    * Pomocna funkce, ktera overi realny vystup widgetu s ocekavanym dle
    * dodaneho pole $data.
    */
    function _check($data)
    {
        foreach ($data as $field_widget) {
            $field = $field_widget['field'];
            foreach ($field_widget['params'] as $test) {
                list($error, $value) = $field->clean($test['clean']);
                if (is_null($test['error'])) {
                    // chyba se neocekava
                    if (is_null($error)) {
                        // kontrola typu a hodnoty
                        $this->assertType(gettype($test['result']), $value, 'Hodnota "'.$value.'" prisla v neocekavanem typu.');
                        if (is_bool($test['result'])) {
                            if ($test['result']) {
                                $this->assertTrue($value);
                            }
                            else {
                                $this->assertFalse($value);
                            }
                        }
                        else {
                            $this->assertEquals($test['result'], $value);
                        }
                    }
                    else {
                        // chyba se stala, musime zarvat
                        $this->assertNull($value, 'Chyba! Metoda clean mela vratit jako hodnotu null, ale vratila "'.$value.'".');
                        $this->fail('Field neoceavane vratil chybu "'.$error.'" pro hodnotu "'.$test['clean'].'".');
                    }
                }
                else {
                    // ocekava se chyba
                    if (is_null($error)) {
                        $this->fail('Field mel pro hodnotu "'.$test['clean'].'" vratit chybu "'.$test['error'].'", ale nevratil ji.');
                    }
                    else {
                        // kontrola error hlasky
                        $this->assertNull($value, 'Chyba! Metoda clean mela vratit jako hodnotu null, ale vratila "'.$value.'".');
                        $this->assertEquals($test['error'], $error);
                    }
                }
            }
        }
    }

    /**
    * Kontrola CharField.
    */
    function test_charfield()
    {
        $DATA = array(
            array(
                'field' => new CharField(),
                'params' => array(
                    array(
                        'clean'=>1, 'error'=>null, 'result'=>'1'
                    ),
                    array(
                        'clean'=>'hello', 'error'=>null, 'result'=>'hello'
                    ),
                    array(
                        'clean'=>null, 'error'=>'This field is required.', 'result'=>null
                    ),
                    array(
                        'clean'=>'', 'error'=>'This field is required.', 'result'=>null
                    ),
                    array(
                        'clean'=>array(1,2,3), 'error'=>null, 'result'=>implode("\n", array('array (', '  0 => 1,', '  1 => 2,', '  2 => 3,', ')'))
                    ),
                )
            ),
            array(
                'field' => new CharField(array('required'=>false)),
                'params' => array(
                    array(
                        'clean'=>1, 'error'=>null, 'result'=>'1'
                    ),
                    array(
                        'clean'=>'hello', 'error'=>null, 'result'=>'hello'
                    ),
                    array(
                        'clean'=>null, 'error'=>null, 'result'=>''
                    ),
                    array(
                        'clean'=>'', 'error'=>null, 'result'=>''),
                    array(
                        'clean'=>array(1,2,3), 'error'=>null, 'result'=>implode("\n", array('array (', '  0 => 1,', '  1 => 2,', '  2 => 3,', ')'))
                    ),
                )
            ),
            array(
                'field' => new CharField(array('required'=>false, 'max_length'=>10)),
                'params' => array(
                    array(
                        'clean'=>'12345', 'error'=>null, 'result'=>'12345'
                    ),
                    array(
                        'clean'=>'1234567890', 'error'=>null, 'result'=>'1234567890'
                    ),
                    array(
                        'clean'=>'1234567890a', 'error'=>'Ensure this value has at most 10 characters (it has 11).', 'result'=>null
                    ),
                )
            ),
            array(
                'field' => new CharField(array('required'=>true, 'min_length'=>10)),
                'params' => array(
                    array(
                        'clean'=>'', 'error'=>'This field is required.', 'result'=>null
                    ),
                    array(
                        'clean'=>'12345', 'error'=>'Ensure this value has at least 10 characters (it has 5).', 'result'=>null
                    ),
                    array(
                        'clean'=>'1234567890', 'error'=>null, 'result'=>'1234567890'
                    ),
                    array(
                        'clean'=>'1234567890a', 'error'=>null, 'result'=>'1234567890a'
                    ),
                )
            ),

            // kontrola trimovani hodnoty
            array(
                'field' => new CharField(array('required' => true)),
                'params' => array(
                    array(
                        'clean' => '  ', 'error' => 'This field is required.', 'result' => '',
                        'clean' => ' trim me  ', 'error' => null, 'result' => 'trim me' 
                    )
                ),
            ),
            array(
                'field' => new CharField(array('required' => true, 'trim_value' => false)),
                'params' => array(
                    array(
                        'clean' => '  ', 'error' => null, 'result' => '  ',
                        'clean' => ' do not trim me ', 'error' => null, 'result' => ' do not trim me ' 
                    )
                ),
            ),

            // nullovani prazdnych stringu
            array(
                'field' => new CharField(array('required' => false, 'trim_value' => false, 'null_empty_output' => true)),
                'params' => array(
                    array(
                        'clean' => '', 'error' => null, 'result' => null
                    )
                ),
            ),
            array(
                'field' => new CharField(array('required' => false, 'trim_value' => false, 'null_empty_output' => true)),
                'params' => array(
                    array(
                        'clean' => ' ', 'error' => null, 'result' => ' '
                    )
                ),
            ),
            array(
                'field' => new CharField(array('required' => false, 'trim_value' => true, 'null_empty_output' => true)),
                'params' => array(
                    array(
                        'clean' => ' ', 'error' => null, 'result' => null
                    )
                ),
            )
        );

        $this->_check($DATA);
    }

    /**
    * Kontrola IntegerField.
    */
    function test_integerfield()
    {
        $DATA = array(
            array(
                'field' => new IntegerField(),
                'params' => array(
                    array(
                        'clean'=>'', 'error'=>'This field is required.', 'result'=>null
                    ),
                    array(
                        'clean'=>null, 'error'=>'This field is required.', 'result'=>null
                    ),
                    array(
                        'clean'=>'1', 'error'=>null, 'result'=>1
                    ),
                    array(
                        'clean'=>'23', 'error'=>null, 'result'=>23
                    ),
                    array(
                        'clean'=>'a', 'error'=>'Enter a whole number.', 'result'=>null
                    ),
                    array(
                        'clean'=>42, 'error'=>null, 'result'=>42
                    ),
                    array(
                        'clean'=>3.14, 'error'=>'Enter a whole number.', 'result'=>null
                    ),
                    array(
                        'clean'=>'1 ', 'error'=>null, 'result'=>1
                    ),
                    array(
                        'clean'=>' 1', 'error'=>null, 'result'=>1
                    ),
                    array(
                        'clean'=>' 1 ', 'error'=>null, 'result'=>1
                    ),
                    array(
                        'clean'=>'1a', 'error'=>'Enter a whole number.', 'result'=>null
                    ),
                    array(
                        'clean'=>'01', 'error'=>'Enter a whole number.', 'result'=>null
                    ),
                    array(
                        'clean'=>'00', 'error'=>'Enter a whole number.', 'result'=>null
                    ),
                    array(
                        'clean'=>'01', 'error'=>'Enter a whole number.', 'result'=>null
                    ),
                    array(
                        'clean'=>'+0', 'error'=>'Enter a whole number.', 'result'=>null
                    ),
                    array(
                        'clean'=>'-0', 'error'=>'Enter a whole number.', 'result'=>null
                    ),

                    array(
                        'clean'=>' +20 ', 'error'=>null, 'result'=> 20
                    ),
                    array(
                        'clean'=>' -20 ', 'error'=>null, 'result'=> -20
                    ),
                    array(
                        'clean'=>' 0 ', 'error'=>null, 'result'=> 0
                    ),
                )
            ),
            array(
                'field' => new IntegerField(array('required'=>false)),
                'params' => array(
                    array(
                        'clean'=>'', 'error'=>null, 'result'=>null
                    ),
                    array(
                        'clean'=>null, 'error'=>null, 'result'=>null
                    ),
                    array(
                        'clean'=>'1', 'error'=>null, 'result'=>1
                    ),
                    array(
                        'clean'=>'23', 'error'=>null, 'result'=>23
                    ),
                    array(
                        'clean'=>'a', 'error'=>'Enter a whole number.', 'result'=>null
                    ),
                    array(
                        'clean'=>'1 ', 'error'=>null, 'result'=>1
                    ),
                    array(
                        'clean'=>' 1', 'error'=>null, 'result'=>1
                    ),
                    array(
                        'clean'=>' 1 ', 'error'=>null, 'result'=>1
                    ),
                    array(
                        'clean'=>'1a', 'error'=>'Enter a whole number.', 'result'=>null
                    ),
                )
            ),
            array(
                'field' => new IntegerField(array('max_value'=>10)),
                'params' => array(
                    array(
                        'clean'=>null, 'error'=>'This field is required.', 'result'=>null
                    ),
                    array(
                        'clean'=>1, 'error'=>null, 'result'=>1
                    ),
                    array(
                        'clean'=>10, 'error'=>null, 'result'=>10
                    ),
                    array(
                        'clean'=>11, 'error'=>'Ensure this value is less than or equal to 10.', 'result'=>null
                    ),
                    array(
                        'clean'=>'10', 'error'=>null, 'result'=>10
                    ),
                    array(
                        'clean'=>'11', 'error'=>'Ensure this value is less than or equal to 10.', 'result'=>null
                    ),
                )
            ),
            array(
                'field' => new IntegerField(array('min_value'=>10)),
                'params' => array(
                    array(
                        'clean'=>null, 'error'=>'This field is required.', 'result'=>null
                    ),
                    array(
                        'clean'=>1, 'error'=>'Ensure this value is greater than or equal to 10.', 'result'=>null
                    ),
                    array(
                        'clean'=>10, 'error'=>null, 'result'=>10
                    ),
                    array(
                        'clean'=>11, 'error'=>null, 'result'=>11
                    ),
                    array(
                        'clean'=>'10', 'error'=>null, 'result'=>10
                    ),
                    array(
                        'clean'=>'11', 'error'=>null, 'result'=>11
                    ),
                )
            ),
            array(
                'field' => new IntegerField(array('min_value'=>10, 'max_value'=>20)),
                'params' => array(
                    array(
                        'clean'=>null, 'error'=>'This field is required.', 'result'=>null
                    ),
                    array(
                        'clean'=>1, 'error'=>'Ensure this value is greater than or equal to 10.', 'result'=>null
                    ),
                    array(
                        'clean'=>10, 'error'=>null, 'result'=>10
                    ),
                    array(
                        'clean'=>11, 'error'=>null, 'result'=>11
                    ),
                    array(
                        'clean'=>'10', 'error'=>null, 'result'=>10
                    ),
                    array(
                        'clean'=>'11', 'error'=>null, 'result'=>11
                    ),
                    array(
                        'clean'=>20, 'error'=>null, 'result'=>20
                    ),
                    array(
                        'clean'=>21, 'error'=>'Ensure this value is less than or equal to 20.', 'result'=>null
                    ),
                )
            ),
        );

        $this->_check($DATA);
    }

    /**
    * Kontrola FloatField.
    */
    function test_floatfield()
    {
        $DATA = array(
            array(
                'field' => new FloatField(),
                'params' => array(
                    array(
                        'clean'=>'', 'error'=>'This field is required.', 'result'=>null
                    ),
                    array(
                        'clean'=>null, 'error'=>'This field is required.', 'result'=>null
                    ),
                    array(
                        'clean'=>'1', 'error'=>null, 'result'=>(float)1
                    ),
                    array(
                        'clean'=>'23', 'error'=>null, 'result'=>(float)23
                    ),
                    array(
                        'clean'=>'3.14', 'error'=>null, 'result'=>(float)3.14
                    ),
                    array(
                        'clean'=>3.14, 'error'=>null, 'result'=>(float)3.14
                    ),
                    array(
                        'clean'=>42, 'error'=>null, 'result'=>(float)42
                    ),
                    array(
                        'clean'=>'a', 'error'=>'Enter a number.', 'result'=>null
                    ),
                    array(
                        'clean'=>'1.0 ', 'error'=>null, 'result'=>(float)1
                    ),
                    array(
                        'clean'=>' 1.0', 'error'=>null, 'result'=>(float)1
                    ),
                    array(
                        'clean'=>' 1.0 ', 'error'=>null, 'result'=>(float)1
                    ),
                    array(
                        'clean'=>'1.0a', 'error'=>'Enter a number.', 'result'=>null
                    ),
                )
            ),
            array(
                'field' => new FloatField(array('required'=>false)),
                'params' => array(
                    array(
                        'clean'=>'', 'error'=>null, 'result'=>null
                    ),
                    array(
                        'clean'=>null, 'error'=>null, 'result'=>null
                    ),
                    array(
                        'clean'=>'1', 'error'=>null, 'result'=>(float)1
                    ),
                )
            ),
            array(
                'field' => new FloatField(array('min_value'=>0.5, 'max_value'=>1.5)),
                'params' => array(
                    array(
                        'clean'=>1.6, 'error'=>'Ensure this value is less than or equal to 1.5.', 'result'=>null
                    ),
                    array(
                        'clean'=>0.4, 'error'=>'Ensure this value is greater than or equal to 0.5.', 'result'=>null
                    ),
                    array(
                        'clean'=>1.5, 'error'=>null, 'result'=>(float)1.5
                    ),
                    array(
                        'clean'=>'0.5', 'error'=>null, 'result'=>(float)0.5
                    ),
                    array(
                        'clean'=>'1', 'error'=>null, 'result'=>(float)1
                    ),
                )
            ),
        );

        $this->_check($DATA);
    }

    /**
    * Kontrola BooleanField.
    */
    function test_booleanfield()
    {
        $DATA = array(
            array(
                'field' => new BooleanField(),
                'params' => array(
                    array(
                        'clean'=>'', 'error'=>'This field is required.', 'result'=>null
                    ),
                    array(
                        'clean'=>null, 'error'=>'This field is required.', 'result'=>null
                    ),
                    array(
                        'clean'=>true, 'error'=>null, 'result'=>true
                    ),
                    array(
                        'clean'=>false, 'error'=>null, 'result'=>false
                    ),
                    array(
                        'clean'=>1, 'error'=>null, 'result'=>true
                    ),
                    array(
                        'clean'=>0, 'error'=>null, 'result'=>false
                    ),
                    array(
                        'clean'=>'Antikrist 14 rocks', 'error'=>null, 'result'=>true
                    ),
                    array(
                        'clean'=>'true', 'error'=>null, 'result'=>true
                    ),
                    array(
                        'clean'=>'false', 'error'=>null, 'result'=>false
                    ),
                )
            ),
        );

        $this->_check($DATA);
    }

    /**
    * Kontrola RegexField (obdoba CharField, ale obsah je otestovan podle dodaneho regexp).
    */
    function test_regexfield()
    {
        $DATA = array(
            array(
                'field' => new RegexField('/^\d[A-F]\d$/'),
                'params' => array(
                    array(
                        'clean'=>'2A2', 'error'=>null, 'result'=>'2A2'
                    ),
                    array(
                        'clean'=>'3F3', 'error'=>null, 'result'=>'3F3'
                    ),
                    array(
                        'clean'=>'3G3', 'error'=>'Enter a valid value.', 'result'=>null
                    ),
                    array(
                        'clean'=> ' 2AA', 'error'=>'Enter a valid value.', 'result'=>null
                    ),
                    array(
                        'clean'=> '2A2 ', 'error'=> null, 'result'=> '2A2' // zde trimne mezera na konci...
                    ),
                    array(
                        'clean'=> '', 'error'=>'This field is required.', 'result'=>null
                    ),
                )
            ),
            array(
                'field' => new RegexField('/^\d[A-F]\d$/',array("trim_value" => false)),
                'params' => array(
                    array(
                        'clean'=>'2A2', 'error'=>null, 'result'=>'2A2'
                    ),
                    array(
                        'clean'=>'3F3', 'error'=>null, 'result'=>'3F3'
                    ),
                    array(
                        'clean'=>'3G3', 'error'=>'Enter a valid value.', 'result'=>null
                    ),
                    array(
                        'clean'=> ' 2AA', 'error'=>'Enter a valid value.', 'result'=>null
                    ),
                    array(
                        'clean'=> '2A2 ', 'error'=>'Enter a valid value.', 'result'=>null
                    ),
                    array(
                        'clean'=> '', 'error'=>'This field is required.', 'result'=>null
                    ),
                )
            ),
            array(
                'field' => new RegexField('/^\d[A-F]\d$/', array('required'=>false)),
                'params' => array(
                    array(
                        'clean'=>'2A2', 'error'=>null, 'result'=>'2A2'
                    ),
                    array(
                        'clean'=>'3F3', 'error'=>null, 'result'=>'3F3'
                    ),
                    array(
                        'clean'=>'3G3', 'error'=>'Enter a valid value.', 'result'=>null
                    ),
                    array(
                        'clean'=>'', 'error'=>null, 'result'=>''
                    ),
                )
            ),
            // RegexField muzeme prinutit vypisovat user-defined error hlasku
            array(
                'field' => new RegexField('/^\d\d\d\d$/', array('error_message'=>'Enter a four-digit number.')),
                'params' => array(
                    array(
                        'clean'=>'1234', 'error'=>null, 'result'=>'1234'
                    ),
                    array(
                        'clean'=>'123', 'error'=>'Enter a four-digit number.', 'result'=>null
                    ),
                    array(
                        'clean'=>'abcd', 'error'=>'Enter a four-digit number.', 'result'=>null
                    ),
                )
            ),
            // RegexField muze mit min/max parametry
            array(
                'field' => new RegexField('/^\d+$/', array('min_length'=>5, 'max_length'=>10)),
                'params' => array(
                    array(
                        'clean'=>'123', 'error'=>'Ensure this value has at least 5 characters (it has 3).', 'result'=>null
                    ),
                    array(
                        'clean'=>'abc', 'error'=>'Ensure this value has at least 5 characters (it has 3).', 'result'=>null
                    ),
                    array(
                        'clean'=>'12345', 'error'=>null, 'result'=>'12345'
                    ),
                    array(
                        'clean'=>'1234567890', 'error'=>null, 'result'=>'1234567890'
                    ),
                    array(
                        'clean'=>'12345678901', 'error'=>'Ensure this value has at most 10 characters (it has 11).', 'result'=>null
                    ),
                    array(
                        'clean'=>'12345a', 'error'=>'Enter a valid value.', 'result'=>null
                    ),
                )
            ),
            // vypnuti vychoziho nullovani prazdnych stringu
            array(
                'field' => new RegexField('/^\d\d\d\d$/', array('error_message'=>'Enter a four-digit number.','required' => false,'null_empty_output' => false)),
                'params' => array(
                    array(
                        'clean'=>'', 'error'=>null, 'result'=>''
                    ),
                )
            ),
        );

        $this->_check($DATA);
    }

    /**
    * Kontrola EmailField.
    */
    function test_emailfield()
    {
        $DATA = array(
            array(
                'field' => new EmailField(),
                'params' => array(
                    array(
                        'clean'=>'', 'error'=>'This field is required.', 'result'=>null
                    ),
                    array(
                        'clean'=>null, 'error'=>'This field is required.', 'result'=>null
                    ),
                    array(
                        'clean'=>'person@example.com', 'error'=>null, 'result'=>'person@example.com'
                    ),
                    array(
                        'clean'=>'foo', 'error'=>'Enter a valid e-mail address.', 'result'=>null
                    ),
                    array(
                        'clean'=>'foo@', 'error'=>'Enter a valid e-mail address.', 'result'=>null
                    ),
                    array(
                        'clean'=>'foo@bar', 'error'=>'Enter a valid e-mail address.', 'result'=>null
                    ),
                )
            ),
            // emailove policko muze mit nadefinovanou i min/max delku
            array(
                'field' => new EmailField(array('min_length'=>10, 'max_length'=>15)),
                'params' => array(
                    array(
                        'clean'=>'a@foo.com', 'error'=>'Ensure this value has at least 10 characters (it has 9).', 'result'=>null
                    ),
                    array(
                        'clean'=>'alf@foo.com', 'error'=>null, 'result'=>'alf@foo.com'
                    ),
                    array(
                        'clean'=>'alf123456788@foo.com', 'error'=>'Ensure this value has at most 15 characters (it has 20).', 'result'=>null
                    ),
                )
            ),
            array(
                'field' => new EmailField(array("required" => false)),
                'params' => array(  
                    array('clean' => 'a@foo.com', 'error' => null, 'result' => 'a@foo.com'),
                    array('clean' => ' a@foo.com ', 'error' => null, 'result' => 'a@foo.com'),
                    array('clean' => '', 'error' => null, 'result' => null),
                ),
            ),
        );

        $this->_check($DATA);
    }

    /**
    * Kontrola ChoiceField.
    */
    function test_choicefield()
    {
        $DATA = array(
            array(
                'field' => new ChoiceField(array('choices'=>array('1'=>'1', '2'=>'2'))),
                'params' => array(
                    array(
                        'clean'=>'', 'error'=>'This field is required.', 'result'=>null
                    ),
                    array(
                        'clean'=>null, 'error'=>'This field is required.', 'result'=>null
                    ),
                    array(
                        'clean'=>1, 'error'=>null, 'result'=>'1'
                    ),
                    array(
                        'clean'=>'1', 'error'=>null, 'result'=>'1'
                    ),
                    array(
                        'clean'=>'3', 'error'=>'Select a valid choice. That choice is not one of the available choices.', 'result'=>null
                    ),
                )
            ),
            array(
                'field' => new ChoiceField(array('choices'=>array('1'=>'1', '2'=>'2'), 'required'=>false)),
                'params' => array(
                    array(
                        'clean'=>'', 'error'=>null, 'result'=>null
                    ),
                    array(
                        'clean'=>null, 'error'=>null, 'result'=>null
                    ),
                    array(
                        'clean'=>1, 'error'=>null, 'result'=>'1'
                    ),
                    array(
                        'clean'=>'1', 'error'=>null, 'result'=>'1'
                    ),
                    array(
                        'clean'=>'3', 'error'=>'Select a valid choice. That choice is not one of the available choices.', 'result'=>null
                    ),
                )
            ),
            array(
                'field' => new ChoiceField(array('choices'=>array('J'=>'John', 'P'=>'Paul'))),
                'params' => array(
                    array(
                        'clean'=>'J', 'error'=>null, 'result'=>'J'
                    ),
                    array(
                        'clean'=>'John', 'error'=>'Select a valid choice. That choice is not one of the available choices.', 'result'=>null
                    ),
                )
            ),

        );

        $this->_check($DATA);
    }


    /**
    * Kontrola MultipleChoiceField.
    * 
    * NOTE: vyrazuju to, protoze tohle asi v PHP nerozjedu...
    */
    function test_multiplechoicefield()
    {
        $DATA = array(
            array(
                'field' => new MultipleChoiceField(array('choices'=>array('1'=>'1', '2'=>'2'))),
                'params' => array(
                    array(
                        'clean'=>'', 'error'=>'This field is required.', 'result'=>null
                    ),
                    array(
                        'clean'=>null, 'error'=>'This field is required.', 'result'=>null
                    ),
                    array(
                        'clean'=>array(1), 'error'=>null, 'result'=>array('1')
                    ),
                    array(
                        'clean'=>array('1'), 'error'=>null, 'result'=>array('1')
                    ),
                    array(
                        'clean'=>array('1', '2'), 'error'=>null, 'result'=>array('1', '2')
                    ),
                    array(
                        'clean'=>array(1, '2'), 'error'=>null, 'result'=>array('1', '2')
                    ),
                    array(
                        'clean'=>array('1', 2), 'error'=>null, 'result'=>array('1', '2')
                    ),
                    array(
                        'clean'=>'hello', 'error'=>'Enter a list of values.', 'result'=>null
                    ),
                    array(
                        'clean'=>array(), 'error'=>'This field is required.', 'result'=>null
                    ),
                    array(
                        'clean'=>array('3'), 'error'=>'Select a valid choice. 3 is not one of the available choices.', 'result'=>null
                    ),
                )
            ),
            array(
                'field' => new MultipleChoiceField(array('choices'=>array('1'=>'1', '2'=>'2'), 'required'=>false)),
                'params' => array(
                    array(
                        'clean'=>'', 'error'=>null, 'result'=>array()
                    ),
                    array(
                        'clean'=>null, 'error'=>null, 'result'=>array()
                    ),
                    array(
                        'clean'=>array(1), 'error'=>null, 'result'=>array('1')
                    ),
                    array(
                        'clean'=>array('1'), 'error'=>null, 'result'=>array('1')
                    ),
                    array(
                        'clean'=>array('1', '2'), 'error'=>null, 'result'=>array('1', '2')
                    ),
                    array(
                        'clean'=>array(1, '2'), 'error'=>null, 'result'=>array('1', '2')
                    ),
                    array(
                        'clean'=>array('1', 2), 'error'=>null, 'result'=>array('1', '2')
                    ),
                    array(
                        'clean'=>'hello', 'error'=>'Enter a list of values.', 'result'=>null
                    ),
                    array(
                        'clean'=>array(), 'error'=>null, 'result'=>array()
                    ),
                    array(
                        'clean'=>array('3'), 'error'=>'Select a valid choice. 3 is not one of the available choices.', 'result'=>null
                    ),
                )
            ),
        );

        $this->_check($DATA);
    }

    function test_ipaddressfield(){
        $DATA = array(array(
            'field' => new IPAddressField(array("required" => false)),
            'params' => array(
                array('clean' => '10.10.2.2', 'error' => null, 'result' => '10.10.2.2'),
                array('clean' => 'FEDC:BA98:7654:3210:FEDC:BA98:7654:3210', 'error' => null, 'result' => 'FEDC:BA98:7654:3210:FEDC:BA98:7654:3210'),
                array('clean' => 'fedc:ba98:7654:3210:fedc:ba98:7654:3210', 'error' => null, 'result' => 'fedc:ba98:7654:3210:fedc:ba98:7654:3210'),
                array('clean' => 'FF01:0:0:0:0:0:0:101', 'error' => null, 'result' => 'FF01:0:0:0:0:0:0:101'),
                array('clean' => 'FEC0:0:0:40::', 'error' => null, 'result' => 'FEC0:0:0:40::'),
                array('clean' => 'FEC0:0:0:40::1', 'error' => null, 'result' => 'FEC0:0:0:40::1'),
                array('clean' => 'xx', 'error' => 'Enter a valid IP address.', 'result' => null),
                array('clean' => '', 'error' => null, 'result' => null),
            )
        ));

        $this->_check($DATA);
    }

    function test_datefield(){
        $DATA = array(array(
            'field' => new DateField(array("required" => false)),
            'params' => array(
                array('clean' => '', 'error' => null, 'result' => null),
                array('clean' => ' ', 'error' => null, 'result' => null),
                array('clean' => ' 31.1.2008 ', 'error' => null, 'result' => '2008-01-31'),
                array('clean' => ' 311.1.2008 ', 'error' => 'Enter a valid date.', 'result' => null),
            )
        ),array(
            'field' => new DateField(array("required" => false, "null_empty_output" => false)),
            'params' => array(
                array('clean' => '', 'error' => null, 'result' => ''),
                array('clean' => ' ', 'error' => null, 'result' => ''),
                array('clean' => ' 31.1.2008 ', 'error' => null, 'result' => '2008-01-31'),
                array('clean' => ' 311.1.2008 ', 'error' => 'Enter a valid date.', 'result' => null),
            )
        )
        );

        $this->_check($DATA);
    }
}

// vim: set et ts=4 sw=4 enc=utf-8 fenc=utf-8 si: 
?>
