<?php
/* Bloomberg site URL */
define('BLOOMBERG_URL', 'http://www.bloomberg.com');

/* URL of funds list in Indonesia */
define('FUNDS_LIST_URL', BLOOMBERG_URL . '/markets/funds/country/indonesia/');

/* Saved pages extension */
define('SAVED_PAGE_EXT', '.html');
/* Bloomberg saved pages directory */
define('SAVED_PAGES_DIR', 'bloomberg-pages/');
/* Bloomberg funds list pages directory */
define('FUNDS_LIST_PAGES_DIR', SAVED_PAGES_DIR . 'funds-list/');

/* Scraped data directory */
define('DATA_DIR', 'data/');
/* Scraped funds list file */
define('FUNDS_LIST_FILE', DATA_DIR . 'funds-list.csv');