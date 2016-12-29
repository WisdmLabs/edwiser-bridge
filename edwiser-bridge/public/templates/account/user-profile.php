<div class="eb-user-profile" >

<?php
if (isset($_GET['eb_action']) && $_GET['eb_action'] === 'edit-profile') {
    $template_loader->wpGetTemplate(
        'account/edit-user-profile.php',
        array(
            'user_avatar' => $user_avatar,
            'user' => $user,
            'user_meta' => $user_meta,
            'enrolled_courses' => $enrolled_courses,
            'template_loader' => $template_loader
        )
    );
} else {
    ?>
    <section class="eb-user-info">
        <aside class="eb-user-picture">
            <?php echo $user_avatar; ?>
        </aside>
        <div class="eb-user-data">
            <?php echo '<div>'.@$user->first_name.' '.@$user->last_name.'</div>'; ?>
            <?php echo '<div>'.$user->user_email.'</div>'; ?>
        </div>

        <div class="eb-edit-profile" >
            <a href="<?php echo esc_url(add_query_arg('eb_action', 'edit-profile', get_permalink())); ?>" class="wdm-btn">Edit Profile</a>
        </div>
    </section>
    <?php
}
?>

    <section class="eb-user-courses">
        <div class="course-heading" ><span>S.No.</span> <span>Enrolled Courses</span></div>
        <div class="eb-course-data">
<?php
if (!empty($enrolled_courses)) {
    foreach ($enrolled_courses as $key => $course) {
        echo '<div class="eb-course-section course_'.$course->ID.'">';
        echo '<div>'.($key + 1).'. </div>';
        echo '<div><a href="'.get_the_permalink($course->ID).'">'.$course->post_title.'</a></div>';
        echo app\wisdmlabs\edwiserBridge\EBPaymentManager::accessCourseButton($course->ID);
        echo '</div>';
    }
} else {
    echo '<p class="eb-no-course">
            Looks like you are not enrolled in any course, get your first course
            <a href="'.site_url('/courses').'">Here</a>
        </p>';
}
?>
        </div>
    </section>

</div>