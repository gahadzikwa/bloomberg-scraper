<?php
set_time_limit(0);

/* Include global configuration file */
require_once('config/global.php');

/* Include FundsDetailScraper class */
require_once('classes/FundsDetailScraper.php');

/* Create FundsDetailScraper instance */
$fundsDetailScraper = new \BloombergScraper\FundsDetailScraper();

/* Scrape funds detail */
$fundsDetail = $fundsDetailScraper->run();

