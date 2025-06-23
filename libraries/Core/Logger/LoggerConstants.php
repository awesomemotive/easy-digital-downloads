<?php

namespace EDD\Vendor\Core\Logger;

use EDD\Vendor\Psr\Log\LogLevel;

class LoggerConstants
{
    public const NON_SENSITIVE_HEADERS = [
        "Accept", "Accept-Charset", "Accept-Encoding", "Accept-Language",
        "Access-Control-Allow-Origin", "Cache-Control", "Connection",
        "Content-Encoding", "Content-Language", "Content-Length", "Content-Location",
        "Content-MD5", "Content-Range", "Content-Type", "Date", "ETag", "Expect",
        "Expires", "From", "Host", "If-Match", "If-Modified-Since", "If-None-Match",
        "If-Range", "If-Unmodified-Since", "Keep-Alive", "Last-Modified", "Location",
        "Max-Forwards", "Pragma", "Range", "Referer", "Retry-After", "Server",
        "Trailer", "Transfer-Encoding", "Upgrade", "User-Agent", "Vary", "Via",
        "Warning", "X-Forwarded-For", "X-Requested-With", "X-Powered-By"
    ];
    public const ALLOWED_LOG_LEVELS = [
        LogLevel::EMERGENCY, LogLevel::ALERT, LogLevel::CRITICAL, LogLevel::ERROR,
        LogLevel::WARNING, LogLevel::NOTICE, LogLevel::INFO, LogLevel::DEBUG
    ];
    public const METHOD = 'method';
    public const URL = 'url';
    public const HEADERS = 'headers';
    public const BODY = 'body';
    public const STATUS_CODE = 'statusCode';
    public const CONTENT_LENGTH = 'contentLength';
    public const CONTENT_TYPE = 'contentType';
    public const CONTENT_LENGTH_HEADER = 'content-length';
    public const CONTENT_TYPE_HEADER = 'content-type';
}
