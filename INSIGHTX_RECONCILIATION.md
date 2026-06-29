# INSIGHTX_CONTRACT vs the real InsightX code — reconciliation

**What this is:** the proposed [`INSIGHTX_CONTRACT.md`](../INSIGHTX_CONTRACT.md) was written *before* we
had the InsightX source. Now that we do (`insightx/`), this document reconciles
the contract against what InsightX **actually is**, so the contract can be
revised to *map onto* the existing platform instead of asking the team to rebuild
capabilities they already have.

**Bottom line:** InsightX is a thin **Next.js client over the NELC Apigee
gateway** (`api.nelc.gov.sa`). Most of the contract's lifecycle — auth,
prompt→SQL, publish, key-minting, revoke, consumer read — **already exists
upstream**, just in a different shape (synchronous, project-scoped, Apigee
keys). The contract should therefore (1) **bind to the real Apigee endpoints**
for what exists, and (2) **narrow its "asks" to ~5 genuine gaps** + a few
security fixes — not a from-scratch `/v1/reports` API.

---

## 1. What InsightX actually is

```
 InsightX (insightx/, Next.js)                 NELC Apigee gateway
 ─────────────────────────────                 ────────────────────────────────
  UI (chat + data pipeline)        OAuth2       api.nelc.gov.sa /oauth2/v1/token
  3 thin route handlers     ── client_creds ──► ai-assistant/v1/query   (prompt→SQL→sample)
  + Server Actions                  Bearer      ai-assistant/v1/create-api (publish→api_url)
  Postgres: User + Storage(KV)  ◄────────────►  ai-assistant/v1/api-data  (read results)
  (no reports/jobs tables)                      insightx/v1/app  (Apigee dev-app: key+secret)
                                                data-analytics/v1/datasets
```

- **The real engine is upstream.** InsightX's Next.js app only proxies. Its
  *entire* local HTTP surface is `POST /api/create-app`, `GET /api/data`,
  `DELETE /api/delete-app`, plus NextAuth. The prompt→SQL iteration and
  "create-api" run as Next.js **Server Actions** (`app/actions.ts`), not HTTP
  endpoints. (`insightx/lib/api/index.ts`, `insightx/app/api/*/route.ts`)
- **InsightX stores almost nothing.** Local Postgres = `User` (an LDAP/AD
  mirror) + `Storage` (a generic `key→jsonb` KV). "Projects"/saved queries live
  in that KV, client-side. There is **no reports/jobs/publications table** — that
  state lives upstream in Apigee. (`insightx/lib/db/schema.ts`)
- **Auth is Apigee OAuth2**, not a NELC-issued service key: server-side
  `client_credentials` (`APIGEE_INSIGHTX_API_KEY/SECRET`) → `access_token`.
  Human UI access is LDAP via NextAuth. (`lib/api/index.ts:50`, `app/(auth)/auth.ts`)
- **The model is synchronous**, not an async job lifecycle: `/query` returns
  `{sample_data, sql_query}` immediately; the *iterate-until-satisfied* loop is
  just re-calling `/query` with `human_feedback` instead of `human_query`.

## 2. The Apigee endpoints that already exist (target these now)

All confirmed in `insightx/lib/api/index.ts`. `APIGEE_BASE_URL = https://api-test.nelc.gov.sa` (`config/const.ts`); app/key mgmt + OAuth use `https://api.nelc.gov.sa`.

| Capability | Real endpoint | Shape |
|---|---|---|
| **OAuth** | `POST api.nelc.gov.sa/oauth2/v1/token` | `client_credentials` → `{access_token}` |
| **List datasets** | `GET {base}/data-analytics/v1/datasets` | → `string[]` |
| **Prompt→SQL (+ iterate)** | `GET {base}/ai-assistant/v1/query?dataset_id&proj_name&query_id&user_id&human_query` *(or `human_feedback` on refine)* | → `{ sample_data[], sql_query }` |
| **Publish project as API** | `GET {base}/ai-assistant/v1/create-api?user_id&proj_name` | → `{ api_url }` |
| **Mint consumer credential** (Apigee dev app) | `POST api.nelc.gov.sa/insightx/v1/app` `{app_name,user_id,proj_name}` | → `{ app_id, developer_id, credentials:[{consumer_key, consumer_secret, api_products[], expires_at, issued_at, status}] }` |
| **Consumer reads results** | `GET {base}/ai-assistant/v1/api-data?user_id&proj_name&page&page_size` — public form: `api.nelc.gov.sa/insightx/v1/api-data?apikey=<consumer_key>` | → result rows (paginated, untyped) |
| **Revoke** | `DELETE api.nelc.gov.sa/insightx/v1/app/{app_name}` | → ok |

## 3. Contract §-by-§ reconciliation

