<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Cache;


function buildSitemap(string $locale)
{
    $base = rtrim(config('app.url'), '/');
    $prefix = $locale === 'ar' ? '' : '/' . $locale;

    $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><urlset/>');
    $xml->addAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');

    $add = function ($loc, $priority = '0.80', $lastmod = null) use ($xml) {
        $u = $xml->addChild('url');
        $u->addChild('loc', htmlspecialchars($loc, ENT_QUOTES));
        $u->addChild('lastmod', $lastmod ?: now()->toAtomString());
        $u->addChild('priority', $priority);
    };

    $add("{$base}{$prefix}", '1.00');

    $add("{$base}{$prefix}/menu", '0.90');

    $add("{$base}{$prefix}/packages", '0.90');

    $add("{$base}{$prefix}/business", '0.80');

    $add("{$base}{$prefix}/download-app", '0.80');

    $add("{$base}{$prefix}/privacy-policy", '0.60');

    $add("{$base}{$prefix}/contact-us", '0.60');

    return $xml->asXML();
}



Route::get('/sitemap-ar.xml', function () {
    $xml = Cache::remember(
        'sitemap-ar',
        now()->addDays(7),
        fn () => buildSitemap('ar')
    );

    return response($xml, 200, ['Content-Type' => 'application/xml']);
});

Route::get('/sitemap-en.xml', function () {
    $xml = Cache::remember(
        'sitemap-en',
        now()->addDays(7),
        fn () => buildSitemap('en')
    );

    return response($xml, 200, ['Content-Type' => 'application/xml']);
});
