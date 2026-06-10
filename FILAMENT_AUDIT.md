# Filament Resource Audit

This file documents gaps between the current Filament admin resources and the Nova resources they replace.
Each change is independently scoped so it can be planned and implemented in isolation.

The branch is `feature/filament`. Nova lives in `app/Nova/`. Filament resources live in `app/Filament/Resources/`.

---

## Change 1 — EateryArea: fix field name typo

**File:** `app/Filament/Resources/EatingOut/EateryAreas/Schemas/EateryAreaForm.php:33`

**Problem:** `TextInput::make('rea')` is a typo — the column is `area`. This silently saves nothing when creating or editing an eatery area.

**Fix:** Change `'rea'` to `'area'`.

---

## Change 2 — Eatery: add facebook_url and instagram_url fields

**File:** `app/Filament/Resources/EatingOut/Eateries/Schemas/EateryForm.php`

**Problem:** Two columns were added to the `wheretoeat` table after this branch was created — `facebook_url` and `instagram_url` — and are managed in Nova but absent from the Filament form.

**Fix:** Add `TextInput::make('facebook_url')` and `TextInput::make('instagram_url')` to the contact details section of `EateryForm`. Both are nullable URL fields. See the Nova resource at `app/Nova/Resources/EatingOut/Eateries.php` for reference on where they sit in the layout.

---

## Change 3 — Blog: add missing fields

**Files:**
- `app/Filament/Resources/MainSite/Blogs/Schemas/BlogForm.php`
- `app/Filament/Resources/MainSite/Blogs/Tables/BlogsTable.php`

**Problem:** Several fields were added to blogs after this branch was created, and the `body` field uses the wrong component type.

**Missing fields to add to the form:**

| Field | Type | Notes |
|-------|------|-------|
| `short_title` | TextInput | Nullable, sits near `title` |
| `header_image_alt_text` | TextInput | Sits near the header image upload |
| `show_author` | Toggle | Boolean column |
| `primary_tag_id` | Select | BelongsTo `BlogTag`; model has `primaryTag()` relation |
| `faqs` | Repeater | Column is a JSON array cast (`'faqs' => 'array'` in model). Each entry has a `question` and `answer`. See Nova repeater at `app/Nova/Repeaters/ArticleFaq.php` for field names. |

