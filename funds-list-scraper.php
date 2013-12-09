<?php
set_time_limit(0);

/* Include global configuration file */
require_once('config/global.php');

/* Include FundsListScraper class */
require_once('classes/FundsListScraper.php');

/* Create FundsListScraper instance */
$fundsListScraper = new \BloombergScraper\FundsListScraper();

/* Scrape funds list */
$totalFunds = $fundsListScraper->run();

/* Echoing saved result */
echo 'Bloomberg Indonesia funds list pages successfuly scraped.<br>';
echo 'Total funds scraped: ' . $totalFunds . '<br>';
echo '<a href="' . FUNDS_LIST_PAGES_DIR . '">Go to saved fund list pages directory</a>';
