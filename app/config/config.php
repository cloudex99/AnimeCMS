<?php

if (isset($_GET['debug']) && $_GET['debug'] == 'true') {
    ini_set("display_errors", "1");
    error_reporting(E_ALL);
    define('DEBUG', true);
} else {
    define('DEBUG', true);
}

const BASE_DIR = __DIR__.'/../../';
const SITE_URL = 'http://animecms.site'; //change to your site url

const API_URL = 'https://animeapi.com';
const API_KEY = '5af6d169-c508-48bd-8f70-02d3c38b206b';

const CACHE_PREFIX = 'cms_'; //cache prefix for different sites that are hosted on same server. keep short.

const DEFAULT_EXPIRE_TIME = 3600*24; // 1 day. Do not set to less than 1 day.
const RECENT_UPDATE_TIME = 3600*3; //4 Hours. How often to fetch recent episodes/anime

const LOGIN_SECRET = ''; //secret key for login authentication through forums
const LOGIN_ENDPOINT = '/login';

const DUBBED_ONLY = false; //set to true if you only want dubbed shows

const TEMPLATE_BASE = ''; //template directory in the views folder
