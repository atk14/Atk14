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
		$smarty->assign([
			"namespace" => "",
			"controller" => "articles",
			"action" => "index",
			"lang" => "en",
			"params" => new Dictionary(["offset" => 20]),
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
		$finder = new MockFinder(123,24);
		$smarty->assign([
			"namespace" => "",
			"controller" => "articles",
			"action" => "index",
			"lang" => "en",
			"params" => new Dictionary([]),
			"finder" => $finder,
		]);

		$output = smarty_function_paginator([],$smarty);

		// Total amount
		$this->assertStringContains('<span class="badge badge-secondary">123</span> items total',$output);

		// First page
		$this->assertStringContains('<li class="page-item active first-child"><a class="page-link" href="/en/articles/" rel="nofollow">1</a></li>',$output);

	}

	// The "limit" param (independent from max_amount) controls how many items around
	// the current offset are marked as active, and where the "next" link points to.
	function test_limit_param_widens_the_active_range(){
		$smarty = Atk14Utils::GetSmarty();
		$smarty->assign([
			"namespace" => "",
			"controller" => "articles",
			"action" => "index",
			"lang" => "en",
			"params" => new Dictionary(["offset" => 20]),
		]);

		$output = smarty_function_paginator([
			"total_amount" => 111,
			"max_amount" => 10,
			"limit" => 20,
		],$smarty);

		$this->assertStringContains('<li class="page-item active"><a class="page-link" href="/en/articles/?offset=20" rel="nofollow">3</a></li>',$output);
		$this->assertStringContains('<li class="page-item active"><a class="page-link" href="/en/articles/?offset=30" rel="nofollow">4</a></li>',$output);
		$this->assertStringContains('<a class="page-link" href="/en/articles/?offset=40" rel="nofollow">Next',$output);
	}

	// Explicitly passed params take precedence over a finder, but only for the fields
	// actually passed - a field not passed still falls back to the finder's value.
	function test_explicit_params_take_precedence_over_finder_per_field(){
		$smarty = Atk14Utils::GetSmarty();
		$finder = new MockFinder(9999,999); // would produce a totally different listing
		$smarty->assign([
			"namespace" => "",
			"controller" => "articles",
			"action" => "index",
			"lang" => "en",
			"params" => new Dictionary(["offset" => 20]),
			"finder" => $finder,
		]);

		$output = smarty_function_paginator([
			"total_amount" => 111,
			"max_amount" => 10,
			// "limit" intentionally not passed -> should still come from the finder (999)
		],$smarty);

		// total_amount/max_amount from params, not from the finder's 9999
		$this->assertStringContains('<span class="badge badge-secondary">111</span> items total',$output);

		// limit still taken from the finder (999), so every page is within the active range
		$this->assertStringContains('<li class="page-item active"><a class="page-link" href="/en/articles/?offset=110" rel="nofollow">12</a></li>',$output);
	}

	// No pagination links are rendered when everything fits within max_amount.
	function test_no_pagination_links_when_total_amount_fits_in_one_page(){
		$smarty = Atk14Utils::GetSmarty();
		$smarty->assign([
			"namespace" => "",
			"controller" => "articles",
			"action" => "index",
			"lang" => "en",
			"params" => new Dictionary([]),
		]);

		$output = smarty_function_paginator([
			"total_amount" => 8,
			"max_amount" => 10,
		],$smarty);

		$this->assertStringContains('<span class="badge badge-secondary">8</span> items total',$output);
		$this->assertStringNotContains('<ul class="pagination">',$output);
	}

	// total_amount/max_amount/limit passed as params must be cast to int,
	// so arbitrary strings can't leak into the rendered HTML.
	function test_total_amount_param_is_cast_to_int(){
		$smarty = Atk14Utils::GetSmarty();
		$smarty->assign([
			"namespace" => "",
			"controller" => "articles",
			"action" => "index",
			"lang" => "en",
			"params" => new Dictionary([]),
		]);

		$output = smarty_function_paginator([
			"total_amount" => "111<script>alert(1)</script>",
			"max_amount" => 10,
		],$smarty);

		$this->assertStringContains('<span class="badge badge-secondary">111</span> items total',$output);
		$this->assertStringNotContains('<script>',$output);
	}


	function test_total_amount_is_small(){
		$smarty = Atk14Utils::GetSmarty();
		$finder = new MockFinder(5,20);
		$smarty->assign([
			"namespace" => "",
			"controller" => "articles",
			"action" => "index",
			"lang" => "en",
			"params" => new Dictionary([]),
			"finder" => $finder,
		]);

		$output = smarty_function_paginator([],$smarty);

		$this->assertEquals(trim('
<div class="pagination-container">
<p><span class="badge badge-secondary">5</span> items total</p>
</div>
		'),$output);
	}

	// If $total_amount is less than 5, no content is generated
	function test_total_amount_is_too_small(){
		$smarty = Atk14Utils::GetSmarty();
		$finder = new MockFinder(4,20);
		$smarty->assign([
			"namespace" => "",
			"controller" => "articles",
			"action" => "index",
			"lang" => "en",
			"params" => new Dictionary([]),
			"finder" => $finder,
		]);

		$output = smarty_function_paginator([],$smarty);

		$this->assertEquals("",$output);
	}
}
