<?php
set_time_limit(0);

/* Include global configuration file */
require_once('config/global.php');

/* Include FundsListScraper class */
require_once('classes/FundsListScraper.php');

/* Create FundsListScraper instance */
$fundsListScraper = new \BloombergScraper\FundsListScraper();

/* Scrape funds list */
$fundsListScraper->run();
