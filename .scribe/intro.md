# Introduction

Multi-vendor digital product marketplace API for managing products, orders, wallets, sellers, and more.

<aside>
    <strong>Base URL</strong>: <code>http://127.0.0.1:8000</code>
</aside>

Welcome to the **GameHub API** — a RESTful JSON API that powers a multi-vendor digital product marketplace. Sellers list game keys and digital products, customers browse and purchase them using wallet balance or payment gateways (Stripe, PayPal, Cryptomus).

<aside>As you scroll, you'll see code examples in the dark area to the right (or inline on mobile). Switch languages using the tabs at the top right.</aside>

---

## Quick Start (Next.js Integration)

```
1. GET  /api/v1/settings/bootstrap        → Fetch site config, currencies, CAPTCHA settings
2. POST /api/v1/auth/register             → Create an account (or POST /auth/login)
3. GET  /api/v1/products                  → Browse the catalog
4. POST /api/v1/checkout/sessions         → Start a checkout session
5. POST /api/v1/checkout/sessions/{id}/pay → Pay with a gateway or wallet
6. GET  /api/v1/my-orders                 → View purchased orders
7. GET  /api/v1/my-keys                   → Retrieve purchased keys
```

---

## Base URL & Versioning

All endpoints are prefixed with `/api/v1`. The base URL depends on your environment:

| Environment | Base URL |
|-------------|----------|
| Local       | `http://127.0.0.1:8000/api/v1` |
| Production  | `https://your-domain.com/api/v1` |

---

## Authentication

