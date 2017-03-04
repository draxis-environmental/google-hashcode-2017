<?php
/**
 * Created by PhpStorm.
 * User: Ap
 * Date: 26/02/17
 * Time: 22:10
 */

function sort_requests_by_total($a, $b)
{
    if($a->total == $b->total) return 0;
    return ($a->total > $b->total) ? -1 : 1;
}

function sort_videos_by_requests($a, $b)
{
    if($a->numberOfRequests == $b->numberOfRequests) return 0;
    return ($a->numberOfRequests > $b->numberOfRequests) ? -1 : 1;
}

function show_progress($done, $total, $items='items', $size=20) {

    // if we go over our bound, just ignore it
    if($done > $total) return;

    $perc=(double)($done/$total);
    $bar=floor($perc*$size);
    $status_bar="\r[";
    $status_bar.=str_repeat("=", $bar);

    if($bar<$size){
        $status_bar.=">";
        $status_bar.=str_repeat(" ", $size-$bar);
    } else {
        $status_bar.="=";
    }

    $disp=number_format($perc*100, 0);
    $status_bar.="] $disp%  $done/$total $items";
    echo "$status_bar";

    flush();
    echo "\n";

    // when done, send a newline
    if($done == $total) {
        echo "\n\n\n";
    }


}
