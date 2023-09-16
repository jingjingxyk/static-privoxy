<?php

use SwooleCli\Library;
use SwooleCli\Preprocessor;

return function (Preprocessor $p) {
    $curl_prefix = CURL_PREFIX;
    $openssl_prefix = OPENSSL_PREFIX;
    $zlib_prefix = ZLIB_PREFIX;
    $cares_prefix = CARES_PREFIX;

    $p->addLibrary(
        (new Library('curl'))
            ->withHomePage('https://curl.se/')
            ->withManual('https://curl.se/docs/install.html')
            ->withLicense('https://github.com/curl/curl/blob/master/COPYING', Library::LICENSE_SPEC)
            ->withUrl('https://curl.se/download/curl-8.3.0.tar.gz')
            ->withPrefix($curl_prefix)
            ->withConfigure(
                <<<EOF

            # 使用 cmake 构建CURL 需要解决这个问题   https://github.com/curl/curl/issues/6167
            # curl bug 信息：-lgcc -lgcc_s -lc -lgcc -lgcc_s
            # sed -i.save s@\${CMAKE_C_IMPLICIT_LINK_LIBRARIES}@@ CMakeLists.txt

            ./configure --help

            PACKAGES='zlib openssl libcares libbrotlicommon libbrotlidec libbrotlienc libzstd libnghttp2 '
            PACKAGES="\$PACKAGES  libssh2 libnghttp3 libngtcp2  libngtcp2_crypto_quictls" # libidn2 libngtcp2_crypto_openssl

            CPPFLAGS="$(pkg-config  --cflags-only-I  --static \$PACKAGES)" \
            LDFLAGS="$(pkg-config   --libs-only-L    --static \$PACKAGES)" \
            LIBS="$(pkg-config      --libs-only-l    --static \$PACKAGES)" \
            ./configure --prefix={$curl_prefix}  \
            --enable-static \
            --disable-shared \
            --without-librtmp \
            --disable-ldap \
            --disable-rtsp \
            --enable-http \
            --enable-alt-svc \
            --enable-hsts \
            --enable-http-auth \
            --enable-mime \
            --enable-cookies \
            --enable-doh \
            --enable-threaded-resolver \
            --enable-ipv6 \
            --enable-proxy  \
            --enable-websockets \
            --enable-get-easy-options \
            --enable-file \
            --enable-mqtt \
            --enable-unix-sockets  \
            --enable-progress-meter \
            --enable-optimize \
            --with-zlib={$zlib_prefix} \
            --enable-ares={$cares_prefix} \
            --with-nghttp2 \
            --with-ngtcp2 \
            --with-nghttp3 \
            --without-libidn2 \
            --with-libssh2 \
            --with-openssl  \
            --with-default-ssl-backend=openssl \
            --without-gnutls \
            --without-mbedtls \
            --without-wolfssl \
            --without-bearssl \
            --without-rustls

EOF
            )
            ->withPkgName('libcurl')
            ->withBinPath($curl_prefix . '/bin/')
            ->withDependentLibraries(
                'openssl',
                'cares',
                'zlib',
                'brotli',
                'libzstd',
                'nghttp2',
                'nghttp3',
                'ngtcp2',
                'libssh2'
            ) # 'libidn2',
    );
};
