<?php
/**
 * FundsDetailScraper - Class for scraping funds detail page
 * 
 * @author  Risan Bagja Pradana <risanbagja@yahoo.com>
 * @package BloombergScraper
 * @version 1.0
 */
namespace BloombergScraper;
use DomDocument;
use DomXPath;

class FundsDetailScraper
{
    /* DIV class name that hold fund name */
    const FUND_NAME_CLASS = 'ticker_header_top';

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
     * @return  object  An instance of FundsDetailScraper class
     * @access  public
     */
    public function __construct()
    {
        /* Create new DomDocument object */
        $this->dom = new DomDocument();
    }


    /**
     * Main function to scrape funds detail data
     * 
     * @return  int     Total funds detail successfuly scraped
     * @access  public
     */
    public function run()
    {
        /* Load DOM element of the scraped page */
        @$this->dom->loadHTMLFile(FUNDS_DETAILS_PAGES_DIR . 0 . SAVED_PAGE_EXT);

        /* Create an xPath to do a DOM query */
        $this->xpath = new DomXPath($this->dom);

        $fundName = $this->getFundName();
        var_dump($fundName);
    }


    /**
     * Function to get fund name
     * 
     * @return  string  The fund name
     * @access  private
     */
    private function getFundName()
    {
        /* Get DIV that hold fund name */
        $fundName = $this->getNodesByClass(self::FUND_NAME_CLASS, 0);

        /* Extract fund name in H2 element */
        $fundName = $fundName->getElementsByTagName('h2')->item(0)->textContent;
        return $fundName;
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