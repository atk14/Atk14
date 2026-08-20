#!/bin/bash

printf 'HTTP/1.1 200 OK\r\n'
printf 'Content-Type: text/plain\r\n'
printf 'Transfer-Encoding: chunked\r\n'
printf '\r\n'
printf 'a\r\nHi'
