# Eatery Health Monitoring Feature

## Overview

Automated monitoring system to detect potentially closed eateries by:
1. Checking website URLs for non-2xx responses
2. Checking Google Places API for business status (permanently/temporarily closed)

Alerts are surfaced in Nova for manual review - **no automated closure of eateries**.

---

## Database Changes

### 1. Add column to `wheretoeat`

```
google_place_id: string, nullable
```

### 2. New table: `wheretoeat_checks`

One-to-one relationship with `wheretoeat`. Tracks when each check type was last run.

```
id: bigint, pk
wheretoeat_id: bigint, fk (unique)
website_checked_at: timestamp, nullable
google_checked_at: timestamp, nullable
created_at: timestamp
updated_at: timestamp
```

Model: `EateryCheck`

### 3. New table: `wheretoeat_alerts`

One-to-many relationship with `wheretoeat`. Stores failures that need human review.

```
id: bigint, pk
wheretoeat_id: bigint, fk
type: string ('website', 'google_places')
details: text/json (HTTP status, error message, google business_status, etc.)
completed: bool, default false
ignored: bool, default false
created_at: timestamp
updated_at: timestamp
```

Model: `EateryAlert`

---

## Exclusion Criteria

All checks should exclude eateries where:
- `live = false` (already handled by global scope)
- `closed_down = true`
- `county_id = 1` (nationwide chains, no physical location)
- doesnt have an existing 'alert' that is completed = false and ignored = false

Additionally:
- Website checks: must have a `website` URL
- Google checks: If a record doesn't have a `google_place_id`, still attempt to resolve it, but if still not found, then skip alert creation (not an error condition)

---

## Actions

Using the action pattern for reusability across jobs, Nova actions, and tinker.

### `CheckEateryWebsiteAction`

**Input:** Eatery model
**Logic:**
1. Make HTTP request to eatery's website URL
2. If 2xx: return success
3. If non-2xx: return failure with status code/error details

**Output:** DTO with success bool, status code, error message

### `CheckGooglePlaceStatusAction`

**Input:** Eatery model
**Logic:**
1. If no `google_place_id`, then early return, skip alert creation - this action isn't responsible for getting a place id, just for checking it.
2. Call Google Places API (Place Details) for `business_status`
3. Return status (`OPERATIONAL`, `CLOSED_TEMPORARILY`, `CLOSED_PERMANENTLY`)

**Output:** DTO with status, business_status value, or skipped flag

### `GetGooglePlaceIdAction`

**Input:** Eatery model (needs name, lat, lng)
**Logic:**
1. If the column is already populated, return it immediately, if not:
2. Call Google Places API (Find Place or Text Search) with name + lat/lng bias
3. If confident match found (single result within ~50m), return place_id and update the model.
4. If no match or ambiguous, return null

**Output:** place_id string or null

### `CreateEateryAlertAction`

**Input:** Eatery model, type, details
**Logic:**
1. Check if unresolved alert already exists (completed=false AND ignored=false) for this eatery+type
2. If exists: skip (avoid duplicates)
3. If not: create new alert record

**Output:** EateryAlert model or null if skipped

---

## Jobs

### `ProcessEateryWebsiteChecksJob` (Scheduled daily)

**Logic:**
1. Query eateries where:
   - Has website URL
   - `closed_down = false`
   - `county_id != 1`
   - No active alert exists for type 'website'
   - `wheretoeat_checks.website_checked_at` is null OR > 30 days ago
2. Order by `website_checked_at` ascending (oldest first)
3. Limit to batch size (configurable constant, e.g., 150)
4. For each eatery: dispatch `CheckSingleEateryWebsiteJob`

### `CheckSingleEateryWebsiteJob`

**Input:** Eatery model
**Logic:**
1. Run `CheckEateryWebsiteAction`
2. If failed: run `CreateEateryAlertAction` with type 'website'
3. Update/create `wheretoeat_checks` record with `website_checked_at = now()`

### `ProcessEateryGoogleChecksJob` (Scheduled daily)

**Logic:**
1. Query eateries where:
   - `closed_down = false`
   - `county_id != 1`
   - No active alert exists for type 'google_places'
   - `wheretoeat_checks.google_checked_at` is null OR > 30/60 days ago
