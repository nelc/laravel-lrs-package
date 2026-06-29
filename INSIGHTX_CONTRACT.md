# InsightX API Contract — v2 (Apigee-aligned)

**Status:** Revised after reviewing the InsightX source (`insightx/`).
**Owner (consumer):** NELC Orchestrator Hub — Data & Reports factory (`orchestrator-hub/lib/insightx.py`)
**Audience:** InsightX / NELC Apigee (`ai-assistant`) engineers
**Supersedes:** v1 (2026-06-21), which specified a from-scratch `/v1/reports` API *before* we had the code.
**Companion:** [`docs/INSIGHTX_RECONCILIATION.md`](docs/INSIGHTX_RECONCILIATION.md) — the full code-to-contract mapping.

> ### What changed in v2
> v1 asked InsightX to **build** an estimate/submit/draft/publish/revoke API. Having now read the source, we found InsightX is a **thin Next.js client over the NELC Apigee gateway**, and **most of that lifecycle already exists upstream** (`ai-assistant/v1/*`, `insightx/v1/app`). v2 therefore:
> 1. **Binds the hub to the real endpoints** for what already works (auth, query/iterate, publish, key-minting, read, revoke) — see [§4](#4-the-platform-today-used-as-is).
> 2. **Narrows the implementation asks to five additions** — see [§5](#5-the-five-required-additions).
> 3. **Adds three security-hardening requests** — see [§6](#6-security-hardening-required-for-production).
>
> Net: this is no longer "build us an API" — it's "extend five things on the API you already run."

---

## 1. Purpose

The NELC Orchestrator Hub runs a **Data & Reports factory**: it takes a structured `DataRequest`, turns it into a report via InsightX, gets it approved by the requester, and publishes a **scoped, access-controlled** result to the consumer that asked for it. The hub's adapter, `lib/insightx.py`, drives InsightX programmatically. This document is the contract between the hub and InsightX/Apigee.

The **governance bar is unchanged from v1** and is non-negotiable:
- A published result is **scoped, authenticated, and expiring** — never world-public, never raw dataset / direct BigQuery access.
- Every column carries a **PII / sensitivity** tag the hub uses to decide what may be published.
- A re-submitted request **must not re-run or re-bill** the query.

v2 keeps that bar; it just expresses it against InsightX's actual platform.

---

## 2. Architecture & conventions

```
 Orchestrator Hub (lib/insightx.py)            NELC Apigee gateway
 ──────────────────────────────────            ─────────────────────────────────────────
  api mode  ── OAuth2 client_creds ──►          api.nelc.gov.sa /oauth2/v1/token
            ── Bearer access_token ──►          ai-assistant/v1/query     (prompt→SQL→sample)
                                                ai-assistant/v1/create-api (publish → api_url)
                                                ai-assistant/v1/api-data   (consumer read)
                                                insightx/v1/app            (dev-app: consumer key/secret)
                                                data-analytics/v1/datasets
```

| Topic | Rule |
|---|---|
| Hosts | Prod `https://api.nelc.gov.sa`; non-prod `https://api-test.nelc.gov.sa` (`APIGEE_BASE_URL`). OAuth + app/key mgmt are on `api.nelc.gov.sa`. |
| Transport | HTTPS only, TLS 1.2+. **TLS certificate verification MUST be enabled** end to end (see [§6.1](#61-enable-tls-verification)). |
| Content type | `application/json; charset=utf-8`. |
| Timestamps | ISO 8601 UTC, e.g. `2026-06-29T14:30:00Z`. |
| Identifiers | InsightX scopes work by **`user_id` + `proj_name` + `query_id`** (not a single opaque `job_id`). The hub maps its `DataRequest` id to `(user_id, proj_name)` and threads `external_request_id` for correlation ([§5.4](#54-idempotency--correlation)). |
| Unknown fields | Both sides MUST ignore unknown JSON fields (forward-compatible). |

---

## 3. Authentication (already exists — bind to it)

Service-to-service auth is **Apigee OAuth2 `client_credentials`**, which the hub adopts as-is (no new key scheme):

```http
POST https://api.nelc.gov.sa/oauth2/v1/token
Content-Type: application/x-www-form-urlencoded

grant_type=client_credentials&client_id=<APIGEE_INSIGHTX_API_KEY>&client_secret=<APIGEE_INSIGHTX_API_SECRET>
```
→ `{ "access_token": "…" }`, presented as `Authorization: Bearer <access_token>` on every subsequent call.

- Missing/invalid token → **`401`**; valid token lacking scope → **`403`**.
- **Rotation:** please support **overlapping (dual-active) client credentials** so the hub can rotate with zero downtime (issue new → hub switches → revoke old).
- These hub↔InsightX **service** credentials are distinct from the **consumer** credentials minted at publish time ([§4.4](#44-publish--mint-a-consumer-credential)).

---

## 4. The platform today (used as-is)

These endpoints are **confirmed in `insightx/lib/api/index.ts`** and the hub's `api` mode binds to them directly. No change requested here except where [§5](#5-the-five-required-additions)/[§6](#6-security-hardening-required-for-production) note it.

### 4.1 List datasets
```http
GET {base}/data-analytics/v1/datasets        Authorization: Bearer <token>
```
→ `string[]` of dataset ids. The hub picks `dataset_id` from this.

### 4.2 Query — prompt → SQL → sample (and the iterate loop)
```http
GET {base}/ai-assistant/v1/query?dataset_id&proj_name&query_id&user_id&human_query=<NL prompt>[&prefix]
```
→ `{ "sample_data": [ {…} ], "sql_query": "SELECT …" }`

- **Refinement / "iterate until satisfied"**: re-call with **`human_feedback=<correction>`** instead of `human_query` (same `proj_name`/`query_id`). This is the existing prompt-iteration loop; the hub uses it to converge a draft.
- `sql_query` is the generated SQL (maps to v1's `generated_sql`).
- ⚠️ Today a compile failure is signalled by the Arabic word **`خطأ`** appearing inside `sql_query`. Please also return a **structured error** (see [§5.2](#52-result--draft-metadata-schema--row-count--pii-tags) / [§7 errors](#7-errors)) so the hub doesn't string-match.

### 4.3 Publish — create the project API
```http
GET {base}/ai-assistant/v1/create-api?user_id&proj_name        Authorization: Bearer <token>
```
→ `{ "api_url": "…" }` — exposes the project's result as a readable API.

### 4.4 Publish — mint a consumer credential (Apigee Developer App)
```http
POST https://api.nelc.gov.sa/insightx/v1/app
{ "app_name": "...", "user_id": "...", "proj_name": "..." }
```
→
```json
{
  "app_id": "…", "developer_id": "…", "name": "…", "status": "approved",
  "credentials": [{
    "consumer_key": "…", "consumer_secret": "…",
    "api_products": [{ "apiproduct": "…", "status": "approved" }],
    "expires_at": "…", "issued_at": "…", "status": "approved"
  }]
}
```
This is the **publication credential** — a per-app key/secret bound to an API product, with an Apigee `expires_at`. (Today the credential is delivered to consumers as `?apikey=<consumer_key>` in the URL — [§6.2](#62-move-the-consumer-key-out-of-the-url) asks to change that.)

### 4.5 Consumer read
```http
GET {base}/ai-assistant/v1/api-data?user_id&proj_name[&page&page_size]    Authorization: Bearer <token>
```
(public form: `https://api.nelc.gov.sa/insightx/v1/api-data?apikey=<consumer_key>`) → the report's result rows, paginated.

### 4.6 Revoke
```http
DELETE https://api.nelc.gov.sa/insightx/v1/app/{app_name}        Authorization: Bearer <token>
```
→ invalidates the app (and its consumer key). Today this is keyed by `app_name`; [§5.3](#53-scoped-authenticated-expiring-publication) asks for revoke-by-publication + immediate effect.

---

## 5. The five required additions

Everything else already exists. These five are the actual implementation asks. Each is small relative to the platform.

### 5.1 Dry-run cost / scan estimate

**Why:** the hub enforces a `MAX_SCAN_BYTES` guardrail and must not spend money on an oversized scan. **No query runs; nothing is billed beyond a BigQuery dry-run.**

**Ask:** a dry-run that mirrors the `query` inputs, e.g.
```http
GET {base}/ai-assistant/v1/estimate?dataset_id&proj_name&user_id&human_query
```
→
```json
{ "bytes_scanned": 48318382080, "estimated_cost_usd": 0.2266, "partitions_touched": 91 }
```
Maps to BigQuery `jobs.insert` with `dryRun: true`. If the prompt can't compile → `422` with a `prompt_compile_error` ([§7](#7-errors)). MUST be safe to call repeatedly.

### 5.2 Result / draft metadata (schema + row count + PII tags)

**Why:** today `query` returns `sample_data` (a preview) + `sql_query`. To decide what may be published — and to publish safely — the hub needs the **full result shape and a sensitivity tag per column**. This is **mandatory** for governance.

**Ask:** add to the query result (or a `GET {base}/ai-assistant/v1/result?proj_name&query_id&user_id`):
```json
{
  "proj_name": "…", "query_id": "…",
  "generated_sql": "SELECT …",
  "row_count": 273,
  "period": { "start": "2026-04-01", "end": "2026-06-30" },
  "schema": [
    { "name": "region",        "type": "STRING",  "pii": false, "sensitivity": "public" },
    { "name": "active_learners","type": "INTEGER", "pii": false, "sensitivity": "internal" },
    { "name": "learner_email",  "type": "STRING",  "pii": true,  "sensitivity": "restricted" }
  ]
}
```
`schema[].pii` (boolean) and `schema[].sensitivity` (`public` | `internal` | `confidential` | `restricted`) are **required** on every column. `restricted`/`pii` columns may only be published if an approver explicitly scopes them in ([§5.3](#53-scoped-authenticated-expiring-publication)).

### 5.3 Scoped, authenticated, expiring publication

**Why:** today `create-api` publishes the **whole project**, the key rides in the URL, there's no consumer allow-list, and the Apigee `expires_at` isn't enforced as a hard publication expiry. The hub's governance bar requires a **projection**, not whole-project access.

**Ask:** let publish accept (and enforce) a scope, e.g. extend `create-api`/`app`:
```json
{
  "user_id": "…", "proj_name": "…",
  "column_scope": ["region", "month", "active_learners"],
  "row_scope": { "region": ["riyadh", "makkah"] },
  "consumer_allowlist": ["consumer_nelc_dashboards"],
  "expires_at": "2026-07-21T00:00:00Z"
}
```
The published endpoint MUST then, on **every** read:
- serve **only** `column_scope` columns and `row_scope` rows;
- accept the consumer credential in an **`Authorization` header** (not a URL `?apikey=`), and reject `consumer_id` not in `consumer_allowlist` → `403`;
- return **`410 Gone`** once `expires_at` has passed **or** the publication is revoked;
- (validation) reject a `column_scope` column absent from the [§5.2](#52-result--draft-metadata-schema--row-count--pii-tags) schema → `422`; reject `expires_at` in the past → `422`.

Re-publishing a project replaces its scope/allowlist/expiry (idempotent re-scope). Revoke MUST be **immediate** and keyed to the publication.

### 5.4 Idempotency & correlation

**Why:** the hub retries on network blips/restarts; a retry must never trigger a second BigQuery run (a second bill).

**Ask:** accept on `query` / `estimate` / `create-api`:
- **`external_request_id`** — the hub's `DataRequest` id; **echo it on every response and webhook** so both sides can reconcile.
- **`idempotency_key`** — opaque token. The tuple **(`external_request_id`, `idempotency_key`)** is the dedup key: the **same tuple ⇒ the same result, no new run, no new bill**. A *different* `idempotency_key` (same `external_request_id`) is a deliberate re-run. A reused tuple with a materially different prompt → `409 idempotency_key_conflict`.

### 5.5 Webhooks (or confirmed polling)

**Why:** the hub wants to react when a report is ready/published and to audit each consumer read. InsightX today notifies via **Mailjet email** only.

**Ask (preferred):** POST a signed callback to a hub-registered URL on each lifecycle event — `generated`, `published`, `accessed` (one per consumer read, for audit) — each echoing `external_request_id`. Sign with HMAC-SHA256 over the raw body in an `X-InsightX-Signature` header (shared secret, registered out of band), retried with backoff.

**Acceptable alternative:** confirm there are **no** webhooks and the hub should **poll** `api-data` / a status read; we'll implement polling with backoff instead. Either is fine — we just need to know which.

---

## 6. Security hardening (required for production)

Found while reviewing the code; please close these out before go-live.

### 6.1 Enable TLS verification
`insightx/lib/api/index.ts:6` creates an undici `Agent` with `rejectUnauthorized: false`, disabling TLS certificate verification on **all** upstream calls (flagged in-code as a temporary expired-cert workaround). This exposes every hub↔Apigee call to interception. Replace the expired certificate(s) and **re-enable verification**.

### 6.2 Move the consumer key out of the URL
The published URL is `…/insightx/v1/api-data?apikey=<consumer_key>` — API keys in query strings leak into access logs, browser history, and `Referer` headers. Accept the consumer credential in an **`Authorization` header** instead (this is part of [§5.3](#53-scoped-authenticated-expiring-publication)).

### 6.3 Authenticate the publish/revoke routes
InsightX's `POST /api/create-app` and `DELETE /api/delete-app` Next.js routes don't check a session — any caller who can reach them triggers a server-side OAuth mint and can create/delete Apigee apps. Gate them with the same auth as `/api/data`.

---

## 7. Errors

Non-2xx responses SHOULD use `{ "error": { "code": "<snake_case>", "message": "<human>" } }` so the hub branches on `error.code`, not prose.

| HTTP | `error.code` | When |
|---|---|---|
| 400 | `invalid_request` | Malformed body / missing required field |
| 401 | `unauthorized` | Bad/missing service or consumer credential |
| 403 | `forbidden` | Authenticated but not permitted (e.g. consumer not in allow-list) |
| 404 | `not_found` | Unknown `proj_name`/`query_id`/publication |
| 409 | `idempotency_key_conflict` | Reused tuple with a different prompt ([§5.4](#54-idempotency--correlation)) |
| 410 | `published_expired` / `published_revoked` | Read after expiry/revoke ([§5.3](#53-scoped-authenticated-expiring-publication)) |
| 422 | `prompt_compile_error` / `column_not_in_schema` / `expiry_in_past` | Semantically invalid |
| 429 | `rate_limited` | Throttled (honor `Retry-After`) |
| 5xx | `internal_error` / `service_unavailable` | Upstream/BigQuery fault — hub retries idempotently |

This **replaces** the `خطأ`-in-`sql_query` string signal ([§4.2](#42-query--prompt--sql--sample-and-the-iterate-loop)).

---

## 8. State / lifecycle mapping (synchronous model)

The hub owns its `DataRequest` states; this maps them onto InsightX's (synchronous) calls.

| Hub `DataRequest` state | InsightX call | Notes |
|---|---|---|
| `received` | — | hub creates the request |
| `validated` | **`/ai-assistant/v1/estimate`** ([§5.1](#51-dry-run-cost--scan-estimate)) + `MAX_SCAN_BYTES` check | guardrail; no query runs |
| `generating` | `/ai-assistant/v1/query` (`human_query`) | synchronous; returns sample + sql_query |
| `draft_ready` | `/ai-assistant/v1/query` (`human_feedback` to refine) + **result metadata** ([§5.2](#52-result--draft-metadata-schema--row-count--pii-tags)) | PII/sensitivity tags pulled here |
| `awaiting_requester_approval` | — (hub-internal human gate) | InsightX is not asked to approve |
| `published` | `create-api` + `insightx/v1/app` **with scope** ([§5.3](#53-scoped-authenticated-expiring-publication)) | scoped, header-auth, expiring endpoint live |
| `delivered` | `api-data` read (+ `accessed` webhook, [§5.5](#55-webhooks-or-confirmed-polling)) | consumer has consumed the report |
| *(failure)* | structured error ([§7](#7-errors)) | hub marks the request failed; may re-run with a new `idempotency_key` |
| *(teardown)* | `delete-app` / revoke | `410 Gone` thereafter |

---

## 9. Endpoint summary

| Capability | Endpoint | Status |
|---|---|---|
| Auth | `POST /oauth2/v1/token` (client_credentials) | ✅ exists ([§3](#3-authentication-already-exists--bind-to-it)) |
| List datasets | `GET /data-analytics/v1/datasets` | ✅ exists |
| Query / iterate | `GET /ai-assistant/v1/query` (`human_query`/`human_feedback`) | ✅ exists ([§4.2](#42-query--prompt--sql--sample-and-the-iterate-loop)) |
| Publish (project) | `GET /ai-assistant/v1/create-api` | ✅ exists |
| Consumer credential | `POST /insightx/v1/app` | ✅ exists |
| Consumer read | `GET /ai-assistant/v1/api-data` | ✅ exists |
| Revoke | `DELETE /insightx/v1/app/{app_name}` | ✅ exists |
| **Estimate** | `GET /ai-assistant/v1/estimate` (proposed) | ➕ add ([§5.1](#51-dry-run-cost--scan-estimate)) |
| **Result metadata + PII tags** | extend query result / `…/result` | ➕ add ([§5.2](#52-result--draft-metadata-schema--row-count--pii-tags)) |
| **Scoped publication** | extend `create-api`/`app` + enforce | ➕ add ([§5.3](#53-scoped-authenticated-expiring-publication)) |
| **Idempotency / correlation** | `external_request_id` + `idempotency_key` | ➕ add ([§5.4](#54-idempotency--correlation)) |
| **Webhooks** | signed callbacks (or confirm polling) | ➕ add ([§5.5](#55-webhooks-or-confirmed-polling)) |
| TLS verify on / key-in-header / auth publish routes | hardening | 🔒 fix ([§6](#6-security-hardening-required-for-production)) |

---

*Questions or proposed changes → the NELC Orchestrator Hub team. The adapter that consumes this is `orchestrator-hub/lib/insightx.py`; the code-to-contract mapping is `docs/INSIGHTX_RECONCILIATION.md`.*
