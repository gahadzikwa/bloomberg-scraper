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

        $nodes = $this->getNodesByClass('ticker_data', 0);
        echo '<pre>';
        print_r($nodes);
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