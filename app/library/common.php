<?php

function print_ln($data)
{
    if (is_array($data) || is_object($data)) {
        echo "<pre>";
        print_r($data);
        echo "</pre>";
    } else {
        echo $data . '</br>';
    }
}

function str_lreplace($search, $replace, $subject)
{
    $pos = strrpos($subject, $search);
    if($pos !== false){
        $subject = substr_replace($subject, $replace, $pos, strlen($search));
    }
    return $subject;
}

function substri_count($haystack, $needle)
{
    return substr_count(strtoupper($haystack), strtoupper($needle));
}

function time_ago($datetime,$full = false) {
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    $diff->w = floor($diff->d / 7);
    $diff->d -= $diff->w * 7;
    $string = array(
        'y' => 'year',
        'm' => 'month',
        'w' => 'week',
        'd' => 'day',
        'h' => 'hour',
        'i' => 'min',
        's' => 'sec',
    );
    foreach ($string as $k => &$v) {
        if ($diff->$k) {
            $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
        } else {
            unset($string[$k]);
        }
    }
    if (!$full) $string = array_slice($string, 0, 1);
    return $string ? implode(', ', $string) . ' ago' : 'just now';
}

function compareByName($a, $b) {
    return strcmp($a->english, $b->english);
}

function date_compare($a, $b)
{
    $t1 = strtotime($a->date);
    $t2 = strtotime($b->date);
    return $t2 - $t1;
}

function paginate($array, $pageSize = 10, $page = 1)
{
    if(empty($array))
        return [];
    $page = ($page < 1) ? 1 : $page;
    $pages = array_chunk($array, $pageSize);
    return $page > sizeof($pages) ? $pages[sizeof($pages) - 1] : $pages[$page - 1];
}

function execution_time(){
    return round(( microtime(true) - TIME_START), 5);
}