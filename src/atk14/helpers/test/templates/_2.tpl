	{increment_counter}
	TEMPLATE 2 ({$r})

	{assert var=a value=2 message="2-1" comment="Assigned int the super-parent template"}
	{assert var=b value=1 message="2-2" comment="Assigned in the main template" smarty3=true}
	{assert var=c value=3 message="2-3" comment="Parameter of the parent template"}
	{assert var=d value=5 message="2-4" comment="Parameter of me" smarty3=true}
	{assert var=e value=1 message="2-5" comment="From the controller" smarty3=true}
  ----
	{$r}
	{$k}
	----
	{assert_consume value=$r key=$k}
	A: {$a} = 1
	B: {$b} = 1
	C: {$c} = 3
	D: {$d} = 5
	R: {$r}
	{assign var=d value=7}
	{assign var=b value=2}
	{assign var=e value=9}
