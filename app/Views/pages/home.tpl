{extends file="layouts/main.tpl"}

{block name="content"}
    <div class="home-page">
        <h1 class="page-title">Последние публикации</h1>

        {if $categories}
            {foreach $categories as $category}
                <section class="category-section">

                    <header class="category-section__header">
                        <div class="category-section__meta">
                            <h2 class="category-section__title">
                                <a href="/category/{$category.id}">
                                    {$category.name}
                                </a>
                            </h2>

                            {if $category.description}
                                <p class="category-section__description">
                                    {$category.description}
                                </p>
                            {/if}
                        </div>

                        <a
                            href="/category/{$category.id}"
                            class="btn btn--outline"
                        >
                            Все статьи
                        </a>
                    </header>

                    {if $category.posts}
                        <div class="post-grid">
                            {foreach $category.posts as $post}
                                {include file="partials/post-card.tpl" post=$post}
                            {/foreach}
                        </div>
                    {else}
                        <p class="no-posts">В этой категории пока нет статей.</p>
                    {/if}

                </section>
            {/foreach}
        {else}
            <div class="empty-state">
                <p>Категории пока не добавлены.</p>
            </div>
        {/if}
    </div>
{/block}