## Tokenly Accounts

[![Build Status](https://travis-ci.org/tokenly/accounts.svg?branch=master)](https://travis-ci.org/tokenly/accounts)

This is public service for tokenly accounts.  It is a web application to register and authenticate user accounts for use by other tokenly services.  It is also an oAuth2 provider.

###Public API

Tokenly Accounts features various public API methods for use in third party applications. Basic documentation found below:

**Token Controlled Access API Methods:**

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



