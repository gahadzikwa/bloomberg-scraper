<?php
set_time_limit(0);

/* Include global configuration file */
require_once('config/global.php');

/* Include SavePages class */
require_once('classes/SavePages.php');

/* Create SavePages instance */
$savePages = new \BloombergScraper\SavePages();

/* Save funds details pages */
$pagesSaved = $savePages->saveFundsList(1);

/* Echoing saved result */
echo 'Bloomberg Indonesia funds detail pages successfuly saved.<br>';
echo 'Total funds detail saved: ' . $pagesSaved . '<br>';
echo '<a href="' . FUNDS_DETAILS_PAGES_DIR . '">Go to saved funds detail directory</a>';
