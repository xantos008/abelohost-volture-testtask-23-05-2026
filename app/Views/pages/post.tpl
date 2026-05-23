{extends file="layouts/main.tpl"}

{block name="content"}
    <article class="post-page">

        {* ── Шапка статьи ───────────────────────────────────────────────── *}
        <header class="post-page__header">
            <h1 class="post-page__title">{$post.title}</h1>

            <div class="post-page__meta">

                {* Категории *}
                {if $categories}
                    <div class="post-page__categories">
                        {foreach $categories as $category}
                            <a
                                href="/category/{$category.id}"
                                class="tag"
                            >
                                {$category.name}
                            </a>
                        {/foreach}
                    </div>
                {/if}

                {* Дата и просмотры *}
                <div class="post-page__stats">
                    <time
                            class="post-page__date"
                            datetime="{$post.published_at}"
                    >
                        {$post.published_at|date_format:"%d %B %Y"}
                    </time>

                    <span class="post-page__views">
                    👁 {$post.views_count}
                </span>
                </div>

            </div>
        </header>

        {* ── Обложка статьи ─────────────────────────────────────────────── *}
        {if $post.image}
            <div class="post-page__cover">
                <img
                        src="{$post.image}"
                        alt="{$post.title}"
                        class="post-page__image"
                >
            </div>
        {/if}

        {* ── Описание (лид) ─────────────────────────────────────────────── *}
        {if $post.description}
            <p class="post-page__lead">{$post.description}</p>
        {/if}

        {* ── Основной контент ───────────────────────────────────────────── *}
        {*
            nofilter отключает автоэкранирование Smarty для этого поля.
            Это сознательное решение: контент хранится в БД как доверенный HTML,
            который вносит редактор/администратор, а не конечный пользователь.
            Для пользовательского ввода (комментарии и т.п.) nofilter
            применять НЕЛЬЗЯ.
        *}
        <div class="post-page__content">
            {$post.content nofilter}
        </div>

        {* ── Футер статьи ───────────────────────────────────────────────── *}
        <footer class="post-page__footer">
            <a href="/" class="btn btn--outline">
                &larr; На главную
            </a>

            {if $categories}
                <div class="post-page__back-links">
                    {foreach $categories as $category}
                        <a
                            href="/category/{$category.id}"
                            class="btn btn--outline"
                        >
                            &larr; {$category.name}
                        </a>
                    {/foreach}
                </div>
            {/if}
        </footer>

    </article>

    {* ── Похожие статьи ─────────────────────────────────────────────────── *}
    {include file="partials/related-posts.tpl" relatedPosts=$relatedPosts}

{/block}