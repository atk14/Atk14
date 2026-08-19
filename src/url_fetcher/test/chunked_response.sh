#!/bin/bash

printf 'HTTP/1.1 200 OK\r\n'
printf 'Content-Type: text/plain\r\n'
printf 'Transfer-Encoding: chunked\r\n'
printf '\r\n'
printf '5\r\nHello\r\n'
printf '1\r\n \r\n'
printf '6\r\nWorld!\r\n'
printf '0\r\n\r\n'
