<?php
set_time_limit(0);
require_once('classes/FundsDetailScraper.php');

$scraper = new FundsDetailScraper();

if (isset($_GET['reset'])) {
    $scraper->reset('data/funds-list.json', 'data/funds-list-scraped.json');
}
