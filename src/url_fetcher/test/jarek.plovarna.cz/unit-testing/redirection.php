<?php
switch($_GET["type"]){
	case "absolute":
		header("Location: /unit-testing/content.php?type=absolute");
		break;
	case "relative":
		header("Location: content.php?type=relative");
		break;
	default:
		header("Location: http://jarek.plovarna.cz/unit-testing/content.php?type=full_address");
}
