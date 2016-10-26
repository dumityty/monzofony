<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Edcs\OAuth2\Client\Provider\Mondo;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
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
        $created = $txn->offsetGet('created');
        $date = date('l, j M', strtotime($created));
        // todo: need to sort the transactions inside the date grouping.
        $transactions[$date][] = $txn;
      }

      $transactions = array_reverse($transactions);
      return $this->render('default/index.html.twig', [
        'account_name' => $description,
        'created' => $created/100,
        'balance' => ($balance/100),
        'spent_today' => ($spent_today/100),
        'transaction_dates' => $transactions,
        'token' => $_SESSION['access_token'],
      ]);
    }

    /**
     * @Route("/login", name="login")
     */
    public function loginAction(Request $request)
    {
      $provider = new Mondo([
        'clientId'     => $this->getParameter('client_id'),
        'clientSecret' => $this->getParameter('client_secret'),
        'redirectUri'  => $this->getParameter('redirect_url'),
      ]);

      if (!isset($_GET['code'])) {
          // If we don't have an authorization code then get one
          $authUrl = $provider->getAuthorizationUrl();
          $_SESSION['oauth2state'] = $provider->getState();
          header('Location: '.$authUrl);
          exit;
        }
        // Check given state against previously stored one to mitigate CSRF attack
        elseif (empty($_GET['state']) || ($_GET['state'] !== $_SESSION['oauth2state'])) {
          unset($_SESSION['oauth2state']);
          exit('Invalid state');
        } else {
          // Try to get an access token (using the authorization code grant)
          $token = $provider->getAccessToken('authorization_code', [
            'code' => $_GET['code']
          ]);

          // Optional: Store the token in the session so we can refresh the page while we're testing
          $_SESSION['access_token'] = [
            'access_token' => $token->getToken(),
            'expires' => $token->getExpires(),
            'refresh_token' => $token->getRefreshToken(),
            'resource_owner_id' => $token->getResourceOwnerId()
          ];
          header('Location: /');
          exit;
        }

    }

}