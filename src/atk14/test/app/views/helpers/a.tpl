<h1>Books</h1>

{a controller=books}List Books{/a} |
{a controller=books action=detail id=123 _title="Book info"}Book#123{/a} |
{a action="books/detail" id=456 _anchor=detail}Book#456{/a}
{a action="books/detail" id=789 _anchor=detail _class=""}Book#789{/a}
{a action="books/detail" id=7890 _anchor=detail _class="active"}Book#7890{/a}
