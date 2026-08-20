#!/bin/bash

printf 'HTTP/1.1 200 OK\r\n'
printf 'Content-Type: text/plain\r\n'
printf 'Transfer-Encoding: chunked\r\n'
printf '\r\n'
printf 'ZZZ\r\nGarbageDataHere\r\n0\r\n\r\n'
