{render partial="article_item" from=$articles_set_1} {* item is set to "article" automatically *}

{render partial="article" from=$articles_set_2} {* item is set to "article" automatically *}

{render partial="a_item" from=$articles_set_3 item=article} {* item is set to "article" explicitly *}
