<?php
// vim: set et:
class tc_xmole extends tc_base{

	var $_test_xml = '
		<data>
			<user type="admin">honza lenivy</user>
			<user type="normal">pan vorel</user>
			<block align="left" images="no">
				lorem ipsum
				kolem kixnul
			</block>
		</data>
	';

  function test_basic_usage(){
    $xm = new XMole('
    <planets system="Solar">
     <earth order="3rd">We love it</earth>
     <earth:moons>
      <moons:count>1</moons:count>
     </earth:moons>
    </planets>
    ');

    $this->assertEquals("We love it",$xm->get_data("/planets/earth"));
    $this->assertEquals("We love it",$xm->get_data("planets/earth"));
    $this->assertEquals("We love it",$xm->get_data("earth"));

    $this->assertEquals("1",$xm->get_data("earth:moons/moons:count"));

    $this->assertEquals("3rd",$xm->get_attribute("/planets/earth","order"));
    $this->assertEquals("3rd",$xm->get_attribute("planets/earth","order"));
    $this->assertEquals("3rd",$xm->get_attribute("earth","order"));

    $this->assertEquals(array("order" => "3rd"),$xm->get_attributes("/planets/earth"));
    $this->assertEquals(array("order" => "3rd"),$xm->get_attributes("planets/earth"));
    $this->assertEquals(array("order" => "3rd"),$xm->get_attributes("earth"));

    $this->assertEquals(array("system" => "Solar"),($xm->get_attributes()));
    $this->assertEquals(array("system" => "Solar"),($xm->get_attributes("/")));

    $this->assertEquals("planets",$xm->get_root_name());
    $this->assertEquals("",$xm->get_data()); // korenovy elemnt <planets> neobsahuje zadna data
    $this->assertEquals("Solar",$xm->get_attribute("/","system"));
    $this->assertEquals("Solar",$xm->get_attribute("system")); // pokud zjistujeme atribut korenoveho elementu, nemusim uvadet $element_path
    $this->assertNull($xm->get_attribute("/","non_existing"));
    $this->assertNull($xm->get_attribute("non_existing"));

    $earth = $xm->get_child();
    $this->assertEquals("earth",$earth->get_root_name());
    $this->assertEquals("We love it",$earth->get_data()); // zde je korenovy element uz <earth>
    $this->assertEquals("3rd",$earth->get_attribute("/","order"));
    $this->assertEquals("3rd",$earth->get_attribute("/earth","order"));
    $this->assertEquals("3rd",$earth->get_attribute("earth/","order"));
    $this->assertEquals("3rd",$earth->get_attribute("order"));
    $this->assertNull($earth->get_attribute("non_existing"));
    $this->assertNull($earth->get_attribute("/earth/","non_existing"));
  }

  function test_to_string(){
    $xm = new XMole();
    $this->assertEquals("[empty XMole]","$xm");

    $xm = new XMole("<xml><error></xml>");
    $this->assertEquals("[XMole with invalid document: XML parser error (76): Mismatched tag on line 1]","$xm");

    $xm = new XMole($_src = trim('
    <planets system="Solar">
     <earth order="3rd">We love it</earth>
    </planets>
    '));
    $this->assertEquals($_src,"$xm");
  }

  function test_get_xmole_by_first_matching_branch(){
    $xmole = new XMole();

    $this->assertTrue($xmole->parse($this->_test_xml));
    $this->assertNull($xmole->get_first_matching_branch("/user"));
    
    $xmole2 = $xmole->get_xmole_by_first_matching_branch("/nonexisting/branf");
    $this->assertNull($xmole2);

    $xmole2 = $xmole->get_xmole_by_first_matching_branch("/data/user");
    $this->assertNotNull($xmole2);

    $this->assertTrue(is_object($xmole2));
    $this->assertEquals("honza lenivy",$xmole2->get_element_data("/user"));
    
    $this->assertEquals("admin",$xmole2->get_attribute_value("/user","type"));
  }

  function test_get_xmoles_by_all_matching_branches(){
    $xmole = new XMole();

    $this->assertTrue($xmole->parse($this->_test_xml));
    
    $xmoles = $xmole->get_xmoles_by_all_matching_branches("/nonexisting/branf");
    $this->assertTrue(is_array($xmoles));
    $this->assertEquals(0,sizeof($xmoles));

    $xmoles = $xmole->get_xmoles_by_all_matching_branches("/data/user");
    $this->assertTrue(is_array($xmoles));
    $this->assertEquals(2,sizeof($xmoles));

    $this->assertEquals("honza lenivy",$xmoles[0]->get_element_data("/user"));
    $this->assertEquals("pan vorel",$xmoles[1]->get_element_data("/user"));
  }

  function test_comparing_xml(){
    $xm_people = new XMole($people = '
    <lide>
      <kluk vek="15" vyska="165" />
    </lide>
    ');

    $xm_same_people = new XMole($same_people = '
    <lide>
      <kluk vyska="165" vek="15" />
    </lide>
    ');

    $xm_different_people = new XMole($different_people = '
    <lide>
      <kluk vyska="165" vek="16" />
    </lide>
    ');

    $xm_invalid_people = new XMole($invalid_people = '
      <lide>
      </lideee>
    ');

    // porovnavani instanci
    $this->assertNull(XMole::AreSame($xm_people,$xm_invalid_people));
    $this->assertNull(XMole::AreSame($xm_invalid_people,$xm_people));

    $this->assertTrue(XMole::AreSame($xm_people,$xm_same_people));
    $this->assertFalse(XMole::AreSame($xm_people,$xm_different_people));

    // porovnavani stringu
    $this->assertNull(XMole::AreSame($people,$invalid_people));
    $this->assertNull(XMole::AreSame($invalid_people,$people));

    $this->assertTrue(XMole::AreSame($people,$same_people));
    $this->assertFalse(XMole::AreSame($people,$different_people));
  }

  function test_root_children(){
    $xmole = new XMole('
      <computers from="the begining" till="present">
        <computer
          manufacturer="Statny majetok Zavadka"
          model="Mato"
        >Nice compo</computer>
        <computer
          manufacturer="atari"
          model="520 STM"
        />
        <computer
          manufacturer="commodore"
          model="Amiga 1200"
        />
      </computers>
    ');
    $this->assertFalse($xmole->error());

    $this->assertEquals("computers",$xmole->get_root_name());
    $this->assertEquals(array(
      "from" => "the begining",
      "till" => "present"
    ),$xmole->get_attributes());
    $this->assertEquals(array(
      "from" => "the begining",
      "till" => "present"
    ),$xmole->get_root_attributes());

    $c1 = $xmole->get_child(0);
    $this->assertEquals("computer",$c1->get_root_name());
    $this->assertEquals(array(
      "manufacturer" => "Statny majetok Zavadka",
      "model" => "Mato"
    ),$c1->get_attributes());
    $this->assertEquals("Nice compo",$c1->get_data());

    $children = $xmole->get_children();
    $this->assertEquals(3,sizeof($children));
    $this->assertEquals("Mato",$children[0]->get_attribute("model"));
    $this->assertEquals("520 STM",$children[1]->get_attribute("model"));
    $this->assertEquals("Amiga 1200",$children[2]->get_attribute("model"));
  }

  function test_trim_data(){
    $xml = "<element> ABC </element>";
    
    // ve vychozim stavu se data trimuji
    $x = new XMole($xml);
    $this->assertEquals("ABC",$x->get_element_data("element"));

    $x = new XMole($xml,array("trim_data" => false));
    $this->assertEquals(" ABC ",$x->get_element_data("element"));

    $x = new XMole();
    $x->parse($xml);
    $this->assertEquals("ABC",$x->get_element_data("element"));

    $x = new XMole();
    $x->set_trim_data(false);
    $x->parse($xml);
    $this->assertEquals(" ABC ",$x->get_element_data("element"));
  }

  function test_new_instance(){
    $xmole = new XMole();
    $xmole->set_trim_data(false);
    $xmole->set_input_encoding("WINDOWS-1250");
    $xmole->set_output_encoding("UTF-8");

    $x2 = $xmole->_new_instance();
    $this->assertFalse($x2->trim_data());
    $this->assertEquals("WINDOWS-1250",$x2->get_input_encoding());
    $this->assertEquals("UTF-8",$x2->get_output_encoding());
  }

  function test_demo_branch_searching(){
     // pokud jsou vetve jednoznacne identifikovatelne, najdeme je takto
     $xmole = new XMole('
       <people>
        <boy>
          <name>Jamis</name>
          <status>happy</status>
        </boy>
        <girl>
          <name>Janet</name>
          <status>fine</status>
        </girl>
       </people>
     ');
     $boy = $xmole->get_xmole("boy");
     $this->assertEquals("Jamis",$boy->get_data("name"));
     $this->assertEquals("happy",$boy->get_data("status"));

     $girl = $xmole->get_xmole("girl");
     $this->assertEquals("Janet",$girl->get_data("name"));
     $this->assertEquals("fine",$girl->get_data("status"));

     // metoda get_xmoles vraci vsechny instance podmince vyhovujicich vetvi

     $xmole = new XMole('
       <people>
        <person type="boy">
          <name>Jamis</name>
          <status>happy</status>
        </person>
        <person type="girl">
          <name>Janet</name>
          <status>fine</status>
        </person>
       </people>
     ');

     $xmoles = $xmole->get_xmoles("person");

     $this->assertEquals("boy",$xmoles[0]->get_attribute("person","type"));
     $this->assertEquals("boy",$xmoles[0]->get_attribute("type")); // u korenoveho elementu muzeme vynechat $element_path
     $this->assertEquals("Jamis",$xmoles[0]->get_data("name"));
     $this->assertEquals("happy",$xmoles[0]->get_data("status"));

     $this->assertEquals("girl",$xmoles[1]->get_attribute("person","type"));
     $this->assertEquals("girl",$xmoles[1]->get_attribute("type")); // u korenoveho elementu muzeme vynechat $element_path
     $this->assertEquals("Janet",$xmoles[1]->get_data("name"));
     $this->assertEquals("fine",$xmoles[1]->get_data("status"));

     // metoda get_child spolu s indexem vrati instanci prislusneho child elementu

     $boy = $xmole->get_child(0);
     $this->assertEquals("Jamis",$boy->get_data("name"));
     $this->assertEquals("happy",$boy->get_data("status"));

     $girl = $xmole->get_child(1);
     $this->assertEquals("Janet",$girl->get_data("name"));
     $this->assertEquals("fine",$girl->get_data("status"));

     $this->assertNull($xmole->get_child(2));

     // metodou get_next_child je mozne projit vsechny child elementy (napr. ve smycce while)

     $p = $xmole->get_next_child();
     $this->assertEquals("Jamis",$p->get_data("name"));
     $this->assertEquals("happy",$p->get_data("status"));

     $p = $xmole->get_next_child();
     $this->assertEquals("Janet",$p->get_data("name"));
     $this->assertEquals("fine",$p->get_data("status"));

     $this->assertNull($xmole->get_next_child());

     $xmole->reset_next_child_index();
     $p = $xmole->get_next_child();
     $this->assertEquals("Jamis",$p->get_data("name"));
  }

  function test_errors(){
    $xmole = new XMole();

    $this->assertEquals(false,$xmole->parse('<xml><a>FAIL</b></xml>',$err_code,$err_message));
    $this->assertEquals(76,$err_code);
    $this->assertEquals('XML parser error (76): Mismatched tag on line 1',$err_message);

    $this->assertEquals(false,$xmole->parse('',$err_code,$err_message));
    $this->assertEquals(null,$err_code);
    $this->assertEquals('empty XML data',$err_message);

    $this->assertEquals(false,$xmole->parse('<xml><a>FAIL</a>',$err_code,$err_message));
    $this->assertEquals(null,$err_code);
    $this->assertEquals('missing the end of the document',$err_message);
  }

  function test_set_encoding(){
    $xmole = new XMole();
    $this->assertEquals(null,$xmole->get_input_encoding());
    $this->assertEquals(null,$xmole->get_output_encoding());

    $xmole->set_encoding("ISO-8859-2");
    $this->assertEquals("ISO-8859-2",$xmole->get_input_encoding());
    $this->assertEquals("ISO-8859-2",$xmole->get_output_encoding());

    $xmole->set_input_encoding("UTF-8");
    $this->assertEquals("UTF-8",$xmole->get_input_encoding());
    $this->assertEquals("ISO-8859-2",$xmole->get_output_encoding());

    $xmole->set_output_encoding("WINDOWS-1250");
    $this->assertEquals("UTF-8",$xmole->get_input_encoding());
    $this->assertEquals("WINDOWS-1250",$xmole->get_output_encoding());

    // --
  
    $xmole = new XMole();
    $xmole->set_output_encoding("ASCII");
    $xmole->parse('<'.'?xml version="1.0" encoding="UTF-8"?'.'><xml><text color="hnědá">hnědá_lištička_skákala</text></xml>');
    $this->assertEquals('hneda_listicka_skakala',$xmole->get_element_data('text'));
    $this->assertEquals('hneda',$xmole->get_attribute('text','color'));
  }

  function test_encode_special_characters() {
    $this->assertEquals("&lt;", XMole::ToXML("<"));
    $this->assertEquals("&gt;", XMole::ToXML(">"));
    $this->assertEquals("&amp;", XMole::ToXML("&"));
    $this->assertEquals("nejaky textik\ndalsi\ttextik &amp; more", XMole::ToXML("nejaky textik\x07\x0adalsi\x09textik & more"));

    $this->assertEquals("A,B", XMole::ToXML("A,B"));

    $this->assertEquals("ěščřžýáíéůúĚŠČŘŽÝÁÍÉŮÚ", XMole::ToXML("ěščřžýáíéůúĚŠČŘŽÝÁÍÉŮÚ"));
  }
}
