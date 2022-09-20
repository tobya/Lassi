<?php

namespace Lassi\Interfaces;

interface LassiMapper
{
    /**
     * Return updated mapped user. $user will already be populated with matching fields.  Map function can overwrite
     * if desired.
     * @param $lassiuser
     * @param $user
     * @return mixed
     */
    public function map($lassiuser, $user);


}
