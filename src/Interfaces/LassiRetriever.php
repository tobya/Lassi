<?php


namespace Lassi\Interfaces;


interface LassiRetriever
{
    public function Users($LastSyncDate, $extradata = null);

    

    public function User($lassiuserid);
}
