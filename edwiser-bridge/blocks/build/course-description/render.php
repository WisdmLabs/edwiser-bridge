<?php

$post_id = get_the_ID();

if (get_post_type($post_id) !== 'eb_course') {
    $course_id = get_post_meta($post_id, 'courseId', true);
} else {
    $course_id = $post_id;
}

echo '<div ' . get_block_wrapper_attributes() . '>';
echo sprintf(
    '<div id="eb-course-description" 
            data-course-id="%s"
            data-show-recommended-courses="%s"
        ></div></div>',
    esc_attr($course_id),
    esc_attr($attributes['showRecommendedCourses'] ? 'true' : 'false')
);
