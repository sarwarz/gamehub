<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta content="IE=edge,chrome=1" http-equiv="X-UA-Compatible">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <title>Laravel API Documentation</title>

    <link href="https://fonts.googleapis.com/css?family=Open+Sans&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset("/vendor/scribe/css/theme-default.style.css") }}" media="screen">
    <link rel="stylesheet" href="{{ asset("/vendor/scribe/css/theme-default.print.css") }}" media="print">

    <script src="https://cdn.jsdelivr.net/npm/lodash@4.17.10/lodash.min.js"></script>

    <link rel="stylesheet"
          href="https://unpkg.com/@highlightjs/cdn-assets@11.6.0/styles/obsidian.min.css">
    <script src="https://unpkg.com/@highlightjs/cdn-assets@11.6.0/highlight.min.js"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jets/0.14.1/jets.min.js"></script>

    <style id="language-style">
        /* starts out as display none and is replaced with js later  */
                    body .content .bash-example code { display: none; }
                    body .content .javascript-example code { display: none; }
            </style>

    <script>
        var tryItOutBaseUrl = "http://localhost";
        var useCsrf = Boolean();
        var csrfUrl = "/sanctum/csrf-cookie";
    </script>
    <script src="{{ asset("/vendor/scribe/js/tryitout-5.6.0.js") }}"></script>

    <script src="{{ asset("/vendor/scribe/js/theme-default-5.6.0.js") }}"></script>

</head>

<body data-languages="[&quot;bash&quot;,&quot;javascript&quot;]">

<a href="#" id="nav-button">
    <span>
        MENU
        <img src="{{ asset("/vendor/scribe/images/navbar.png") }}" alt="navbar-image"/>
    </span>
</a>
<div class="tocify-wrapper">
    
            <div class="lang-selector">
                                            <button type="button" class="lang-button" data-language-name="bash">bash</button>
                                            <button type="button" class="lang-button" data-language-name="javascript">javascript</button>
                    </div>
    
    <div class="search">
        <input type="text" class="search" id="input-search" placeholder="Search">
    </div>

    <div id="toc">
                    <ul id="tocify-header-introduction" class="tocify-header">
                <li class="tocify-item level-1" data-unique="introduction">
                    <a href="#introduction">Introduction</a>
                </li>
                            </ul>
                    <ul id="tocify-header-authenticating-requests" class="tocify-header">
                <li class="tocify-item level-1" data-unique="authenticating-requests">
                    <a href="#authenticating-requests">Authenticating requests</a>
                </li>
                            </ul>
                    <ul id="tocify-header-endpoints" class="tocify-header">
                <li class="tocify-item level-1" data-unique="endpoints">
                    <a href="#endpoints">Endpoints</a>
                </li>
                                    <ul id="tocify-subheader-endpoints" class="tocify-subheader">
                                                    <li class="tocify-item level-2" data-unique="endpoints-POSTapi-v1-auth-register">
                                <a href="#endpoints-POSTapi-v1-auth-register">Register new user</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="endpoints-POSTapi-v1-auth-login">
                                <a href="#endpoints-POSTapi-v1-auth-login">Login</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="endpoints-POSTapi-v1-auth-logout">
                                <a href="#endpoints-POSTapi-v1-auth-logout">Logout</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="endpoints-GETapi-v1-auth-me">
                                <a href="#endpoints-GETapi-v1-auth-me">Get logged in user</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="endpoints-GETapi-v1-currencies">
                                <a href="#endpoints-GETapi-v1-currencies">Get all active currencies</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="endpoints-GETapi-v1-currencies-default">
                                <a href="#endpoints-GETapi-v1-currencies-default">Get default currency</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="endpoints-GETapi-v1-currencies-convert">
                                <a href="#endpoints-GETapi-v1-currencies-convert">Convert an amount into another currency
Example: /api/currencies/convert?amount=100&to=EUR</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="endpoints-GETapi-v1-currencies--code-">
                                <a href="#endpoints-GETapi-v1-currencies--code-">Get currency by code (e.g. USD, EUR)</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="endpoints-GETapi-v1-taxonomies-categories">
                                <a href="#endpoints-GETapi-v1-taxonomies-categories">GET api/v1/taxonomies/categories</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="endpoints-GETapi-v1-taxonomies-platforms">
                                <a href="#endpoints-GETapi-v1-taxonomies-platforms">GET api/v1/taxonomies/platforms</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="endpoints-GETapi-v1-taxonomies-types">
                                <a href="#endpoints-GETapi-v1-taxonomies-types">GET api/v1/taxonomies/types</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="endpoints-GETapi-v1-taxonomies-regions">
                                <a href="#endpoints-GETapi-v1-taxonomies-regions">GET api/v1/taxonomies/regions</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="endpoints-GETapi-v1-taxonomies-languages">
                                <a href="#endpoints-GETapi-v1-taxonomies-languages">GET api/v1/taxonomies/languages</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="endpoints-GETapi-v1-taxonomies-works-on">
                                <a href="#endpoints-GETapi-v1-taxonomies-works-on">GET api/v1/taxonomies/works-on</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="endpoints-GETapi-v1-taxonomies-developers">
                                <a href="#endpoints-GETapi-v1-taxonomies-developers">GET api/v1/taxonomies/developers</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="endpoints-GETapi-v1-taxonomies-publishers">
                                <a href="#endpoints-GETapi-v1-taxonomies-publishers">GET api/v1/taxonomies/publishers</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="endpoints-GETapi-v1-products">
                                <a href="#endpoints-GETapi-v1-products">Product listing (paginated + filters)</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="endpoints-GETapi-v1-products--id-">
                                <a href="#endpoints-GETapi-v1-products--id-">Single product detail</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="endpoints-GETapi-v1-orders">
                                <a href="#endpoints-GETapi-v1-orders">List all buyer orders (paginated)</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="endpoints-GETapi-v1-orders--id-">
                                <a href="#endpoints-GETapi-v1-orders--id-">Show a single order with all details</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="endpoints-POSTapi-v1-orders">
                                <a href="#endpoints-POSTapi-v1-orders">Create a new order</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="endpoints-PUTapi-v1-orders--id--status">
                                <a href="#endpoints-PUTapi-v1-orders--id--status">Update order status (admin/seller action)</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="endpoints-PUTapi-v1-orders--id--pay">
                                <a href="#endpoints-PUTapi-v1-orders--id--pay">Mark order as paid (add transaction + update invoice)</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="endpoints-POSTapi-v1-orders--id--refund">
                                <a href="#endpoints-POSTapi-v1-orders--id--refund">Refund an order</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="endpoints-POSTapi-v1-orders--id--notes">
                                <a href="#endpoints-POSTapi-v1-orders--id--notes">Add a note to an order</a>
                            </li>
                                                                        </ul>
                            </ul>
            </div>

    <ul class="toc-footer" id="toc-footer">
                    <li style="padding-bottom: 5px;"><a href="{{ route("scribe.postman") }}">View Postman collection</a></li>
                            <li style="padding-bottom: 5px;"><a href="{{ route("scribe.openapi") }}">View OpenAPI spec</a></li>
                <li><a href="http://github.com/knuckleswtf/scribe">Documentation powered by Scribe ✍</a></li>
    </ul>

    <ul class="toc-footer" id="last-updated">
        <li>Last updated: January 16, 2026</li>
    </ul>
</div>

<div class="page-wrapper">
    <div class="dark-box"></div>
    <div class="content">
        <h1 id="introduction">Introduction</h1>
<aside>
    <strong>Base URL</strong>: <code>http://localhost</code>
</aside>
<pre><code>This documentation aims to provide all the information you need to work with our API.

&lt;aside&gt;As you scroll, you'll see code examples for working with the API in different programming languages in the dark area to the right (or as part of the content on mobile).
You can switch the language used with the tabs at the top right (or from the nav menu at the top left on mobile).&lt;/aside&gt;</code></pre>

        <h1 id="authenticating-requests">Authenticating requests</h1>
<p>This API is not authenticated.</p>

        <h1 id="endpoints">Endpoints</h1>

    

                                <h2 id="endpoints-POSTapi-v1-auth-register">Register new user</h2>

<p>
</p>



<span id="example-requests-POSTapi-v1-auth-register">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "http://localhost/api/v1/auth/register" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"name\": \"vmqeopfuudtdsufvyvddq\",
    \"email\": \"kunde.eloisa@example.com\",
    \"password\": \"4[*UyPJ\\\"}6\"
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost/api/v1/auth/register"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "name": "vmqeopfuudtdsufvyvddq",
    "email": "kunde.eloisa@example.com",
    "password": "4[*UyPJ\"}6"
};

fetch(url, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-v1-auth-register">
</span>
<span id="execution-results-POSTapi-v1-auth-register" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-v1-auth-register"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-v1-auth-register"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-v1-auth-register" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-v1-auth-register">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-v1-auth-register" data-method="POST"
      data-path="api/v1/auth/register"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-v1-auth-register', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-v1-auth-register"
                    onclick="tryItOut('POSTapi-v1-auth-register');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-v1-auth-register"
                    onclick="cancelTryOut('POSTapi-v1-auth-register');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-v1-auth-register"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/v1/auth/register</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-v1-auth-register"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-v1-auth-register"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>name</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="name"                data-endpoint="POSTapi-v1-auth-register"
               value="vmqeopfuudtdsufvyvddq"
               data-component="body">
    <br>
<p>Must not be greater than 255 characters. Example: <code>vmqeopfuudtdsufvyvddq</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>email</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="email"                data-endpoint="POSTapi-v1-auth-register"
               value="kunde.eloisa@example.com"
               data-component="body">
    <br>
<p>Must be a valid email address. Example: <code>kunde.eloisa@example.com</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>password</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="password"                data-endpoint="POSTapi-v1-auth-register"
               value="4[*UyPJ"}6"
               data-component="body">
    <br>
<p>Must be at least 8 characters. Example: <code>4[*UyPJ"}6</code></p>
        </div>
        </form>

                    <h2 id="endpoints-POSTapi-v1-auth-login">Login</h2>

<p>
</p>



<span id="example-requests-POSTapi-v1-auth-login">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "http://localhost/api/v1/auth/login" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"email\": \"qkunze@example.com\",
    \"password\": \"O[2UZ5ij-e\\/dl4m{o,\"
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost/api/v1/auth/login"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "email": "qkunze@example.com",
    "password": "O[2UZ5ij-e\/dl4m{o,"
};

