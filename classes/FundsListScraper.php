<?php
/**
 * FundsListScraper - Class for scraping funds list page
 * 
 * @author  Risan Bagja Pradana <risanbagja@yahoo.com>
 * @package BloombergScraper
 * @version 1.0
 */
namespace BloombergScraper;

class FundsListScraper
{
    public function run()
    {
        $html = file_get_contents(FUNDS_LIST_PAGES_DIR . '1' . SAVED_PAGES_EXT);
        echo $html;
    } 
}