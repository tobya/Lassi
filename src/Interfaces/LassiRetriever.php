<?php


namespace Lassi\Interfaces;


interface LassiRetriever
{
    public function Users($LastSync_StartDate,$LastSync_EndDate, $extradata = null);



    public function User($lassiuserid);
}
