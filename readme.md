# Tokenpass

[![Build Status](https://travis-ci.org/tokenly/tokenpass.svg?branch=master)](https://travis-ci.org/tokenly/tokenpass)

[![Coverage Status](https://coveralls.io/repos/github/tokenly/tokenpass/badge.svg?branch=master)](https://coveralls.io/github/tokenly/tokenpass?branch=master)

Global user accounts service powering the [Tokenly](https://tokenly.com) ecosystem.  
Features bitcoin address proof-of-ownership and a "Token Controlled Access" API, allowing applications to grant user access or permisions based on the contents of their bitcoin wallets (e.g [Counterparty tokens](https://counterparty.io)).

##Tokenpass API

In order to allow users to login to your application using their Tokenpass account, you first need to register the application and obtain a pair of API keys. Most available API methods require at least a ```client_id```. 

Register and sign in to your Tokenpass account here: https://tokenpass.tokenly.com

Once signed in, go to the "API Keys / My Apps" link in the sidebar. Here you can register applications and manage your API keys.

If your application is built in PHP, you may use the [Accounts-Client](https://github.com/tokenly/accounts-client) class for easier implementation.

See documentation below:

###TCA API

**Check Token Controlled Access [User]**

* **Endpoint:** /api/v1/tca/check/{username}
* **Request Method:** GET
* **Example URL:** https://tokenpass.tokenly.com/api/v1/tca/check/cryptonaut?LTBCOIN=1000&client_id={CLIENT_API_ID}
* **Authentication:** must pass in valid application ```client_id```
* **Returns:** result (boolean)
* **Basic usage:** include a list of assets to check in your query string, in format ASSET=MIN_AMOUNT
* **Advanced usage:** for more complicated rule sets, you may include an ```op``` (logic operator) as well as a ```stackop``` (secondary logic operator) field in your query string. Append with "_0", "_1" etc. to apply different operators to different asset checks (depends on order of asset list).
* Valid ```op``` values are [==, =, !, !=, >, >= (default), <, <=] and valid ```stackop``` values are [AND (default), OR]
* **Advanced usage example:**
 * ```https://tokenpass.tokenly.com/api/v1/tca/check/cryptonaut?LTBCOIN=10000&op_0==&TOKENLY=1&stackop_1=OR```
 * translates to "return true if user Cryptonaut has exactly 10,000 LTBCOIN OR has at least 1 TOKENLY"
* TCA component source code: https://github.com/tokenly/token-controlled-access/blob/master/src/Tokenly/TCA/Access.php
* Any user you query must have authenticated with your client application at least once, with the "tca" scope applied.


**Code Example (PHP):**
```

$username = 'cryptonaut';
$rules = array('LTBCOIN' => 1000); //check to see if user has at least 1000 LTBCOIN
$api_url = 'https://tokenpass.tokenly.com/api/v1';
$call = file_get_contents($api_url.'/tca/check/'.$username.'?'.http_build_query($rules));
$decode = json_decode($call, true);

if($decode['result']){
  //user has correct amount of tokens, give them access to something
}
else{
  //user does not meet token requirements, do something else
}

```

-------------------------------

**Get User Public Bitcoin Addresses**

* **Endpoint:** /api/v1/tca/addresses/{username}
* **Request Method:** GET
* **Example URL:** https://tokenpass.tokenly.com/api/v1/tca/addresses/cryptonaut?client_id={CLIENT_API_ID}
* **Authentication:** must pass in valid application ```client_id```
* **Returns:** 
 * result (array)
    * address (string)
    * balances (array)
    * public (boolean)
    * label (string)
    * verified (boolean) *
    * active (boolean) *
* **Notes:** Returns a list of all **public** bitcoin addresses for the specified user, as well as each addresses' token balances. Registered addresses are private by default. Balances are given in satoshis
* Any user you query must have authenticated with your client application at least once, with the "tca" scope applied.
* If the user has the scope "private-address" applied to their client connection, non-public addresses may be shown to you. 
* * ```verified``` and ```active``` are only included when interacting using a user ```oauth_token``` (i.e, requires user authentication)
* Add ```/refresh``` to the request to force token balances to update.


**Example Response**

```
{
  "result": [
    {
      "address": "15fx1Gqe4KodZvyzN6VUSkEmhCssrM1yD7",
      "balances": {
        "LTBCOIN": "4243876235088",
        "LTBONEHUNDRED": "100000000",
        "TOKENLY": "0",
        "XCP": "0"
      },
      "public": true,
      "label": "test address"
    }
  ]
}

```
-------------------------------


**Check Address Token Controlled Access**

* **Endpoint:** /api/v1/tca/check-address/{address}
* **Request Method:** GET
* **Example URL:** https://tokenpass.tokenly.com/api/v1/tca/check/1DB3rtNQ8WkriAK225bktuxSYAmhSxndJe?LTBCOIN=1000&sig={SIGNED_MESSAGE}
* **Authentication:** must pass in a ```sig``` field containing a signed message of the first 10 characters in the requested bitcoin address, from said address. e.g 1DB3rtNQ8W
* **Returns:** result (boolean)
* **Basic usage:** include a list of assets to check in your query string, in format ASSET=MIN_AMOUNT
* **Advanced usage:** see user-based TCA check method above
* This method **does not** need a registered client ID or prior user authentication.

-------------------------------

###Address Management API

**Get Bitcoin Address Details**

* **Endpoint:** /api/v1/tca/addresses/{username}/{address}
* **Request Method:** GET
* **Example URL:** https://tokenpass.tokenly.com/api/v1/tca/addresses/cryptonaut/1DB3rtNQ8WkriAK225bktuxSYAmhSxndJe?client_id={CLIENT_ID}&oauth_token={USER_AUTH_TOKEN}
* **Authentication:** Valid application client ID, tca scope must be applied, private-address scope to view private address details. OAuth token for viewing unverified or inactive address & obtaining verification code.
* **Returns:** 
  * result
    * type (string)
    * address (string
    * label (string)
    * public (boolean) 
    * active (boolean) \*
    * verified (boolean) \*
    * verify_code (string) \*
    * balances (Array)
* **Notes:** gives you details on a specific users' address, including token balances. 
* *only included when using oauth_token

---------------------------

**Register Bitcoin Address**

* **Endpoint:** /api/v1/tca/addresses
* **Request Method:** POST
* **Example URL:** https://tokenpass.tokenly.com/api/v1/tca/addresses?client_id={CLIENT_ID}&oauth_token={USER_AUTH_TOKEN}
* **Parameters:**
  * address (string, required)
  * label (string)
  * public (boolean)
  * active (boolean)
  * type (string, default "btc")
* **Authentication:** Valid application client ID, OAuth access token
* **Returns:** 
  * result
    * type (string)
    * address (string
    * label (string)
    * public (boolean) 
    * active (boolean) 
    * verified (boolean) 
    * verify_code (string) 
* **Notes:** Registers a new bitcoin address in the system. An address must be verified via proof-of-ownership before it can be used for Token Controlled Access features.

---------------------------

**Verify Address**

* **Endpoint:** /api/v1/tca/addresses/{username}/{address}
* **Request Method:** POST
* **Example URL:** https://tokenpass.tokenly.com/api/v1/tca/addresses/cryptonaut/1DB3rtNQ8WkriAK225bktuxSYAmhSxndJe?client_id={CLIENT_ID}&oauth_token={USER_AUTH_TOKEN}
* **Parameters:**
  * signature (string, required)
* **Authentication:** Valid application client ID, OAuth access token
* **Returns:** 
  * result (boolean)
* **Notes:** signature should be a signed message ```verify_code``` from the desired bitcoin address

---------------------------

**Update Address Details**

* **Endpoint:** /api/v1/tca/addresses/{username}/{address}
* **Request Method:** PATCH
* **Example URL:** https://tokenpass.tokenly.com/api/v1/tca/addresses/cryptonaut/1DB3rtNQ8WkriAK225bktuxSYAmhSxndJe?client_id={CLIENT_ID}&oauth_token={USER_AUTH_TOKEN}
* **Parameters:**
  * label (string)
  * public (boolean)
  * active (boolean)
* **Authentication:** Valid application client ID, OAuth access token
* **Returns:** same info as get address details


---------------------------

**Delete Address**

* **Endpoint:** /api/v1/tca/addresses/{username}/{address}
* **Request Method:** DELETE
* **Example URL:** https://tokenpass.tokenly.com/api/v1/tca/addresses/cryptonaut/1DB3rtNQ8WkriAK225bktuxSYAmhSxndJe?client_id={CLIENT_ID}&oauth_token={USER_AUTH_TOKEN}
* **Authentication:** Valid application client ID, OAuth access token
* **Returns:** 
  * result (boolean)
* **Notes:** Removes registered bitcoin address from the users' account

---------------------------

**Lookup Bitcoin Address by Username**

* **Endpoint:** /api/v1/lookup/user/{username}
* **Request Method:** GET
* **Example URL:** https://tokenpass.tokenly.com/api/v1/lookup/user/cryptonaut?client_id={CLIENT_ID}
* **Authentication:** Valid application client ID
* **Returns:**
  * result (array)
    * username (string)
    * address (string)

---------------------------

**Lookup Username by Bitcoin Address**

* **Endpoint:** /api/v1/lookup/address/{address}
* **Request Method:** GET
* **Example URL:** https://tokenpass.tokenly.com/api/v1/lookup/address/1DB3rtNQ8WkriAK225bktuxSYAmhSxndJe?client_id={CLIENT_ID}
* **Authentication:** Valid application client ID
* **Returns:**
  * result (array)
    * username (string)
    * address (string)


---------------------------

**Instant Address Registration + Verification**

* **Endpoint:** /api/v1/instant-verify/{username}
* **Request Method:** POST
* **Example URL:** https://tokenpass.tokenly.com/api/v1/instant-verify/cryptonaut?msg={MSG}&address={BITCOIN_ADDRESS}&sig={SIGNATURE}
* **Authentication:** None needed
* **URL Parameters:** 
  * msg - secret verification message, obtained from QR code on Tokenpass inventory page
  * address - the bitcoin address you want to register/verify
  * sig - signature of the secret message
* **Returns:**
  * result (boolean)
* **Notes:** Scanning the QR code on the Tokenpass inventory page gives you the full endpoint URL including the ```msg``` variable. Simply append the ```address``` and ```sig``` parameters to this URL and then make a POST request to it to complete verification. The user's web browser will automatically refresh upon completion to reflect the registration.
* The ```msg``` variable can be obtained in two other ways besides QR code. 1)  (for browser extensions) The HTML element surrounding the QR image on the Token Inventory page has the id ```#instant-address-qr```, which contains the attribute ```data-verify-message```. 2) If a Tokenpass user has authenticated with your app, ```msg``` is just a sha256 hash of the users' ```uuid``` 

---------------------------

###Provisional Transactions API

Provisional transactions (a.k.a "token promises") are a feature
which allows an application to provide instant Token Controlled Access functionality
to a user, without requiring them to wait until the tokens are actually delivered and confirmed
in their bitcoin wallets. Applications register "source addresses" to a whitelist using a proof-of-ownership
method similar to what end users experience. The source address is then considered trusted and 
transactions can be promised to destination addresses, either expiring after a period of time or updated to match
the real corresponding bitcoin transaction. Source addresses can only promise amounts up to their current token balance. 
0-conf transactions are automatically registered as promises and cleared when confirmed.

**Register Source Address to Whitelist**

* **Endpoint:** /api/v1/tca/provisional/register
* **Request Method:** POST
* **Authentication:** Valid application client ID
* **Parameters:** 
  * address - bitcoin source address
  * proof - verification message signed by source address
  * assets (optional) - comma separated list or array of assets to restrict this address for promises
* **Returns:**
  * result (boolean)
* **Notes:** The ```proof``` parameter should be a signature of the following message: ```<btc_address>_<sha256 hash of client_id>```
* Resubmit request with proof again to update list of restricted assets (or leave null to allow all)

---------------------------

**Delete Source Address from Whitelist**

* **Endpoint:** /api/v1/tca/provisional/{address}
* **Request Method:** DELETE
* **Authentication:** Valid application client ID
* **Returns:**
  * result (boolean)

---------------------------

**List Whitelisted Source Addresses**

* **Endpoint:** /api/v1/tca/provisional
* **Request Method:** GET
* **Authentication:** Valid application client ID
* **Returns:**
  * result (boolean)
  * proof_suffix (string)
  * whitelist (array)
    * (array)
        * address (string)
        * assets (null|array)
* **Notes:** ```proof_suffix``` value can be appended to a bitcoin address to produce the required proof-of-ownership message for registering to provisional whitelist.

---------------------------

**Manually Submit Provisional Transaction**

* **Endpoint:** /api/v1/tca/provisional/tx
* **Request Method:** POST
* **Authentication:** Valid application client ID
* **Parameters:** 
  * source - source address, must be on provisional whitelist
  * destination - destination bitcoin address
  * asset - token to promise
  * quantity - amount, in satoshis
  * expiration - timestamp when this promise expires
  * txid (optional) - txid if the bitcoin transaction has been generated/sent
  * fingerprint (optional) - tx fingerprint from xchain, if applicable
  * ref (optional) - extra optional reference data
* **Returns:**
  * result (boolean)
  * tx (array)
    * source (string)
    * destination (string)
    * asset (string)
    * quantity (integer)
    * fingerprint (string)
    * txid (string)
    * ref (string)
    * expiration (unix timestamp)
    * created_at (timestamp)
    * updated_at (timestamp)
    * promise_id (integer)
* **Notes:** if a real txid/fingerprint is set, tokenpass will automatically remove this provisional tx after 2 confirmations (or when expire hits).
* Cannot submit promise transactions which would exceed source addresses real balance

---------------------------

**List Provisional Transactions**

* **Endpoint:** /api/v1/tca/provisional/tx
* **Request Method:** GET
* **Authentication:** Valid application client ID
* **Returns:**
  * result (boolean)
  * list (array)
    * (array)
        * source (string)
        * destination (string)
        * asset (string)
        * quantity (integer)
        * fingerprint (string)
        * txid (string)
        * ref (string)
        * expiration (unix timestamp)
        * created_at (timestamp)
        * updated_at (timestamp)
        * promise_id (integer)
* **Notes:** Shows all provisional transactions associated with your API client_id

---------------------------

**Get Provisional Transaction**
* **Endpoint:** /api/v1/tca/provisional/tx/{promise_id|txid|fingerprint}
* **Request Method:** GET
* **Authentication:** Valid application client ID
* **Returns:**
  * result (boolean)
  * tx (array)
    * source (string)
    * destination (string)
    * asset (string)
    * quantity (integer)
    * fingerprint (string)
    * txid (string)
    * ref (string)
    * expiration (unix timestamp)
    * created_at (timestamp)
    * updated_at (timestamp)
    * promise_id (integer)
    
---------------------------
    
**Delete Provisional Transaction**
* **Endpoint:** /api/v1/tca/provisional/{promise_id|txid|fingerprint}
* **Request Method:** DELETE
* **Authentication:** Valid application client ID
* **Returns:**
  * result (boolean)
  
  
---------------------------

**Update Provisional Transaction**
* **Endpoint:** /api/v1/tca/provisional/tx/{promise_id|txid|fingerprint}
* **Request Method:** PATCH
* **Authentication:** Valid application client ID
* **Parameters:** 
  * quantity (optional) - amount, in satoshis
  * expiration (optional) - timestamp when this promise expires
  * txid (optional) - txid if the bitcoin transaction has been generated/sent
  * fingerprint (optional) - tx fingerprint from xchain, if applicable
  * ref (optional) - extra optional reference data
* **Returns:**
  * result (boolean)
  * tx (array)
    * source (string)
    * destination (string)
    * asset (string)
    * quantity (integer)
    * fingerprint (string)
    * txid (string)
    * ref (string)
    * expiration (unix timestamp)
    * created_at (timestamp)
    * updated_at (timestamp)
    * promise_id (integer)
    
---------------------------
  

# Local Development

## Running a local copy

```bash
git clone https://github.com/tokenly/tokenpass.git
cd tokenpass
cp .env.example .env
```


## Run Tests for Development

```bash
git clone https://github.com/tokenly/tokenpass.git
cd tokenpass
composer install
./vendor/bin/phpunit
```
