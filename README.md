# HTTP/2 Server Push for WordPress

[![Build Status](https://travis-ci.org/tarosky/http2-server-push-preload.svg?branch=master)](https://travis-ci.org/tarosky/http2-server-push-preload)

This is a plugin which sends link headers for HTTP/2 server push.

## Requires

* Nginx 1.13.9 or later
* WordPress 4.9 or later
* PHP 7 or later

## Download

Please download `http2-server-push-preload.zip` of the latest version from following.

https://github.com/tarosky/http2-server-push-preload/releases

It has a custom updater from GitHub.

## Customizing

There is a filter hook for items to send as link header.

```
add_filter( 'http2_server_preload_items', function( $items ) {
  $new = array(
    'image' => array(
      '/wp-content/uploads/favicon.png'
    ),
  );

  return array_merge_recursive( $items, $new );
} );
```

In this example, the response header will be sent like following.

```
Link: ..., </wp-content/uploads/favicon.png>; rel=preload; as=image, ...
```

## Configurating HTTP/2 Server Push for Nginx

You need following configuration for Nginx.

```
server {
    # Ensure that HTTP/2 is enabled for the server
    listen 443 ssl http2;

    ...
    ...
    ...

    # Intercept Link header and initiate requested Pushes
    location = / {
        proxy_pass http://upstream;
        ...
        ...
        ...
        proxy_buffer_size   128k;
        proxy_buffers   4 256k;
        proxy_busy_buffers_size   256k;
        http2_push_preload on;
    }
}
```

## Verifying with a Command-Line Client (nghttp)

You can see the `PUSH_PROMISE` that were pushed by the server with the `nghttp` command like following.

```
$ nghttp -nv https://example.com/ | grep PUSH_PROMISE
[  0.217] recv PUSH_PROMISE frame <length=76, flags=0x04, stream_id=13>
[  0.217] recv PUSH_PROMISE frame <length=84, flags=0x04, stream_id=13>
[  0.217] recv PUSH_PROMISE frame <length=75, flags=0x04, stream_id=13>
[  0.217] recv PUSH_PROMISE frame <length=103, flags=0x04, stream_id=13>
[  0.217] recv PUSH_PROMISE frame <length=108, flags=0x04, stream_id=13>
[  0.217] recv PUSH_PROMISE frame <length=61, flags=0x04, stream_id=13>
[  0.217] recv PUSH_PROMISE frame <length=90, flags=0x04, stream_id=13>
[  0.217] recv PUSH_PROMISE frame <length=90, flags=0x04, stream_id=13>
[  0.217] recv PUSH_PROMISE frame <length=89, flags=0x04, stream_id=13>
[  0.217] recv PUSH_PROMISE frame <length=99, flags=0x04, stream_id=13>
```

To install `nghttp` for macOS:

```
$ brew install nghttp2
```
