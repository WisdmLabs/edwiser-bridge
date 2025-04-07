import React from 'react';
import Carousel from 'react-multi-carousel';
import Course from './course';

function CourseCarousel({ courses }) {
  const carouselResponsive = {
    superLargeDesktop: {
      breakpoint: { max: 4000, min: 1200 },
      items: 3,
    },
    desktop: {
      breakpoint: { max: 1200, min: 992 },
      items: 3,
    },
    tablet: {
      breakpoint: { max: 992, min: 768 },
      items: 2,
    },
    mobile: {
      breakpoint: { max: 768, min: 576 },
      items: 2,
    },
    smallMobile: {
      breakpoint: { max: 576, min: 0 },
      items: 1,
    },
  };

  return (
    <Carousel
      responsive={carouselResponsive}
      infinite={false}
      showDots={false}
      arrows={true}
      centerMode={false}
    >
      {courses.map((course) => (
        <div
          key={course.id}
          className="eb-courses__carousel-item-wrapper"
          style={{
            padding: '2px 32px 2px 0',
            height: '100%',
            boxSizing: 'border-box',
          }}
        >
          <Course course={course} />
        </div>
      ))}
    </Carousel>
  );
}

export default CourseCarousel;
