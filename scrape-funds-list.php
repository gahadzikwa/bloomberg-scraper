<?php
set_time_limit(0);
require_once('classes/FundsListScraper.php');

$scraper = new FundsListScraper();
$scraper->scrape()->save('data/funds-list.json'); ?>

<p>
    <strong>Total Funds Scraped:</strong>
    <?php echo $scraper->getTotalFunds(); ?>
</p>
<p>
    <a href="<?php echo $scraper->getFilename(); ?>">
        Result Data
    </a>
</p>