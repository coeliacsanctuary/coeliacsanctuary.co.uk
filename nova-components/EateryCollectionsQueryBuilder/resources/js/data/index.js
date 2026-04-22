import { count, join } from '../objects';

export const whereFields = ['website', 'gf_menu_link'];

export const whereRelations = [
  { label: 'town', column: '[parent].town_id' },
  { label: 'county', column: '[parent].county_id' },
  { label: 'country', column: '[parent].country_id' },
  { label: 'area', column: '[parent].area_id' },
  { label: 'type', column: 'type_id' },
  { label: 'venueType', column: 'venue_type_id' },
  { label: 'cuisine', column: 'cuisine_id' },
];

export const whereHas = [
  {
    label: 'feature',
    relation: 'features',
    column: 'wheretoeat_assigned_features.feature_id',
    table: 'wheretoeat_assigned_features',
    localKey: 'wheretoeat.id',
    foreignKey: 'wheretoeat_assigned_features.wheretoeat_id',
  },
];

export const orderables = [
  { label: 'name', column: 'ordering' },
  {
    label: 'rating',
    column: 'rating_count',
    additional: {
      counts: [
        count(
          'wheretoeat_reviews',
          '[parent].id',
          'wheretoeat_id',
          'rating_count',
        ),
      ],
    },
  },
  {
    label: 'town',
    column: 'wheretoeat_towns.town',
    table: 'wheretoeat_towns',
    localKey: '[parent].town_id',
    foreignKey: 'wheretoeat_towns.id',
  },
  {
    label: 'county',
    column: 'wheretoeat_counties.county',
    table: 'wheretoeat_counties',
    localKey: '[parent].county_id',
    foreignKey: 'wheretoeat_counties.id',
  },
  {
    label: 'country',
    column: 'wheretoeat_countries.country',
    table: 'wheretoeat_countries',
    localKey: '[parent].country_id',
    foreignKey: 'wheretoeat_countries.id',
  },
  {
    label: 'area',
    column: 'wheretoeat_areas.area',
    table: 'wheretoeat_areas',
    localKey: '[parent].area_id',
    foreignKey: 'wheretoeat_areas.id',
  },
];

export const whereCount = [
  {
    label: 'reviews',
    table: 'wheretoeat_reviews',
    localKey: '[parent].id',
    foreignKey: 'wheretoeat_id',
    alias: 'review_count',
  },
];

export const whereAverage = [
  {
    label: 'reviews',
    table: 'wheretoeat_reviews',
    column: 'rating',
    localKey: '[parent].id',
    foreignKey: 'wheretoeat_id',
    alias: 'average_rating',
  },
];
