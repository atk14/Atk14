<?php
switch($_GET["type"]){
	case "absolute":
		header("Location: /unit-testing/content.php?type=absolute");
		break;
	case "relative":
		header("Location: content.php?type=relative");
		break;
	case "full_address_without_closing_slash":
		header("Location: https://example.com");
		break;
	default:
		header("Location: http://jarek.plovarna.cz/unit-testing/content.php?type=full_address");
}
