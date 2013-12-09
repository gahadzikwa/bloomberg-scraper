<?php
/**
 * FundsListScraper - Class for scraping funds list page
 * 
 * @author  Risan Bagja Pradana <risanbagja@yahoo.com>
 * @package BloombergScraper
 * @version 1.0
 */
namespace BloombergScraper;
use DomDocument;
use DomXPath;

class FundsListScraper
{
    public function run()
    {
        $dom = new DomDocument();
        @$dom->load(FUNDS_LIST_PAGES_DIR . '1' . SAVED_PAGE_EXT);
        $xPath = new DomXPath($dom);
        $className = 'ticker_data';
        $nodes = $xPath->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' $className ')]");
        echo '<pre>';
        print_r($nodes->item(0));
    } 
}