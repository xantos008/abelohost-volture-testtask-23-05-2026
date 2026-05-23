{extends file="layouts/main.tpl"}

{block name="content"}
    <h1>Последние публикации</h1>

    {if $categories}
        {foreach $categories as $category}
            <section class="category-block">
                <h2>{$category.name}</h2>
                {if $category.description}
                    <p>{$category.description}</p>
                {/if}
                {* Статьи добавим в следующем этапе *}
                <a href="/category/{$category.id}" class="btn">Все статьи</a>
            </section>
        {/foreach}
    {else}
        <p>Категории пока не добавлены.</p>
    {/if}
{/block}