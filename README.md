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
