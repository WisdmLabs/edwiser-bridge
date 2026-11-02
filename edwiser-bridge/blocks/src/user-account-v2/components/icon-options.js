import { useCallback, useMemo, useState } from 'react';

export default function IconOptions({ onSelectTabIcon, icons, tabIcon }) {
  const [searchTerm, setSearchTerm] = useState('');

  // Search icons
  const filteredIcons = useMemo(() => {
    if (!searchTerm) return icons;

    const searchLower = searchTerm.toLowerCase();
    return icons.filter((icon) => {
      const nameMatch = icon.name.toLowerCase().includes(searchLower);
      const metaMatch =
        icon.meta &&
        icon.meta.some((keyword) =>
          keyword.toLowerCase().includes(searchLower)
        );

      return nameMatch || metaMatch;
    });
  }, [icons, searchTerm]);

  const handleSearch = useCallback((event) => {
    setSearchTerm(event.target.value);
  }, []);

  const handleIconClick = useCallback(
    (iconId) => {
      onSelectTabIcon(iconId);
    },
    [onSelectTabIcon]
  );

  return (
    <div className="eb-user-account-v2__icon-options">
      <input
        type="text"
        placeholder="Search Icons..."
        name="icon-search"
        value={searchTerm}
        onChange={handleSearch}
        className="eb-user-account-v2__icon-search"
      />
      <div className="eb-user-account-v2__icons-list">
        {filteredIcons.map((icon) => {
          const isActive = icon.id === tabIcon;

          return (
            <div
              key={icon.id}
              onClick={() => handleIconClick(icon.id)}
              className={`eb-user-account-v2__icon ${isActive ? 'active' : ''}`}
              title={icon.name}
            >
              <icon.icon />
              <span className="eb-user-account-v2__icon-name">{icon.name}</span>
            </div>
          );
        })}
      </div>
    </div>
  );
}
