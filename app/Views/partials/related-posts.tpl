{*
    Блок похожих статей.

    Ожидаемые переменные:
      $relatedPosts — list<array> с ключами: id, title, description,
                      image, views_count, published_at
*}
{if $relatedPosts}
    <aside class="related-posts">
        <h2 class="related-posts__title">Похожие статьи</h2>

        <div class="post-grid post-grid--related">
            {foreach $relatedPosts as $post}
                {include file="partials/post-card.tpl" post=$post}
            {/foreach}
        </div>
    </aside>
{/if}