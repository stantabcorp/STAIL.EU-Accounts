# STAIL.EU-Accounts
The new way to have accounts - STAIL.EU Accounts allows you to manage users with administration file, securely, and for free!

[![Latest Stable Version](https://poser.pugx.org/stan-tab-corp/staileu-accounts/v/stable)](https://packagist.org/packages/stan-tab-corp/staileu-accounts) [![Total Downloads](https://poser.pugx.org/stan-tab-corp/staileu-accounts/downloads)](https://packagist.org/packages/stan-tab-corp/staileu-accounts) [![License](https://poser.pugx.org/stan-tab-corp/staileu-accounts/license)](https://packagist.org/packages/stan-tab-corp/staileu-accounts) 

# Installation

`composer require stan-tab-corp/staileu-accounts`

```php
$stail = new \STAILEUAccounts\STAILEUAccounts("<private key>", "<public key>", false|new \STAILEUAccounts\Cache("<cache folder path>"));
```
* The first parameter is your private key
* The second parameter is your public key
* The thid parameter can be false or an instance of `\STAILEUAccounts\Cache`

The parameter in the instance of `\STAILEUAccounts\Cache` can be empty (the path will be ./.stail_cache) or you can give a folder path.

# How to 

## Login an user

To login an user, you just have to call:

```php
    $stail->login("username", "password");
```

* The first parameter is the username
* The second id the password

It will return you an instance of \STAILEUAccounts\Login, here it starts to be complex, if the user doesn't use two factor authentification, you can get the *c-sa token* by calling `getCSAToken()`, but if the user uses two factor authentification you have to get the *TFA Token*, and the code the user received on his mobile phone. To get the *TFA Token* call `getTFAToken()`.

You will ask me: How I know if a user has enabled Two Factor Authentification?

Very simple: call `isUsingTfa()` it will return you a boolean

Now, how to get the *c-sa token* from the *TFA Token*:

```php
    $tfa = new \STAILEUAccounts\LoginTFA($stail, "tfa token");
```

* The first parameter is an instance of \STAILEUAccounts\STAILEUAccounts
* The second parameter is the TFA Token

Then:
```php
    $tfa->sendTFACode("code recived by the user");
```

* The parameter is the code received by the user on his mobile phone

Now you have a *c-sa token*! Yeah! But... You will ask me: I want the user's uuid!

No problem! Before, remember that you need the c-sa token to edit the user's profile, the token expire one hour after the login.

You have to call:

```php
    $stail->check("c-sa token");
```

This beautiful function will return you `false` or the user's uuid! Ta-da!

You have login the user, now you can do whatever you want!

### Another way to login an user:

You can use our form to login an user, in the case you will just receive the *c-sa token* and you just have to get the uuid and you're done!

## Register a user
Documentation is coming soon...
## Get the login form URL
Documentation is coming soon...
## Get the register form URL
Documentation is coming soon...
## Get the password forgot form URL
Documentation is coming soon...
## Get a user's username
Documentation is coming soon...
## Get a user's uuid
Documentation is coming soon...
## Get a user's email address
Documentation is coming soon...
## Get a user's avatar
Documentation is coming soon...
## Get a user's registration date
Documentation is coming soon...
## Know if a user's email address is verified
Documentation is coming soon...
## Know if a user's phone number is verified
Documentation is coming soon...
## Verify a user's email address
Documentation is coming soon...
## Verify a user's phone number
Documentation is coming soon...
## Logout a user
Documentation is coming soon...
## Change a user's username
Documentation is coming soon...
## Change a user's password
Documentation is coming soon...
## Change a user's email address
Documentation is coming soon...
## Change a user's phone number
Documentation is coming soon...
## Change a user's avatar
Documentation is coming soon...
