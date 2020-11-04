<?php
/**
 * Invoice Ninja (https://invoiceninja.com).
 *
 * @link https://github.com/invoiceninja/invoiceninja source repository
 *
 * @copyright Copyright (c) 2020. Invoice Ninja LLC (https://invoiceninja.com)
 *
 * @license https://opensource.org/licenses/AAL
 */

namespace App\Repositories;

use App\Factory\ClientFactory;
use App\Models\Client;
use App\Repositories\ClientContactRepository;
use App\Utils\Traits\GeneratesCounter;
use App\Utils\Traits\SavesDocuments;
use Illuminate\Http\Request;

/**
 * ClientRepository.
 */
class ClientRepository extends BaseRepository
{
    use GeneratesCounter;
    use SavesDocuments;

    /**
     * @var ClientContactRepository
     */
    protected $contact_repo;

    /**
     * ClientController constructor.
     * @param ClientContactRepository $contact_repo
     */
    public function __construct(ClientContactRepository $contact_repo)
    {
        $this->contact_repo = $contact_repo;
    }
    
    /**
     * Saves the client and its contacts.
     *
     * @param array $data The data
     * @param Client $client The client
     *
     * @return     Client|Client|null  Client Object
     *
     * @throws \Laracasts\Presenter\Exceptions\PresenterException
     * @todo       Write tests to make sure that custom client numbers work as expected.
     */
    public function save(array $data, Client $client) : ?Client
    {
        $client->fill($data);

        $client->save();

        if ($client->id_number == '' || ! $client->id_number) {
            $client->id_number = $this->getNextClientNumber($client);
        }

        $client->save();

        $this->contact_repo->save($data, $client);

        if (empty($data['name'])) {
            $data['name'] = $client->present()->name();
        }

        //info("{$client->present()->name} has a balance of {$client->balance} with a paid to date of {$client->paid_to_date}");

        if (array_key_exists('documents', $data)) {
            $this->saveDocuments($data['documents'], $client);
        }

        return $client;
    }

    /**
     * Store clients in bulk.
     *
     * @param array $client
     * @return Client|null
     */
    public function create($client): ?Client
    {
        return $this->save(
            $client,
            ClientFactory::create(auth()->user()->company()->id, auth()->user()->id)
        );
    }
}