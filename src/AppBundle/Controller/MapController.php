<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class MapController extends Controller
{
    /**
     * @Route("/map")
     */
    public function mapAction()
    {
        // todo: do this dynamically with including controllers:
        // http://symfony.com/doc/2.0/book/templating.html#embedding-controllers

        // todo: use the refresh token to get a new access token?
        if (!isset($_SESSION['access_token']) || (time() > $_SESSION['access_token']['expires'])) {
            return $this->redirectToRoute('login');
        }

        $client = new \GuzzleHttp\Client([
          'base_uri' => 'https://api.monzo.com',
          'headers'  => [
            'Authorization' => 'Bearer ' . $_SESSION['access_token']['access_token'],
          ],
        ]);
        $client = new \Http\Adapter\Guzzle6\Client($client);

        // need a better way to handle this
        // maybe middlewares?
        try {
            $accounts = new \Edcs\Mondo\Resources\Accounts($client);
            $collection = $accounts->get();
        } catch (\Exception $e) {
            if ($e->getCode() == 401) {
                return $this->redirectToRoute('login');
            }
        }

        $account = $collection[0];
        $account_id = $account->getId();
        $description = $account->getDescription();
        $created = $account->getCreated();

        $balance = new \Edcs\Mondo\Resources\Balances($client);
        $entity = $balance->get($account_id);
        $balance = $entity->getBalance();
        $spent_today = $entity->getSpendToday();

        $collection = new \Edcs\Mondo\Resources\Transactions($client);
        $transactions_collection = $collection->get($account_id);
        foreach ($transactions_collection as $txn) {
          $transactions[] = $txn;
        }
        $transactions = array_reverse($transactions);

        return $this->render('default/map.html.twig', array(
          'account_name' => $description,
          'created' => $created/100,
          'balance' => ($balance/100),
          'transactions' => $transactions,
          'token' => $_SESSION['access_token'],
        ));
    }

}
