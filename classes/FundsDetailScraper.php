<?php
class FundsDetailScraper
{
    // Bloomberg base URL
    const BLOOMBERG_URL = 'http://www.bloomberg.com';


    // DIV class name that hold fund name
    const FUND_NAME_CLASS = 'ticker_header_top';


    /**
     * JSON filename that hold funds list data
     * 
     * @var     string  $fundsListFilename
     * @access  private
     */
    private $fundsListFilename;


    /**
     * CSV filename that would hold all funds detail data
     * 
     * @var     string  $resultFilename
     * @access  private
     */
    private $resultFilename;


    /**
     * An array that hold funds list data
     * 
     * @var     array   $fundsList
     * @access  private
     */
    private $fundsList = array();


    /**
     * An instance of DOM Document class
     * 
     * @var     object  $dom
     * @access  private
     */
    private $dom;


    /**
     * An instance of DOM XPath class
     * 
     * @var     object  $xpath
     * @access  private
     */
    private $xpath;


    /**
     * Our class constructor
     * 
     * @param   string  JSON file where the scraped funds list being tracked
     * @param   string  CSV file where all funds detail would be saved
     * @return  object  An instance of FundsDetailScraper class
     * @access  public
     */
    public function __construct($fundsListFilename, $resultFilename)
    {
        // Save funds list
        $this->fundsListFilename = $fundsListFilename;
        $this->fundsList = json_decode(file_get_contents($fundsListFilename));

        // Set result filename
        $this->resultFilename = $resultFilename;

        // Create new DomDocument object
        $this->dom = new DomDocument();
    }


    /**
     * Function to reset scraper
     * 
     * @param   string  $fundList       JSON file that hold funds list
     * @param   string  $newFundsList   JSON file where scraper fund list would 
     *                                  be saved
     * @return  void
     * @access  public
     */
    public function reset($fundsList, $newFundsList)
    {
        $fundsList = json_decode(file_get_contents($fundsList))->list;
        $i = 1;
        $newList = array();
        foreach ($fundsList as $fund) {
            array_push($newList, array(
                'url'       => "samples/funds-detail/{$i}.html",
                'scraped'   => false
            ));
            $i++;
        }

        // Reset restult file
        file_put_contents($this->resultFilename, '');

        // Create a new list
        file_put_contents($newFundsList, json_encode($newList));
        echo "Scraper reset: <a href='{$newFundsList}'>Funds List</a>";
    }


    /**
     * Our main method to scrape funds detail
     * 
     * @return  void
     * @access  public
     */
    public function scrape()
    {
        $total = count($this->fundsList);
        for ($i = 0; $i < $total; $i++) {
            // If current fund has already scraped, skip it!
            if ($this->fundsList[$i]->scraped) continue;

            // Get current fund detail
            $data = $this->scrapeDetail($this->fundsList[$i]->url);
            $data = implode(';', $data);

            // Get saved funds detail
            $detail = file_get_contents($this->resultFilename);
            $detail = explode("\n", $detail);

            // Insert the current fund
            $detail[$i] = $data;
            $detail = implode("\n", $detail);
            file_put_contents($this->resultFilename, $detail);
            
            // Update funds list status
            $this->fundsList[$i]->scraped = true;
            file_put_contents($this->fundsListFilename, 
                json_encode($this->fundsList));

            // Inform user
            echo ($i+1) . ' Pages Scraped: ' . 
                $this->fundsList[$i]->url . '<br>';
        }

        echo "FINISHED: <a href='{$this->resultFilename}'>Funds Detail</a>";
    }


    /**
     * Method for scraping the current page
     * 
     * @param   string  $url    URL of the page that would be scraped
     * @access  private
     */
    private function scrapeDetail($url)
    {
        // Load DOM element of the scraped page
        @$this->dom->loadHTMLFile($url);

        // Create an xPath to do a DOM query
        $this->xpath = new DomXPath($this->dom);

        return array(
            $this->getFundName()
        );
    }


    /**
     * Method for retrieving current fund name
     * 
     * @return  string  The fund name being scraped
     * @access  private
     */
    private function getFundName()
    {
        // Get DIV that hold fund name
        $fundName = $this->getNodesByClass(self::FUND_NAME_CLASS, 0);

        // Extract fund name in H2 element
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