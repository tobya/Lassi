<?php


namespace Lassi\Interfaces;


interface LassiSetter
{
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
