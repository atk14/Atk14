<?php
class tc_sendhtmlmail extends tc_base{

	function test(){
		$ar = sendhtmlmail(array(
			"from" => "info@test.cz",
			"subject" => "Toto je testovaní, šefíku!",
			"charset" => "UTF-8",

			"to" => "jarek@plovarna.cz",
			"plain" => "Plain text version",
			"html" => '
        <html>
          <body>
            <h1>Html version</h1>
            
            <img src="cid:rhSSwwo">
            <p>That\'s a nice image. Isn\'t it?</p>

            <img src="cid:jau72aakKJD">
            <p>Fine color.</p>
            
          </body>
        </html>',
			"images" => array(
				array(
					"filename" => "120_x_120.jpg",
					"content" => Files::GetFileContent("120_x_120.jpg",$err,$err_str),
					"cid" => "rhSSwwo"
				),
				array(
					"filename" => "red_box.png",
					"content" => Files::GetFileContent("red_box.png",$err,$err_str),
					"cid" => "jau72aakKJD"
				)
			),
		));
		$this->assertEquals(true,is_null($ar["accepted_for_delivery"])); // messages are not sent in testing environment
		$this->assertEquals('-finfo@test.cz',$ar["additional_parameters"]);
		//var_dump($ar);

		/*
		echo "Subject: ".$ar["subject"]."\n";
		echo "To: ".$ar["to"]."\n";
		echo $ar["headers"]."\n";
		echo $ar["body"]."\n\n";
		*/
		//TODO: do some testing
	}
}
