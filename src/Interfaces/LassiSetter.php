<?php


namespace Lassi\Interfaces;


use Illuminate\Contracts\Auth\Authenticatable;

interface LassiSetter
{

    /**
     * Should the client accept the lassi user
     * return true or false
     * @param $lassiuser
     * @return mixed
     */
   public function Accept($lassiuser);

    /**
     * Move any desired values from lassiuser to created user.
     * return $user after changes and saving.
     * @param $lassiuser
     * @param $user
     * @return mixed | Authenticatable;
     */
   public function Update($lassiuser, $user);

}
