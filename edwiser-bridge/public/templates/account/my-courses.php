        <section class="eb-user-courses">
            <div class="course-heading" >
                <span><?php _e('S.No.', 'eb-textdomain');
        ?></span>
                <span><?php _e('Enrolled Courses', 'eb-textdomain');
        ?></span>
            </div>
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
                    ?>
                    <p class="eb-no-course">
                        <?php
                        printf(
                            __(
                                'Looks like you are not enrolled in any course, get your first course %s',
                                'eb-textdomain'
                            ),
                            '<a href="'.esc_url(site_url('/courses')).'">'.__('here', 'eb-textdomain').'</a>.'
                        );
                    ?>
                    </p>
                    <?php
                }
        ?>
            </div>
        </section>
