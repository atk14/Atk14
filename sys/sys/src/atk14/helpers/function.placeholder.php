<?
/**
* <html>
* <head>
*		<title>My Shiny Web Application</title>
*		{placeholder for="head"}
* </head>
*	<body>
* 	{placeholder} {* stands for {placeholder for="main"} *}
*	</body>
*	</html>
*/
function smarty_function_placeholder($params,&$smarty){
	$id = isset($params["for"]) ? $params["for"] : "main"; 
	if(!isset($smarty->atk14_contents[$id])){ $smarty->atk14_contents[$id] = ""; }
	return "<%atk14_content[$id]%>"; // returns an internal sign, which will be replaced later within controller
}
?>
