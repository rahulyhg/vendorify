<?php

namespace App\Libraries;

use OAuth;
use OAuth\OAuth2\Token\StdOAuth2Token;
use App\Vendor;

class Google
{
    
    /**
     * @var string $token
     */
    private $token;

    /**
     * default constructor
     */
    public function __construct($token) {
        $this->token = $token;
    }

    /**
     * connect
     *  - connect google account to vendor admin
     *
     * @param string $vendor_id
     * @param string $code
     * @return void
     */
    public function connect($vendor_id, $code) {
        // get google service
        $googleService = OAuth::consumer('Google', env('GOOGLE_REDIRECT_URL'));

        // check if code is valid

        // if code is provided get user data and sign in
        if (!is_null($code)) {

            // This was a callback request from google, get the token
            $token = $googleService->requestAccessToken($code);

            // Send a request with it
            $userinfo = json_decode($googleService->request('https://www.googleapis.com/oauth2/v1/userinfo'), true);

            // store / update token
            $vendor = Vendor::find($vendor_id);
            $vendor->google_email = $userinfo['email'];
            $vendor->google_token = $token->getAccessToken();
            $vendor->touch();

            // redirect
            return redirect('/admin');
        }
        
        // if not ask for permission first
        else {
            // get googleService authorization
            $url = $googleService->getAuthorizationUri();

            // return to google login url
            return redirect((string)$url);
        }
    }

    /**
     * disconnect
     *  - disconnect a google account (clears pending vendors)
     *
     * @param int $vendor_id
     * @return boolean
     */
    public function disconnect($vendor_id) {

        $user = Vendor::find($vendor_id);
        $user->google_token = '';
        $user->google_email = '';
        $user->touch();

        Vendor::where('status','pending')->delete();

        return true;
    }

    /** 
     * syncContacts
     *  - 
     * 
     * @return void
     */
    public function syncContacts() {
       
        $token_interface = new StdOAuth2Token($this->token);
        $google = OAuth::consumer('Google');
        $token = $google->getStorage()->storeAccessToken('Google', $token_interface);

        // get google service
        $googleService = OAuth::consumer('Google');

        // Send a request with it
        $response = $googleService->request('https://www.google.com/m8/feeds/contacts/default/full?alt=json&max-results=500');

        $result = json_decode($response, true);

        // Going through the array to clear it and create a new clean array with only the email addresses
        $vendors = []; // initialize the new array
        foreach ($result['feed']['entry'] as $contact) {

            if (isset($contact['gd$email'])) { // Sometimes, a contact doesn't have email address
                $vendor = Vendor::firstOrNew(array('email' => $contact['gd$email'][0]['address']));
                $vendor->phone = isset($contact['gd$phoneNumber']) ? $contact['gd$phoneNumber'][0]['$t'] : '';
                $vendor->name = $contact['title']['$t'];
                $vendor->business = isset($contact['content']) ? $contact['content']['$t'] : '';
                $vendor->touch();

                array_push($vendors, $vendor);
            }
        }
        
        return $vendors;
       
    }

}