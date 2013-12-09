<?php
set_time_limit(0);

/* Include global configuration file */
require_once('config/global.php');

/* Include SavePages class */
require_once('classes/SavePages.php');

/* Create SavePages instance */
$savePages = new \BloombergScraper\SavePages();

/* Save funds list pages */
$pagesSaved = $savePages->saveFundsList(3);

/* Echoing saved result */
echo 'Bloomberg Indonesia funds list pages successfuly saved.<br>';
echo 'Pages saved: ' . $pagesSaved . '<br>';
echo '<a href="' . FUNDS_LIST_FILE . '">Go to saved fund list pages directory</a>';
