# Tokenpass

[![Build Status](https://travis-ci.org/tokenly/tokenpass.svg?branch=master)](https://travis-ci.org/tokenly/tokenpass)

[![Coverage Status](https://coveralls.io/repos/github/tokenly/tokenpass/badge.svg?branch=master)](https://coveralls.io/github/tokenly/tokenpass?branch=master)

Global user accounts service powering the [Tokenly](https://tokenly.com) ecosystem.  
Features bitcoin address proof-of-ownership and a "Token Controlled Access" API, allowing applications to grant user access or permissions based on the contents of their bitcoin wallets (e.g [Counterparty tokens](https://counterparty.io)).

#API Documentation

For API docs, **[click here](https://apidocs.tokenly.com/tokenpass/)**.

# Local Development

## Running a local copy

```bash
git clone https://github.com/tokenly/tokenpass.git
cd tokenpass
cp .env.example .env
composer install
```

After the ```.env``` file is set up, run ```php artisan migrate```. 


## Run Tests for Development

```bash
git clone https://github.com/tokenly/tokenpass.git
cd tokenpass
composer install
./vendor/bin/phpunit
```
