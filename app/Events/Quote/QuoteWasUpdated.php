<?php
/**
 * Invoice Ninja (https://invoiceninja.com)
 *
 * @link https://github.com/invoiceninja/invoiceninja source repository
 *
 * @copyright Copyright (c) 2020. Invoice Ninja LLC (https://invoiceninja.com)
 *
 * @license https://opensource.org/licenses/AAL
 */

namespace App\Events\Quote;

use Illuminate\Queue\SerializesModels;

/**
 * Class QuoteWasUpdated.
 */
class QuoteWasUpdated
{
    use SerializesModels;
    public $quote;

    public $company;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Quote $quote, $company)
    {
        $this->quote = $quote;
        $this->company = $company;
    }
