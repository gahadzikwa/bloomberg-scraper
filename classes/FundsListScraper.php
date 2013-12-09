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
    /**
     * An instance of DomDocument class
     * 
     * @var     object  $dom
     * @access  private
     */
    private $dom;


    /**
     * An instance of DomXPath class
     * 
     * @var     object  $xpath
     * @access  private
     */
    private $xpath;


    /**
     * A class constructor
     * 
     * @return  object  An instance of FundsListScraper class
     * @access  public
     */
    public function __construct()
    {
        /* Create new DomDocument object */
        $this->dom = new DomDocument();
    }


    /**
     * Main function to scrape funds list data
     * 
     * @access  public
     */
    public function run()
    {
        @$this->dom->load(FUNDS_LIST_PAGES_DIR . '1' . SAVED_PAGE_EXT);
        $this->xpath = new DomXPath($this->dom);

        $className = 'ticker_data1';
        $nodes = $this->xpath->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' $className ')]");
        echo '<pre>';
        print_r($nodes);
    }
}