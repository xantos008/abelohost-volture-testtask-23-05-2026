{extends file="layouts/main.tpl"}

{block name="content"}
    <div class="category-page">

        {* ── Шапка категории ────────────────────────────────────────────── *}
        <header class="category-page__header">
            <h1 class="category-page__title">{$category.name}</h1>

            {if $category.description}
                <p class="category-page__description">
                    {$category.description}
                </p>
            {/if}
        </header>

        {* ── Панель сортировки ──────────────────────────────────────────── *}
        <div class="sort-bar">
            <span class="sort-bar__label">Сортировка:</span>

            <a
                href="/category/{$category.id}?sort=date&page=1"
                class="sort-bar__btn {if $sort === 'date'}sort-bar__btn--active{/if}"
            >
                По дате
            </a>

            <a
                href="/category/{$category.id}?sort=views&page=1"
                class="sort-bar__btn {if $sort === 'views'}sort-bar__btn--active{/if}"
            >
                По просмотрам
            </a>
        </div>

        {* ── Список статей ──────────────────────────────────────────────── *}
        {if $posts}
            <div class="post-grid">
                {foreach $posts as $post}
                    {include file="partials/post-card.tpl" post=$post}
                {/foreach}
            </div>
        {else}
            <div class="empty-state">
                <p>В этой категории пока нет статей.</p>
            </div>
        {/if}

        {* ── Пагинация ───────────────────────────────────────────────────── *}
        {*
            Передаём baseUrl без page= — pagination.tpl сам подставит номер.
            sort сохраняется при переходе между страницами.
        *}
        {assign var="baseUrl" value="/category/`$category.id`?sort=`$sort`"}
        {include
        file="partials/pagination.tpl"
        currentPage=$currentPage
        totalPages=$totalPages
        baseUrl=$baseUrl
        }

    </div>
{/block}