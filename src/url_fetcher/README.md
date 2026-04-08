UrlFetcher
==========

UrlFetcher is a PHP class providing methods to make HTTP requests.

- GET, POST, PUT, DELETE requests
- HTTPS with SSL peer verification
- HTTP Basic Authentication
- Automatic redirect following
- Proxy support
- GZIP content decoding
- Configurable timeouts

Installation
------------

    composer require atk14/url-fetcher

Basic usage
-----------

    $fetcher = new UrlFetcher("http://www.example.com/content.dat");

    if(!$fetcher->found()){
      echo $fetcher->getErrorMessage();
      exit(1);
    }

    $content = $fetcher->getContent(); // returns StringBufferTemporary

    $status_code    = $fetcher->getStatusCode();    // e.g. 200
    $status_message = $fetcher->getStatusMessage(); // e.g. "OK"
    $content_type   = $fetcher->getContentType();   // e.g. "application/pdf"
    $charset        = $fetcher->getContentCharset(); // e.g. "UTF-8"
    $content_length = $fetcher->getContentLength(); // e.g. "12345"
    $filename       = $fetcher->getFilename();      // e.g. "content.dat"

    $request_headers  = $fetcher->getRequestHeaders();
    $response_headers = $fetcher->getResponseHeaders();

HTTP Basic Authentication
-------------------------

Credentials can be passed directly in the URL:

    $fetcher = new UrlFetcher("https://username:password@www.example.com:444/private/content.dat");
    if($fetcher->found()){
      echo $fetcher->getContent();
    }

Or set explicitly:

    $fetcher = new UrlFetcher("https://www.example.com/private/content.dat");
    $fetcher->setAuthorization("username", "password");
    if($fetcher->found()){
      echo $fetcher->getContent();
    }

    $fetcher->resetAuthorization(); // clear credentials

POST request
------------

Array is sent as `application/x-www-form-urlencoded`:

    $fetcher = new UrlFetcher("https://www.example.com/api/articles/");
    if($fetcher->post(["title" => "Sample article", "body" => "Lorem Ipsum..."])){
      echo $fetcher->getContent();
    }

Raw string body with a custom content type:

    $fetcher = new UrlFetcher("https://www.example.com/api/articles/");
    $fetcher->post($json, ["content_type" => "application/json"]);

PUT and DELETE requests
-----------------------

    $fetcher = new UrlFetcher("https://www.example.com/api/articles/123/");
    $fetcher->put($json, ["content_type" => "application/json"]);

    $fetcher = new UrlFetcher("https://www.example.com/api/articles/123/");
    $fetcher->delete();

Custom headers
--------------

Headers passed to the constructor are sent with every request made by the instance:

    $fetcher = new UrlFetcher("https://www.example.com/", [
      "additional_headers" => ["X-App-Version: 1.2", "Accept: application/json"]
    ]);

Headers passed to `post()`, `put()`, `delete()` or `fetchContent()` are sent only with that single request:

    $fetcher->post($data, ["additional_headers" => ["X-Request-ID: abc123"]]);

Inspecting response headers
----------------------------

    // single header value (case-insensitive)
    $type = $fetcher->getHeaderValue("Content-Type"); // "text/html; charset=UTF-8"

    // all headers as an associative array
    $headers = $fetcher->getResponseHeaders(["as_hash" => true, "lowerize_keys" => true]);
    echo $headers["content-type"];

Redirections
------------

Redirects (301, 302, 303) are followed automatically. The default limit is 5. It can be changed in the constructor:

    $fetcher = new UrlFetcher("http://www.example.com/", ["max_redirections" => 10]);

    // disable redirections entirely
    $fetcher = new UrlFetcher("http://www.example.com/", ["max_redirections" => 0]);

After a redirect, `getUrl()` returns the final URL:

    $fetcher = new UrlFetcher("http://www.example.com/old-page/");
    $fetcher->getContent();
    echo $fetcher->getUrl(); // the final URL after redirection

SSL / HTTPS
-----------

SSL peer verification is enabled by default. It can be disabled for self-signed or expired certificates:

    $fetcher = new UrlFetcher("https://www.example.com/", [
      "verify_peer"      => false,
      "verify_peer_name" => false,
    ]);

The default can also be changed globally via a PHP constant before including the library:

    define("URL_FETCHER_VERIFY_PEER", false);

Proxy
-----

    $fetcher = new UrlFetcher("http://www.example.com/", [
      "proxy" => "tcp://127.0.0.1:8118"
    ]);

Timeouts
--------

Two separate timeouts can be configured:

- **socket_timeout** — connection timeout in seconds (default: 5.0)
- **read_timeout** — time to wait for data after connecting (default: 30.0)

    $fetcher = new UrlFetcher("http://www.example.com/", [
      "socket_timeout" => 3.0,
      "read_timeout"   => 10.0,
    ]);

They can also be changed at any time. Both methods return the previous value:

    $prev = $fetcher->setSocketTimeout(3.0);
    $prev = $fetcher->setReadTimeout(10.0);

User-Agent
----------

    $fetcher = new UrlFetcher("http://www.example.com/", [
      "user_agent" => "MyApp/2.0"
    ]);

UrlFetcherViaCommand
--------------------

`UrlFetcherViaCommand` is a subclass that sends the HTTP request to an external command via stdin and reads the response from stdout. This is useful for testing or simulating HTTP requests inside an application (e.g. ATK14's `scripts/simulate_http_request`).

    $fetcher = new UrlFetcherViaCommand("nc localhost 80", "http://localhost/content.dat");
    if($fetcher->found()){
      echo $fetcher->getContent();
    }

Licence
-------

UrlFetcher is free software distributed [under the terms of the MIT license](http://www.opensource.org/licenses/mit-license)
