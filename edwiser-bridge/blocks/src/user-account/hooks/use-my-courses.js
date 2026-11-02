import { useState, useEffect } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';

export default function useMyCourses(recommendedCoursesCount) {
  const [enrolledCourses, setEnrolledCourses] = useState([]);
  const [recommendedCourses, setRecommendedCourses] = useState([]);
  const [coursesPageUrl, setCoursesPageUrl] = useState('');
  const [isLoading, setIsLoading] = useState(true);

  // Data fetching effect
  useEffect(() => {
    const fetchCourses = async () => {
      try {
        // Fetch data from the API endpoint
        const response = await apiFetch({
          path: `/eb/api/v1/my-courses?number_of_recommended_courses=${recommendedCoursesCount}`,
        });

        // Update data state with response
        setEnrolledCourses(response.enrolled_courses);
        setRecommendedCourses(response.recommended_courses);
        setCoursesPageUrl(response.courses_page_url);
      } catch (error) {
        console.error('Error fetching courses:', error);
      } finally {
        setIsLoading(false);
      }
    };

    fetchCourses();
  }, []);

  return {
    enrolledCourses,
    recommendedCourses,
    coursesPageUrl,
    isLoading,
  };
}
