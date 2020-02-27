<?php
/**
 * Partial: Page - Extensions.
 *
 * @var object
 */
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
?>

<div class="wrap edwiser eb_extensions_wrap">
    <h2>
        <?php _e('Our Extensions', 'eb-textdomain'); ?>
        <a href="https://edwiser.org/bridge/extensions/" target="_blank" class="add-new-h2">
            <?php _e('Browse all extensions', 'eb-textdomain'); ?>
        </a>
    </h2>
    <br />
    <?php
    if ($extensions) {
        ?>
        <ul class="extensions">
            <?php
            $extensions = $extensions->popular;
            $i = 0;
            foreach ($extensions as $extension) {
                if ($i > 7) {
                    break;
                }

                echo '<li class="product" title="' . __('Click here to know more', 'eb-textdomain') . '">';
                echo '<a href="'.$extension->link.'" target="_blank">';
                if (!empty($extension->image)) {
                    echo '<img src="'.$extension->image.'"/>';
                } else {
                    echo '<h3>'.$extension->title.'</h3>';
                }
                    //echo '<span class="price">' . $extension->price . '</span>';
                    echo '<p>'.$extension->excerpt.'</p>';
                    echo '</a>';
                    echo '</li>';
                    ++$i;
            }
            ?>
        </ul>
        <br />
        <a href="https://edwiser.org/bridge/extensions/" target="_blank" class="browse-all">
            <?php _e('Browse all our extensions', 'eb-textdomain');?>
        </a>
        <?php
    } else {
        ?>
        <p>
            <?php
            printf(
                __(
                    'Our list of extensions for Edwiser Bridge can be found here:
            <a href="%s" target="_blank">Edwiser Bridge Extensions</a>',
                    'eb-textdomain'
                ),
                'https://edwiser.org/bridge/extensions/'
            );
            ?>
        </p>
        <?php
    }
    ?>
</div>