| Contract § | Capability | Status | Reality / the ask |
|---|---|---|---|
| §3 | Service-key auth (`Bearer ix_live_…`, rotation) | **different** | Use Apigee **OAuth2 `client_credentials`** (already used by the hub adapter pattern). No new key scheme needed; rotation = Apigee credential rotation. |
| §4 | **Dry-run cost estimate** (`bytes_scanned`, `estimated_cost_usd`) | **missing** | Nothing produces a cost/scan estimate anywhere. **Genuine new ask** to the ai-assistant/BigQuery team (a `dryRun` estimate before running). |
| §5 | Submit report (idempotent) | **partial / different** | `/ai-assistant/v1/query` does prompt→SQL→sample, but **synchronous** and with **no `idempotency_key`/`external_request_id`**. Either add idempotency upstream, or the hub accepts the synchronous model + dedups on its side. |
| §6 | Status / job lifecycle | **missing** | No `queued→generating→draft_ready` job; `/query` returns immediately. The contract's async lifecycle collapses to one synchronous call (or upstream adds jobs). |
| §7 | Machine-readable draft: schema + **PII/sensitivity tags** + `row_count` + `generated_sql` | **partial** | `sql_query` = `generated_sql` ✅. But `sample_data` is a *preview*, and there is **no column schema, no `pii`/`sensitivity` tags, no `row_count`**. PII/sensitivity tagging is a **genuine new ask** (and is mandatory for our governance). |
| §9 | **Scoped, authed, expiring publish** | **different (largely exists)** | `create-api` + `insightx/v1/app` already produce a key-authenticated, Apigee-product-scoped endpoint with an Apigee `expires_at`. **But**: scope = *whole project* (no `row_scope`×`column_scope`), the key is passed as a **URL `?apikey=`** (not a per-consumer bearer), there's **no `consumer_allowlist`**, and `expires_at` **isn't enforced** as a publication expiry (no `410 Gone`). |
| §9.4 | Consumer credential distinct from service key | **partial** | Apigee `consumer_key/secret` per app ✅, but delivered via `?apikey=` in the URL and not allow-listed per consumer. |
| §10 | Revoke | **partial** | `delete-app` exists but is keyed by **`app_name`**, not by job/publication, and immediacy/`410` isn't guaranteed. |
| §11 | **Webhooks** (HMAC lifecycle/audit callbacks) | **missing** | No callbacks at all — InsightX notifies via **Mailjet email** (`lib/mailjet.ts`). Either upstream adds webhooks, or the hub **polls** `api-data`/status. |
| §12 | `external_request_id` correlation + idempotency | **missing** | No correlation id threads through. **Genuine new ask** (or hub-side correlation only). |

## 4. The genuine gaps to request from the upstream (ai-assistant / Apigee) team

Everything else already exists. The real asks shrink to **five**:

1. **Dry-run cost/scan estimate** before running a query (§4).
2. **Result metadata on the draft**: full column **schema + per-column PII/sensitivity tags + `row_count`** (§7) — mandatory for what we may publish.
3. **True scoped publication** (§9): row/column scope over a result (not just whole-project), a **bearer/header credential** instead of `?apikey=` in the URL, a **consumer allowlist**, and **enforced expiry → `410 Gone`**.
4. **Idempotency + `external_request_id` correlation** so hub retries never re-run/re-bill (§5.4/§12).
5. **Webhooks** for `generated`/`published`/`accessed` (§11) — or we accept polling.

## 5. Security issues found (worth fixing regardless of the contract)

- **TLS verification is disabled.** `lib/api/index.ts:6` creates an undici `Agent` with `rejectUnauthorized: false` for *all* upstream calls — a man-in-the-middle exposure (flagged in-code as a temporary expired-cert workaround).
- **Consumer key in the URL.** The published URL is `…/api-data?apikey=<consumer_key>` (`components/data-pipeline-container.tsx`) — keys in query strings leak into logs, history, and referrers. Should be a header credential.
- **Unauthenticated publish/revoke routes.** `POST /api/create-app` and `DELETE /api/delete-app` don't check a session — any caller who can reach the route triggers a server-side OAuth mint and can create/delete Apigee apps. (`/api/data` *is* session-gated.)

## 6. What this means for our side (`orchestrator-hub/lib/insightx.py`)

The adapter's **`api` mode** should target the **real Apigee endpoints** above, not the hypothetical `/v1/reports`:
- `estimate()` → **no upstream support yet** → keep `operator` mode (or hold until gap #1 ships).
- `dispatch()`/generate → `GET /ai-assistant/v1/query` (first call `human_query`; refine with `human_feedback`), OAuth2 `client_credentials` for the token.
- `fetch_draft()` → `sql_query` + `sample_data` today; **PII tags/schema missing** → operator fills those until gap #2.
- `publish()` → `create-api` + `POST /insightx/v1/app` (returns `consumer_key/secret`, `api_products`, `expires_at`); **scope is whole-project** until gap #3.
- consumer read → `GET /ai-assistant/v1/api-data` (or the public `?apikey=` form).
- `revoke()` → `DELETE /insightx/v1/app/{app_name}`.

Net: the adapter can integrate **for real today** for query + publish + read + revoke; **estimate, PII tags, true scoping, idempotency, and webhooks stay operator-mediated** until the five upstream gaps land.

## 7. Recommendation

Rewrite `INSIGHTX_CONTRACT.md` as **v2 — "Apigee-aligned"**: keep the governance
requirements (scoped/authed/expiring publication, PII tags, idempotency, no
world-public/raw access) as the *target state*, but express every capability
against the **actual** OAuth2 + `ai-assistant/v1` + `insightx/v1/app` surface, and
relabel the document's "asks" to the **five gaps** in §4 above. That turns the
contract from "build this API" into "extend these five things on the API you
already run" — a far smaller, more credible request for the InsightX/Apigee team.

*Sources: `insightx/lib/api/index.ts`, `insightx/app/api/*/route.ts`,
`insightx/app/actions.ts`, `insightx/lib/db/schema.ts`,
`insightx/components/data-pipeline-container.tsx`, `insightx/config/const.ts`.*