2. Order by `google_checked_at` ascending
3. Limit to batch size (configurable constant)
4. For each eatery: dispatch `CheckSingleEateryGoogleJob`

### `CheckSingleEateryGoogleJob`

**Input:** Eatery model
**Logic:**
1. Run `GetGooglePlaceIdAction`, if fails, early return.
2. Run `CheckGooglePlaceStatusAction` 
3. If `CLOSED_PERMANENTLY` or `CLOSED_TEMPORARILY`: run `CreateEateryAlertAction`
4. Update/create `wheretoeat_checks` record with `google_checked_at = now()`

### `PopulateMissingGooglePlaceIdsJob` (Optional, manual/one-time)

For bulk population of `google_place_id` without running status checks.

**Logic:**
1. Query eateries where `google_place_id` is null, apply exclusions
2. Limit to batch size
3. For each: run `GetGooglePlaceIdAction`, save result to eatery

---

## Nova Integration

### EateryAlert Resource

Display pending alerts for review.

**Fields:**
- ID
- Eatery (BelongsTo, link to eatery)
- Type
- Details
- Status (computed: pending/completed/ignored)
- Created At

**Filters:**
- Type (website, google_places)
- Status (pending, completed, ignored)

**Actions:**
- Mark as Completed
- Mark as Ignored
- Bulk actions for same

**Menu badge:** Count of pending alerts (where completed=false AND ignored=false)

### Eatery Resource Updates

- Add `google_place_id` field (Text, nullable)
- Add Nova action: "Run Health Check" (async) - dispatches both check jobs for that eatery

### EateryCheck Resource (Optional)

Low priority - mainly for debugging. Shows last check timestamps per eatery.

---

## Configuration

Keep it simple initially:

```php
// In job classes as constants
private const BATCH_SIZE = 150;
private const WEBSITE_CHECK_INTERVAL_DAYS = 30;
private const GOOGLE_CHECK_INTERVAL_DAYS = 30; // or 60
```

Could move to config file later if needed.

---

## API Budget Considerations

### Google Places API

- **Find Place:** ~$17 per 1000 requests (one-time per eatery to get place_id)
- **Place Details:** ~$17 per 1000 requests (monthly status checks)
- **Free tier:** $200/month credit

With ~3000 eateries:
- Initial place_id population: ~$50 one-time (spread over weeks)
- Monthly status checks: ~$50/month

Spreading checks over 30-60 days keeps daily costs low and predictable.

### Mitigation

- Cache `google_place_id` permanently (only fetch once)
- Configurable batch sizes to spread API usage
- Skip eateries that can't be matched (no retry spam)

---

## Implementation Order

### Phase 1: Foundation
1. Migration: add `google_place_id` to `wheretoeat`
2. Migration: create `wheretoeat_checks` table
3. Migration: create `wheretoeat_alerts` table
4. Models: `EateryCheck`, `EateryAlert`
5. Relationships on `Eatery` model

### Phase 2: Website Checks
1. `CheckEateryWebsiteAction`
2. `CreateEateryAlertAction`
3. `CheckSingleEateryWebsiteJob`
4. `ProcessEateryWebsiteChecksJob`
5. Schedule job in console kernel

### Phase 3: Nova Integration (Alerts)
1. `EateryAlert` Nova resource
2. Filters and actions
3. Menu badge for pending count

### Phase 4: Google Places Integration
1. `GetGooglePlaceIdAction`
2. `CheckGooglePlaceStatusAction`
3. `CheckSingleEateryGoogleJob`
4. `ProcessEateryGoogleChecksJob`
5. Schedule job
6. Add `google_place_id` to Eatery Nova resource

### Phase 5: Extras
1. Nova action to manually trigger health check
2. `PopulateMissingGooglePlaceIdsJob` for bulk population
3. `EateryCheck` Nova resource (if useful)

---

## Future Enhancements (Out of Scope)

- Additional check types (social media links, phone number validation, etc.)
- Configurable check intervals per eatery or category
- Email/Slack notifications for new alerts
- Analytics dashboard (closure rate trends, etc.)
- Auto-retry logic for transient failures
