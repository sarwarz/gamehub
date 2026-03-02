<?php

use Knuckles\Scribe\Extracting\Strategies;
use Knuckles\Scribe\Config\Defaults;
use Knuckles\Scribe\Config\AuthIn;
use function Knuckles\Scribe\Config\{removeStrategies, configureStrategy};

// Only the most common configs are shown. See the https://scribe.knuckles.wtf/laravel/reference/config for all.

return [
    // The HTML <title> for the generated documentation.
    'title' => 'GameHub API Documentation',

    // A short description of your API. Will be included in the docs webpage, Postman collection and OpenAPI spec.
    'description' => 'Multi-vendor digital product marketplace API for managing products, orders, wallets, sellers, and more.',

    // Text to place in the "Introduction" section, right after the `description`. Markdown and HTML are supported.
    'intro_text' => <<<'INTRO'
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
INTRO,

    // The base URL displayed in the docs.
    'base_url' => config("app.url"),

    // Routes to include in the docs
    'routes' => [
        [
            'match' => [
                // Match only routes whose paths match this pattern (use * as a wildcard to match any characters). Example: 'users/*'.
                'prefixes' => ['api/*'],

                // Match only routes whose domains match this pattern (use * as a wildcard to match any characters). Example: 'api.*'.
                'domains' => ['*'],
            ],

            // Include these routes even if they did not match the rules above.
            'include' => [
                // 'users.index', 'POST /new', '/auth/*'
            ],

            // Exclude these routes even if they matched the rules above.
            'exclude' => [
                // 'GET /health', 'admin.*'
            ],
        ],
    ],

    // The type of documentation output to generate.
    // - "static" will generate a static HTMl page in the /public/docs folder,
    // - "laravel" will generate the documentation as a Blade view, so you can add routing and authentication.
    // - "external_static" and "external_laravel" do the same as above, but pass the OpenAPI spec as a URL to an external UI template
    'type' => 'laravel',

    // See https://scribe.knuckles.wtf/laravel/reference/config#theme for supported options
    'theme' => 'default',

    'static' => [
        // HTML documentation, assets and Postman collection will be generated to this folder.
        // Source Markdown will still be in resources/docs.
        'output_path' => 'public/docs',
    ],

    'laravel' => [
        // Whether to automatically create a docs route for you to view your generated docs. You can still set up routing manually.
        'add_routes' => true,

        // URL path to use for the docs endpoint (if `add_routes` is true).
        // By default, `/docs` opens the HTML page, `/docs.postman` opens the Postman collection, and `/docs.openapi` the OpenAPI spec.
        'docs_url' => '/docs',

        // Directory within `public` in which to store CSS and JS assets.
        // By default, assets are stored in `public/vendor/scribe`.
        // If set, assets will be stored in `public/{{assets_directory}}`
        'assets_directory' => null,

        // Middleware to attach to the docs endpoint (if `add_routes` is true).
        'middleware' => [],
    ],

    'external' => [
        'html_attributes' => []
    ],

    'try_it_out' => [
        'enabled' => true,

        'base_url' => null,

        'use_csrf' => false,

        'csrf_url' => '/sanctum/csrf-cookie',
    ],

    // How is your API authenticated? This information will be used in the displayed docs, generated examples and response calls.
    'auth' => [
        // Set this to true if ANY endpoints in your API use authentication.
        'enabled' => true,

        // Set this to true if your API should be authenticated by default. If so, you must also set `enabled` (above) to true.
        // You can then use @unauthenticated or @authenticated on individual endpoints to change their status from the default.
        'default' => true,

        // Where is the auth value meant to be sent in a request?
        'in' => AuthIn::BEARER->value,

        // The name of the auth parameter (e.g. token, key, apiKey) or header (e.g. Authorization, Api-Key).
        'name' => 'Authorization',

        // The value of the parameter to be used by Scribe to authenticate response calls.
        // This will NOT be included in the generated documentation. If empty, Scribe will use a random value.
        'use_value' => env('SCRIBE_AUTH_KEY'),

        // Placeholder your users will see for the auth parameter in the example requests.
        // Set this to null if you want Scribe to use a random value as placeholder instead.
        'placeholder' => '{YOUR_API_TOKEN}',

        // Any extra authentication-related info for your users. Markdown and HTML are supported.
        'extra_info' => 'Get your token by calling `POST /api/v1/auth/login` with your email and password. See the **Authentication** section for full details including registration, password reset, and CAPTCHA requirements.',
    ],

    // Example requests for each endpoint will be shown in each of these languages.
    // Supported options are: bash, javascript, php, python
    // To add a language of your own, see https://scribe.knuckles.wtf/laravel/advanced/example-requests
    // Note: does not work for `external` docs types
    'example_languages' => [
        'bash',
        'javascript',
    ],

    // Generate a Postman collection (v2.1.0) in addition to HTML docs.
    // For 'static' docs, the collection will be generated to public/docs/collection.json.
    // For 'laravel' docs, it will be generated to storage/app/scribe/collection.json.
    // Setting `laravel.add_routes` to true (above) will also add a route for the collection.
    'postman' => [
        'enabled' => true,

        'overrides' => [
            // 'info.version' => '2.0.0',
        ],
    ],

    // Generate an OpenAPI spec in addition to docs webpage.
    // For 'static' docs, the collection will be generated to public/docs/openapi.yaml.
    // For 'laravel' docs, it will be generated to storage/app/scribe/openapi.yaml.
    // Setting `laravel.add_routes` to true (above) will also add a route for the spec.
    'openapi' => [
        'enabled' => true,

        // The OpenAPI spec version to generate. Supported versions: '3.0.3', '3.1.0'.
        // OpenAPI 3.1 is more compatible with JSON Schema and is becoming the dominant version.
        // See https://spec.openapis.org/oas/v3.1.0 for details on 3.1 changes.
        'version' => '3.0.3',

        'overrides' => [
            // 'info.version' => '2.0.0',
        ],

        // Additional generators to use when generating the OpenAPI spec.
        // Should extend `Knuckles\Scribe\Writing\OpenApiSpecGenerators\OpenApiGenerator`.
        'generators' => [],
    ],

    'groups' => [
        'default' => 'Other',

        'order' => [
            // ── Getting Started ──────────────────────────
            'Settings',
            'Currencies',
            'Authentication',

            // ── Storefront (Public) ──────────────────────
            'Products',
            'Product Attributes',
            'Product Offers',
            'Product Reviews',
            'Search',
            'Sliders',
            'Website',
            'Menus',
            'Pages',
            'Static Pages',
            'Blogs',
            'Blog Categories',
            'Blog Comments',
            'FAQ',
            'Seller Storefront',

            // ── Customer Account ─────────────────────────
            'User',
            'User Dashboard',
            'User Profile',
            'User Addresses',
            'Wishlist',
            'Recently Viewed',
            'Price & Stock Alerts',
            'Notifications',
            'Product Requests',

            // ── Shopping & Checkout ──────────────────────
            'Cart',
            'Checkout',
            'Orders',
            'Order Keys',
            'Coupons',
            'Taxes',
            'Refund Requests',

            // ── Wallet & Payments ────────────────────────
            'Wallet',
            'Transactions',
            'Payment Methods',

            // ── Seller Platform ──────────────────────────
            'Seller Application',
            'Sellers',
            'Seller Offers',
            'Seller Products',
            'Seller Orders',
            'Seller Coupons',
            'Seller Withdrawals',
            'Seller Reviews',
            'Seller Analytics',
            'Seller Tickets',

            // ── Wholesale ────────────────────────────────
            'Wholesale',

            // ── Affiliate ────────────────────────────────
            'Affiliate Program',
            'Affiliate Tracking',

            // ── Communication ────────────────────────────
            'Support Tickets',
            'Ticket Departments',
            'Newsletter',
            'Contact',

            // ── System & Webhooks ────────────────────────
            'Payment Webhooks',
        ],
    ],

    // Custom logo path. This will be used as the value of the src attribute for the <img> tag,
    // so make sure it points to an accessible URL or path. Set to false to not use a logo.
    // For example, if your logo is in public/img:
    // - 'logo' => '../img/logo.png' // for `static` type (output folder is public/docs)
    // - 'logo' => 'img/logo.png' // for `laravel` type
    'logo' => false,

    // Customize the "Last updated" value displayed in the docs by specifying tokens and formats.
    // Examples:
    // - {date:F j Y} => March 28, 2022
    // - {git:short} => Short hash of the last Git commit
    // Available tokens are `{date:<format>}` and `{git:<format>}`.
    // The format you pass to `date` will be passed to PHP's `date()` function.
    // The format you pass to `git` can be either "short" or "long".
    // Note: does not work for `external` docs types
    'last_updated' => 'Last updated: {date:F j, Y}',

    'examples' => [
        // Set this to any number to generate the same example values for parameters on each run,
        'faker_seed' => 1234,

        // With API resources and transformers, Scribe tries to generate example models to use in your API responses.
        // By default, Scribe will try the model's factory, and if that fails, try fetching the first from the database.
        // You can reorder or remove strategies here.
        'models_source' => ['factoryCreate', 'factoryMake', 'databaseFirst'],
    ],

    // The strategies Scribe will use to extract information about your routes at each stage.
    // Use configureStrategy() to specify settings for a strategy in the list.
    // Use removeStrategies() to remove an included strategy.
    'strategies' => [
        'metadata' => [
            ...Defaults::METADATA_STRATEGIES,
        ],
        'headers' => [
            ...Defaults::HEADERS_STRATEGIES,
            Strategies\StaticData::withSettings(data: [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ]),
        ],
        'urlParameters' => [
            ...Defaults::URL_PARAMETERS_STRATEGIES,
        ],
        'queryParameters' => [
            ...Defaults::QUERY_PARAMETERS_STRATEGIES,
        ],
        'bodyParameters' => [
            ...Defaults::BODY_PARAMETERS_STRATEGIES,
        ],
        'responses' => configureStrategy(
            Defaults::RESPONSES_STRATEGIES,
            Strategies\Responses\ResponseCalls::withSettings(
                only: ['GET *'],
                // Recommended: disable debug mode in response calls to avoid error stack traces in responses
                config: [
                    'app.debug' => false,
                ]
            )
        ),
        'responseFields' => [
            ...Defaults::RESPONSE_FIELDS_STRATEGIES,
        ]
    ],

    // For response calls, API resource responses and transformer responses,
    // Scribe will try to start database transactions, so no changes are persisted to your database.
    // Tell Scribe which connections should be transacted here. If you only use one db connection, you can leave this as is.
    'database_connections_to_transact' => [config('database.default')],

    'fractal' => [
        // If you are using a custom serializer with league/fractal, you can specify it here.
        'serializer' => null,
    ],
];
