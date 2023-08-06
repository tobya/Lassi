# Lassi
Sync Users across Laravel Web apps

Laravel Auth Syncronised Sign In

## Background

In our company we have several applications across multiple sites.  They perform differnt functions and it is a definite convenience to have them split across seperate domains.  Some are available to our staff members and some are available to customers and some are available to both.  We have a central application that has a customer record for everyone (this can easily be converted to a user record).  

Logging in to multiple websites was becoming a pain as I did not wish to have to make people create seperate account son each site and have to figure out who was a customer, who was a staff member and assign roles as required.  So I wrote Lassi.  It is not 0Auth and it is not SSI and many may think its not a good idea but it solves a problem I have.

## Overview

Lassi (Laravel Auth Syncronised Sign In) uses a Client - Server model to have one repository of user accounts which is requested and syncronised to the clients on a scheduled basis.

> php artisan lassi:sync

Will syncronise all users that have changed since the last sync.

By default the syncronsiation process will copy all fields on the users table from the server to the client. Including...

- name
- email
- email_verified_at
- password
- lassi_user_id (a UUID for a lassi user)

the following will not be copied _remember_token , created_at, updated_at , current_team_id_

**ANY** fields that exist in **both** the server __users__ table and the client __users__ table will be copied.

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


## Custom Retriever 

By default the Server will return all users that have a changed (updated_at) since the date passed in.  If you want to return a different set of users, it is possible to provide a custom User Retriever.

Simply create a new class that implements the `\Lassi\Interfaces\LassiRetriever` Interface which as a single `Users` function that returns a collection of Users.

````
class GoldenRetriever implements \Lassi\Interfaces\LassiRetriever
{

    public function Users($LastSyncDate, $extradata = null){
        return Users::where('updated_at','>', $LastSyncDate)->where('email','like','%@example.com')->get();   
    }
 }
 
````

and set it it in the `config\lassi.php`

````
return [

    'server' => [
        ....
        'retriever' =>  App\Classes\GoldenRetriever::class,
        
        ],
````

### Adding additional fields 

Sometimes it may be desired to provide additional information with the user being retrieved.  eg.  perhaps there is an internal id that is retrieved from a seperate table.  This can be added at this stage.


````
class GoldenRetriever implements \Lassi\Interfaces\LassiRetriever
{

    public function Users($LastSyncDate, $extradata = null){
        $RetrievedUsers = Users::where('updated_at','>', $LastSyncDate)->where('email','like','%@example.com')->get();   
        $RetrievedUsers = $RetrievedUsers->map(function($user){
          $user->specialid = $user->specialModel->getid();
          return $user;
        });
        return $RetrievedUsers;
    }
 }
 
````

## Custom Setter

By default Lassi will create a user in the client for every user returned.  If you wish to choose if a particular user should be created, then you can implement the Custom Setter Interface `\Lassi\Interfaces\LassiSetter`

## Custom Rete Limiter

v2  If you need to set a rate limit on how many requests are made to the server when undating a large number of jobs simply create a rate limiter called `lassi-updates` and it will be applied.

