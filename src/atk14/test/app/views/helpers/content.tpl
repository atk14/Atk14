{trim}

A song just for fun

{* Page title *}
{content for=title strategy=prepend}La Musique{/content}

{* Greeting *}
{content for=greeting strategy=replace}Hello{/content}

{* Five little monkeys *}
{content for=monkeys strategy=prepend}jumping on the bed. {/content}
{content for=monkeys strategy=prepend}Five little monkeys {/content}

{content for=monkeys} Mama called the doctor and the doctor said,{/content}
{content for=monkeys strategy=append} "No more monkeys jumping on the bed!"{/content}

{/trim}
