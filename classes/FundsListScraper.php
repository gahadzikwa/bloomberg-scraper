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
    /* Funds list table class name */
    const FUNDS_LIST_TABLE_CLASS = 'ticker_data';

    /* Next page anchor class name */
    const NEXT_PAGE_CLASS = 'next_page';

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

        $nextPage = $this->getNextPageUrl();
        $fundsList = $this->getFundsList();

        echo '<pre>';
        print_r($fundsList);
    }


    /**
     * Get fund list data
     * 
     * @access  private
     * @return  array   An array that hold fund list data
     */
    private function getFundsList()
    {
        /* Get table that contain of fund list data */
        $table = $this->getNodesByClass(self::FUNDS_LIST_TABLE_CLASS, 0);
        $rows = $table->getElementsByTagName('tr');
        
        /* Loop through each row */
        $fundsList = array();
        for ($i = 1; $i < $rows->length; $i++) {
            /* Extract each columns */
            $cols = $rows->item($i)->getElementsByTagName('td');
            $fundsList[] = array(
                $cols->item(0)->textContent,            // Name
                $cols->item(1)->textContent,            // Symbol
                $cols->item(2)->textContent,            // Type
                $cols->item(3)->textContent,            // Objective
                $this->getFundUrl($cols->item(0))       // Get fund URL
            );
        }

        return $fundsList;
    }


    /**
     * Function to get fund URL
     * 
     * @param   object  A DOMNode object of a fund list row that contains fund
     *                  URL
     * @return  string  A URL to fund detail
     * @access  private
     */
    private function getFundUrl($rowNode)
    {
        $anchor = $rowNode->getElementsByTagName('a')->item(0);
        return BLOOMBERG_URL . $anchor->getAttribute('href');
    }


    /**
     * Function to get the next page url
     * 
     * @return  mixed   It will return FALSE if there is no more page afterward.
     *                  Or it will return next page url.
     * @access  private
     */
    private function getNextPageUrl()
    {
        /* Get next page anchor node by using its class name */
        $nextPage = $this->getNodesByClass(self::NEXT_PAGE_CLASS, 0);

        /* If no next page anchor, return false */
        if (!$nextPage) return false;

        /* Or else return HREF attribute */
        return BLOOMBERG_URL . $nextPage->getAttribute('href');
    }


    /**
     * Function to get nodes object by class name
     * 
     * @param   string  $className  The requested class name to search for
     * @param   int     $index      An optional parameter which indicate an 
     *                              index of node to return
     * @return  mixed   Would return FALSE if there is no matched nodes or the
     *                  requested nodes at specified index is not available.
     *                  Would return a nodeList object if there is no index 
     *                  specified. Would return a single node object at 
     *                  specified index.
     * @access  private
     */
    private function getNodesByClass($className, $index = null)
    {
        /* Use xPath to query nodes by class name */
        $nodeList = $this->xpath->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' $className ')]");

        /* If there is no nodes match, return false */
        if ($nodeList->length <= 0) return false;

        /* If $index is not specified, return all matched nodes */
        if (is_null($index)) return $nodeList;

        /* If requested $index is not available, return false */
        if ($index >= $nodeList->length) return false;

        /* Return nodes at requested $index */
        return $nodeList->item($index);
    }
}