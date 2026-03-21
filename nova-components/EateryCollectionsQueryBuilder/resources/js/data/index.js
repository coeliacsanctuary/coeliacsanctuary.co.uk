export const whereFields = ['website', 'gf_menu_link'];

export const whereRelations = [
  { label: 'town', column: 'town_id' },
  { label: 'county', column: 'county_id' },
  { label: 'country', column: 'country_id' },
  { label: 'area', column: 'area_id' },
  { label: 'type', column: 'type_id' },
  { label: 'venueType', column: 'venue_type_id' },
  { label: 'cuisine', column: 'cuisine_id' },
];

export const whereHas = [
  { label: 'feature', relation: 'features', column: 'id' },
];

export const orderables = [
  { label: 'name', column: 'name' },
  { label: 'rating', column: 'rating_count' },
  { label: 'town', column: 'town.town' },
  { label: 'county', column: 'county.county' },
  { label: 'country', column: 'country.country' },
  { label: 'area', column: 'area.area' },
];

export const whereCount = [
  {
    label: 'reviews',
    localKey: 'id',
    foreignKey: 'wheretoeat_id',
    alias: 'review_count',
  },
];

export const whereAverage = [
  {
    label: 'reviews',
    column: 'rating',
    localKey: 'id',
    foreignKey: 'wheretoeat_id',
    alias: 'average_rating',
  },
];
