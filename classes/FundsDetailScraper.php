<?php
class FundsDetailScraper
{
    // Bloomberg base URL
    const BLOOMBERG_URL = 'http://www.bloomberg.com';


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
     * @return  object  An instance of FundsDetailScraper class
     * @access  public
     */
    public function __construct()
    {
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

        file_put_contents($newFundsList, json_encode($newList));
        echo "Scraper reset: <a href='{$newFundsList}'>Funds List</a>"
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