<h2>{$page_title}</h2>

{form}
	<fieldset>
		{render partial=shared/form_field field=q}

		<div class="buttons">
			<button type="submit">{t}Search{/t}</button>
		</div>
	</fieldset>
{/form}

<p>{a action=create_new}{t}Create a new creature{/t}{/a}</p>

{if $finder}
	{if $finder->isEmpty()}
		<p>{t}No creature was found.{/t}</p>
	{else}

		<table>
			<thead>
				<tr>
					<th>{t}Id{/t}</th>
					<th>{t}Name{/t}</th>
					<th></th>
				</tr>
			</thead>
			<tbody>
				{render partial=creature_item from=$finder->getRecords() item=creature}
			</tbody>
		</table>

		{paginator}

	{/if}
{/if}

<hr />
<p>
	{t escape=no}<em>The Creature Show</em> gives you a opportunity to inspect a functional ATK14 code immediatelly after a new web-app installation.{/t}<br />
	{t}For a beginner there is a lot of things to study. Inspect following files and direcories.{/t}
</p>

<pre><code>db/migrations/0001_table_creatures.sql
db/migrations/0002_content_for_creatures.sql
db/migrations/0003_more_content_for_creatures.php
app/models/creature.php
app/controllers/creatures_controller.php
app/views/creatures/*
app/forms/creatures/*
test/models/tc_creature.php
test/controllers/tc_creatures.php
config/routers/default_router.php</code></pre>
