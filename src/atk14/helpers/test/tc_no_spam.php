<?php
class TcNoSpam extends TcBase {
	function test(){
		//block
		$template = null;
		$repeat = false;
		$this->assertEquals('<span class="atk14_no_spam">samantha[at-sign]doe[dot-sign]com</span>',smarty_block_no_spam(array(),'samantha@doe.com',$template,$repeat));
		$this->assertEquals('<span class="atk14_no_spam" data-text="Contact Samantha Doe">samantha[at-sign]doe[dot-sign]com</span>',smarty_block_no_spam(array("text" => "Contact Samantha Doe"),'samantha@doe.com',$template,$repeat));
		$this->assertEquals('<span class="atk14_no_spam" data-text="Contact Samantha Doe" data-attrs="{&quot;class&quot;:&quot;btn btn-primary&quot;}">samantha[at-sign]doe[dot-sign]com</span>',smarty_block_no_spam(array("text" => "Contact Samantha Doe","class" => "btn btn-primary"),'samantha@doe.com',$template,$repeat));

		// modifier
		$this->assertEquals('<span class="atk14_no_spam">jon[at-sign]doe[dot-sign]com</span>',smarty_modifier_no_spam("jon@doe.com"));
		$this->assertEquals('<span class="atk14_no_spam" data-text="Contact John Doe">jon[at-sign]doe[dot-sign]com</span>',smarty_modifier_no_spam("jon@doe.com","text=Contact John Doe"));
		$this->assertEquals('<span class="atk14_no_spam" data-text="Contact John Doe" data-attrs="{&quot;class&quot;:&quot;btn btn-primary&quot;}">jon[at-sign]doe[dot-sign]com</span>',smarty_modifier_no_spam("jon@doe.com","text=Contact John Doe,class=btn btn-primary"));
	}
}
