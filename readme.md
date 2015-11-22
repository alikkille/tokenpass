## Tokenly Accounts

[![Build Status](https://travis-ci.org/tokenly/accounts.svg?branch=master)](https://travis-ci.org/tokenly/accounts)

This is public service for tokenly accounts.  It is a web application to register and authenticate user accounts for use by other tokenly services.  It is also an oAuth2 provider.

###Public API

Tokenly Accounts features various public API methods for use in third party applications. Basic documentation found below:

**Check Token Controlled Access**

* **Endpoint:** /api/v1/tca/check/{username}
* **Example URL:** https://accounts.tokenly.com/api/v1/tca/check/cryptonaut?LTBCOIN=1000
* **Authentication:** none required
* **Returns:** result (boolean)
* **Basic usage:** include a list of assets to check in your query string, in format ASSET=MIN_AMOUNT
* **Advanced usage:** for more complicated rule sets, you may include an ```op``` (logic operator) as well as a ```stackop``` (secondary logic operator) field in your query string. Append with "_0", "_1" etc. to apply different operators to different asset checks (depends on order of asset list).
* Valid ```op``` values are [==, =, !, !=, >, >= (default), <, <=] and valid ```stackop``` values are [AND (default), OR]
* **Advanced usage example:**
 * ```https://accounts.tokenly.com/api/v1/tca/check/cryptonaut?LTBCOIN=10000&op_0==&TOKENLY=1&stackop_1=OR```
 * translates to "return true if user Cryptonaut has exactly 10,000 LTBCOIN OR has at least 1 TOKENLY"
* TCA component source code: https://github.com/tokenly/token-controlled-access/blob/master/src/Tokenly/TCA/Access.php


**Code Example (PHP):**
```

$username = 'cryptonaut';
$rules = array('LTBCOIN' => 1000); //check to see if user has at least 1000 LTBCOIN
$api_url = 'https://accounts.tokenly.com/api/v1';
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

**Get Public Bitcoin Addresses**

* **Endpoint:** /api/v1/tca/addresses/{username}
* **Example URL:** https://accounts.tokenly.com/api/v1/tca/addresses/cryptonaut
* **Authentication:** none required
* **Returns:** 
 * result (array)
   * address (string)
    * balances (array)
* **Notes:** Returns a list of all **public** bitcoin addresses for the specified user, as well as each addresses' token balances. Registered addresses are private by default. Balances are given in satoshis


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
      }
    }
  ]
}

```
