<?php
require_once('classes/FundsDetailScraper.php');

$scraper = new FundsDetailScraper('data/funds-list-scraped.json',
    'data/funds-detail.csv');

if (isset($_GET['reset'])) {
    $scraper->reset('data/funds-list.json', 'data/funds-list-scraped.json');
}

else {
    $scraper->scrape();
}
