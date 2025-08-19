import React, { useState, useMemo } from 'react';
import { TextInput, Select, Pagination, Skeleton } from '@mantine/core';
import { Icons } from '../icons';
import { __ } from '@wordpress/i18n';
import { useOrders } from '../../hooks/use-orders';
import { decodeHTMLEntities } from '../../utils';

function Orders() {
  const { orders, isLoading } = useOrders();
  const [search, setSearch] = useState('');
  const [sortBy, setSortBy] = useState(null);
  const [reverseSortDirection, setReverseSortDirection] = useState(false);
  const [page, setPage] = useState(1);
  const [pageSize, setPageSize] = useState(10);

  // Sorting function
  const setSorting = (field) => {
    if (sortBy !== field) {
      // First click on a new field: sort ascending
      setSortBy(field);
      setReverseSortDirection(false);
    } else if (!reverseSortDirection) {
      // Second click on same field: sort descending
      setReverseSortDirection(true);
    } else {
      // Third click on same field: remove sorting
      setSortBy(null);
      setReverseSortDirection(false);
    }
  };

  // Get sort icon
  const getSortIcon = (field) => {
    const isActive = sortBy === field;
    const isDescending = reverseSortDirection;

    return (
      <div className="eb-orders__table-header-item-icon">
        <div
          style={{
            color: isActive && !isDescending ? '#008b91' : '#abbebe',
          }}
        >
          &#9650;
        </div>
        <div
          style={{
            color: isActive && isDescending ? '#008b91' : '#abbebe',
          }}
        >
          &#9660;
        </div>
      </div>
    );
  };

  // Filter and sort data
  const filteredData = useMemo(() => {
    let filtered = orders.filter((item) => {
      const searchTerm = search.toLowerCase();

      // Search in course titles
      const courseMatch =
        item.courses &&
        item.courses.some(
          (course) =>
            course.title && course.title.toLowerCase().includes(searchTerm)
        );

      // Search in date
      const dateMatch =
        item.date && item.date.toLowerCase().includes(searchTerm);

      // Search in order ID
      const orderIdMatch =
        item.eb_order_id && item.eb_order_id.toString().includes(searchTerm);

      // Search in status
      const statusMatch =
        item.status && item.status.toLowerCase().includes(searchTerm);

      return courseMatch || dateMatch || orderIdMatch || statusMatch;
    });

    if (sortBy) {
      filtered.sort((a, b) => {
        let aValue = a[sortBy];
        let bValue = b[sortBy];

        // Handle date sorting
        if (sortBy === 'date') {
          aValue = new Date(aValue);
          bValue = new Date(bValue);
        }

        // Handle course sorting (sort by first course title)
        if (sortBy === 'courses') {
          aValue = a.courses && a.courses.length > 0 ? a.courses[0].title : '';
          bValue = b.courses && b.courses.length > 0 ? b.courses[0].title : '';
        }

        if (aValue < bValue) {
          return reverseSortDirection ? 1 : -1;
        }
        if (aValue > bValue) {
          return reverseSortDirection ? -1 : 1;
        }
        return 0;
      });
    }

    return filtered;
  }, [orders, search, sortBy, reverseSortDirection]);

  // Paginate data
  const paginatedData = useMemo(() => {
    if (pageSize === 'all') {
      return filteredData;
    }
    const startIndex = (page - 1) * pageSize;
    const endIndex = startIndex + pageSize;
    return filteredData.slice(startIndex, endIndex);
  }, [filteredData, page, pageSize]);

  // Show loading state
  if (isLoading) {
    return <OrdersSkeleton />;
  }

  return (
    <div className="eb-user-account__orders eb-orders__container">
      <h3 className="eb-orders__title">{__('Orders', 'edwiser-bridge')}</h3>

      <div className="eb-orders__filter">
        <Select
          value={pageSize.toString()}
          onChange={(value) => {
            setPageSize(value === 'all' ? 'all' : Number(value));
            setPage(1);
          }}
          data={[
            { value: '5', label: __('5 per page', 'edwiser-bridge') },
            { value: '10', label: __('10 per page', 'edwiser-bridge') },
            { value: '25', label: __('25 per page', 'edwiser-bridge') },
            { value: 'all', label: __('All', 'edwiser-bridge') },
          ]}
          comboboxProps={{
            withinPortal: false,
          }}
          checkIconPosition="right"
          maxDropdownHeight={350}
          rightSection={<Icons.chevronDown />}
          leftSection={<Icons.listOrder />}
          allowDeselect={false}
        />

        <TextInput
          placeholder={__(
            'Search by course name, date, order ID, or status...',
            'edwiser-bridge'
          )}
          value={search}
          onChange={(event) => setSearch(event.currentTarget.value)}
          leftSection={<Icons.search />}
          leftSectionPointerEvents="none"
        />
      </div>

      {filteredData.length === 0 ? (
        <div className="eb-orders__empty-state">
          <h4 className="eb-orders__empty-state-title">
            {__('No orders found', 'edwiser-bridge')}
          </h4>
          <p className="eb-orders__empty-state-description">
            {search
              ? __(
                  'No orders match your search criteria. Try adjusting your search terms.',
                  'edwiser-bridge'
                )
              : __(
                  "You haven't placed any orders yet. Browse our products to get started!",
                  'edwiser-bridge'
                )}
          </p>
        </div>
      ) : (
        <div className="eb-orders__table-wrapper">
          <table className="eb-orders__table">
            <thead>
              <tr className="eb-orders__table-header">
                <th
                  className="eb-orders__table-header-item eb-orders__table-header-item--order-id"
                  onClick={() => setSorting('eb_order_id')}
                >
                  <div className="eb-orders__table-header-item-content">
                    {__('Order ID', 'edwiser-bridge')}
                    {getSortIcon('eb_order_id')}
                  </div>
                </th>
                <th
                  className="eb-orders__table-header-item eb-orders__table-header-item--course"
                  onClick={() => setSorting('courses')}
                >
                  <div className="eb-orders__table-header-item-content">
                    {__('Course', 'edwiser-bridge')}
                    {getSortIcon('courses')}
                  </div>
                </th>
                <th
                  className="eb-orders__table-header-item eb-orders__table-header-item--date"
                  onClick={() => setSorting('date')}
                >
                  <div className="eb-orders__table-header-item-content">
                    {__('Date', 'edwiser-bridge')}
                    {getSortIcon('date')}
                  </div>
                </th>
                <th
                  className="eb-orders__table-header-item eb-orders__table-header-item--status"
                  onClick={() => setSorting('status')}
                >
                  <div className="eb-orders__table-header-item-content">
                    {__('Status', 'edwiser-bridge')}
                    {getSortIcon('status')}
                  </div>
                </th>
              </tr>
            </thead>
            <tbody>
              {paginatedData.map((row) => (
                <tr key={row.eb_order_id} className="eb-orders__table-row">
                  <td className="eb-orders__table-row-item eb-orders__table-row-item--order-id">
                    #{row.eb_order_id}
                  </td>
                  <td className="eb-orders__table-row-item eb-orders__table-row-item--course">
                    {row.courses && row.courses.length > 0 ? (
                      <ul className="eb-user-order-courses">
                        {row.courses.map((course, index) => (
                          <li key={index}>
                            {course.link ? (
                              <a
                                href={course.link}
                                target="_blank"
                                rel="noopener noreferrer"
                                className="eb-orders__course-link"
                              >
                                {decodeHTMLEntities(course.title)}
                              </a>
                            ) : (
                              <span>{course.title}</span>
                            )}
                          </li>
                        ))}
                      </ul>
                    ) : (
                      <span>
                        {__('No courses available', 'edwiser-bridge')}
                      </span>
                    )}
                  </td>
                  <td className="eb-orders__table-row-item eb-orders__table-row-item--date">
                    {row.date}
                  </td>
                  <td className="eb-orders__table-row-item eb-orders__table-row-item--status">
                    {row.status}
                  </td>
                </tr>
              ))}
            </tbody>
            {/* <tfoot>
              <tr className="eb-orders__table-footer">
                <th className="eb-orders__table-footer-item eb-orders__table-footer-item--order-id">
                  <div className="eb-orders__table-footer-item-content">
                    {__('Order ID', 'edwiser-bridge')}
                  </div>
                </th>
                <th className="eb-orders__table-footer-item eb-orders__table-footer-item--course">
                  <div className="eb-orders__table-footer-item-content">
                    {__('Course', 'edwiser-bridge')}
                  </div>
                </th>
                <th className="eb-orders__table-footer-item eb-orders__table-footer-item--date">
                  <div className="eb-orders__table-footer-item-content">
                    {__('Date', 'edwiser-bridge')}
                  </div>
                </th>
                <th className="eb-orders__table-footer-item eb-orders__table-footer-item--status">
                  <div className="eb-orders__table-footer-item-content">
                    {__('Status', 'edwiser-bridge')}
                  </div>
                </th>
              </tr>
            </tfoot> */}
          </table>
        </div>
      )}

      {filteredData.length > 0 && (
        <div className="eb-orders__pagination">
          <span className="eb-orders__pagination-text">
            {pageSize === 'all' ? (
              <>
                {__('Showing all', 'edwiser-bridge')} {filteredData.length}{' '}
                {__('entries', 'edwiser-bridge')}
              </>
            ) : (
              <>
                {__('Showing', 'edwiser-bridge')}{' '}
                {Math.min((page - 1) * pageSize + 1, filteredData.length)}{' '}
                {__('to', 'edwiser-bridge')}{' '}
                {Math.min(page * pageSize, filteredData.length)}{' '}
                {__('of', 'edwiser-bridge')} {filteredData.length}{' '}
                {__('entries', 'edwiser-bridge')}
              </>
            )}
          </span>

          {pageSize !== 'all' && (
            <Pagination
              total={Math.ceil(filteredData.length / pageSize)}
              value={page}
              onChange={setPage}
              size="sm"
            />
          )}
        </div>
      )}
    </div>
  );
}

export default Orders;

export const OrdersSkeleton = () => {
  return (
    <div className="eb-user-account__orders">
      <h3 className="eb-orders__title">{__('Orders', 'edwiser-bridge')}</h3>
      <div className="eb-orders__filter">
        <Skeleton width={160} height={36} />
        <Skeleton width={280} height={36} />
      </div>
      <div className="eb-orders__table-wrapper">
        <Skeleton width="100%" height={480} />
      </div>
      <div className="eb-orders__pagination">
        <Skeleton width={180} height={20} />
        <Skeleton width={240} height={32} />
      </div>
    </div>
  );
};
