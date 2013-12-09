<?php
/* Include needed classes */
require_once('classes/SavePages.php');

$savePages = new \BloombergScraper\SavePages();
$savePages->saveFundsList();