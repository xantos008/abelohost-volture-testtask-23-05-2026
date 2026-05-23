{*
    Переиспользуемая карточка статьи.
    Принимает переменную $post с ключами:
      id, title, description, image, views_count, published_at
*}
<article class="post-card">
    {if $post.image}
        <a href="/post/{$post.id}" class="post-card__image-link">
            <img
                src="{$post.image}"
                alt="{$post.title}"
                class="post-card__image"
                loading="lazy"
            >
        </a>
    {/if}

    <div class="post-card__body">
        <h3 class="post-card__title">
            <a href="/post/{$post.id}">{$post.title}</a>
        </h3>

        <p class="post-card__description">{$post.description}</p>

        <footer class="post-card__meta">
            <time
                    class="post-card__date"
                    datetime="{$post.published_at}"
            >
                {$post.published_at|date_format:"%d.%m.%Y"}
            </time>

            <span class="post-card__views">
                👁 {$post.views_count}
            </span>
        </footer>
    </div>
</article>