<?php
class FundsDetailScraper
{
    // Bloomberg base URL
    const BLOOMBERG_URL = 'http://www.bloomberg.com';
    // Bloomberg timezone, use EST
    const BLOOMBERG_TIMEZONE = 'America/New_York';
    // Jakarta Timezone, GMt+7
    const JAKARTA_TIMEZONE = 'Asia/Jakarta';
    // Money Market asset class
    const MONEY_MARKET_TYPE = 'Money Market';
    // Real Estate asset class
    const REAL_ESTATE_TYPE = 'Real Estate';
    // ETF fund type
    const ETF_TYPE = 'ETF';


    // DIV class name that hold fund name
    const FUND_NAME_CLASS = 'ticker_header_top';
    // DIV class name that became fund header
    const FUND_HEADER_CLASS = 'ticker_header';
    // P class name that hold last updated date
    const DATE_CLASS = 'fine_print';
    // DIV class name that hold exchange type detail
    const EXCHANGE_TYPE_CLASS = 'exchange_type';
    // Span class name that hold price value
    const PRICE_CLASS = 'price';
    // Span class name that hold price method value
    const PRICE_METHOD_CLASS = 'price_method_value';
    // Span class name that hold price when it's trending is up
    const TRENDING_UP_CLASS = 'trending_up';
    // Span class name that hold price when it's trending is down
    const TRENDING_DOWN_CLASS = 'trending_down';
    // Span class name that hold price when it's trending is flat
    const TRENDING_NONE_CLASS = 'trending_none';
    // Snapshot table class name
    const SNAPSHOT_CLASS = 'snapshot_table';
    // Profile p class name
    const PROFILE_CLASS = 'profile_no_margin';
    // Extended profile div class name
    const EXTENDED_PROFILE_CLASS = 'extended_profile';


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

        // Reset result file
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

