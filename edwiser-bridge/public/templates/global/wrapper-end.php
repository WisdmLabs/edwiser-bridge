<?php
/**
 * Primary wrapper end HTML content.
 */
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

$template = get_option('template');

switch ($template) {
    case 'twentysixteen':
        echo '</main></div>';
        if ($enable_right_sidebar) {
            if (is_active_sidebar($sidebar_id)) {
            ?>
                <aside id="secondary" class="sidebar widget-area" role="complementary">
                    <?php dynamic_sidebar($sidebar_id); ?>
                </aside>
            <?php
            }
        }
        break;
    case 'twentyseventeen':
        echo '</main></div>';
        if ($enable_right_sidebar) {
            if (is_active_sidebar($sidebar_id)) {
            ?>
                <aside id="secondary" class="widget-area" role="complementary">
                    <?php dynamic_sidebar($sidebar_id); ?>
                </aside>
            <?php
            }
        }
        echo '</div>';
        break;
    default:
        echo '</div></div>';
        if ($enable_right_sidebar) {
            if (is_active_sidebar($sidebar_id)) {
            ?>
                <aside id="secondary" class="sidebar widget-area" role="complementary">
                    <?php dynamic_sidebar($sidebar_id); ?>
                </aside>
            <?php
            }
        }
        break;
}
