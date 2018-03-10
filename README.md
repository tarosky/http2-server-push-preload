# HTTP/2 Server Push for WordPress

[![Build Status](https://travis-ci.org/tarosky/http2-server-push.svg?branch=master)](https://travis-ci.org/tarosky/http2-server-push)

This is a plugin which sends link headers to integrate HTTP/2 server push.

## Requires

* Nginx 1.13.9 or later
* WordPress 4.9 or later
* PHP 7 or later

## Configurating HTTP/2 Server Push for Nginx

You need following configuration for Nginx.

```
server {
    # Ensure that HTTP/2 is enabled for the server
    listen 443 ssl http2;


    ssl_certificate ssl/certificate.pem;
    ssl_certificate_key ssl/key.pem;

    root /var/www/html;

    # Intercept Link header and initiate requested Pushes
    location = /myapp {
        proxy_pass http://upstream;
        **http2_push_preload on;**
    }
}
```