**Body field:** The `body` field is currently a `Textarea`. The `Blog` model now implements `HasRichContent` / uses `InteractsWithRichContent` (Filament's own rich content system). It should use `RichEditor` instead. Check the Nova `Body` field usage and the model's `registerMediaCollections` — there is a `body` media collection for inline images.

**Reference:** `app/Nova/Resources/Main/Blog.php`

---

## Change 4 — Recipe: add missing fields

**Files:**
- `app/Filament/Resources/MainSite/Recipes/Schemas/RecipeForm.php`

**Problem:** Several fields were added to recipes after this branch was created.

**Missing fields to add to the form:**

| Field | Type | Notes |
|-------|------|-------|
| `short_title` | TextInput | Nullable, sits near `title` |
| `header_image_alt_text` | TextInput | Sits near the header image upload |
| `faqs` | Repeater | Same JSON array structure as Blog FAQs (question + answer). Column is cast `'faqs' => 'array'` in the Recipe model. |
| Related Recipes | RelationManager or searchable Select | Model has `relatedRecipes()` BelongsToMany via `recipes_related_recipes` pivot. Nova used a custom `RelatedRecipesSearch` field. A `RelatedRecipesRelationManager` is likely the cleanest Filament approach. |

**Reference:** `app/Nova/Resources/Main/Recipe.php`, `app/Models/Recipes/Recipe.php`

---

## Change 5 — Collection: rewrite items management for new group structure

**Files:**
- `app/Filament/Resources/MainSite/Collections/RelationManagers/ItemsRelationManager.php` — needs replacing
- `app/Filament/Resources/MainSite/Collections/Schemas/CollectionForm.php` — minor: fix `description` field name

**Problem:** After this branch was created, the collection items structure was completely redesigned:

**Old structure (no longer exists):**
- `collection_items` table → `CollectionItem` model (this model does not exist anymore)
- Flat list of items directly on a collection

**New structure:**
- `collection_groups` table → `CollectionGroup` model (`app/Models/Collections/CollectionGroup.php`)
  - `collection_id`, `title`, `position`
  - `hasMany(CollectionGroupItem)`
  - Implements `Sortable` (spatie/eloquent-sortable, order column: `position`)
- `collection_group_items` table → `CollectionGroupItem` model (`app/Models/Collections/CollectionGroupItem.php`)
  - `collection_group_id`, `item_type`, `item_id`, `position`
  - `morphTo('item')` → Blog, Recipe, etc.
  - Implements `Sortable` (order column: `position`)
- `Collection` model has `groups()` hasMany, eager-loads `['groups', 'groups.items']`

The current `ItemsRelationManager` references `CollectionItem` on every line and will throw a class-not-found error at runtime.

**What's needed:**
Replace `ItemsRelationManager` with a `GroupsRelationManager` (managing `CollectionGroup` records). Each group should allow managing its `CollectionGroupItem` entries — the cleanest approach is a nested `Repeater` inside the relation manager's form for the items (item_type select → Blog/Recipe, item_id searchable select, position auto-handled by sortable).

The `position` column is managed by spatie/eloquent-sortable automatically on create — no need to expose it as a visible field, but the table should be reorderable.

**Also:** `CollectionForm` has a `body` Textarea — the Collection model maps `description` to `long_description` via an Attribute accessor. Check whether the form field should be `long_description` (the actual column) or if the accessor handles saving too.

**Reference:** `app/Nova/Resources/Main/Collection.php`, `app/Models/Collections/Collection.php`, `app/Models/Collections/CollectionGroup.php`, `app/Models/Collections/CollectionGroupItem.php`

---

## Change 6 — EateryArea / EateryCounty / EateryTown: add lat/lng fields

**Files:**
- `app/Filament/Resources/EatingOut/EateryAreas/Schemas/EateryAreaForm.php`
- `app/Filament/Resources/EatingOut/EateryCounties/Schemas/EateryCountyForm.php`
- `app/Filament/Resources/EatingOut/EateryTowns/Schemas/EateryTownForm.php`

**Problem:** The `wheretoeat_counties`, `wheretoeat_towns`, and `wheretoeat_areas` tables all have `lat` and `lng` text columns. Nova exposes these on all three resources, but the Filament forms for all three are missing them.

**Fix:** Add `TextInput::make('lat')` and `TextInput::make('lng')` to each of the three form schemas. Both are nullable text fields (not numeric — they are stored as strings in the DB). Place them logically after the name/slug fields.

**Reference:** `app/Nova/Resources/EatingOut/Counties.php`, `app/Nova/Resources/EatingOut/Towns.php`, `app/Nova/Resources/EatingOut/Areas.php`

---

## Change 7 — Announcement: fix expires_at column type in table

**File:** `app/Filament/Resources/MainSite/Announcements/Tables/AnnouncementsTable.php:26`

**Problem:** PHPStan reports `Cannot call method isPast() on string` — the `expires_at` column is being used as a Carbon instance but the table column treats it as a string. Either the model is missing a date cast for `expires_at`, or the table column type needs changing to `DateTimeColumn`.

**Fix:** Check the `Announcement` model for a date cast on `expires_at`. If missing, add it. Then verify the table column renders correctly. The Nova resource shows this as a date field.

**Reference:** `app/Nova/Resources/Main/AnnouncementResource.php`

---

## Change 8 — SealiacOverview: null guard on morphTo in table

**File:** `app/Filament/Resources/MainSite/SealiacOverviews/Tables/SealiacOverviewsTable.php:27,33`

**Problem:** PHPStan reports `Cannot access constant class on Illuminate\Database\Eloquent\Model|null` on two lines. The table accesses `::class` on the result of a morphTo relationship, which can be null, without a null guard.

**Fix:** Add null checks before accessing `::class` on the morph result. The rows without a related model should either be filtered out in the query or handled gracefully in the column state closure.

**Reference:** `app/Nova/Resources/Main/SealiacOverviews.php`
