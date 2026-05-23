{*
    Переиспользуемый блок пагинации.

    Ожидаемые переменные:
      $currentPage  — текущая страница (int)
      $totalPages   — всего страниц (int)
      $baseUrl      — базовый URL без page= (string)
                      пример: "/category/3?sort=date"
*}
{if $totalPages > 1}
    <nav class="pagination" aria-label="Навигация по страницам">

        {* Кнопка «Назад» *}
        {if $currentPage > 1}
            <a
                href="{$baseUrl}&page={$currentPage - 1}"
                class="pagination__btn pagination__btn--prev"
                aria-label="Предыдущая страница"
            >
                &larr;
            </a>
        {else}
            <span class="pagination__btn pagination__btn--prev pagination__btn--disabled">
            &larr;
        </span>
        {/if}

        {* Номера страниц *}
        {section name="p" start=1 loop=$totalPages+1 step=1}
            {assign var="pageNum" value=$smarty.section.p.index}

            {if $pageNum == $currentPage}
                <span class="pagination__page pagination__page--active">
                {$pageNum}
            </span>
            {else}
                <a
                    href="{$baseUrl}&page={$pageNum}"
                    class="pagination__page"
                >
                    {$pageNum}
                </a>
            {/if}
        {/section}

        {* Кнопка «Вперёд» *}
        {if $currentPage < $totalPages}
            <a
                href="{$baseUrl}&page={$currentPage + 1}"
                class="pagination__btn pagination__btn--next"
                aria-label="Следующая страница"
            >
                &rarr;
            </a>
        {else}
            <span class="pagination__btn pagination__btn--next pagination__btn--disabled">
            &rarr;
        </span>
        {/if}

    </nav>
{/if}