Most endpoints require a **Bearer token** via [Laravel Sanctum](https://laravel.com/docs/sanctum).

```
Authorization: Bearer {YOUR_API_TOKEN}
Content-Type: application/json
Accept: application/json
```

**Get a token:** `POST /api/v1/auth/login` with `email` and `password`.
**Revoke it:** `POST /api/v1/auth/logout` (authenticated).

Tokens do not expire automatically. Endpoints marked `requires authentication` return `401` without a valid token. Public endpoints (products, settings, blogs, etc.) need no token.

---

## Response Format

Every response uses a consistent JSON envelope:

**Success (200, 201):**
```json
{
  "status": true,
  "message": "Products fetched successfully",
  "data": { ... }
}
```

**Error (4xx, 5xx):**
```json
{
  "status": false,
  "message": "Validation failed",
  "errors": {
    "email": ["The email field is required."]
  }
}
```

Your Next.js API client can rely on checking `status === true` for all responses.

---

## HTTP Status Codes

| Code | Meaning | When It Happens |
|------|---------|-----------------|
| `200` | OK | Successful read or update |
| `201` | Created | Resource created (order, review, etc.) |
| `204` | No Content | Successful delete |
| `400` | Bad Request | Invalid input |
| `401` | Unauthenticated | Missing or invalid Bearer token |
| `403` | Forbidden | Insufficient permissions (e.g. not a seller) |
| `404` | Not Found | Resource doesn't exist |
| `422` | Unprocessable | Validation errors (check `errors` field) |
| `429` | Too Many Requests | Rate limit exceeded — check `Retry-After` header |
| `500` | Server Error | Unexpected error |
| `503` | Maintenance | Platform is in maintenance mode |

---

## Pagination

List endpoints return paginated results. Control pagination with query parameters:

```
GET /api/v1/products?page=2&per_page=20
```

**Response structure:**
```json
{
  "status": true,
  "data": [ ... ],
  "meta": {
    "current_page": 1,
    "last_page": 5,
    "per_page": 12,
    "total": 58
  },
  "links": {
    "first": "...?page=1",
    "last": "...?page=5",
    "prev": null,
    "next": "...?page=2"
  }
}
```

---

## Rate Limiting

Requests are limited per user (default: 60/minute, configurable by admin). When exceeded:

- Response: `429 Too Many Requests`
- Header: `Retry-After: <seconds>`
- Headers on every response: `X-RateLimit-Limit`, `X-RateLimit-Remaining`

---

## CAPTCHA Protection

Certain endpoints (`login`, `register`, `forgot-password`, `contact`, `subscribe`) may require a CAPTCHA token. Check the bootstrap response:

```json
{
  "captcha": {
    "provider": "turnstile",
    "site_key": "0x4AAAA..."
  }
}
```

| Provider | What to send |
|----------|-------------|
| `"none"` | Don't send anything |
| `"recaptcha"` | Render Google reCAPTCHA v3, send token as `captcha_token` |
| `"turnstile"` | Render Cloudflare Turnstile widget, send token as `captcha_token` |

---

## Bootstrapping Your Next.js App

Call this **once on app startup** to get everything you need:

```
GET /api/v1/settings/bootstrap
```

Returns: site branding, SEO config, checkout rules, currencies, wallet settings, CAPTCHA config, and default currency — all in a single cached request (5 min TTL).

Use this to:
- Set page titles, favicons, colors from `settings.branding`
- Configure CAPTCHA widget from `settings.captcha`
- Format prices using `default_currency` and `settings.currency_locale`
- Check `settings.maintenance.enabled` to show a maintenance page
- Check `settings.registration.registration_enabled` to show/hide signup

---

## Implementation Flows

### Customer Purchase Flow

```
┌─────────────────────────────────────────────────────────────┐
│ 1. Browse    GET /products?category=action&sort=popular     │
│ 2. Detail    GET /products/{id}                             │
│ 3. Reviews   GET /products/{id}/reviews                     │
│ 4. Checkout  POST /checkout/sessions                        │
│              body: { items: [{offer_id, quantity}],          │
│                      coupon_code?, payment_method }          │
│ 5. Pay       POST /checkout/sessions/{uuid}/pay             │
│              body: { payment_method: "stripe" }              │
│              → Returns gateway redirect URL                  │
│ 6. Result    GET /checkout/sessions/{uuid}/result            │
│              → Poll until status is "completed"              │
│ 7. Keys      GET /my-keys/order/{order_id}                  │
│              → Returns purchased license keys                │
└─────────────────────────────────────────────────────────────┘
```

### Seller Onboarding Flow

```
┌─────────────────────────────────────────────────────────────┐
│ 1. Apply     POST /seller-application                       │
│              body: { business_name, description, ... }       │
│ 2. Status    GET /seller-application/status                  │
│              → "pending" / "approved" / "rejected"           │
│ 3. Profile   GET /seller/profile                             │
│ 4. List      POST /seller/offers                             │
│              body: { product_id, price, quantity, ... }       │
│ 5. Keys      POST /seller/offers/{id}/keys                  │
│              body: { keys: ["KEY-1", "KEY-2", ...] }         │
│ 6. Sales     GET /seller/orders                              │
│ 7. Earnings  GET /seller/earnings                            │
│ 8. Withdraw  POST /seller/withdraws                          │
│              body: { amount, method: "paypal", ... }         │
│ 9. Analytics GET /seller/analytics/overview                  │
└─────────────────────────────────────────────────────────────┘
```

### Wallet Flow

```
┌─────────────────────────────────────────────────────────────┐
│ 1. Check     GET /wallet                                    │
│              → { balance, currency }                         │
│ 2. Deposit   POST /wallet/deposit                           │
│              body: { amount, payment_method: "stripe" }      │
│              → Returns gateway redirect URL                  │
│ 3. Confirm   POST /wallet/deposit/confirm                   │
│              → Verifies payment and credits wallet           │
│ 4. Pay       POST /wallet/pay                               │
│              body: { order_id, amount }                       │
│ 5. Transfer  POST /wallet/transfer                          │
│              body: { recipient_email, amount }               │
│ 6. History   GET /wallet/transactions?type=deposit           │
└─────────────────────────────────────────────────────────────┘
```

### Support Ticket Flow

```
┌─────────────────────────────────────────────────────────────┐
│ 1. Departments  GET /ticket-departments                     │
│ 2. Create       POST /tickets                               │
│                 body: { department_id, subject, message }    │
│ 3. List         GET /tickets                                │
│ 4. View         GET /tickets/{id}                           │
│ 5. Reply        POST /tickets/{id}/reply                    │
│                 body: { message, attachments[] }             │
│ 6. Escalate     POST /tickets/{id}/escalate                 │
│ 7. Close        POST /tickets/{id}/close                    │
└─────────────────────────────────────────────────────────────┘
```

### Refund Flow

```
┌─────────────────────────────────────────────────────────────┐
│ 1. Request   POST /refunds                                  │
│              body: { order_id, reason, amount? }             │
│ 2. Track     GET /refunds/{id}                              │
│              → "pending" / "approved" / "rejected"           │
│ 3. Cancel    POST /refunds/{id}/cancel                      │
│ 4. List      GET /refunds                                   │
└─────────────────────────────────────────────────────────────┘
```

---

## Content Endpoints (SSR / SSG)

These public endpoints are ideal for Next.js `getStaticProps` / `getServerSideProps`:

| Endpoint | Use For |
|----------|---------|
| `GET /website/homepage` | Homepage sections (hero, featured, trending) |
| `GET /website/shop` | Shop page layout config |
| `GET /website/footer` | Footer content & links |
| `GET /menus/{location}` | Navigation menus (header, footer, sidebar) |
| `GET /sliders` | Homepage banners & promotions |
| `GET /blogs` | Blog listing for content pages |
| `GET /faq` | FAQ page grouped by category |
| `GET /page/about` | About Us page |
| `GET /page/terms` | Terms of Service |
| `GET /page/privacy` | Privacy Policy |
| `GET /pages/{slug}` | Any CMS page by slug |

---

## Next.js API Client Example

```javascript
// lib/api.ts
const API_BASE = process.env.NEXT_PUBLIC_API_URL + '/api/v1';

class ApiClient {
  private token: string | null = null;

  setToken(token: string) { this.token = token; }
  clearToken() { this.token = null; }

  async request(method, endpoint, body?) {
    const headers: any = {
      'Accept': 'application/json',
      'Content-Type': 'application/json',
    };
    if (this.token) headers['Authorization'] = `Bearer ${this.token}`;

    const res = await fetch(`${API_BASE}${endpoint}`, {
      method,
      headers,
      body: body ? JSON.stringify(body) : undefined,
    });

    const json = await res.json();
    if (!json.status) throw new ApiError(json.message, res.status, json.errors);
    return json.data;
  }

  get(endpoint) { return this.request('GET', endpoint); }
  post(endpoint, body?) { return this.request('POST', endpoint, body); }
  put(endpoint, body?) { return this.request('PUT', endpoint, body); }
  delete(endpoint) { return this.request('DELETE', endpoint); }
}

export const api = new ApiClient();
```

