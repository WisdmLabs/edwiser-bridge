import { MantineProvider, Pagination } from '@mantine/core';
import apiFetch from '@wordpress/api-fetch';
import { __ } from '@wordpress/i18n';
import React, { useEffect, useState } from 'react';
import CourseControls from './components/course-controls';
import CourseGrid, { CourseGridSkeleton } from './components/course-grid';
import CourseCarousel from './components/course-carousel';

function CourseListing({
  pageTitle = '',
  hideTitle = false,
  hideFilters = false,
  coursesPerPage = 9,
  groupByCategory = false,
  categoryPerPage = 3,
  categories = '',
  horizontalScroll = false,
}) {
  const [courses, setCourses] = useState([]);
  const [categoriesList, setCategoriesList] = useState([]);
  const [categorizedCourses, setCategorizedCourses] = useState({});
  const [displayedCategories, setDisplayedCategories] = useState([]);
  const [searchTerm, setSearchTerm] = useState('');
  const [debouncedSearchTerm, setDebouncedSearchTerm] = useState('');
  const [sortOrder, setSortOrder] = useState('latest');
  const [selectedCategory, setSelectedCategory] = useState('all');
  const [currentPage, setCurrentPage] = useState(1);
  const [totalPages, setTotalPages] = useState(1);
  const [isLoading, setIsLoading] = useState(true);

  // Debounce search term to reduce API calls
  useEffect(() => {
    const timer = setTimeout(() => {
      setDebouncedSearchTerm(searchTerm);
    }, 750);

    return () => clearTimeout(timer);
  }, [searchTerm]);

  // Fetch courses based on filters and pagination
  useEffect(() => {
    const fetchCourses = async () => {
      setIsLoading(true);

      // Construct dynamic API path with filters
      let path = `/eb/api/v1/courses?page=${currentPage}&per_page=${coursesPerPage}&search=${debouncedSearchTerm}&sort_order=${sortOrder.toLowerCase()}`;

      // Add category filter if not set to "All"
      if (selectedCategory !== 'all') {
        const categoryObj = categoriesList.find(
          (cat) => cat.name.toLowerCase() === selectedCategory
        );
        if (categoryObj) {
          path += `&category=${categoryObj.slug}`;
        }
      }

      // Add the admin-specified categories filter (if provided)
      if (categories) {
        path += `&categories=${categories}`;
      }

      // Add grouping parameters
      if (groupByCategory) {
        path += `&group_by_category=true`;
        if (categoryPerPage > 0) {
          path += `&category_per_page=${categoryPerPage}`;
        }
      }

      // Add horizontal scroll parameter
      if (horizontalScroll) {
        path += `&horizontal_scroll=true`;

        // If horizontal scroll without groupByCategory, get all courses
        if (!groupByCategory) {
          path += `&per_page=-1`; // Get all courses for carousel
        }
      }

      try {
        const response = await apiFetch({ path });

        if (groupByCategory) {
          setCategorizedCourses(response.categorized_courses || {});
          setDisplayedCategories(response.displayed_categories || []);
        } else {
          setCourses(response.courses || []);
        }

        setCategoriesList(response.categories || []);
        setTotalPages(response.total_pages || 1);
      } catch (error) {
        console.error('Error fetching courses:', error);
        setCourses([]);
        setCategorizedCourses({});
        setDisplayedCategories([]);
      } finally {
        setIsLoading(false);
      }
    };

    fetchCourses();
  }, [
    debouncedSearchTerm,
    sortOrder,
    selectedCategory,
    currentPage,
    groupByCategory,
    categoryPerPage,
    categories,
    horizontalScroll,
  ]);

  return (
    <MantineProvider>
      <div className="eb-courses__wrapper">
        {!hideTitle && (
          <h2 className="eb-title" style={{ marginBottom: '16px' }}>
            {__(pageTitle, 'edwiser-bridge')}
          </h2>
        )}
        {!hideFilters && (
          <CourseControls
            searchTerm={searchTerm}
            setSearchTerm={setSearchTerm}
            sortOrder={sortOrder}
            setSortOrder={setSortOrder}
            selectedCategory={selectedCategory}
            setSelectedCategory={setSelectedCategory}
            categories={categoriesList}
          />
        )}
        {isLoading ? (
          <CourseGridSkeleton coursesPerPage={coursesPerPage} />
        ) : groupByCategory ? (
          displayedCategories.map((category) => (
            <div key={category.id} className="eb-courses__categorized-courses">
              <h3 className="eb-courses__categorized-title">{category.name}</h3>
              {horizontalScroll ? (
                <CourseCarousel
                  courses={categorizedCourses[category.slug] || []}
                />
              ) : (
                <CourseGrid courses={categorizedCourses[category.slug] || []} />
              )}
            </div>
          ))
        ) : horizontalScroll ? (
          <CourseCarousel courses={courses} />
        ) : (
          <CourseGrid courses={courses} />
        )}

        {/* Show pagination only if not using horizontal scroll without grouping */}
        {(!horizontalScroll || groupByCategory) && totalPages > 1 && (
          <div className="eb-courses__pagination">
            <Pagination
              total={totalPages}
              value={currentPage}
              onChange={setCurrentPage}
              disabled={isLoading}
            />
          </div>
        )}
      </div>
    </MantineProvider>
  );
}

export default CourseListing;
