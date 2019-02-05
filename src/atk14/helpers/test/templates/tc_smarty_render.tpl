{increment_counter}
{assert var=b value=null message=T1}
{assign var=b value=1}
{assert var=b value=1 message="T1.1"}
{assert var=a value=1 message=T2}
{assert var=c value=null message=T3}
{assert var=e value=1 message=T4}

{render partial='1' c=2}

{assert var=a value=1 message=T5}
TEMPLATE MAIN
A: {$a} = 1
B: {$b} = 1
C: {$c} =
D: {$d} =
{render partial=tc_smarty_render_item from=$one item_name='tc_smarty_render'}
{render partial=tc_smarty_render_item from=$one item=item item_name='item'}
{render partial=tc_smarty_render_item from=$one item=item item_name='item'}
