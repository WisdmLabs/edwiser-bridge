<?php

/**
 * The template for displaying all single moodle courses.
 */
namespace app\wisdmlabs\edwiserBridge;

$wrapper_args = array();

$eb_template = get_option('eb_template');
if (isset($eb_template['single_enable_right_sidebar']) && $eb_template['single_enable_right_sidebar'] === 'yes') {
    $wrapper_args['enable_right_sidebar'] = true;
    $wrapper_args['parentcss'] = '';
} else {
    $wrapper_args['enable_right_sidebar'] = false;
    $wrapper_args['parentcss'] = 'width:100%;';
}
$wrapper_args['sidebar_id'] = isset($eb_template['single_right_sidebar']) ? $eb_template['single_right_sidebar'] : '';

$template_loader = new EbTemplateLoader(
    edwiserBridgeInstance()->getPluginName(),
    edwiserBridgeInstance()->getVersion()
);
?>

<?php get_header(); ?>

<?php $template_loader->wpGetTemplate('global/wrapper-start.php', $wrapper_args); ?>

<?php do_action('eb_before_single_course'); ?>
<?php

$ebShrtcodeWrapper =  new EbShortcodeMyCourses();

while (have_posts()) :
    the_post();
    $template_loader->wpGetTemplatePart('content-single', get_post_type());

    $ebShrtcodeWrapper->generateRecommendedCourses();
    comments_template();
endwhile;
?>
<?php do_action('eb_after_single_course'); ?>

<?php $template_loader->wpGetTemplate('global/wrapper-end.php', $wrapper_args); ?>
<?php

if (file_exists(get_template_directory_uri().'/sidebar.php')) {
    get_sidebar();
}
?>
<?php

get_footer();
