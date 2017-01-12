<?php
/**
 * eb_courses pagination template.
 */
if ($max_num_pages <= 1) {
    return;
}
$current_page = (get_query_var('paged')) ? get_query_var('paged') : 1;
$end_size = 3;
$mid_size = 3;
$start_pages = range(1, $end_size);
$end_pages = range($max_num_pages - $end_size + 1, $max_num_pages);
$mid_pages = range($current_page - $mid_size, $current_page + $mid_size);
$pages = array_intersect(range(1, $max_num_pages), array_merge($start_pages, $end_pages, $mid_pages));
$prev_page = 0;
?>
<nav class="eb-pagination">
    <ul>
        <?php if ($current_page && $current_page > 1) : ?>
            <li><?php previous_posts_link('&larr;'); ?></li>
        <?php endif; ?>

        <?php
        foreach ($pages as $page) {
            if ($prev_page != $page - 1) {
                echo '<li><span class="gap">...</span></li>';
            }
            if ($current_page == $page) {
                echo '<li><span class="current" data-page="'.$page.'">'.$page.'</span></li>';
            } else {
                echo '<li><a href="'.get_pagenum_link($page).'" data-page="'.$page.'">'.$page.'</a></li>';
            }
            $prev_page = $page;
        }
        ?>

        <?php if ($current_page && $current_page < $max_num_pages) : ?>
            <li><?php next_posts_link('&rarr;', $max_num_pages); ?></li>
        <?php endif; ?>
    </ul>
</nav>