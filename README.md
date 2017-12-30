<p align="center"><img src="https://cdn.stail.eu/accounts/logo-stail-small.png"></p>

<p align="center">
<a href="https://packagist.org/packages/stan-tab-corp/staileu-accounts"><img src="https://poser.pugx.org/stan-tab-corp/staileu-accounts/v/stable" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/stan-tab-corp/staileu-accounts"><img src="https://poser.pugx.org/stan-tab-corp/staileu-accounts/downloads" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/stan-tab-corp/staileu-accounts"><img src="https://poser.pugx.org/stan-tab-corp/staileu-accounts/license" alt="License"></a>
<a href="https://discord.gg/hQnY3jP"><img src="https://discordapp.com/api/guilds/365929044442873898/widget.png" alt="Discord Server"></a>
</p>

# About STAIL.EU Accounts
STAIL.EU Accounts is an account manager, that allows us to manage user without having to store sensitive data! You have all control other your users. Another main feature is that a user only needs one account to access multiple websites like yours! As we store all data, you don't have to do any administration.

STAIL.EU Accounts is 100% free and it will be forever!

STAIL.EU Accounts is the new way to have accounts!

* Our website: https://stail.eu
* Our discord server: https://discord.gg/hQnY3jP


# Documentation

## Installation

`composer require stan-tab-corp/staileu-accounts`

```php
$stail = new \STAILEUAccounts\STAILEUAccounts("<private key>", "<public key>", false|new \STAILEUAccounts\Cache("<cache folder path>"));
```
* The first parameter is your private key
* The second parameter is your public key
* The thid parameter can be false or an instance of `\STAILEUAccounts\Cache`

The parameter in the instance of `\STAILEUAccounts\Cache` can be empty (the path will be ./.stail_cache) or you can give a folder path.

## How to 

### Login an user

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

#### Another way to login an user:

You can use our form to login an user, in the case you will just receive the *c-sa token* and you just have to get the uuid and you're done!

### Register a user
In order to register a user call:
```php
    $stail->register("username", "password", "email"|null, "phone number"|null, "ip");
```

* The first parameter is the username
* The second parameter his the password
* The third parameter is the user email address, this filed is *not required*, please set it at `null` if no email address is given
* The fourth one is the phone number, this field is *not required*, if empty please set it to `null`
* The fifth is the user's ip\*

This function will return a c-a token, in order to get the uuid, call
```php
    $stail->check("c-sa token");
```

\* Why we are getting the user's IP? 
1. There are many reasons why. First of all, if the user lost his password, and if he didn't give a phone number or an email address, he can send us an email, with the ip he adds when he registered, his username, and a copy of his identity card.
2. We are making statistics, which are done at the end of each month, with the country of origin and the user's language.
3. We are also counting how many user get registered with the same ip to check if there is not bot spamming.

### Get the login form URL
If your website doesn't have an SSL certificate you have to use our form!
To get the login form URL please call:

```php
    $stail->loginForm("callback url");
```

* The parameter needs to be the URL the user will be redirected to when he completes the login process.

### Get the register form URL
If your website doesn't have an SSL certificate you have to use our form!
To get the registration form url please call:

```php
    $stail->registerForm("callback url");
```

* The parameter needs to be the URL the user will be redirected to when he complete the registration process.

### Get the password forgot form URL
If your website doesn't have an SSL certificate you have to use our form!
To get the password forgot form url please call:

```php
    $stail->forgotForm("callback url");
```

* The parameter needs to be the URL the user will be redirected to when he complete the password reset process.

### Get a user's username
You have the uuid but you want his username? **NO PROBLEM!**
Just call:
```php
    $stail->getUsername("uuid");
```

* The parameter is the user's uuid

### Get a user's uuid
You have the user's username but you want his uuid?
Just call:
```php
    $stail->getUUID("username");
```

* The parameter is the user's username

### Get a user's email address
You have the user's uuid but you want his email address?
Just call:
```php
    $stail->getEmail("uuid");
```

* The parameter is the user's uuid

### Get a user's avatar
You have the user's uuid but you want his avatar?
Just call:
```php
    $stail->getAvatar("uuid");
```

* The parameter is the user's uuid

This function will return you an instance of STAILEUAccounts\Avatar. To get the avatar in base64 call `getBase64()` if you want the URL call `getUrl()`

### Get a user's registration date
You have the user's uuid but you want his registration date?
Just call:
```php
    $stail->getRegistrationDate("uuid");
```

* The parameter is the user's uuid

### Know if a user's email address is verified
I know you will ask me to check if an email address is verified correct?
So... simply call:

```php
    $stail->isEmailAddressVerified("uuid");
```

* The parameter is the user's uuid
* The function will return you a boolean

### Know if a user's phone number is verified
Again? But the phone number correct?
So...

```php
    $stail->isPhoneNumberVerified("uuid");
```

* The parameter is the user's uuid
* The function will return you a boolean

### Verify a user's email address
Ok ok, now you know that the user didn't verify his email address, so you want to resend a demand

```php
    $stail->verifyEmailAddress("uuid");
```

* The parameter is the user's uuid

### Verify a user's phone number
Ok, the same thing with the phone number?

```php
    $stail->verifyPhoneNumber("uuid");
```

* The parameter is the user's uuid

### Logout a user
Now we will learn how to log out a user. This will result in a deletion of the c-sa token.

```php
    $stail->logout("c-sa");
```

* The parameter is the c-sa token

### All in one!
There is one more function, and this one is pretty cool!
You can gather all the user data with only one request!

```php
    $stail->getUser("uuid"):
```

* The parameter is the user's uuid

### Change a user's username
Now we will begin in a very special section, we will edit the user's profile. Note that if an app is modifying a user profile without the account owner authorization, the app will be deleted and the website banned!

In order to change the username call:

```php
    $stail->changeUsername("username", "uuid", "c-sa");
```

* The first parameter is the new username
* The second parameter is the user's uuid
* The third parameter is the c-sa token

### Change a user's password
In order to change the password call:

```php
    $stail->changePassword("password", "uuid", "c-sa");
```

* The first parameter is the new password
* The second parameter is the user's uuid
* The third parameter is the c-sa token

### Change a user's email address
In order to change the email address call:

```php
    $stail->changeEmail("email address", "uuid", "c-sa");
```

* The first parameter is the new email address
* The second parameter is the user's uuid
* The third parameter is the c-sa token

### Change a user's phone number
In order to change the phone number call:

```php
    $stail->changeNumber("phone number", "uuid", "c-sa");
```

* The first parameter is the new phone number
* The second parameter is the user's uuid
* The third parameter is the c-sa token

### Change a user's avatar
In order to change the avatar call:

```php
    $stail->changeAvatar("avatar url", "uuid", "c-sa");
```

* The first parameter is the new avatar URL (It must be accessible from outside)
* The second parameter is the user's uuid
* The third parameter is the c-sa token
