<?php
/**
 * SavePages class - A class for saving Bloomberg pages
 * 
 * @author  Risan Bagja Pradana <risanbagja@yahoo.com>
 * @package BloombergScarper
 * @version 1.0
 */
namespace BloombergScraper;

class SavePages
{
    /**
     * Method to save Indonesia funds list pages
     * 
     * @param   int     $totalPages Total pages to be saved
     * @return  int     Number of pages saved
     * @access  public
     */
    public function saveFundsList($totalPages)
    {
        /* Pages saved counter */
        $pagesSaved = 0;

        /* Loop through each asked pages */
        for ($i = 1; $i <= $totalPages; $i++) {
            /* Determine funds list URL */
            $url = FUNDS_LIST_URL;
            if ($i > 1) $url .= $i + '/';   // Append page number starting from page-2

            /* Get the page */
            $html = file_get_contents($url);

            /* If page scraped successfully, save it! */
            if ($html !== false) {
                $filename = FUNDS_LIST_PAGES_DIR . $i . SAVED_PAGE_EXT; // Target file
                file_put_contents($filename, $html);                    // Save the page
                $pagesSaved++;                                          // Increase counter
            }
        }
        
        /* Return number of pages saved */
        return $pagesSaved;
    }


    /**
     * Function to save fund detail web page
     * 
     * @param   int     $totalPages Total pages to be saved
     * @return  int     Number of pages to be saved
     * @access  public
     */
    public function saveFundPage($totalPages)
    {
        /* Pages saved counter */
        $pagesSaved = 0;

        /* Extract each funds list item */
        $fundsList = file_get_contents(FUNDS_LIST_FILE);
        $fundsList = explode("\n", $fundsList);

        /* Loop through each asked pages */
        for ($i = 0; $i < $totalPages; $i++) {
            /* Extract each column */
            $list = explode(',', $fundsList[$i]);

            /* Get fund url */
            $url = $list[4];

            /* Get fund web page */
            $html = file_get_contents($url);

            /* If page scraped successfully, save it! */
            if ($html !== false) {
                $filename = FUNDS_DETAILS_PAGES_DIR . $i . SAVED_PAGE_EXT;  // Target file
                file_put_contents($filename, $html);                        // Save the page
                $pagesSaved++;                                              // Increase counter
            }
        }

        /* Return number of pages saved */
        return $pagesSaved;
    }
}