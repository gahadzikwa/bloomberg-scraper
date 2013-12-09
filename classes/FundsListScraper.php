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
     * A URL of page to be scraped
     * 
     * @var     string $url
     * @access  private
     */
    private $url;


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

        /* Initialize first URL to be scraped */
        $this->url = FUNDS_LIST_URL;
    }


    /**
     * Main function to scrape funds list data
     * 
     * @return  int     Total funds list successfuly scraped
     * @access  public
     */
    public function run()
    {
        /* Scrape all funds list */
        $fundsList = $this->scrapeAllFundsList(); 

        /* Write scraped data to a file */
        $this->saveFundsList($fundsList);

        /* Return total funds list scraped */
        return count($fundsList);
    }


    /**
     * Function to save scraped funds list data to a file
     * 
     * @param   array   An array that hold scraped funds list data
     * @return  void
     * @access  private
     */
    private function saveFundsList($fundsList)
    {
        /* A variable to hold funds list data ti be writen */
        $data = '';

        /* Loop through each funds list data */
        $totalFunds = count($fundsList);
        for ($i = 0; $i < $totalFunds; $i++) {
            $data .= implode(',', $fundsList[$i]);      // Create a comma separated data
            if ($i < $totalFunds - 1) $data .= "\n";    // Add a line break
        }

        /* Write data to a file */
        file_put_contents(FUNDS_LIST_FILE, $data);
    }


    /**
     * Function to scrape all funds list data in each page
     * 
     * @return  array   An array that hold all scraped funds list data
     * @access  private
     */
    private function scrapeAllFundsList()
    {
        /* An array to hold fund list data */
        $fundsList = array();

        /* Loop through each page */
        while (!$this->url) {
            /* Load DOM element of the scraped page */
            @$this->dom->load($this->url);

            /* Create an xPath to do a DOM query */
            $this->xpath = new DomXPath($this->dom);

            /* Scrape fund list diplayed on the current page */
            $fundsList = array_merge($fundsList, $this->getFundsList());

            /* Get the next page URL */
            $this->url = $this->getNextPageUrl();
        }

        return $fundsList;
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