<?php
define("ATK14_USE_SMARTY4", true);
define("ATK14_DOCUMENT_ROOT", __DIR__ );
define("USING_BOOTSTRAP4",true);
require_once('../../../../load.php');

class MockFinder {

	public $total_amount;
	public $limit;

	function __construct($total_amount,$limit){
		$this->total_amount = $total_amount;
		$this->limit = $limit;
	}

	function getTotalAmount(){ return $this->total_amount; }
	function getLimit(){ return $this->limit; }
}

class TcPaginator extends TcBase {

	function test_parameters(){
		$smarty = Atk14Utils::GetSmarty();
		$params = new Dictionary(["offset" => 20]);
		$smarty->assign([
			"namespace" => "",
			"controller" => "articles",
			"action" => "index",
			"lang" => "en",
			"params" => $params
		]);

		$output = smarty_function_paginator([
			"total_amount" => 111,
			"max_amount" => 10,
		],$smarty);

		// Total amount
		$this->assertStringContains('<span class="badge badge-secondary">111</span> items total',$output);

		// First page
		$this->assertStringContains('<li class="page-item"><a class="page-link" href="/en/articles/" rel="nofollow">1</a></li>',$output);

		// Active page
		$this->assertStringContains('<li class="page-item active"><a class="page-link" href="/en/articles/?offset=20" rel="nofollow">3</a></li>',$output);
	}

	function test_finder_assigned_into_the_template(){
		$smarty = Atk14Utils::GetSmarty();
		$params = new Dictionary([]);
		$finder = new MockFinder(123,24);
		$smarty->assign([
			"namespace" => "",
			"controller" => "articles",
			"action" => "index",
			"lang" => "en",
			"params" => $params,
			"finder" => $finder,
		]);

		$output = smarty_function_paginator([],$smarty);

		// Total amount
		$this->assertStringContains('<span class="badge badge-secondary">123</span> items total',$output);

		// First page
		$this->assertStringContains('<li class="page-item active first-child"><a class="page-link" href="/en/articles/" rel="nofollow">1</a></li>',$output);

	}
}
