# Lassi
Sync Users across Laravel Web apps

Laravel Auth Syncronised Sign In

## Background

In our company we have several applications across multiple sites.  The perform differnt functions are it is a definite convenience to have them across seperate domains.  Some are available to our staff members and some are available to customers and some are available to both.  We have a central application that has a customer record for everyone (this can easily be converted to a user record).  

Logging in to multiple websites was becoming a pain as I did not wish to have to make people create seperate account son each site and have to figure out who was a customer, who was a staff member and assign roles as required.  So I wrote Lassi.  It is not 0Auth and it is not SSI and many may think its not a good idea but it solves a problem I have.

## Overview

Lassi (Laravel Auth Syncronised Sign In) uses a Client - Server model to have one repository of user accounts which is requested and syncronised to the clients on a scheduled basis.

> php auth lassi:sync

Will syncronise all users that have changed since the last sync.

By default the syncronsiation process will copy the following user fields from the server to the client

- name
- email
- email_verified_at
- password
- lassi_user_id (a UUID for a lassi user)

the following will not be copied __ remember_token , created_at, updated_at , current_team_id  __

Additionally **ANY** other fields that exist in **both** the server __users__ table and the client __users__ table will be copied.

You may specify if lassi is to ignore any other fields.

## Usage

### Requirements

Currently Requires use of Sanctum tokens to allow access to lassi routes.  User with api Token must have lassi_read permission.

### Server
Firstly install Lassi on your server

> composer require tobya/lassi

then run migration and publish Lassi Vendor Files.

````
php artisan migrate
php artisan vendor:publish --tag=lassi
````


### Client

Install Lassi on your client

> composer require tobya/lassi

then run migration and publish Lassi Vendor Files.

````
php artisan migrate
php artisan vendor:publish --tag=lassi
````

Set Required Enviroment variables in .env

````
LASSI_SERVER=https://user.for.server.example.com
LASSI_TOKEN=apitokenofuseryouwishtoconnectas-musthavelassi-read-permission
````

Run Sync
````
php artisan lassi:sync
`````

This command will sync all users on the server to the client.  Running the command a second time will sync any users that have changed since the last time it was run.
