{increment_counter}
{if $c==3}
	{render partial="2" d=5}
	{render partial="2" d=5 from=$array item=r key=k}
	{assert var=a value=2 message="1i1" comment="Assigned in parent"}
	{assign var=a value=5}
	TEMPLATE 1 inner
	{assert var=a value=5 message="1i1.1" comment="Assigned here"}
	{assert var=b value=1 message="1i1" comment="Assigned in the main template"}
	{assert var=c value=3 message="1i3" comment="Parameter of the current template"}
	{assert var=d value=6 message="1i3" comment="Parameter of the current template"}
	{assert var=e value=1 message="1i4" comment="From the controller"}
	A: {$a} = 5
	B: {$b} = 1
	C: {$c} = 3
	D: {$d} = 6
	E: {$e} = 1
{else}
	TEMPLATE 1 outer BEFORE
	{assert var=a value=1 message="1ob1.1" comment="From the controller"}
	{assert var=b value=1 message="1ob1" comment="Assigned in the main template"}
	{assert var=c value=2 message="1ob3" comment="Parameter of the current template"}
	{assert var=d value=null message="1ob3" comment="Not set"}
	{assert var=e value=1 message="1ob4" comment="From the controller"}
	A: {$a} = 1
	B: {$b} = 1
	C: {$c} = 2
	D: {$d} = 
	E: {$e} = 1
	{assign var=a value=2}
	{render partial="1" c=3 d=6}
	TEMPLATE 1 outer
	{assign var=d value=6}
	{assert var=a value=2 message="1oa1.1" comment="Assigned here"}
	{assert var=b value=1 message="1oa1" comment="Assigned in the main template"}
	{assert var=c value=2 message="1oa3" comment="Parameter of the current template"}
	{assert var=d value=6 message="1oa3" comment="Assigned here"}
	{assert var=e value=1 message="1oa4" comment="From the controller"}
{/if}
