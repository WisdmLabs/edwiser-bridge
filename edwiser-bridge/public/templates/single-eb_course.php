<?php

/**
 * The template for displaying all single moodle courses.
 */

/* -------------------------------------
 * INTIALIZATION START
 * Do not repalce these inititializations
 --------------------------------------*/



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
    edwiser_bridge_instance()->get_plugin_name(),
    edwiser_bridge_instance()->get_version()
);

/* -------------------------------------
 * INTIALIZATION END
 --------------------------------------*/

 get_header(); 

/* -------------------------------------
 * Content Wrapper replace this with your theme wrapper i.e comment this and add your archive.php files container div, if archive.php is not present then check template heirarchy here https://developer.wordpress.org/themes/basics/template-hierarchy/#examples 
 --------------------------------------*/

$template_loader->wp_get_template('global/wrapper-start.php', $wrapper_args); 


/* -------------------------------------
 * CONTENT START
 --------------------------------------*/

do_action('eb_before_single_course');


$ebShrtcodeWrapper =  new Eb_Shortcode_My_Courses();

while (have_posts()) :
    the_post();
    $template_loader->wp_get_template_part('content-single', get_post_type());

    $ebShrtcodeWrapper->generateRecommendedCourses();
    comments_template();
endwhile;

do_action('eb_after_single_course'); 

?>

<!-- </div> -->

<?php


    // Use this Hook to add sidebar container
do_action('eb_archive_before_sidebar', $wrapper_args);
    
get_sidebar();
    
// Use this Hook to close sidebar containers. 
do_action('eb_archive_after_sidebar', $wrapper_args);



/* -------------------------------------
 * CONTENT END
 --------------------------------------*/






/* -------------------------------------
 * Content Wrapper replace this with your theme wrapper end section i.e comment this and add your archive.php files container div end section, if archive.php is not present then check template heirarchy here https://developer.wordpress.org/themes/basics/template-hierarchy/#examples 
 --------------------------------------*/


$template_loader->wp_get_template('global/wrapper-end.php', $wrapper_args); ?>
<?php

// if (file_exists(get_template_directory_uri().'/sidebar.php')) {
    // get_sidebar();
// }
?>
<?php

get_footer();