        // Get saved funds detail
        $result = file_get_contents($this->resultFilename);
        if (substr($result, 0, 4) != 'Name') {
            $header = array(
                'Name',
                'Symbol',
                'Last Updated Date',
                'Fund Type',
                'Objective',
                'Asset Class',
                'Geographic Focus',
                'Price',
                'Currency',
                'Price Method',
                'Trend Dir',
                'Trend Value',
                'Trend Percentage',
                'YTD',
                '1 Month',
                '3 Month',
                '1 Year',
                '3 Year',
                '5 Year',
                'Beta',
                'Beta Ref',
                '52 Weeks Min Range',
                '52 Weeks Max Range',
                'Days to Maturity',
                'Assets',
                'Open',
                'Volume',
                'High',
                'Low',
                'Primary Exchange',
                'Profile',
                'Inception Date',
                'Telephone',
                'Managers',
                'Web Site'
            );
            $header = implode(';', $header) . "\n";
            $result = $header . $result;
            file_put_contents($this->resultFilename, $result);
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

        $fundName = $this->getFundName();
        $fundSymbol = $this->getFundSymbol();
        $lastUpdatedDate = $this->getLastUpdatedDate();
        $exchangeType = $this->getExchangeType();
        $price = $this->getPrice();
        $priceMethod = $this->getPriceMethod($exchangeType[2]);
        $trending = $this->getTrending($exchangeType[2]);
        $snapshot = $this->getSnapshot($exchangeType);
        $profile = $this->getProfile();
        $extendedProfile = $this->getExtendedProfile();

        return array(
            $fundName,
            $fundSymbol,
            $lastUpdatedDate,
            implode(';', $exchangeType),
            implode(';', $price),
            $priceMethod,
            implode(';', $trending),
            implode(';', $snapshot),
            $profile,
            implode(';', $extendedProfile)
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
     * Function to get fund symbol
     * 
     * @return  string  $symbol Symbol of the current fund
     * @access  private
     */
    private function getFundSymbol()
    {
        // Get fund header DIV
        $header = $this->getNodesByClass(self::FUND_HEADER_CLASS, 0);

        // Get fund symbol
        $symbol = $header->getElementsByTagName('h3')->item(0)->textContent;
        return $symbol;
    }


    /**
     * Method for scraping the last updated date of the fund data
     * 
     * @return  string  A string representation of last updated date in GMT+7
     * @access  private
     */
    private function getLastUpdatedDate()
    {
        // Get last updated date string 
        $date = $this->getNodesByClass(self::DATE_CLASS, 0)->textContent;
        $date = $this->cleanText($date);

        // Remove any unwanted words
        $date = str_replace(array('As of ', 'ET on ', '.'), '', $date);

        // Extract date parts and time part
        $date = explode(' ', $date);

        // Get and reformat date part 
        $datePart = explode('/', $date[1]);
        $datePart = $datePart[2] . '-' . $datePart[0] . '-' . $datePart[1];

        // Get time part
        $timePart = $date[0];

        // Create a date object and convert timezone
        $updatedDate = new DateTime("$datePart $timePart", 
            new DateTimeZone(self::BLOOMBERG_TIMEZONE));
        $updatedDate->setTimeZone(new DateTimeZone(self::JAKARTA_TIMEZONE));

        // Return string representation of last updated date
        return $updatedDate->format('Y-m-d H:i:s');
    }


    /**
     * Method for retrieving exchange type detail
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
        // Get DIV element
        $list = $this->getNodesByClass(self::EXCHANGE_TYPE_CLASS, 0);

        // Get UL element
        $list = $list->getElementsByTagName('ul')->item(0);

        // Get all LI elements
        $list = $list->getElementsByTagName('li');

        // Extract exchange type detail
        $exchangeType = array();
        for ($i = 0; $i < $list->length; $i++) {
            $span = $list->item($i)->getElementsByTagName('span');
            // The value is contained in the second span element
            $exchangeType[] = $this->cleanText($span->item(1)->textContent);
        }

        return $exchangeType;
    }


    /**
     * Method for retrieiving fund price and currency
     * 
     * @return  array   An array that hold both price and its currency
     * @access  private
     */
    private function getPrice()
    {
        // Get price
        $price = $this->getNodesByTagClass('span', self::PRICE_CLASS, 0);
        $price = $this->cleanText($price->textContent);

        // Extract price from currency
        $priceArr = explode(' ', $price);

        // If element contains currency
        if (count($priceArr) >= 2) {
            $priceVal = $priceArr[0];
            $currency = $priceArr[1];
        }

        // If no currency is specified, we assumed it is an IDR
        else {
            $priceVal = $price;
            $currency = 'IDR';
        }

        // Convert price string into a float number
        $priceVal = (float) str_replace(',', '', $priceVal);
        
        return array($priceVal, $currency);
    }


    /**
     * Method for retrieving fund price method
     * 
     * @param   string  Asset class type of the current fund
     * @return  string  Would return a fund price method if there is any. Or an
     *                  empty string is price method is not specified
     * @access  private
     */
    private function getPriceMethod($assetClass)
    {
        // If it is not a money market class, return an empty string
        if ($assetClass != self::MONEY_MARKET_TYPE) return '';

        // Get price method
        $priceMethod = $this->getNodesByTagClass('span', 
            self::PRICE_METHOD_CLASS, 0);

        // If no price method specified, return an empty string
        if (!$priceMethod) return '';

        // Return price method
        return strtolower($this->cleanText($priceMethod->textContent));
    }


    /**
     * Function to scrape trending data
     * 
     * @param   string  $assetClass Asset class type of the current fund
     * @return  array   An array that hold trending direction, trending value
     *                  and trending percentage
     * @access  private
     */
    private function getTrending($assetClass)
    {
        // If it is a money market class, return an empty array
        if ($assetClass == self::MONEY_MARKET_TYPE) return array('', '', '');

        // If trending direction is up
        $trending = $this->getNodesByTagClass('span', 
            self::TRENDING_UP_CLASS, 0);
        if ($trending) return $this->extractTrendingVal($trending, 'up');

        // If trending direction is down
        $trending = $this->getNodesByTagClass('span', 
            self::TRENDING_DOWN_CLASS, 0);
        if ($trending) return $this->extractTrendingVal($trending, 'down');

        // If price is not changing
        $trending = $this->getNodesByTagClass('span', 
            self::TRENDING_NONE_CLASS, 0);
        if ($trending) return array('none', 0, 0);

        // No trending data
        return array('', '', '');
    }


    /**
     * Function to extract trending data
     * 
     * @param   object  $trendingNode   DOMNode obejct that hold trending data
     * @param   string  $trendingDir    Trending direction, the value should be
     *                                  'up' or 'down'
     * @return  array   An array that hold trending direction, trending value
     *                  and trending percentage
     * @access  private
     */
    private function extractTrendingVal($trendingNode, $trendingDir)
    {
        // Get trending text
        $trending = $this->cleanText($trendingNode->textContent);

        // Extract trending value and trending percentage
        $trending = explode(' ', $trending);
        $trendingVal = (double) $trending[0];
        $trendingPercent = (double) str_replace('%', '', $trending[1]);

        return array($trendingDir, $trendingVal, $trendingPercent);
    }


    /**
     * Function to scrape snapshot table
     * 
     * @param   array   $exchangeType Exchange type data
     * @return  array   An array that hold trending direction, trending value
     *                  and trending percentage
     * @access  private
     */
    private function getSnapshot($exchangeType)
    {
        // Get snapshot table
        $table = $this->getNodesByClass(self::SNAPSHOT_CLASS, 0);
        // Get table row
        $rows = $table->getElementsByTagName('tr');

        // For non money-market class, real estate class and non ETF
        if ($exchangeType[0] != self::ETF_TYPE && 
          $exchangeType[2] != self::MONEY_MARKET_TYPE &&
          $exchangeType[2] != self::REAL_ESTATE_TYPE) {
            // The first row
            $cols = $rows->item(0)->getElementsByTagName('td');
            $ytd = $this->extractPercentage($cols->item(0)->textContent);
            $month3 = $this->extractPercentage($cols->item(1)->textContent);
            $year3 = $this->extractPercentage($cols->item(2)->textContent);
            $priceRange = $this->extractRange($cols->item(3)->textContent);

            // The second row
            $cols = $rows->item(1)->getElementsByTagName('td');
            $month1 = $this->extractPercentage($cols->item(0)->textContent);
            $year1 = $this->extractPercentage($cols->item(1)->textContent);
            $year5 = $this->extractPercentage($cols->item(2)->textContent);
            $beta = $this->extractFloat($cols->item(3)->textContent);

            // Get beta reference
            $th = $rows->item(1)->getElementsByTagName('th');
            $betaRef = $this->extractBetaRef($th->item(3)->textContent);

            return array(
                $ytd, $month1, $month3, $year1, $year3, $year5, $beta, $betaRef,
                $priceRange[0], $priceRange[1], '', '',
                '', '', '', '', ''
            );
        }

        // For money market class
        elseif ($exchangeType[2] == self::MONEY_MARKET_TYPE) {
            $cols = $rows->item(0)->getElementsByTagName('td');
            $daysToMaturity = $this->extractDaysToMaturity(
                $cols->item(0)->textContent);
            $assets = $this->extractPrice($cols->item(1)->textContent);
            $priceRange = $this->extractRange($cols->item(2)->textContent);

            $cols = $rows->item(0)->getElementsByTagName('th');

            return array(
                '', '', '', '', '', '', '', '',
                $priceRange[0], $priceRange[1], $daysToMaturity, $assets,
                '', '', '', '', ''
            );
        }

        // For real estate class
        elseif ($exchangeType[2] == self::REAL_ESTATE_TYPE) {
            // The first row
            $cols = $rows->item(0)->getElementsByTagName('td');
            $open = $this->extractPrice($cols->item(0)->textContent);
            $hilo = $this->extractRange($cols->item(1)->textContent);
            $primaryExchange = $this->cleanText($cols->item(2)->textContent);

            // The second row
            $cols = $rows->item(1)->getElementsByTagName('td');
            $volume = $this->extractPrice($cols->item(0)->textContent);
            $priceRange = $this->extractRange($cols->item(1)->textContent);
            $beta = $this->extractFloat($cols->item(2)->textContent);

            // Get beta reference
            $th = $rows->item(1)->getElementsByTagName('th');
            $betaRef = $this->extractBetaRef($th->item(2)->textContent);

            return array(
                '', '', '', '', '', '', '', $betaRef,
                $priceRange[0], $priceRange[1], '', '',
                $open, $volume, $hilo[0], $hilo[1], $primaryExchange
            );
        }

        return array('', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '');
    }


    /**
     * Method for retrieiving fund profile
     * 
     * @return  string  A short profile information of the current fund
     * @access  private
     */
    private function getProfile()
    {
        // Get profile
        $profile = $this->getNodesByTagClass('p', self::PROFILE_CLASS, 0);
        return $this->cleanText($profile->textContent);
    }


    /**
     * Method for retrieiving fund extended profile
     * 
     * @return  array   An array that hold extended profile information: 
     *                  inception date, telephone, managers, and web site
     * @access  private
     */
    private function getExtendedProfile()
    {
        // Get extended profile DIV
        $profile = $this->dom->getElementById(self::EXTENDED_PROFILE_CLASS);
        // Get each column data
        $cols = $profile->getElementsByTagName('td');

        return array(
            // Inception date
            $this->extractDate($cols->item(0)->textContent),
            // Telephone
            $this->extractTelephone($cols->item(1)->textContent),
            // Managers
            $this->extractText($cols->item(2)->textContent),
            // Web site
            $this->extractHref($cols->item(3))
        );
    }


    /**
     * Method for parsing percentation value
     * 
     * @param   string  A string to be parsed
     * @return  float   Parsed value
     * @access  private
     */
    private function extractPercentage($val)
    {
        $val = $this->cleanText($val);      // Clean the text
        // If no data available, return an empty string
        if ($val == '-') return '';

        $val = str_replace('%', '', $val);  // Remove percentage
        return (float) $val;                // Convert to float
    }


    /**
     * Method for parsing float value
     * 
     * @param   string  A string to be parsed
     * @return  float   Parsed value
     * @access  private
     */
    private function extractFloat($val)
    {
        $val = $this->cleanText($val);      // Clean the text
        // If no data available, return an empty string
        if ($val == '-') return '';
        return (float) $val;                // Convert to float
    }


    /**
     * Method for parsing text value
     * 
     * @param   string  A string to be parsed
     * @return  float   Parsed value
     * @access  private
     */
    private function extractText($val)
    {
        $val = $this->cleanText($val);              // Clean the text
        // If no data available, return an empty string
        if ($val == '-') return '';
        return $val;
    }


    /**
     * Method for parsing link HREF
     * 
     * @param   object  Node item that hold anchor link element
     * @return  string  HREF in link element
     * @access  private
     */
    private function extractHref($node)
    {
        $link = $node->getElementsByTagName('a');
        // If there is no link element, return an empty string
        if ($link->length <= 0) return '';

        // Return HREF data
        return $link->item(0)->getAttribute('href');
    }


    /**
     * Method for extracting price range
     * 
     * @param   string  $priceRange Price range text
     * @return  array   An array that hold max & min of price range
     * @access  private
     */
    private function extractRange($priceRange)
    {
        $priceRange = $this->cleanText($priceRange);
        $priceRange = explode(' - ', $priceRange);
        return array(
            (float) str_replace(',', '', $priceRange[0]),
            (float) str_replace(',', '', $priceRange[1])
        );
    }


    /**
     * Method for extracting price range
     * 
     * @param   string  $title  Beta reference title
     * @return  string  Beta reference
     * @access  private
     */
    private function extractBetaRef($title)
    {
        $title = $this->cleanText($title);
        return str_replace(array('Beta vs ', ':'), '', $title);
    }


    /**
     * Method for parsing days to maturity value
     * 
     * @param   string  A string to be parsed
     * @return  string  Parsed value
     * @access  private
     */
    private function extractDaysToMaturity($val)
    {
        $val = $this->cleanText($val);              // Clean the text
        // If no data available, return an empty string
        if ($val == '-') return '';

        return $val;
    }


    /**
     * Method for parsing price value
     * 
     * @param   string  A string to be parsed
     * @return  float   Parsed value
     * @access  private
     */
    private function extractPrice($val)
    {
        $val = $this->cleanText($val);              // Clean the text
        // If no data available, return an empty string
        if ($val == '-') return '';
        return (float) str_replace(',', '', $val);  // Convert to float
    }


    /**
     * Method for extracting date
     * 
     * @param   string  $date   A date string to be parsed
     * @return  string  Formated date string: Y-m-d
     * @access  private
     */
    private function extractDate($date)
    {
        // Clean up date
        $date = $this->cleanText($date);
        // Extract date
        $datePart = explode('-', $date);
        // Re-contruct date: Y-m-d
        if (strlen($datePart[2]) >= 4) {
            $date = $datePart[2] . '-' . $datePart[0] . '-' . $datePart[1];
        }

        // Create a date object and convert timezone
        $date = new DateTime($date, new DateTimeZone(self::JAKARTA_TIMEZONE));

        // Return string representation
        return $date->format('Y-m-d');
    }


    /**
     * Method for extracting telephone
     * 
     * @param   string  $val    A string that hold telephone numbers
     * @return  string  Clean telephone number
     * @access  private
     */
    private function extractTelephone($val)
    {
        // Replace un-needed text
        $val = str_replace(array('Tel', 'Phone', ':', '+'), '', $val);
        // Clean up data
        return $this->cleanText($val);
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
        // Use xPath to query nodes by tag and class name
        $nodeList = $this->xpath->query("//{$tag}[contains(concat(' ', normalize-space(@class), ' '), ' $className ')]");

        // If there is no nodes match, return false
        if ($nodeList->length <= 0) return false;

        // If $index is not specified, return all matched nodes
        if (is_null($index)) return $nodeList;

        // If requested $index is not available, return false
        if ($index >= $nodeList->length) return false;

        // Return nodes at requested $index
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
        // Convert any whitescapeces into a normal spaces
        $text = preg_replace('/\s+/', ' ', $text);

        // Trim text
        $text = trim($text);
        return $text;
    }
}