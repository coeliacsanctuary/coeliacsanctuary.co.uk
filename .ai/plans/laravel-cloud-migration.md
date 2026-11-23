# Laravel Cloud Migration

## Overview

Exploring a move from the current $25 DigitalOcean droplet (managed by Forge, deployed with Envoyer) to Laravel Cloud, alongside other apps already running there. Likely starting with a dev environment and seeing how it goes.

Discussion started 2026-11-09. Nothing provisioned yet.

---

## Current Setup

One droplet: **4 GB RAM · 2 vCPU · 80 GB SSD**. App, MySQL, Redis, Horizon and Inertia SSR all run on the one instance. Media is on S3 (apart from files in `public/`).

### Measurements (2026-11-09)

| Resource | Measured | Notes |
| --- | --- | --- |
| CPU | ~20% average, spikes to low 30s and ~40% | Consistent over 24 hours and over a week |
| Memory | 60–70% (24 hours), 50–70% (week) | 85% spike during deploys (`npm install` / `npm ci` on the box) |
| Disk | 20–25% (16–20 GB) | Code, built assets, previous Envoyer releases, MySQL data |
| MySQL data | 1.8 GB | `/var/lib/mysql` |
| Traffic | ~1,000 visitors/day | Lower overnight (UK), never drops to zero |
| Jobs | ~2,600 in the past hour (one reading) | ~99% on the journey tracker queue |

### Memory by process

| Process | Memory |
| --- | --- |
| `php8.4` (CLI, mostly Horizon) | 1,228 MB |
| `mysqld` | 382 MB |
| `php-fpm8.4` | 323 MB |
| `redis-server` | 224 MB (RSS) |
| `node` (Inertia SSR) | 141 MB |
| `nginx` | 23 MB |

### Redis

- `used_memory`: 460 MB, peak 508 MB
- db0 (Horizon / queues): 39,839 keys
- db1 (cache): 75 keys, 33.1 MB

---

## App Compute

- London Flex 1 GB instance, **$14/month** always on
- Autoscale up to 3 (maybe 5). Expect it to sit on one instance ~90% of the time, to be monitored
- Ceiling if every instance ran all month: $42 at 3, $70 at 5
- Measured web-side memory ~490 MB (`php-fpm` + SSR `node` + `nginx`), so 512 MB is too tight
- Hibernation enabled, but rarely expected to trigger as traffic never drops to zero
- Inertia SSR is a toggle in the Cloud UI

---

## Database

- Staying on MySQL. Not converting 12 years of data to Postgres
- 1 GB Flex MySQL, up to **$15.40/month** always on
- Storage $0.12/GB: 1.8 GB ≈ $0.22 (5 GB for growth ≈ $0.60)
- RAM is the consideration, not storage. `mysqld` uses 382 MB on the droplet today
- Hibernation available but, like compute, rarely expected to trigger in production

---

## Cache

Horizon is dropped on Cloud, so Redis would only hold the cache: 75 keys, 33.1 MB. Heavy `rememberForever` use means size grows with content, not traffic.

| | Redis-compatible store (250 MB) | Database driver |
| --- | --- | --- |
| Cost | up to $6/month | $0 |
| Code change | None | `cache` table migration (`make:cache-table`), driver switch in env |
| Cache hits | Stay off MySQL | Become MySQL primary-key lookups |

- No cache tags or direct `Redis::` calls in `app/` or `routes/`, so the database driver is viable
- The database driver supports atomic locks
- Plan: try the database driver on dev first, add Redis if the extra queries show up

---

## Queues

Managed queues replace Horizon. One managed queue per queue name, each with its own size and scale options. Currently 4 queues, likely more later.

- Operations: $1 per million. ~62,000 jobs/day ≈ 1.9 million/month ≈ **$1.90/month** (at one operation per job)
- 512 MB worker: $0.0132/hour ($0.00000365/second), **$9.64/month** if always on
- Journey tracker queue is busy constantly, so effectively always on
- Other queues are nearly idle
- Range for 4 queues: ~$10–$39/month, expected near the low end (**~$12/month** including operations)
- Journey tracker doesn't run outside production, so dev queue costs should be close to zero

---

## Cost Comparison

### Production estimate

| | Monthly |
| --- | --- |
| App compute (1 GB Flex, London) | $14.00 |
| MySQL (1 GB Flex) | ~$15.62 |
| Queues | ~$12.00 |
| Cache | $0 (database driver) or $6 (Redis) |
| **Total** | **~$41.62–$47.62** |

Cloud is already ~$130/month (including the $20 plan fee) with other apps, so it becomes ~$172–$178/month.

### Against today

| | Monthly |
| --- | --- |
| Today: droplet + Forge + Envoyer | $54 |
| After this app moves: Cloud + Forge | ~$61–$67 |
| Once the two other $5 droplets move too: Cloud only | ~$42–$48, plus their Cloud costs |

- Envoyer and this droplet can be cancelled once the app moves
- Forge stays until the two $5 droplets (one is the dev server, one other) are also migrated
- The two $5 droplets ($10) are left out of every row, as they're paid either way until they move

---

## Open Questions

- Do idle managed queues scale to zero, or keep a minimum of one instance?
- How does Cloud count operations per job?
