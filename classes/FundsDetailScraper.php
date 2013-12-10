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
use DateTime;
use DateTimeZone;

class FundsDetailScraper
{
    /* DIV class name that hold fund name */
    const FUND_NAME_CLASS = 'ticker_header_top';

    /* DIV class name that became fund header */
    const FUND_HEADER_CLASS = 'ticker_header';

    /* DIV class name that hold exchange type detail */
    const EXCHANGE_TYPE_CLASS = 'exchange_type';

    /* P class name that hold last updated date */
    const DATE_CLASS = 'fine_print';

    /* Bloomberg timezone, use EST */
    const BLOOMBERG_TIMEZONE = 'America/New_York';

    /* My Timezone, GMt+7 */
    const MY_TIMEZONE = 'Asia/Jakarta';

    /* Span class name that hold price value */
    const PRICE_CLASS = 'price';

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

        $name = $this->getFundName();
        $symbol = $this->getFundSymbol();
        $updatedDate = $this->getLastUpdatedDate();
        $exchangeType = $this->getExchangeType();
        $price = $this->getPrice();

        echo '<pre>';
        print_r($price);
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
     * Function to get fund symbol
     * 
     * @return  string  $symbol Symbol of the current fund
     * @access  private
     */
    private function getFundSymbol()
    {
        /* Get fund header DIV */
        $header = $this->getNodesByClass(self::FUND_HEADER_CLASS, 0);

        /* Get fund symbol */
        $symbol = $header->getElementsByTagName('h3')->item(0)->textContent;
        return $symbol;
    }


    /**
     * Function to get last updated date
     * 
     * @return  string  A string representation of last updated date in GMT+7
     * @access  private
     */
    private function getLastUpdatedDate()
    {
        /* Get last updated date string */
        $date = $this->getNodesByClass(self::DATE_CLASS, 0)->textContent;
        $date = $this->cleanText($date);

        /* Remove any unwanted words */
        $date = str_replace(array('As of ', 'ET on ', '.'), '', $date);

        /* Extract date parts and time part */
        $date = explode(' ', $date);

        /* Get and reformat date part */
        $datePart = explode('/', $date[1]);
        $datePart = $datePart[2] . '-' . $datePart[0] . '-' . $datePart[1];

        /* Get time part */
        $timePart = $date[0];

        /* Create a date object and convert timezone */
        $updatedDate = new DateTime("$datePart $timePart", new DateTimeZone(self::BLOOMBERG_TIMEZONE));
        $updatedDate->setTimeZone(new DateTimeZone(self::MY_TIMEZONE));

        /* Return string representation of last updated date */
        return $updatedDate->format('Y-m-d H:i:s');
    }


    /**
     * Function to get exchange type detail
     * 
     * @return  array   A numeric key array that hold exchange type detail.
     *                  [0] => Fund type
     *                  [1] => Objective
     *                  [2] => Assets class
     *                  [3] => Geographic focus
     * @access  private
     */
    private function getExchangeType()
    {
        /* Get DIV element */
        $list = $this->getNodesByClass(self::EXCHANGE_TYPE_CLASS, 0);

        /* Get UL element */
        $list = $list->getElementsByTagName('ul')->item(0);

        /* Get all LI elements */
        $list = $list->getElementsByTagName('li');

        /* Extract exchange type detail */
        $exchangeType = array();
        for ($i = 0; $i < $list->length; $i++) {
            $span = $list->item($i)->getElementsByTagName('span');
            // The value is contained in the second span element
            $exchangeType[] = $this->cleanText($span->item(1)->textContent);
        }

        return $exchangeType;
    }


    /**
     * Function to get fund price and currency
     * 
     * @return  array   An array that hold both price and its currency
     * @access  private
     */
    private function getPrice()
    {
        /* Get price */
        $price = $this->getNodesByTagClass('span', self::PRICE_CLASS, 0);
        $price = $this->cleanText($price->textContent);

        /* Extract price from currency */
        $priceArr = explode(' ', $price);

        /* If element contains currency */
        if (count($priceArr) >= 2) {
            $priceVal = $priceArr[0];
            $currency = $priceArr[1];
        }

        /* If no currency is specified, we assumed it is an IDR */
        else {
            $priceVal = $price;
            $currency = 'IDR';
        }

        /* Convert price string into a float number */
        $priceVal = (float) str_replace(',', '', $priceVal);
        
        return array($priceVal, $currency);
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


    /**
     * Function to get nodes object by tag and class name
     * 
     * @param   string  $tag        The HTML tag to look for
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
    private function getNodesByTagClass($tag, $className, $index = null)
    {
        /* Use xPath to query nodes by tag and class name */
        $nodeList = $this->xpath->query("//{$tag}[contains(concat(' ', normalize-space(@class), ' '), ' $className ')]");

        /* If there is no nodes match, return false */
        if ($nodeList->length <= 0) return false;

        /* If $index is not specified, return all matched nodes */
        if (is_null($index)) return $nodeList;

        /* If requested $index is not available, return false */
        if ($index >= $nodeList->length) return false;

        /* Return nodes at requested $index */
        return $nodeList->item($index);
    }


    /**
     * A method to clean up text: trim, remove line breaks
     * 
     * @param   string  A text to be cleaned up
     * @return  string  A clean text
     * @access  private
     */
    private function cleanText($text)
    {
        /* Convert any whitescapeces into a normal spaces */
        $text = preg_replace('/\s+/', ' ', $text);

        /* Trim text */
        $text = trim($text);
        return $text;
    }
}