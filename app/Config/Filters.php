<?php
namespace Config;

use CodeIgniter\Config\Filters as BaseFilters;
use CodeIgniter\Filters\{CSRF, DebugToolbar, ForceHTTPS, Honeypot, InvalidChars, PageCache, PerformanceMetrics, SecureHeaders};

class Filters extends BaseFilters
{
    public array $aliases = [
        'csrf' => CSRF::class, 'toolbar' => DebugToolbar::class,
        'honeypot' => Honeypot::class, 'invalidchars' => InvalidChars::class,
        'secureheaders' => SecureHeaders::class, 'forcehttps' => ForceHTTPS::class,
        'pagecache' => PageCache::class, 'performance' => PerformanceMetrics::class,
        'auth' => \App\Filters\AuthFilter::class,
    ];
    public array $required = ['before' => ['forcehttps'], 'after' => []];
    public array $globals = ['before' => ['invalidchars', 'csrf'], 'after' => ['secureheaders']];
    public array $methods = [];
    public array $filters = [];
}
