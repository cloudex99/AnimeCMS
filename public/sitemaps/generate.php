<?php

if(php_sapi_name() !== 'cli') die('NOT ALLOWED');

ini_set("memory_limit", "-1");

require_once __DIR__.'/../../app/config/config.php';
require_once __DIR__.'/../../vendor/autoload.php';

Config::init();

$animes = Anime::query();
$episodes = [];

$xmlset = '<?xml version="1.0" encoding="UTF-8"?><?xml-stylesheet type="text/xsl" href="sitemap.xsl"?>
<urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
';

$map1 = $xmlset;
$map1.='<url>
            <loc>'.SITE_URL.'</loc>
            <changefreq>hourly</changefreq>
            <priority>1.0</priority>
        </url>';
foreach (Config::get('pages') as $key => $page){
    $map1.='<url>
                <loc>'.SITE_URL.'/'.$key.'</loc>
                <changefreq>weekly</changefreq>
                <priority>0.9</priority>
            </url>';
}
usort($animes, 'date_compare');
foreach($animes as $anime){
    $freq = ($anime->status==='ongoing') ? 'weekly' : 'yearly';
    $map1.="<url>
                <loc>{$anime->url()}</loc>
                <changefreq>$freq</changefreq>
                <priority>0.8</priority>
            </url>";
    echo "getting episodes for $anime->title\n";
    $episodes = array_merge($episodes, $anime->getEpisodes());
}
usort($episodes, 'date_compare');

$map1.= '</urlset>';
$map1 = gzencode($map1, 9);
$file = fopen('./sitemaps/sitemap1.xml.gz', 'w');
fwrite($file, $map1);
fclose($file);

$map2 = $xmlset;
$map3 = $xmlset;
foreach($episodes as $episode){
    $freq = ($episode->anime()->status==='ongoing') ? 'weekly' : 'yearly';
    if($episode->hasDubbed()){
        $map2.="
            <url>
                <loc>{$episode->url('dubbed')}</loc>
                <changefreq>$freq</changefreq>
                <priority>0.7</priority>
            </url>";
    }
    if($episode->hasSubbed()){
        $map3.="
            <url>
                <loc>{$episode->url('subbed')}</loc>
                <changefreq>$freq</changefreq>
                <priority>0.7</priority>
            </url>";
    }
}
$map2.= '</urlset>';
$map3.= '</urlset>';

$map2 = gzencode($map2, 9);
$file = fopen('./sitemaps/sitemap2.xml.gz', 'w');
fwrite($file, $map2);
fclose($file);

$mapindex = '<?xml version="1.0" encoding="UTF-8"?>
<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
$mapindex.='<sitemap>
      <loc>'.SITE_URL.'/sitemaps/sitemap1.xml.gz</loc>
      <lastmod>'.date('Y-m-d').'</lastmod>
   </sitemap>';
$mapindex.='<sitemap>
      <loc>'.SITE_URL.'/sitemaps/sitemap2.xml.gz</loc>
      <lastmod>'.date('Y-m-d').'</lastmod>
   </sitemap>';

if(!DUBBED_ONLY){
    $map3 = gzencode($map3, 9);
    $file = fopen('./sitemaps/sitemap3.xml.gz', 'w');
    fwrite($file, $map3);
    fclose($file);

    $mapindex.='<sitemap>
      <loc>'.SITE_URL.'/sitemaps/sitemap3.xml.gz</loc>
      <lastmod>'.date('Y-m-d').'</lastmod>
    </sitemap>';
}

$mapindex.= '</sitemapindex>';
$mapindex = gzencode($mapindex, 9);
$file = fopen('./sitemaps/sitemap.xml.gz', 'w');
fwrite($file, $mapindex);
fclose($file);