<?php
class FundsListScraper
{
    // Bloomberg base URL
    const BLOOMBERG_URL = 'http://www.bloomberg.com';

    // Funds list table class name
    const FUNDS_LIST_TABLE_CLASS = 'ticker_data';

    // Next page anchor class name
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
     * @var     string  $url
     * @access  private
     */
    private $url = 'http://www.bloomberg.com/markets/funds/country/indonesia/';


    /**
     * An array that hold funds list data
     * 
     * @var     array   $url
     * @access  private
     */
    private $fundsList = array();


    /**
     * Filename where the scraped funds list are saved
     * 
     * @var     string  $filename
     * @access  private
     */
    private $filename;


    /**
     * A class constructor
     * 
     * @return  object  An instance of FundsListScraper class
     * @access  public
     */
    public function __construct()
    {
        // Create new DomDocument object
        $this->dom = new DomDocument();
    }


    /**
     * Method for scraping all funds list data in each page
     * 
     * @return  object  An instance of funds list scraper class
     * @access  public
     */
    public function scrape()
    {
        // Reset funds list data
        $this->fundsList = array();

        // Loop through each page
        while ($this->url) {
            // Load DOM element of the scraped page
            @$this->dom->loadHTMLFile($this->url);

            // Create an xPath to do a DOM query
            $this->xpath = new DomXPath($this->dom);

            // Scrape fund list diplayed on the current page
            $this->fundsList = array_merge(
                $this->fundsList, 
                $this->scrapeFundsList()
            );

            // Get the next page URL
            $this->url = $this->getNextPageUrl();
        }

        return $this;
    }


    /**
     * Function to save scraped funds list data to a file
     * 
     * @param   string  $filename   A filename where the scraped funds would be
     *                              saved.
     * @return  object  An instance of funds list scraper class
     * @access  public
     */
    public function save($filename = 'data/funds-list.json')
    {
        // Get today date
        $now = new DateTime('now', new DateTimeZone('Asia/Jakarta'));

        // Data to be writen
        $data = array(
            'list'  => $this->fundsList,
            'date'  => $now->format('Y-m-d H:i:s')
        );

        // Write data to a file
        $this->filename = $filename;
        file_put_contents($filename, json_encode($data));

        return $this;
    }


    /**
     * Method for retrieving total funds list that has been scraped
     * 
     * @return  int Total funds list that has been scraped
     * @access  public
     */
    public function getTotalFunds()
    {
        return count($this->fundsList);
    }


    /**
     * Method for retrieving the filename where the scraped funds list are saved
     * 
     * @return  string  Filename where the scraped funds are saved
     * @access  public
     */
    public function getFilename()
    {
        return $this->filename;
    }


    /**
     * Scrape funds list data in the current page
     * 
     * @access  private
     * @return  array   An array that hold fund list data
     */
    private function scrapeFundsList()
    {
        // Get table that contain of fund list data
        $table = $this->getNodesByClass(self::FUNDS_LIST_TABLE_CLASS, 0);
        $rows = $table->getElementsByTagName('tr');
        
        // Loop through each row
        $fundsList = array();
        for ($i = 1; $i < $rows->length; $i++) {
            // Extract each columns
            $cols = $rows->item($i)->getElementsByTagName('td');
            $fundsList[] = array(
                $cols->item(0)->textContent,        // Name
                $cols->item(1)->textContent,        // Symbol
                $cols->item(2)->textContent,        // Type
                $cols->item(3)->textContent,        // Objective
                $this->getFundUrl($cols->item(0))   // Get fund URL
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
        // Get next page anchor node by using its class name
        $nextPage = $this->getNodesByClass(self::NEXT_PAGE_CLASS, 0);

        // If no defined HREF attribute, return false
        $url = $nextPage->getAttribute('href');
        if (!$url) return false;

        // Or else return the HREF attribute
        return BLOOMBERG_URL . $url;
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
        // Use xPath to query nodes by class name
        $nodeList = $this->xpath->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' $className ')]");

        // If there is no nodes match, return false
        if ($nodeList->length <= 0) return false;

        // If $index is not specified, return all matched nodes
        if (is_null($index)) return $nodeList;

        // If requested $index is not available, return false
        if ($index >= $nodeList->length) return false;

        // Return nodes at requested $index
        return $nodeList->item($index);
    }
}