fetch(url, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-v1-auth-login">
</span>
<span id="execution-results-POSTapi-v1-auth-login" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-v1-auth-login"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-v1-auth-login"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-v1-auth-login" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-v1-auth-login">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-v1-auth-login" data-method="POST"
      data-path="api/v1/auth/login"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-v1-auth-login', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-v1-auth-login"
                    onclick="tryItOut('POSTapi-v1-auth-login');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-v1-auth-login"
                    onclick="cancelTryOut('POSTapi-v1-auth-login');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-v1-auth-login"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/v1/auth/login</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-v1-auth-login"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-v1-auth-login"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>email</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="email"                data-endpoint="POSTapi-v1-auth-login"
               value="qkunze@example.com"
               data-component="body">
    <br>
<p>Must be a valid email address. Example: <code>qkunze@example.com</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>password</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="password"                data-endpoint="POSTapi-v1-auth-login"
               value="O[2UZ5ij-e/dl4m{o,"
               data-component="body">
    <br>
<p>Example: <code>O[2UZ5ij-e/dl4m{o,</code></p>
        </div>
        </form>

                    <h2 id="endpoints-POSTapi-v1-auth-logout">Logout</h2>

<p>
</p>



<span id="example-requests-POSTapi-v1-auth-logout">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "http://localhost/api/v1/auth/logout" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost/api/v1/auth/logout"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "POST",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-v1-auth-logout">
</span>
<span id="execution-results-POSTapi-v1-auth-logout" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-v1-auth-logout"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-v1-auth-logout"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-v1-auth-logout" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-v1-auth-logout">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-v1-auth-logout" data-method="POST"
      data-path="api/v1/auth/logout"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-v1-auth-logout', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-v1-auth-logout"
                    onclick="tryItOut('POSTapi-v1-auth-logout');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-v1-auth-logout"
                    onclick="cancelTryOut('POSTapi-v1-auth-logout');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-v1-auth-logout"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/v1/auth/logout</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-v1-auth-logout"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-v1-auth-logout"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        </form>

                    <h2 id="endpoints-GETapi-v1-auth-me">Get logged in user</h2>

<p>
</p>



<span id="example-requests-GETapi-v1-auth-me">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://localhost/api/v1/auth/me" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost/api/v1/auth/me"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-auth-me">
            <blockquote>
            <p>Example response (401):</p>
        </blockquote>
                <details class="annotation">
            <summary style="cursor: pointer;">
                <small onclick="textContent = parentElement.parentElement.open ? 'Show headers' : 'Hide headers'">Show headers</small>
            </summary>
            <pre><code class="language-http">cache-control: no-cache, private
content-type: application/json
access-control-allow-origin: *
 </code></pre></details>         <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;status&quot;: &quot;error&quot;,
    &quot;message&quot;: &quot;Unauthenticated&quot;
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-v1-auth-me" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-v1-auth-me"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-v1-auth-me"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-auth-me" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-v1-auth-me">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-auth-me" data-method="GET"
      data-path="api/v1/auth/me"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-auth-me', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-v1-auth-me"
                    onclick="tryItOut('GETapi-v1-auth-me');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-v1-auth-me"
                    onclick="cancelTryOut('GETapi-v1-auth-me');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-v1-auth-me"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/v1/auth/me</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-v1-auth-me"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-v1-auth-me"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        </form>

                    <h2 id="endpoints-GETapi-v1-currencies">Get all active currencies</h2>

<p>
</p>



<span id="example-requests-GETapi-v1-currencies">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://localhost/api/v1/currencies" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost/api/v1/currencies"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-currencies">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <details class="annotation">
            <summary style="cursor: pointer;">
                <small onclick="textContent = parentElement.parentElement.open ? 'Show headers' : 'Hide headers'">Show headers</small>
            </summary>
            <pre><code class="language-http">cache-control: no-cache, private
content-type: application/json
access-control-allow-origin: *
 </code></pre></details>         <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;status&quot;: &quot;success&quot;,
    &quot;data&quot;: [
        {
            &quot;id&quot;: 1,
            &quot;code&quot;: &quot;USD&quot;,
            &quot;name&quot;: &quot;US Dollar&quot;,
            &quot;symbol&quot;: &quot;$&quot;,
            &quot;rate&quot;: &quot;1.00000000&quot;,
            &quot;is_default&quot;: true
        },
        {
            &quot;id&quot;: 2,
            &quot;code&quot;: &quot;EUR&quot;,
            &quot;name&quot;: &quot;Euro&quot;,
            &quot;symbol&quot;: &quot;&euro;&quot;,
            &quot;rate&quot;: &quot;0.92000000&quot;,
            &quot;is_default&quot;: false
        },
        {
            &quot;id&quot;: 3,
            &quot;code&quot;: &quot;GBP&quot;,
            &quot;name&quot;: &quot;British Pound&quot;,
            &quot;symbol&quot;: &quot;&pound;&quot;,
            &quot;rate&quot;: &quot;0.79000000&quot;,
            &quot;is_default&quot;: false
        },
        {
            &quot;id&quot;: 4,
            &quot;code&quot;: &quot;BDT&quot;,
            &quot;name&quot;: &quot;Bangladeshi Taka&quot;,
            &quot;symbol&quot;: &quot;৳&quot;,
            &quot;rate&quot;: &quot;110.25000000&quot;,
            &quot;is_default&quot;: false
        },
        {
            &quot;id&quot;: 5,
            &quot;code&quot;: &quot;JPY&quot;,
            &quot;name&quot;: &quot;Japanese Yen&quot;,
            &quot;symbol&quot;: &quot;&yen;&quot;,
            &quot;rate&quot;: &quot;148.35000000&quot;,
            &quot;is_default&quot;: false
        }
    ]
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-v1-currencies" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-v1-currencies"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-v1-currencies"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-currencies" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-v1-currencies">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-currencies" data-method="GET"
      data-path="api/v1/currencies"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-currencies', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-v1-currencies"
                    onclick="tryItOut('GETapi-v1-currencies');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-v1-currencies"
                    onclick="cancelTryOut('GETapi-v1-currencies');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-v1-currencies"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/v1/currencies</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-v1-currencies"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-v1-currencies"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        </form>

                    <h2 id="endpoints-GETapi-v1-currencies-default">Get default currency</h2>

<p>
</p>



<span id="example-requests-GETapi-v1-currencies-default">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://localhost/api/v1/currencies/default" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost/api/v1/currencies/default"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-currencies-default">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <details class="annotation">
            <summary style="cursor: pointer;">
                <small onclick="textContent = parentElement.parentElement.open ? 'Show headers' : 'Hide headers'">Show headers</small>
            </summary>
            <pre><code class="language-http">cache-control: no-cache, private
content-type: application/json
access-control-allow-origin: *
 </code></pre></details>         <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;status&quot;: &quot;success&quot;,
    &quot;data&quot;: {
        &quot;id&quot;: 1,
        &quot;code&quot;: &quot;USD&quot;,
        &quot;name&quot;: &quot;US Dollar&quot;,
        &quot;symbol&quot;: &quot;$&quot;,
        &quot;is_active&quot;: true,
        &quot;is_default&quot;: true,
        &quot;rate&quot;: &quot;1.00000000&quot;,
        &quot;fetched_at&quot;: &quot;2026-01-15 15:16:11&quot;,
        &quot;created_at&quot;: &quot;2026-01-15T15:16:11.000000Z&quot;,
        &quot;updated_at&quot;: &quot;2026-01-15T15:16:11.000000Z&quot;
    }
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-v1-currencies-default" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-v1-currencies-default"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-v1-currencies-default"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-currencies-default" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-v1-currencies-default">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-currencies-default" data-method="GET"
      data-path="api/v1/currencies/default"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-currencies-default', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-v1-currencies-default"
                    onclick="tryItOut('GETapi-v1-currencies-default');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-v1-currencies-default"
                    onclick="cancelTryOut('GETapi-v1-currencies-default');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-v1-currencies-default"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/v1/currencies/default</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-v1-currencies-default"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-v1-currencies-default"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        </form>

                    <h2 id="endpoints-GETapi-v1-currencies-convert">Convert an amount into another currency
Example: /api/currencies/convert?amount=100&amp;to=EUR</h2>

<p>
</p>



<span id="example-requests-GETapi-v1-currencies-convert">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://localhost/api/v1/currencies/convert" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"amount\": 73,
    \"to\": \"mqe\"
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost/api/v1/currencies/convert"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "amount": 73,
    "to": "mqe"
};

fetch(url, {
    method: "GET",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-currencies-convert">
            <blockquote>
            <p>Example response (404):</p>
        </blockquote>
                <details class="annotation">
            <summary style="cursor: pointer;">
                <small onclick="textContent = parentElement.parentElement.open ? 'Show headers' : 'Hide headers'">Show headers</small>
            </summary>
            <pre><code class="language-http">cache-control: no-cache, private
content-type: application/json
access-control-allow-origin: *
 </code></pre></details>         <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;status&quot;: &quot;error&quot;,
    &quot;message&quot;: &quot;Currency MQE not found or inactive&quot;
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-v1-currencies-convert" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-v1-currencies-convert"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-v1-currencies-convert"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-currencies-convert" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-v1-currencies-convert">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-currencies-convert" data-method="GET"
      data-path="api/v1/currencies/convert"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-currencies-convert', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-v1-currencies-convert"
                    onclick="tryItOut('GETapi-v1-currencies-convert');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-v1-currencies-convert"
                    onclick="cancelTryOut('GETapi-v1-currencies-convert');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-v1-currencies-convert"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/v1/currencies/convert</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-v1-currencies-convert"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-v1-currencies-convert"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>amount</code></b>&nbsp;&nbsp;
<small>number</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="amount"                data-endpoint="GETapi-v1-currencies-convert"
               value="73"
               data-component="body">
    <br>
<p>Must be at least 0. Example: <code>73</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>to</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="to"                data-endpoint="GETapi-v1-currencies-convert"
               value="mqe"
               data-component="body">
    <br>
<p>Must be 3 characters. Example: <code>mqe</code></p>
        </div>
        </form>

                    <h2 id="endpoints-GETapi-v1-currencies--code-">Get currency by code (e.g. USD, EUR)</h2>

<p>
</p>



<span id="example-requests-GETapi-v1-currencies--code-">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://localhost/api/v1/currencies/1" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost/api/v1/currencies/1"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-currencies--code-">
            <blockquote>
            <p>Example response (404):</p>
        </blockquote>
                <details class="annotation">
            <summary style="cursor: pointer;">
                <small onclick="textContent = parentElement.parentElement.open ? 'Show headers' : 'Hide headers'">Show headers</small>
            </summary>
            <pre><code class="language-http">cache-control: no-cache, private
content-type: application/json
access-control-allow-origin: *
 </code></pre></details>         <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;status&quot;: &quot;error&quot;,
    &quot;message&quot;: &quot;API endpoint not found&quot;
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-v1-currencies--code-" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-v1-currencies--code-"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-v1-currencies--code-"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-currencies--code-" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-v1-currencies--code-">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-currencies--code-" data-method="GET"
      data-path="api/v1/currencies/{code}"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-currencies--code-', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-v1-currencies--code-"
                    onclick="tryItOut('GETapi-v1-currencies--code-');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-v1-currencies--code-"
                    onclick="cancelTryOut('GETapi-v1-currencies--code-');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-v1-currencies--code-"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/v1/currencies/{code}</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-v1-currencies--code-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-v1-currencies--code-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>code</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="code"                data-endpoint="GETapi-v1-currencies--code-"
               value="1"
               data-component="url">
    <br>
<p>Example: <code>1</code></p>
            </div>
                    </form>

                    <h2 id="endpoints-GETapi-v1-taxonomies-categories">GET api/v1/taxonomies/categories</h2>

<p>
</p>



<span id="example-requests-GETapi-v1-taxonomies-categories">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://localhost/api/v1/taxonomies/categories" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost/api/v1/taxonomies/categories"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-taxonomies-categories">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <details class="annotation">
            <summary style="cursor: pointer;">
                <small onclick="textContent = parentElement.parentElement.open ? 'Show headers' : 'Hide headers'">Show headers</small>
            </summary>
            <pre><code class="language-http">cache-control: no-cache, private
content-type: application/json
access-control-allow-origin: *
 </code></pre></details>         <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;status&quot;: &quot;success&quot;,
    &quot;data&quot;: [
        {
            &quot;id&quot;: 1,
            &quot;name&quot;: &quot;Operating Systems&quot;,
            &quot;slug&quot;: &quot;operating-systems&quot;
        },
        {
            &quot;id&quot;: 2,
            &quot;name&quot;: &quot;Office &amp; Productivity&quot;,
            &quot;slug&quot;: &quot;office-productivity&quot;
        },
        {
            &quot;id&quot;: 3,
            &quot;name&quot;: &quot;Antivirus &amp; Security&quot;,
            &quot;slug&quot;: &quot;antivirus-security&quot;
        },
        {
            &quot;id&quot;: 4,
            &quot;name&quot;: &quot;Games&quot;,
            &quot;slug&quot;: &quot;games&quot;
        },
        {
            &quot;id&quot;: 5,
            &quot;name&quot;: &quot;Gift Cards&quot;,
            &quot;slug&quot;: &quot;gift-cards&quot;
        },
        {
            &quot;id&quot;: 6,
            &quot;name&quot;: &quot;Software Development Tools&quot;,
            &quot;slug&quot;: &quot;software-development-tools&quot;
        },
        {
            &quot;id&quot;: 7,
            &quot;name&quot;: &quot;Graphic Design&quot;,
            &quot;slug&quot;: &quot;graphic-design&quot;
        }
    ]
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-v1-taxonomies-categories" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-v1-taxonomies-categories"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-v1-taxonomies-categories"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-taxonomies-categories" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-v1-taxonomies-categories">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-taxonomies-categories" data-method="GET"
      data-path="api/v1/taxonomies/categories"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-taxonomies-categories', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-v1-taxonomies-categories"
                    onclick="tryItOut('GETapi-v1-taxonomies-categories');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-v1-taxonomies-categories"
                    onclick="cancelTryOut('GETapi-v1-taxonomies-categories');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-v1-taxonomies-categories"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/v1/taxonomies/categories</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-v1-taxonomies-categories"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-v1-taxonomies-categories"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        </form>

                    <h2 id="endpoints-GETapi-v1-taxonomies-platforms">GET api/v1/taxonomies/platforms</h2>

<p>
</p>



<span id="example-requests-GETapi-v1-taxonomies-platforms">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://localhost/api/v1/taxonomies/platforms" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost/api/v1/taxonomies/platforms"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-taxonomies-platforms">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <details class="annotation">
            <summary style="cursor: pointer;">
                <small onclick="textContent = parentElement.parentElement.open ? 'Show headers' : 'Hide headers'">Show headers</small>
            </summary>
            <pre><code class="language-http">cache-control: no-cache, private
content-type: application/json
access-control-allow-origin: *
 </code></pre></details>         <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;status&quot;: &quot;success&quot;,
    &quot;data&quot;: [
        {
            &quot;id&quot;: 1,
            &quot;name&quot;: &quot;Steam&quot;,
            &quot;slug&quot;: &quot;steam&quot;
        },
        {
            &quot;id&quot;: 2,
            &quot;name&quot;: &quot;Epic Games&quot;,
            &quot;slug&quot;: &quot;epic-games&quot;
        },
        {
            &quot;id&quot;: 3,
            &quot;name&quot;: &quot;Origin&quot;,
            &quot;slug&quot;: &quot;origin&quot;
        },
        {
            &quot;id&quot;: 4,
            &quot;name&quot;: &quot;Uplay&quot;,
            &quot;slug&quot;: &quot;uplay&quot;
        },
        {
            &quot;id&quot;: 5,
            &quot;name&quot;: &quot;GOG&quot;,
            &quot;slug&quot;: &quot;gog&quot;
        },
        {
            &quot;id&quot;: 6,
            &quot;name&quot;: &quot;Xbox&quot;,
            &quot;slug&quot;: &quot;xbox&quot;
        },
        {
            &quot;id&quot;: 7,
            &quot;name&quot;: &quot;PlayStation&quot;,
            &quot;slug&quot;: &quot;playstation&quot;
        },
        {
            &quot;id&quot;: 8,
            &quot;name&quot;: &quot;Nintendo Switch&quot;,
            &quot;slug&quot;: &quot;nintendo-switch&quot;
        },
        {
            &quot;id&quot;: 9,
            &quot;name&quot;: &quot;Windows&quot;,
            &quot;slug&quot;: &quot;windows&quot;
        },
        {
            &quot;id&quot;: 10,
            &quot;name&quot;: &quot;Mac OS&quot;,
            &quot;slug&quot;: &quot;mac-os&quot;
        }
    ]
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-v1-taxonomies-platforms" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-v1-taxonomies-platforms"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-v1-taxonomies-platforms"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-taxonomies-platforms" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-v1-taxonomies-platforms">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-taxonomies-platforms" data-method="GET"
      data-path="api/v1/taxonomies/platforms"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-taxonomies-platforms', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-v1-taxonomies-platforms"
                    onclick="tryItOut('GETapi-v1-taxonomies-platforms');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-v1-taxonomies-platforms"
                    onclick="cancelTryOut('GETapi-v1-taxonomies-platforms');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-v1-taxonomies-platforms"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/v1/taxonomies/platforms</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-v1-taxonomies-platforms"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-v1-taxonomies-platforms"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        </form>

                    <h2 id="endpoints-GETapi-v1-taxonomies-types">GET api/v1/taxonomies/types</h2>

<p>
</p>



<span id="example-requests-GETapi-v1-taxonomies-types">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://localhost/api/v1/taxonomies/types" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost/api/v1/taxonomies/types"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-taxonomies-types">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <details class="annotation">
            <summary style="cursor: pointer;">
                <small onclick="textContent = parentElement.parentElement.open ? 'Show headers' : 'Hide headers'">Show headers</small>
            </summary>
            <pre><code class="language-http">cache-control: no-cache, private
content-type: application/json
access-control-allow-origin: *
 </code></pre></details>         <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;status&quot;: &quot;success&quot;,
    &quot;data&quot;: [
        {
            &quot;id&quot;: 1,
            &quot;name&quot;: &quot;Game&quot;,
            &quot;slug&quot;: &quot;game&quot;,
            &quot;commission&quot;: &quot;10.00&quot;
        },
        {
            &quot;id&quot;: 2,
            &quot;name&quot;: &quot;Software&quot;,
            &quot;slug&quot;: &quot;software&quot;,
            &quot;commission&quot;: &quot;12.50&quot;
        },
        {
            &quot;id&quot;: 3,
            &quot;name&quot;: &quot;Gift Card&quot;,
            &quot;slug&quot;: &quot;gift-card&quot;,
            &quot;commission&quot;: &quot;5.00&quot;
        },
        {
            &quot;id&quot;: 4,
            &quot;name&quot;: &quot;DLC&quot;,
            &quot;slug&quot;: &quot;dlc&quot;,
            &quot;commission&quot;: &quot;8.00&quot;
        },
        {
            &quot;id&quot;: 5,
            &quot;name&quot;: &quot;Subscription&quot;,
            &quot;slug&quot;: &quot;subscription&quot;,
            &quot;commission&quot;: &quot;15.00&quot;
        }
    ]
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-v1-taxonomies-types" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-v1-taxonomies-types"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-v1-taxonomies-types"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-taxonomies-types" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-v1-taxonomies-types">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-taxonomies-types" data-method="GET"
      data-path="api/v1/taxonomies/types"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-taxonomies-types', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-v1-taxonomies-types"
                    onclick="tryItOut('GETapi-v1-taxonomies-types');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-v1-taxonomies-types"
                    onclick="cancelTryOut('GETapi-v1-taxonomies-types');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-v1-taxonomies-types"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/v1/taxonomies/types</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-v1-taxonomies-types"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-v1-taxonomies-types"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        </form>

                    <h2 id="endpoints-GETapi-v1-taxonomies-regions">GET api/v1/taxonomies/regions</h2>

<p>
</p>



<span id="example-requests-GETapi-v1-taxonomies-regions">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://localhost/api/v1/taxonomies/regions" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost/api/v1/taxonomies/regions"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-taxonomies-regions">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <details class="annotation">
            <summary style="cursor: pointer;">
                <small onclick="textContent = parentElement.parentElement.open ? 'Show headers' : 'Hide headers'">Show headers</small>
            </summary>
            <pre><code class="language-http">cache-control: no-cache, private
content-type: application/json
access-control-allow-origin: *
 </code></pre></details>         <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;status&quot;: &quot;success&quot;,
    &quot;data&quot;: [
        {
            &quot;id&quot;: 1,
            &quot;name&quot;: &quot;Global&quot;,
            &quot;slug&quot;: &quot;global&quot;
        },
        {
            &quot;id&quot;: 2,
            &quot;name&quot;: &quot;Europe (EU)&quot;,
            &quot;slug&quot;: &quot;europe-eu&quot;
        },
        {
            &quot;id&quot;: 3,
            &quot;name&quot;: &quot;North America (NA)&quot;,
            &quot;slug&quot;: &quot;north-america-na&quot;
        },
        {
            &quot;id&quot;: 4,
            &quot;name&quot;: &quot;South America (SA)&quot;,
            &quot;slug&quot;: &quot;south-america-sa&quot;
        },
        {
            &quot;id&quot;: 5,
            &quot;name&quot;: &quot;Asia&quot;,
            &quot;slug&quot;: &quot;asia&quot;
        },
        {
            &quot;id&quot;: 6,
            &quot;name&quot;: &quot;Middle East&quot;,
            &quot;slug&quot;: &quot;middle-east&quot;
        },
        {
            &quot;id&quot;: 7,
            &quot;name&quot;: &quot;Africa&quot;,
            &quot;slug&quot;: &quot;africa&quot;
        },
        {
            &quot;id&quot;: 8,
            &quot;name&quot;: &quot;Oceania&quot;,
            &quot;slug&quot;: &quot;oceania&quot;
        }
    ]
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-v1-taxonomies-regions" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-v1-taxonomies-regions"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-v1-taxonomies-regions"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-taxonomies-regions" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-v1-taxonomies-regions">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-taxonomies-regions" data-method="GET"
      data-path="api/v1/taxonomies/regions"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-taxonomies-regions', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-v1-taxonomies-regions"
                    onclick="tryItOut('GETapi-v1-taxonomies-regions');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-v1-taxonomies-regions"
                    onclick="cancelTryOut('GETapi-v1-taxonomies-regions');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-v1-taxonomies-regions"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/v1/taxonomies/regions</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-v1-taxonomies-regions"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-v1-taxonomies-regions"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        </form>

                    <h2 id="endpoints-GETapi-v1-taxonomies-languages">GET api/v1/taxonomies/languages</h2>

<p>
</p>



<span id="example-requests-GETapi-v1-taxonomies-languages">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://localhost/api/v1/taxonomies/languages" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost/api/v1/taxonomies/languages"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-taxonomies-languages">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <details class="annotation">
            <summary style="cursor: pointer;">
                <small onclick="textContent = parentElement.parentElement.open ? 'Show headers' : 'Hide headers'">Show headers</small>
            </summary>
            <pre><code class="language-http">cache-control: no-cache, private
content-type: application/json
access-control-allow-origin: *
 </code></pre></details>         <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;status&quot;: &quot;success&quot;,
    &quot;data&quot;: [
        {
            &quot;id&quot;: 1,
            &quot;name&quot;: &quot;English&quot;,
            &quot;slug&quot;: &quot;english&quot;
        },
        {
            &quot;id&quot;: 2,
            &quot;name&quot;: &quot;French&quot;,
            &quot;slug&quot;: &quot;french&quot;
        },
        {
            &quot;id&quot;: 3,
            &quot;name&quot;: &quot;German&quot;,
            &quot;slug&quot;: &quot;german&quot;
        },
        {
            &quot;id&quot;: 4,
            &quot;name&quot;: &quot;Spanish&quot;,
            &quot;slug&quot;: &quot;spanish&quot;
        },
        {
            &quot;id&quot;: 5,
            &quot;name&quot;: &quot;Italian&quot;,
            &quot;slug&quot;: &quot;italian&quot;
        },
        {
            &quot;id&quot;: 6,
            &quot;name&quot;: &quot;Portuguese&quot;,
            &quot;slug&quot;: &quot;portuguese&quot;
        },
        {
            &quot;id&quot;: 7,
            &quot;name&quot;: &quot;Russian&quot;,
            &quot;slug&quot;: &quot;russian&quot;
        },
        {
            &quot;id&quot;: 8,
            &quot;name&quot;: &quot;Chinese (Simplified)&quot;,
            &quot;slug&quot;: &quot;chinese-simplified&quot;
        },
        {
            &quot;id&quot;: 9,
            &quot;name&quot;: &quot;Chinese (Traditional)&quot;,
            &quot;slug&quot;: &quot;chinese-traditional&quot;
        },
        {
            &quot;id&quot;: 10,
            &quot;name&quot;: &quot;Japanese&quot;,
            &quot;slug&quot;: &quot;japanese&quot;
        },
        {
            &quot;id&quot;: 11,
            &quot;name&quot;: &quot;Korean&quot;,
            &quot;slug&quot;: &quot;korean&quot;
        },
        {
            &quot;id&quot;: 12,
            &quot;name&quot;: &quot;Arabic&quot;,
            &quot;slug&quot;: &quot;arabic&quot;
        },
        {
            &quot;id&quot;: 13,
            &quot;name&quot;: &quot;Turkish&quot;,
            &quot;slug&quot;: &quot;turkish&quot;
        },
        {
            &quot;id&quot;: 14,
            &quot;name&quot;: &quot;Hindi&quot;,
            &quot;slug&quot;: &quot;hindi&quot;
        }
    ]
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-v1-taxonomies-languages" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-v1-taxonomies-languages"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-v1-taxonomies-languages"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-taxonomies-languages" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-v1-taxonomies-languages">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-taxonomies-languages" data-method="GET"
      data-path="api/v1/taxonomies/languages"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-taxonomies-languages', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-v1-taxonomies-languages"
                    onclick="tryItOut('GETapi-v1-taxonomies-languages');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-v1-taxonomies-languages"
                    onclick="cancelTryOut('GETapi-v1-taxonomies-languages');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-v1-taxonomies-languages"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/v1/taxonomies/languages</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-v1-taxonomies-languages"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-v1-taxonomies-languages"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        </form>

                    <h2 id="endpoints-GETapi-v1-taxonomies-works-on">GET api/v1/taxonomies/works-on</h2>

<p>
</p>



<span id="example-requests-GETapi-v1-taxonomies-works-on">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://localhost/api/v1/taxonomies/works-on" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost/api/v1/taxonomies/works-on"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-taxonomies-works-on">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <details class="annotation">
            <summary style="cursor: pointer;">
                <small onclick="textContent = parentElement.parentElement.open ? 'Show headers' : 'Hide headers'">Show headers</small>
            </summary>
            <pre><code class="language-http">cache-control: no-cache, private
content-type: application/json
access-control-allow-origin: *
 </code></pre></details>         <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;status&quot;: &quot;success&quot;,
    &quot;data&quot;: [
        {
            &quot;id&quot;: 1,
            &quot;name&quot;: &quot;Windows&quot;,
            &quot;slug&quot;: &quot;windows&quot;
        },
        {
            &quot;id&quot;: 2,
            &quot;name&quot;: &quot;Mac OS&quot;,
            &quot;slug&quot;: &quot;mac-os&quot;
        },
        {
            &quot;id&quot;: 3,
            &quot;name&quot;: &quot;Linux&quot;,
            &quot;slug&quot;: &quot;linux&quot;
        },
        {
            &quot;id&quot;: 4,
            &quot;name&quot;: &quot;Android&quot;,
            &quot;slug&quot;: &quot;android&quot;
        },
        {
            &quot;id&quot;: 5,
            &quot;name&quot;: &quot;iOS&quot;,
            &quot;slug&quot;: &quot;ios&quot;
        }
    ]
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-v1-taxonomies-works-on" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-v1-taxonomies-works-on"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-v1-taxonomies-works-on"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-taxonomies-works-on" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-v1-taxonomies-works-on">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-taxonomies-works-on" data-method="GET"
      data-path="api/v1/taxonomies/works-on"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-taxonomies-works-on', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-v1-taxonomies-works-on"
                    onclick="tryItOut('GETapi-v1-taxonomies-works-on');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-v1-taxonomies-works-on"
                    onclick="cancelTryOut('GETapi-v1-taxonomies-works-on');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-v1-taxonomies-works-on"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/v1/taxonomies/works-on</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-v1-taxonomies-works-on"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-v1-taxonomies-works-on"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        </form>

                    <h2 id="endpoints-GETapi-v1-taxonomies-developers">GET api/v1/taxonomies/developers</h2>

<p>
</p>



<span id="example-requests-GETapi-v1-taxonomies-developers">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://localhost/api/v1/taxonomies/developers" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost/api/v1/taxonomies/developers"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-taxonomies-developers">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <details class="annotation">
            <summary style="cursor: pointer;">
                <small onclick="textContent = parentElement.parentElement.open ? 'Show headers' : 'Hide headers'">Show headers</small>
            </summary>
            <pre><code class="language-http">cache-control: no-cache, private
content-type: application/json
access-control-allow-origin: *
 </code></pre></details>         <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;status&quot;: &quot;success&quot;,
    &quot;data&quot;: [
        {
            &quot;id&quot;: 1,
            &quot;name&quot;: &quot;Ubisoft&quot;,
            &quot;slug&quot;: &quot;ubisoft&quot;
        },
        {
            &quot;id&quot;: 2,
            &quot;name&quot;: &quot;Rockstar Games&quot;,
            &quot;slug&quot;: &quot;rockstar-games&quot;
        },
        {
            &quot;id&quot;: 3,
            &quot;name&quot;: &quot;Mojang Studios&quot;,
            &quot;slug&quot;: &quot;mojang-studios&quot;
        },
        {
            &quot;id&quot;: 4,
            &quot;name&quot;: &quot;CD Projekt Red&quot;,
            &quot;slug&quot;: &quot;cd-projekt-red&quot;
        },
        {
            &quot;id&quot;: 5,
            &quot;name&quot;: &quot;Valve Corporation&quot;,
            &quot;slug&quot;: &quot;valve-corporation&quot;
        }
    ]
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-v1-taxonomies-developers" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-v1-taxonomies-developers"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-v1-taxonomies-developers"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-taxonomies-developers" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-v1-taxonomies-developers">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-taxonomies-developers" data-method="GET"
      data-path="api/v1/taxonomies/developers"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-taxonomies-developers', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-v1-taxonomies-developers"
                    onclick="tryItOut('GETapi-v1-taxonomies-developers');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-v1-taxonomies-developers"
                    onclick="cancelTryOut('GETapi-v1-taxonomies-developers');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-v1-taxonomies-developers"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/v1/taxonomies/developers</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-v1-taxonomies-developers"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-v1-taxonomies-developers"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        </form>

                    <h2 id="endpoints-GETapi-v1-taxonomies-publishers">GET api/v1/taxonomies/publishers</h2>

<p>
</p>



<span id="example-requests-GETapi-v1-taxonomies-publishers">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://localhost/api/v1/taxonomies/publishers" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost/api/v1/taxonomies/publishers"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-taxonomies-publishers">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <details class="annotation">
            <summary style="cursor: pointer;">
                <small onclick="textContent = parentElement.parentElement.open ? 'Show headers' : 'Hide headers'">Show headers</small>
            </summary>
            <pre><code class="language-http">cache-control: no-cache, private
content-type: application/json
access-control-allow-origin: *
 </code></pre></details>         <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;status&quot;: &quot;success&quot;,
    &quot;data&quot;: [
        {
            &quot;id&quot;: 1,
            &quot;name&quot;: &quot;Ubisoft&quot;,
            &quot;slug&quot;: &quot;ubisoft&quot;
        },
        {
            &quot;id&quot;: 2,
            &quot;name&quot;: &quot;Electronic Arts (EA)&quot;,
            &quot;slug&quot;: &quot;electronic-arts-ea&quot;
        },
        {
            &quot;id&quot;: 3,
            &quot;name&quot;: &quot;Activision&quot;,
            &quot;slug&quot;: &quot;activision&quot;
        },
        {
            &quot;id&quot;: 4,
            &quot;name&quot;: &quot;Square Enix&quot;,
            &quot;slug&quot;: &quot;square-enix&quot;
        },
        {
            &quot;id&quot;: 5,
            &quot;name&quot;: &quot;Bethesda Softworks&quot;,
            &quot;slug&quot;: &quot;bethesda-softworks&quot;
        }
    ]
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-v1-taxonomies-publishers" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-v1-taxonomies-publishers"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-v1-taxonomies-publishers"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-taxonomies-publishers" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-v1-taxonomies-publishers">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-taxonomies-publishers" data-method="GET"
      data-path="api/v1/taxonomies/publishers"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-taxonomies-publishers', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-v1-taxonomies-publishers"
                    onclick="tryItOut('GETapi-v1-taxonomies-publishers');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-v1-taxonomies-publishers"
                    onclick="cancelTryOut('GETapi-v1-taxonomies-publishers');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-v1-taxonomies-publishers"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/v1/taxonomies/publishers</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-v1-taxonomies-publishers"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-v1-taxonomies-publishers"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        </form>

                    <h2 id="endpoints-GETapi-v1-products">Product listing (paginated + filters)</h2>

<p>
</p>



<span id="example-requests-GETapi-v1-products">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://localhost/api/v1/products" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost/api/v1/products"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-products">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <details class="annotation">
            <summary style="cursor: pointer;">
                <small onclick="textContent = parentElement.parentElement.open ? 'Show headers' : 'Hide headers'">Show headers</small>
            </summary>
            <pre><code class="language-http">cache-control: no-cache, private
content-type: application/json
access-control-allow-origin: *
 </code></pre></details>         <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;status&quot;: &quot;success&quot;,
    &quot;message&quot;: &quot;Products fetched successfully&quot;,
    &quot;data&quot;: {
        &quot;products&quot;: [
            {
                &quot;id&quot;: 1,
                &quot;title&quot;: &quot;Windows 11 Home OEM Key&quot;,
                &quot;slug&quot;: &quot;windows-11-home-oem-key&quot;,
                &quot;cover_image&quot;: null,
                &quot;developer&quot;: {
                    &quot;id&quot;: 1,
                    &quot;name&quot;: &quot;Ubisoft&quot;,
                    &quot;slug&quot;: &quot;ubisoft&quot;
                },
                &quot;publisher&quot;: {
                    &quot;id&quot;: 1,
                    &quot;name&quot;: &quot;Ubisoft&quot;,
                    &quot;slug&quot;: &quot;ubisoft&quot;
                },
                &quot;categories&quot;: [
                    {
                        &quot;id&quot;: 1,
                        &quot;name&quot;: &quot;Operating Systems&quot;,
                        &quot;slug&quot;: &quot;operating-systems&quot;,
                        &quot;pivot&quot;: {
                            &quot;product_id&quot;: 1,
                            &quot;category_id&quot;: 1,
                            &quot;created_at&quot;: &quot;2026-01-15T15:16:11.000000Z&quot;,
                            &quot;updated_at&quot;: &quot;2026-01-15T15:16:11.000000Z&quot;
                        }
                    }
                ],
                &quot;platforms&quot;: [
                    {
                        &quot;id&quot;: 1,
                        &quot;name&quot;: &quot;Steam&quot;,
                        &quot;slug&quot;: &quot;steam&quot;,
                        &quot;pivot&quot;: {
                            &quot;product_id&quot;: 1,
                            &quot;platform_id&quot;: 1,
                            &quot;created_at&quot;: &quot;2026-01-15T15:16:11.000000Z&quot;,
                            &quot;updated_at&quot;: &quot;2026-01-15T15:16:11.000000Z&quot;
                        }
                    }
                ],
                &quot;types&quot;: [
                    {
                        &quot;id&quot;: 1,
                        &quot;name&quot;: &quot;Game&quot;,
                        &quot;slug&quot;: &quot;game&quot;,
                        &quot;pivot&quot;: {
                            &quot;product_id&quot;: 1,
                            &quot;type_id&quot;: 1,
                            &quot;created_at&quot;: &quot;2026-01-15T15:16:11.000000Z&quot;,
                            &quot;updated_at&quot;: &quot;2026-01-15T15:16:11.000000Z&quot;
                        }
                    }
                ],
                &quot;regions&quot;: [
                    {
                        &quot;id&quot;: 1,
                        &quot;name&quot;: &quot;Global&quot;,
                        &quot;slug&quot;: &quot;global&quot;,
                        &quot;pivot&quot;: {
                            &quot;product_id&quot;: 1,
                            &quot;region_id&quot;: 1,
                            &quot;created_at&quot;: &quot;2026-01-15T15:16:11.000000Z&quot;,
                            &quot;updated_at&quot;: &quot;2026-01-15T15:16:11.000000Z&quot;
                        }
                    }
                ],
                &quot;languages&quot;: [
                    {
                        &quot;id&quot;: 1,
                        &quot;name&quot;: &quot;English&quot;,
                        &quot;slug&quot;: &quot;english&quot;,
                        &quot;pivot&quot;: {
                            &quot;product_id&quot;: 1,
                            &quot;language_id&quot;: 1,
                            &quot;created_at&quot;: &quot;2026-01-15T15:16:11.000000Z&quot;,
                            &quot;updated_at&quot;: &quot;2026-01-15T15:16:11.000000Z&quot;
                        }
                    }
                ],
                &quot;works_on&quot;: [
                    {
                        &quot;id&quot;: 1,
                        &quot;name&quot;: &quot;Windows&quot;,
                        &quot;slug&quot;: &quot;windows&quot;,
                        &quot;pivot&quot;: {
                            &quot;product_id&quot;: 1,
                            &quot;works_on_id&quot;: 1,
                            &quot;created_at&quot;: &quot;2026-01-15T15:16:11.000000Z&quot;,
                            &quot;updated_at&quot;: &quot;2026-01-15T15:16:11.000000Z&quot;
                        }
                    }
                ],
                &quot;lowest_price&quot;: null
            },
            {
                &quot;id&quot;: 2,
                &quot;title&quot;: &quot;Microsoft Office 2021 Professional Plus&quot;,
                &quot;slug&quot;: &quot;microsoft-office-2021-professional-plus&quot;,
                &quot;cover_image&quot;: null,
                &quot;developer&quot;: {
                    &quot;id&quot;: 1,
                    &quot;name&quot;: &quot;Ubisoft&quot;,
                    &quot;slug&quot;: &quot;ubisoft&quot;
                },
                &quot;publisher&quot;: {
                    &quot;id&quot;: 1,
                    &quot;name&quot;: &quot;Ubisoft&quot;,
                    &quot;slug&quot;: &quot;ubisoft&quot;
                },
                &quot;categories&quot;: [
                    {
                        &quot;id&quot;: 1,
                        &quot;name&quot;: &quot;Operating Systems&quot;,
                        &quot;slug&quot;: &quot;operating-systems&quot;,
                        &quot;pivot&quot;: {
                            &quot;product_id&quot;: 2,
                            &quot;category_id&quot;: 1,
                            &quot;created_at&quot;: &quot;2026-01-15T15:16:11.000000Z&quot;,
                            &quot;updated_at&quot;: &quot;2026-01-15T15:16:11.000000Z&quot;
                        }
                    }
                ],
                &quot;platforms&quot;: [
                    {
                        &quot;id&quot;: 1,
                        &quot;name&quot;: &quot;Steam&quot;,
                        &quot;slug&quot;: &quot;steam&quot;,
                        &quot;pivot&quot;: {
                            &quot;product_id&quot;: 2,
                            &quot;platform_id&quot;: 1,
                            &quot;created_at&quot;: &quot;2026-01-15T15:16:11.000000Z&quot;,
                            &quot;updated_at&quot;: &quot;2026-01-15T15:16:11.000000Z&quot;
                        }
                    }
                ],
                &quot;types&quot;: [
                    {
                        &quot;id&quot;: 1,
                        &quot;name&quot;: &quot;Game&quot;,
                        &quot;slug&quot;: &quot;game&quot;,
                        &quot;pivot&quot;: {
                            &quot;product_id&quot;: 2,
                            &quot;type_id&quot;: 1,
                            &quot;created_at&quot;: &quot;2026-01-15T15:16:11.000000Z&quot;,
                            &quot;updated_at&quot;: &quot;2026-01-15T15:16:11.000000Z&quot;
                        }
                    }
                ],
                &quot;regions&quot;: [
                    {
                        &quot;id&quot;: 1,
                        &quot;name&quot;: &quot;Global&quot;,
                        &quot;slug&quot;: &quot;global&quot;,
                        &quot;pivot&quot;: {
                            &quot;product_id&quot;: 2,
                            &quot;region_id&quot;: 1,
                            &quot;created_at&quot;: &quot;2026-01-15T15:16:11.000000Z&quot;,
                            &quot;updated_at&quot;: &quot;2026-01-15T15:16:11.000000Z&quot;
                        }
                    }
                ],
                &quot;languages&quot;: [
                    {
                        &quot;id&quot;: 1,
                        &quot;name&quot;: &quot;English&quot;,
                        &quot;slug&quot;: &quot;english&quot;,
                        &quot;pivot&quot;: {
                            &quot;product_id&quot;: 2,
                            &quot;language_id&quot;: 1,
                            &quot;created_at&quot;: &quot;2026-01-15T15:16:11.000000Z&quot;,
                            &quot;updated_at&quot;: &quot;2026-01-15T15:16:11.000000Z&quot;
                        }
                    }
                ],
                &quot;works_on&quot;: [
                    {
                        &quot;id&quot;: 1,
                        &quot;name&quot;: &quot;Windows&quot;,
                        &quot;slug&quot;: &quot;windows&quot;,
                        &quot;pivot&quot;: {
                            &quot;product_id&quot;: 2,
                            &quot;works_on_id&quot;: 1,
                            &quot;created_at&quot;: &quot;2026-01-15T15:16:11.000000Z&quot;,
                            &quot;updated_at&quot;: &quot;2026-01-15T15:16:11.000000Z&quot;
                        }
                    }
                ],
                &quot;lowest_price&quot;: null
            },
            {
                &quot;id&quot;: 3,
                &quot;title&quot;: &quot;Grand Theft Auto V (GTA 5) PC - Rockstar Key&quot;,
                &quot;slug&quot;: &quot;grand-theft-auto-v-gta-5-pc-rockstar-key&quot;,
                &quot;cover_image&quot;: null,
                &quot;developer&quot;: {
                    &quot;id&quot;: 2,
                    &quot;name&quot;: &quot;Rockstar Games&quot;,
                    &quot;slug&quot;: &quot;rockstar-games&quot;
                },
                &quot;publisher&quot;: {
                    &quot;id&quot;: 3,
                    &quot;name&quot;: &quot;Activision&quot;,
                    &quot;slug&quot;: &quot;activision&quot;
                },
                &quot;categories&quot;: [
                    {
                        &quot;id&quot;: 1,
                        &quot;name&quot;: &quot;Operating Systems&quot;,
                        &quot;slug&quot;: &quot;operating-systems&quot;,
                        &quot;pivot&quot;: {
                            &quot;product_id&quot;: 3,
                            &quot;category_id&quot;: 1,
                            &quot;created_at&quot;: &quot;2026-01-15T15:16:11.000000Z&quot;,
                            &quot;updated_at&quot;: &quot;2026-01-15T15:16:11.000000Z&quot;
                        }
                    }
                ],
                &quot;platforms&quot;: [
                    {
                        &quot;id&quot;: 1,
                        &quot;name&quot;: &quot;Steam&quot;,
                        &quot;slug&quot;: &quot;steam&quot;,
                        &quot;pivot&quot;: {
                            &quot;product_id&quot;: 3,
                            &quot;platform_id&quot;: 1,
                            &quot;created_at&quot;: &quot;2026-01-15T15:16:11.000000Z&quot;,
                            &quot;updated_at&quot;: &quot;2026-01-15T15:16:11.000000Z&quot;
                        }
                    }
                ],
                &quot;types&quot;: [
                    {
                        &quot;id&quot;: 1,
                        &quot;name&quot;: &quot;Game&quot;,
                        &quot;slug&quot;: &quot;game&quot;,
                        &quot;pivot&quot;: {
                            &quot;product_id&quot;: 3,
                            &quot;type_id&quot;: 1,
                            &quot;created_at&quot;: &quot;2026-01-15T15:16:11.000000Z&quot;,
                            &quot;updated_at&quot;: &quot;2026-01-15T15:16:11.000000Z&quot;
                        }
                    }
                ],
                &quot;regions&quot;: [
                    {
                        &quot;id&quot;: 1,
                        &quot;name&quot;: &quot;Global&quot;,
                        &quot;slug&quot;: &quot;global&quot;,
                        &quot;pivot&quot;: {
                            &quot;product_id&quot;: 3,
                            &quot;region_id&quot;: 1,
                            &quot;created_at&quot;: &quot;2026-01-15T15:16:11.000000Z&quot;,
                            &quot;updated_at&quot;: &quot;2026-01-15T15:16:11.000000Z&quot;
                        }
                    }
                ],
                &quot;languages&quot;: [
                    {
                        &quot;id&quot;: 1,
                        &quot;name&quot;: &quot;English&quot;,
                        &quot;slug&quot;: &quot;english&quot;,
                        &quot;pivot&quot;: {
                            &quot;product_id&quot;: 3,
                            &quot;language_id&quot;: 1,
                            &quot;created_at&quot;: &quot;2026-01-15T15:16:11.000000Z&quot;,
                            &quot;updated_at&quot;: &quot;2026-01-15T15:16:11.000000Z&quot;
                        }
                    }
                ],
                &quot;works_on&quot;: [
                    {
                        &quot;id&quot;: 1,
                        &quot;name&quot;: &quot;Windows&quot;,
                        &quot;slug&quot;: &quot;windows&quot;,
                        &quot;pivot&quot;: {
                            &quot;product_id&quot;: 3,
                            &quot;works_on_id&quot;: 1,
                            &quot;created_at&quot;: &quot;2026-01-15T15:16:11.000000Z&quot;,
                            &quot;updated_at&quot;: &quot;2026-01-15T15:16:11.000000Z&quot;
                        }
                    }
                ],
                &quot;lowest_price&quot;: null
            },
            {
                &quot;id&quot;: 4,
                &quot;title&quot;: &quot;Minecraft Java Edition PC&quot;,
                &quot;slug&quot;: &quot;minecraft-java-edition-pc&quot;,
                &quot;cover_image&quot;: null,
                &quot;developer&quot;: {
                    &quot;id&quot;: 3,
                    &quot;name&quot;: &quot;Mojang Studios&quot;,
                    &quot;slug&quot;: &quot;mojang-studios&quot;
                },
                &quot;publisher&quot;: {
                    &quot;id&quot;: 3,
                    &quot;name&quot;: &quot;Activision&quot;,
                    &quot;slug&quot;: &quot;activision&quot;
                },
                &quot;categories&quot;: [
                    {
                        &quot;id&quot;: 1,
                        &quot;name&quot;: &quot;Operating Systems&quot;,
                        &quot;slug&quot;: &quot;operating-systems&quot;,
                        &quot;pivot&quot;: {
                            &quot;product_id&quot;: 4,
                            &quot;category_id&quot;: 1,
                            &quot;created_at&quot;: &quot;2026-01-15T15:16:11.000000Z&quot;,
                            &quot;updated_at&quot;: &quot;2026-01-15T15:16:11.000000Z&quot;
                        }
                    }
                ],
                &quot;platforms&quot;: [
                    {
                        &quot;id&quot;: 1,
                        &quot;name&quot;: &quot;Steam&quot;,
                        &quot;slug&quot;: &quot;steam&quot;,
                        &quot;pivot&quot;: {
                            &quot;product_id&quot;: 4,
                            &quot;platform_id&quot;: 1,
                            &quot;created_at&quot;: &quot;2026-01-15T15:16:11.000000Z&quot;,
                            &quot;updated_at&quot;: &quot;2026-01-15T15:16:11.000000Z&quot;
                        }
                    }
                ],
                &quot;types&quot;: [
                    {
                        &quot;id&quot;: 1,
                        &quot;name&quot;: &quot;Game&quot;,
                        &quot;slug&quot;: &quot;game&quot;,
                        &quot;pivot&quot;: {
                            &quot;product_id&quot;: 4,
                            &quot;type_id&quot;: 1,
                            &quot;created_at&quot;: &quot;2026-01-15T15:16:11.000000Z&quot;,
                            &quot;updated_at&quot;: &quot;2026-01-15T15:16:11.000000Z&quot;
                        }
                    }
                ],
                &quot;regions&quot;: [
                    {
                        &quot;id&quot;: 1,
                        &quot;name&quot;: &quot;Global&quot;,
                        &quot;slug&quot;: &quot;global&quot;,
                        &quot;pivot&quot;: {
                            &quot;product_id&quot;: 4,
                            &quot;region_id&quot;: 1,
                            &quot;created_at&quot;: &quot;2026-01-15T15:16:11.000000Z&quot;,
                            &quot;updated_at&quot;: &quot;2026-01-15T15:16:11.000000Z&quot;
                        }
                    }
                ],
                &quot;languages&quot;: [
                    {
                        &quot;id&quot;: 1,
                        &quot;name&quot;: &quot;English&quot;,
                        &quot;slug&quot;: &quot;english&quot;,
                        &quot;pivot&quot;: {
                            &quot;product_id&quot;: 4,
                            &quot;language_id&quot;: 1,
                            &quot;created_at&quot;: &quot;2026-01-15T15:16:11.000000Z&quot;,
                            &quot;updated_at&quot;: &quot;2026-01-15T15:16:11.000000Z&quot;
                        }
                    }
                ],
                &quot;works_on&quot;: [
                    {
                        &quot;id&quot;: 1,
                        &quot;name&quot;: &quot;Windows&quot;,
                        &quot;slug&quot;: &quot;windows&quot;,
                        &quot;pivot&quot;: {
                            &quot;product_id&quot;: 4,
                            &quot;works_on_id&quot;: 1,
                            &quot;created_at&quot;: &quot;2026-01-15T15:16:11.000000Z&quot;,
                            &quot;updated_at&quot;: &quot;2026-01-15T15:16:11.000000Z&quot;
                        }
                    }
                ],
                &quot;lowest_price&quot;: null
            },
            {
                &quot;id&quot;: 5,
                &quot;title&quot;: &quot;FIFA 24 (EA Sports) - Origin Key&quot;,
                &quot;slug&quot;: &quot;fifa-24-ea-sports-origin-key&quot;,
                &quot;cover_image&quot;: null,
                &quot;developer&quot;: {
                    &quot;id&quot;: 4,
                    &quot;name&quot;: &quot;CD Projekt Red&quot;,
                    &quot;slug&quot;: &quot;cd-projekt-red&quot;
                },
                &quot;publisher&quot;: {
                    &quot;id&quot;: 2,
                    &quot;name&quot;: &quot;Electronic Arts (EA)&quot;,
                    &quot;slug&quot;: &quot;electronic-arts-ea&quot;
                },
                &quot;categories&quot;: [
                    {
                        &quot;id&quot;: 1,
                        &quot;name&quot;: &quot;Operating Systems&quot;,
                        &quot;slug&quot;: &quot;operating-systems&quot;,
                        &quot;pivot&quot;: {
                            &quot;product_id&quot;: 5,
                            &quot;category_id&quot;: 1,
                            &quot;created_at&quot;: &quot;2026-01-15T15:16:11.000000Z&quot;,
                            &quot;updated_at&quot;: &quot;2026-01-15T15:16:11.000000Z&quot;
                        }
                    }
                ],
                &quot;platforms&quot;: [
                    {
                        &quot;id&quot;: 1,
                        &quot;name&quot;: &quot;Steam&quot;,
                        &quot;slug&quot;: &quot;steam&quot;,
                        &quot;pivot&quot;: {
                            &quot;product_id&quot;: 5,
                            &quot;platform_id&quot;: 1,
                            &quot;created_at&quot;: &quot;2026-01-15T15:16:11.000000Z&quot;,
                            &quot;updated_at&quot;: &quot;2026-01-15T15:16:11.000000Z&quot;
                        }
                    }
                ],
                &quot;types&quot;: [
                    {
                        &quot;id&quot;: 1,
                        &quot;name&quot;: &quot;Game&quot;,
                        &quot;slug&quot;: &quot;game&quot;,
                        &quot;pivot&quot;: {
                            &quot;product_id&quot;: 5,
                            &quot;type_id&quot;: 1,
                            &quot;created_at&quot;: &quot;2026-01-15T15:16:11.000000Z&quot;,
                            &quot;updated_at&quot;: &quot;2026-01-15T15:16:11.000000Z&quot;
                        }
                    }
                ],
                &quot;regions&quot;: [
                    {
                        &quot;id&quot;: 1,
                        &quot;name&quot;: &quot;Global&quot;,
                        &quot;slug&quot;: &quot;global&quot;,
                        &quot;pivot&quot;: {
                            &quot;product_id&quot;: 5,
                            &quot;region_id&quot;: 1,
                            &quot;created_at&quot;: &quot;2026-01-15T15:16:11.000000Z&quot;,
                            &quot;updated_at&quot;: &quot;2026-01-15T15:16:11.000000Z&quot;
                        }
                    }
                ],
                &quot;languages&quot;: [
                    {
                        &quot;id&quot;: 1,
                        &quot;name&quot;: &quot;English&quot;,
                        &quot;slug&quot;: &quot;english&quot;,
                        &quot;pivot&quot;: {
                            &quot;product_id&quot;: 5,
                            &quot;language_id&quot;: 1,
                            &quot;created_at&quot;: &quot;2026-01-15T15:16:11.000000Z&quot;,
                            &quot;updated_at&quot;: &quot;2026-01-15T15:16:11.000000Z&quot;
                        }
                    }
                ],
                &quot;works_on&quot;: [
                    {
                        &quot;id&quot;: 1,
                        &quot;name&quot;: &quot;Windows&quot;,
                        &quot;slug&quot;: &quot;windows&quot;,
                        &quot;pivot&quot;: {
                            &quot;product_id&quot;: 5,
                            &quot;works_on_id&quot;: 1,
                            &quot;created_at&quot;: &quot;2026-01-15T15:16:11.000000Z&quot;,
                            &quot;updated_at&quot;: &quot;2026-01-15T15:16:11.000000Z&quot;
                        }
                    }
                ],
                &quot;lowest_price&quot;: null
            }
        ],
        &quot;currency&quot;: {
            &quot;id&quot;: 1,
            &quot;code&quot;: &quot;USD&quot;,
            &quot;name&quot;: &quot;US Dollar&quot;,
            &quot;symbol&quot;: &quot;$&quot;,
            &quot;is_active&quot;: true,
            &quot;is_default&quot;: true,
            &quot;rate&quot;: &quot;1.00000000&quot;,
            &quot;fetched_at&quot;: &quot;2026-01-15 15:16:11&quot;,
            &quot;created_at&quot;: &quot;2026-01-15T15:16:11.000000Z&quot;,
            &quot;updated_at&quot;: &quot;2026-01-15T15:16:11.000000Z&quot;
        },
        &quot;pagination&quot;: {
            &quot;total&quot;: 5,
            &quot;per_page&quot;: 12,
            &quot;current_page&quot;: 1,
            &quot;last_page&quot;: 1
        }
    }
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-v1-products" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-v1-products"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-v1-products"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-products" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-v1-products">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-products" data-method="GET"
      data-path="api/v1/products"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-products', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-v1-products"
                    onclick="tryItOut('GETapi-v1-products');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-v1-products"
                    onclick="cancelTryOut('GETapi-v1-products');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-v1-products"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/v1/products</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-v1-products"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-v1-products"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        </form>

                    <h2 id="endpoints-GETapi-v1-products--id-">Single product detail</h2>

<p>
</p>



<span id="example-requests-GETapi-v1-products--id-">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://localhost/api/v1/products/1" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost/api/v1/products/1"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-products--id-">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <details class="annotation">
            <summary style="cursor: pointer;">
                <small onclick="textContent = parentElement.parentElement.open ? 'Show headers' : 'Hide headers'">Show headers</small>
            </summary>
            <pre><code class="language-http">cache-control: no-cache, private
content-type: application/json
access-control-allow-origin: *
 </code></pre></details>         <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;status&quot;: &quot;success&quot;,
    &quot;message&quot;: &quot;Product details fetched successfully&quot;,
    &quot;data&quot;: {
        &quot;id&quot;: 1,
        &quot;title&quot;: &quot;Windows 11 Home OEM Key&quot;,
        &quot;slug&quot;: &quot;windows-11-home-oem-key&quot;,
        &quot;sku&quot;: &quot;WIN11-HOME-OEM&quot;,
        &quot;short_description&quot;: null,
        &quot;description&quot;: &quot;Genuine Microsoft Windows 11 Home OEM license key.&quot;,
        &quot;cover_image&quot;: null,
        &quot;gallery&quot;: [],
        &quot;developer&quot;: {
            &quot;id&quot;: 1,
            &quot;name&quot;: &quot;Ubisoft&quot;,
            &quot;slug&quot;: &quot;ubisoft&quot;
        },
        &quot;publisher&quot;: {
            &quot;id&quot;: 1,
            &quot;name&quot;: &quot;Ubisoft&quot;,
            &quot;slug&quot;: &quot;ubisoft&quot;
        },
        &quot;categories&quot;: [
            {
                &quot;id&quot;: 1,
                &quot;name&quot;: &quot;Operating Systems&quot;,
                &quot;slug&quot;: &quot;operating-systems&quot;,
                &quot;pivot&quot;: {
                    &quot;product_id&quot;: 1,
                    &quot;category_id&quot;: 1,
                    &quot;created_at&quot;: &quot;2026-01-15T15:16:11.000000Z&quot;,
                    &quot;updated_at&quot;: &quot;2026-01-15T15:16:11.000000Z&quot;
                }
            }
        ],
        &quot;platforms&quot;: [
            {
                &quot;id&quot;: 1,
                &quot;name&quot;: &quot;Steam&quot;,
                &quot;slug&quot;: &quot;steam&quot;,
                &quot;pivot&quot;: {
                    &quot;product_id&quot;: 1,
                    &quot;platform_id&quot;: 1,
                    &quot;created_at&quot;: &quot;2026-01-15T15:16:11.000000Z&quot;,
                    &quot;updated_at&quot;: &quot;2026-01-15T15:16:11.000000Z&quot;
                }
            }
        ],
        &quot;types&quot;: [
            {
                &quot;id&quot;: 1,
                &quot;name&quot;: &quot;Game&quot;,
                &quot;slug&quot;: &quot;game&quot;,
                &quot;commission&quot;: &quot;10.00&quot;,
                &quot;pivot&quot;: {
                    &quot;product_id&quot;: 1,
                    &quot;type_id&quot;: 1,
                    &quot;created_at&quot;: &quot;2026-01-15T15:16:11.000000Z&quot;,
                    &quot;updated_at&quot;: &quot;2026-01-15T15:16:11.000000Z&quot;
                }
            }
        ],
        &quot;regions&quot;: [
            {
                &quot;id&quot;: 1,
                &quot;name&quot;: &quot;Global&quot;,
                &quot;slug&quot;: &quot;global&quot;,
                &quot;pivot&quot;: {
                    &quot;product_id&quot;: 1,
                    &quot;region_id&quot;: 1,
                    &quot;created_at&quot;: &quot;2026-01-15T15:16:11.000000Z&quot;,
                    &quot;updated_at&quot;: &quot;2026-01-15T15:16:11.000000Z&quot;
                }
            }
        ],
        &quot;languages&quot;: [
            {
                &quot;id&quot;: 1,
                &quot;name&quot;: &quot;English&quot;,
                &quot;slug&quot;: &quot;english&quot;,
                &quot;pivot&quot;: {
                    &quot;product_id&quot;: 1,
                    &quot;language_id&quot;: 1,
                    &quot;created_at&quot;: &quot;2026-01-15T15:16:11.000000Z&quot;,
                    &quot;updated_at&quot;: &quot;2026-01-15T15:16:11.000000Z&quot;
                }
            }
        ],
        &quot;works_on&quot;: [
            {
                &quot;id&quot;: 1,
                &quot;name&quot;: &quot;Windows&quot;,
                &quot;slug&quot;: &quot;windows&quot;,
                &quot;pivot&quot;: {
                    &quot;product_id&quot;: 1,
                    &quot;works_on_id&quot;: 1,
                    &quot;created_at&quot;: &quot;2026-01-15T15:16:11.000000Z&quot;,
                    &quot;updated_at&quot;: &quot;2026-01-15T15:16:11.000000Z&quot;
                }
            }
        ],
        &quot;system_requirements&quot;: {
            &quot;minimum&quot;: [
                {
                    &quot;key&quot;: &quot;Processor&quot;,
                    &quot;value&quot;: &quot;Intel i3&quot;
                },
                {
                    &quot;key&quot;: &quot;RAM&quot;,
                    &quot;value&quot;: &quot;4 GB&quot;
                }
            ],
            &quot;recommended&quot;: [
                {
                    &quot;key&quot;: &quot;Processor&quot;,
                    &quot;value&quot;: &quot;Intel i5&quot;
                },
                {
                    &quot;key&quot;: &quot;RAM&quot;,
                    &quot;value&quot;: &quot;8 GB&quot;
                }
            ]
        },
        &quot;offers&quot;: [],
        &quot;promoted_offers&quot;: [],
        &quot;currencies&quot;: [
            {
                &quot;id&quot;: 1,
                &quot;code&quot;: &quot;USD&quot;,
                &quot;name&quot;: &quot;US Dollar&quot;,
                &quot;symbol&quot;: &quot;$&quot;,
                &quot;is_active&quot;: true,
                &quot;is_default&quot;: true,
                &quot;rate&quot;: &quot;1.00000000&quot;,
                &quot;fetched_at&quot;: &quot;2026-01-15 15:16:11&quot;,
                &quot;created_at&quot;: &quot;2026-01-15T15:16:11.000000Z&quot;,
                &quot;updated_at&quot;: &quot;2026-01-15T15:16:11.000000Z&quot;
            },
            {
                &quot;id&quot;: 2,
                &quot;code&quot;: &quot;EUR&quot;,
                &quot;name&quot;: &quot;Euro&quot;,
                &quot;symbol&quot;: &quot;&euro;&quot;,
                &quot;is_active&quot;: true,
                &quot;is_default&quot;: false,
                &quot;rate&quot;: &quot;0.92000000&quot;,
                &quot;fetched_at&quot;: &quot;2026-01-15 15:16:11&quot;,
                &quot;created_at&quot;: &quot;2026-01-15T15:16:11.000000Z&quot;,
                &quot;updated_at&quot;: &quot;2026-01-15T15:16:11.000000Z&quot;
            },
            {
                &quot;id&quot;: 3,
                &quot;code&quot;: &quot;GBP&quot;,
                &quot;name&quot;: &quot;British Pound&quot;,
                &quot;symbol&quot;: &quot;&pound;&quot;,
                &quot;is_active&quot;: true,
                &quot;is_default&quot;: false,
                &quot;rate&quot;: &quot;0.79000000&quot;,
                &quot;fetched_at&quot;: &quot;2026-01-15 15:16:11&quot;,
                &quot;created_at&quot;: &quot;2026-01-15T15:16:11.000000Z&quot;,
                &quot;updated_at&quot;: &quot;2026-01-15T15:16:11.000000Z&quot;
            },
            {
                &quot;id&quot;: 4,
                &quot;code&quot;: &quot;BDT&quot;,
                &quot;name&quot;: &quot;Bangladeshi Taka&quot;,
                &quot;symbol&quot;: &quot;৳&quot;,
                &quot;is_active&quot;: true,
                &quot;is_default&quot;: false,
                &quot;rate&quot;: &quot;110.25000000&quot;,
                &quot;fetched_at&quot;: &quot;2026-01-15 15:16:11&quot;,
                &quot;created_at&quot;: &quot;2026-01-15T15:16:11.000000Z&quot;,
                &quot;updated_at&quot;: &quot;2026-01-15T15:16:11.000000Z&quot;
            },
            {
                &quot;id&quot;: 5,
                &quot;code&quot;: &quot;JPY&quot;,
                &quot;name&quot;: &quot;Japanese Yen&quot;,
                &quot;symbol&quot;: &quot;&yen;&quot;,
                &quot;is_active&quot;: true,
                &quot;is_default&quot;: false,
                &quot;rate&quot;: &quot;148.35000000&quot;,
                &quot;fetched_at&quot;: &quot;2026-01-15 15:16:11&quot;,
                &quot;created_at&quot;: &quot;2026-01-15T15:16:11.000000Z&quot;,
                &quot;updated_at&quot;: &quot;2026-01-15T15:16:11.000000Z&quot;
            }
        ],
        &quot;meta&quot;: {
            &quot;title&quot;: &quot;Windows 11 Home OEM Key&quot;,
            &quot;description&quot;: &quot;Genuine Microsoft Windows 11 Home OEM license key.&quot;,
            &quot;keywords&quot;: &quot;windows 11 home oem key&quot;,
            &quot;is_featured&quot;: true,
            &quot;sort_order&quot;: 0,
            &quot;delivery_type&quot;: &quot;instant&quot;
        }
    }
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-v1-products--id-" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-v1-products--id-"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-v1-products--id-"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-products--id-" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-v1-products--id-">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-products--id-" data-method="GET"
      data-path="api/v1/products/{id}"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-products--id-', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-v1-products--id-"
                    onclick="tryItOut('GETapi-v1-products--id-');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-v1-products--id-"
                    onclick="cancelTryOut('GETapi-v1-products--id-');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-v1-products--id-"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/v1/products/{id}</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-v1-products--id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-v1-products--id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="id"                data-endpoint="GETapi-v1-products--id-"
               value="1"
               data-component="url">
    <br>
<p>The ID of the product. Example: <code>1</code></p>
            </div>
                    </form>

                    <h2 id="endpoints-GETapi-v1-orders">List all buyer orders (paginated)</h2>

<p>
</p>



<span id="example-requests-GETapi-v1-orders">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://localhost/api/v1/orders" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost/api/v1/orders"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-orders">
            <blockquote>
            <p>Example response (401):</p>
        </blockquote>
                <details class="annotation">
            <summary style="cursor: pointer;">
                <small onclick="textContent = parentElement.parentElement.open ? 'Show headers' : 'Hide headers'">Show headers</small>
            </summary>
            <pre><code class="language-http">cache-control: no-cache, private
content-type: application/json
access-control-allow-origin: *
 </code></pre></details>         <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;status&quot;: &quot;error&quot;,
    &quot;message&quot;: &quot;Unauthenticated&quot;
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-v1-orders" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-v1-orders"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-v1-orders"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-orders" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-v1-orders">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-orders" data-method="GET"
      data-path="api/v1/orders"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-orders', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-v1-orders"
                    onclick="tryItOut('GETapi-v1-orders');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-v1-orders"
                    onclick="cancelTryOut('GETapi-v1-orders');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-v1-orders"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/v1/orders</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-v1-orders"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-v1-orders"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        </form>

                    <h2 id="endpoints-GETapi-v1-orders--id-">Show a single order with all details</h2>

<p>
</p>



<span id="example-requests-GETapi-v1-orders--id-">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://localhost/api/v1/orders/consequatur" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost/api/v1/orders/consequatur"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-GETapi-v1-orders--id-">
            <blockquote>
            <p>Example response (401):</p>
        </blockquote>
                <details class="annotation">
            <summary style="cursor: pointer;">
                <small onclick="textContent = parentElement.parentElement.open ? 'Show headers' : 'Hide headers'">Show headers</small>
            </summary>
            <pre><code class="language-http">cache-control: no-cache, private
content-type: application/json
access-control-allow-origin: *
 </code></pre></details>         <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;status&quot;: &quot;error&quot;,
    &quot;message&quot;: &quot;Unauthenticated&quot;
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-v1-orders--id-" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-v1-orders--id-"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-v1-orders--id-"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-v1-orders--id-" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-v1-orders--id-">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-v1-orders--id-" data-method="GET"
      data-path="api/v1/orders/{id}"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-v1-orders--id-', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-v1-orders--id-"
                    onclick="tryItOut('GETapi-v1-orders--id-');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-v1-orders--id-"
                    onclick="cancelTryOut('GETapi-v1-orders--id-');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-v1-orders--id-"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/v1/orders/{id}</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-v1-orders--id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-v1-orders--id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="id"                data-endpoint="GETapi-v1-orders--id-"
               value="consequatur"
               data-component="url">
    <br>
<p>The ID of the order. Example: <code>consequatur</code></p>
            </div>
                    </form>

                    <h2 id="endpoints-POSTapi-v1-orders">Create a new order</h2>

<p>
</p>



<span id="example-requests-POSTapi-v1-orders">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "http://localhost/api/v1/orders" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"currency\": \"vmq\",
    \"items\": [
        {
            \"product_id\": \"consequatur\",
            \"quantity\": 45
        }
    ],
    \"note\": \"consequatur\",
    \"addresses\": [
        {
            \"type\": \"billing\",
            \"full_name\": \"qeopfuudtdsufvyvddqam\",
            \"email\": \"evert28@example.com\",
            \"phone\": \"qcoynlazghdtqtqxbajwb\",
            \"address_line1\": \"pilpmufinllwloauydlsm\",
            \"city\": \"sjuryvojcybzvrbyickzn\",
            \"country\": \"ky\"
        }
    ]
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost/api/v1/orders"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "currency": "vmq",
    "items": [
        {
            "product_id": "consequatur",
            "quantity": 45
        }
    ],
    "note": "consequatur",
    "addresses": [
        {
            "type": "billing",
            "full_name": "qeopfuudtdsufvyvddqam",
            "email": "evert28@example.com",
            "phone": "qcoynlazghdtqtqxbajwb",
            "address_line1": "pilpmufinllwloauydlsm",
            "city": "sjuryvojcybzvrbyickzn",
            "country": "ky"
        }
    ]
};

fetch(url, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-v1-orders">
</span>
<span id="execution-results-POSTapi-v1-orders" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-v1-orders"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-v1-orders"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-v1-orders" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-v1-orders">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-v1-orders" data-method="POST"
      data-path="api/v1/orders"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-v1-orders', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-v1-orders"
                    onclick="tryItOut('POSTapi-v1-orders');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-v1-orders"
                    onclick="cancelTryOut('POSTapi-v1-orders');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-v1-orders"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/v1/orders</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-v1-orders"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-v1-orders"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>currency</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="currency"                data-endpoint="POSTapi-v1-orders"
               value="vmq"
               data-component="body">
    <br>
<p>Must be 3 characters. Example: <code>vmq</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
        <details>
            <summary style="padding-bottom: 10px;">
                <b style="line-height: 2;"><code>items</code></b>&nbsp;&nbsp;
<small>object[]</small>&nbsp;
 &nbsp;
 &nbsp;
<br>
<p>Must have at least 1 items.</p>
            </summary>
                                                <div style="margin-left: 14px; clear: unset;">
                        <b style="line-height: 2;"><code>product_id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="items.0.product_id"                data-endpoint="POSTapi-v1-orders"
               value="consequatur"
               data-component="body">
    <br>
<p>The <code>id</code> of an existing record in the products table. Example: <code>consequatur</code></p>
                    </div>
                                                                <div style="margin-left: 14px; clear: unset;">
                        <b style="line-height: 2;"><code>offer_id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="items.0.offer_id"                data-endpoint="POSTapi-v1-orders"
               value=""
               data-component="body">
    <br>
<p>The <code>id</code> of an existing record in the seller_offers table.</p>
                    </div>
                                                                <div style="margin-left: 14px; clear: unset;">
                        <b style="line-height: 2;"><code>quantity</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="items.0.quantity"                data-endpoint="POSTapi-v1-orders"
               value="45"
               data-component="body">
    <br>
<p>Must be at least 1. Example: <code>45</code></p>
                    </div>
                                    </details>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
        <details>
            <summary style="padding-bottom: 10px;">
                <b style="line-height: 2;"><code>addresses</code></b>&nbsp;&nbsp;
<small>object[]</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
<br>
<p>optional.</p>
            </summary>
                                                <div style="margin-left: 14px; clear: unset;">
                        <b style="line-height: 2;"><code>type</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="addresses.0.type"                data-endpoint="POSTapi-v1-orders"
               value="billing"
               data-component="body">
    <br>
<p>This field is required when <code>addresses</code> is present. Example: <code>billing</code></p>
Must be one of:
<ul style="list-style-type: square;"><li><code>billing</code></li> <li><code>shipping</code></li></ul>
                    </div>
                                                                <div style="margin-left: 14px; clear: unset;">
                        <b style="line-height: 2;"><code>full_name</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="addresses.0.full_name"                data-endpoint="POSTapi-v1-orders"
               value="qeopfuudtdsufvyvddqam"
               data-component="body">
    <br>
<p>This field is required when <code>addresses</code> is present. Must not be greater than 255 characters. Example: <code>qeopfuudtdsufvyvddqam</code></p>
                    </div>
                                                                <div style="margin-left: 14px; clear: unset;">
                        <b style="line-height: 2;"><code>email</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="addresses.0.email"                data-endpoint="POSTapi-v1-orders"
               value="evert28@example.com"
               data-component="body">
    <br>
<p>Must be a valid email address. Example: <code>evert28@example.com</code></p>
                    </div>
                                                                <div style="margin-left: 14px; clear: unset;">
                        <b style="line-height: 2;"><code>phone</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="addresses.0.phone"                data-endpoint="POSTapi-v1-orders"
               value="qcoynlazghdtqtqxbajwb"
               data-component="body">
    <br>
<p>Must not be greater than 50 characters. Example: <code>qcoynlazghdtqtqxbajwb</code></p>
                    </div>
                                                                <div style="margin-left: 14px; clear: unset;">
                        <b style="line-height: 2;"><code>address_line1</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="addresses.0.address_line1"                data-endpoint="POSTapi-v1-orders"
               value="pilpmufinllwloauydlsm"
               data-component="body">
    <br>
<p>This field is required when <code>addresses</code> is present. Must not be greater than 255 characters. Example: <code>pilpmufinllwloauydlsm</code></p>
                    </div>
                                                                <div style="margin-left: 14px; clear: unset;">
                        <b style="line-height: 2;"><code>city</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="addresses.0.city"                data-endpoint="POSTapi-v1-orders"
               value="sjuryvojcybzvrbyickzn"
               data-component="body">
    <br>
<p>This field is required when <code>addresses</code> is present. Must not be greater than 100 characters. Example: <code>sjuryvojcybzvrbyickzn</code></p>
                    </div>
                                                                <div style="margin-left: 14px; clear: unset;">
                        <b style="line-height: 2;"><code>country</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="addresses.0.country"                data-endpoint="POSTapi-v1-orders"
               value="ky"
               data-component="body">
    <br>
<p>This field is required when <code>addresses</code> is present. Must be 2 characters. Example: <code>ky</code></p>
                    </div>
                                    </details>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>note</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="note"                data-endpoint="POSTapi-v1-orders"
               value="consequatur"
               data-component="body">
    <br>
<p>Example: <code>consequatur</code></p>
        </div>
        </form>

                    <h2 id="endpoints-PUTapi-v1-orders--id--status">Update order status (admin/seller action)</h2>

<p>
</p>



<span id="example-requests-PUTapi-v1-orders--id--status">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request PUT \
    "http://localhost/api/v1/orders/consequatur/status" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"status\": \"consequatur\"
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost/api/v1/orders/consequatur/status"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "status": "consequatur"
};

fetch(url, {
    method: "PUT",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-PUTapi-v1-orders--id--status">
</span>
<span id="execution-results-PUTapi-v1-orders--id--status" hidden>
    <blockquote>Received response<span
                id="execution-response-status-PUTapi-v1-orders--id--status"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-PUTapi-v1-orders--id--status"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-PUTapi-v1-orders--id--status" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-PUTapi-v1-orders--id--status">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-PUTapi-v1-orders--id--status" data-method="PUT"
      data-path="api/v1/orders/{id}/status"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('PUTapi-v1-orders--id--status', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-PUTapi-v1-orders--id--status"
                    onclick="tryItOut('PUTapi-v1-orders--id--status');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-PUTapi-v1-orders--id--status"
                    onclick="cancelTryOut('PUTapi-v1-orders--id--status');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-PUTapi-v1-orders--id--status"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-darkblue">PUT</small>
            <b><code>api/v1/orders/{id}/status</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="PUTapi-v1-orders--id--status"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="PUTapi-v1-orders--id--status"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="id"                data-endpoint="PUTapi-v1-orders--id--status"
               value="consequatur"
               data-component="url">
    <br>
<p>The ID of the order. Example: <code>consequatur</code></p>
            </div>
                            <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>status</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="status"                data-endpoint="PUTapi-v1-orders--id--status"
               value="consequatur"
               data-component="body">
    <br>
<p>Example: <code>consequatur</code></p>
        </div>
        </form>

                    <h2 id="endpoints-PUTapi-v1-orders--id--pay">Mark order as paid (add transaction + update invoice)</h2>

<p>
</p>



<span id="example-requests-PUTapi-v1-orders--id--pay">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request PUT \
    "http://localhost/api/v1/orders/consequatur/pay" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"gateway\": \"consequatur\",
    \"transaction_id\": \"consequatur\",
    \"amount\": 45
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost/api/v1/orders/consequatur/pay"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "gateway": "consequatur",
    "transaction_id": "consequatur",
    "amount": 45
};

fetch(url, {
    method: "PUT",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-PUTapi-v1-orders--id--pay">
</span>
<span id="execution-results-PUTapi-v1-orders--id--pay" hidden>
    <blockquote>Received response<span
                id="execution-response-status-PUTapi-v1-orders--id--pay"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-PUTapi-v1-orders--id--pay"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-PUTapi-v1-orders--id--pay" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-PUTapi-v1-orders--id--pay">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-PUTapi-v1-orders--id--pay" data-method="PUT"
      data-path="api/v1/orders/{id}/pay"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('PUTapi-v1-orders--id--pay', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-PUTapi-v1-orders--id--pay"
                    onclick="tryItOut('PUTapi-v1-orders--id--pay');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-PUTapi-v1-orders--id--pay"
                    onclick="cancelTryOut('PUTapi-v1-orders--id--pay');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-PUTapi-v1-orders--id--pay"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-darkblue">PUT</small>
            <b><code>api/v1/orders/{id}/pay</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="PUTapi-v1-orders--id--pay"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="PUTapi-v1-orders--id--pay"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="id"                data-endpoint="PUTapi-v1-orders--id--pay"
               value="consequatur"
               data-component="url">
    <br>
<p>The ID of the order. Example: <code>consequatur</code></p>
            </div>
                            <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>gateway</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="gateway"                data-endpoint="PUTapi-v1-orders--id--pay"
               value="consequatur"
               data-component="body">
    <br>
<p>Example: <code>consequatur</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>transaction_id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="transaction_id"                data-endpoint="PUTapi-v1-orders--id--pay"
               value="consequatur"
               data-component="body">
    <br>
<p>Example: <code>consequatur</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>amount</code></b>&nbsp;&nbsp;
<small>number</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="amount"                data-endpoint="PUTapi-v1-orders--id--pay"
               value="45"
               data-component="body">
    <br>
<p>Must be at least 0. Example: <code>45</code></p>
        </div>
        </form>

                    <h2 id="endpoints-POSTapi-v1-orders--id--refund">Refund an order</h2>

<p>
</p>



<span id="example-requests-POSTapi-v1-orders--id--refund">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "http://localhost/api/v1/orders/consequatur/refund" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"amount\": 73,
    \"gateway\": \"consequatur\",
    \"transaction_id\": \"consequatur\",
    \"note\": \"consequatur\"
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost/api/v1/orders/consequatur/refund"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "amount": 73,
    "gateway": "consequatur",
    "transaction_id": "consequatur",
    "note": "consequatur"
};

fetch(url, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-v1-orders--id--refund">
</span>
<span id="execution-results-POSTapi-v1-orders--id--refund" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-v1-orders--id--refund"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-v1-orders--id--refund"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-v1-orders--id--refund" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-v1-orders--id--refund">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-v1-orders--id--refund" data-method="POST"
      data-path="api/v1/orders/{id}/refund"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-v1-orders--id--refund', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-v1-orders--id--refund"
                    onclick="tryItOut('POSTapi-v1-orders--id--refund');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-v1-orders--id--refund"
                    onclick="cancelTryOut('POSTapi-v1-orders--id--refund');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-v1-orders--id--refund"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/v1/orders/{id}/refund</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-v1-orders--id--refund"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-v1-orders--id--refund"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="id"                data-endpoint="POSTapi-v1-orders--id--refund"
               value="consequatur"
               data-component="url">
    <br>
<p>The ID of the order. Example: <code>consequatur</code></p>
            </div>
                            <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>amount</code></b>&nbsp;&nbsp;
<small>number</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="amount"                data-endpoint="POSTapi-v1-orders--id--refund"
               value="73"
               data-component="body">
    <br>
<p>Must be at least 0. Example: <code>73</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>gateway</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="gateway"                data-endpoint="POSTapi-v1-orders--id--refund"
               value="consequatur"
               data-component="body">
    <br>
<p>Example: <code>consequatur</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>transaction_id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="transaction_id"                data-endpoint="POSTapi-v1-orders--id--refund"
               value="consequatur"
               data-component="body">
    <br>
<p>Example: <code>consequatur</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>note</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="note"                data-endpoint="POSTapi-v1-orders--id--refund"
               value="consequatur"
               data-component="body">
    <br>
<p>Example: <code>consequatur</code></p>
        </div>
        </form>

                    <h2 id="endpoints-POSTapi-v1-orders--id--notes">Add a note to an order</h2>

<p>
</p>



<span id="example-requests-POSTapi-v1-orders--id--notes">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "http://localhost/api/v1/orders/consequatur/notes" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"note\": \"consequatur\",
    \"is_private\": false
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://localhost/api/v1/orders/consequatur/notes"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "note": "consequatur",
    "is_private": false
};

fetch(url, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>

</span>

<span id="example-responses-POSTapi-v1-orders--id--notes">
</span>
<span id="execution-results-POSTapi-v1-orders--id--notes" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-v1-orders--id--notes"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-v1-orders--id--notes"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-v1-orders--id--notes" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-v1-orders--id--notes">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-v1-orders--id--notes" data-method="POST"
      data-path="api/v1/orders/{id}/notes"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-v1-orders--id--notes', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-v1-orders--id--notes"
                    onclick="tryItOut('POSTapi-v1-orders--id--notes');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-v1-orders--id--notes"
                    onclick="cancelTryOut('POSTapi-v1-orders--id--notes');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-v1-orders--id--notes"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/v1/orders/{id}/notes</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-v1-orders--id--notes"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-v1-orders--id--notes"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="id"                data-endpoint="POSTapi-v1-orders--id--notes"
               value="consequatur"
               data-component="url">
    <br>
<p>The ID of the order. Example: <code>consequatur</code></p>
            </div>
                            <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>note</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="note"                data-endpoint="POSTapi-v1-orders--id--notes"
               value="consequatur"
               data-component="body">
    <br>
<p>Example: <code>consequatur</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>is_private</code></b>&nbsp;&nbsp;
<small>boolean</small>&nbsp;
<i>optional</i> &nbsp;
 &nbsp;
                <label data-endpoint="POSTapi-v1-orders--id--notes" style="display: none">
            <input type="radio" name="is_private"
                   value="true"
                   data-endpoint="POSTapi-v1-orders--id--notes"
                   data-component="body"             >
            <code>true</code>
        </label>
        <label data-endpoint="POSTapi-v1-orders--id--notes" style="display: none">
            <input type="radio" name="is_private"
                   value="false"
                   data-endpoint="POSTapi-v1-orders--id--notes"
                   data-component="body"             >
            <code>false</code>
        </label>
    <br>
<p>Example: <code>false</code></p>
        </div>
        </form>

            

        
    </div>
    <div class="dark-box">
                    <div class="lang-selector">
                                                        <button type="button" class="lang-button" data-language-name="bash">bash</button>
                                                        <button type="button" class="lang-button" data-language-name="javascript">javascript</button>
                            </div>
            </div>
</div>
</body>
</html>
