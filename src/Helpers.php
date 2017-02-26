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