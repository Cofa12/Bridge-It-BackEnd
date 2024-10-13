<?php

namespace App;

trait SaveSocialiteData
{
    //
    public  static $userData;
    public  static $token;

    public  function setData($data,$token):void
    {
        SaveSocialiteData::$userData = $data;
        SaveSocialiteData::$token=$token;
    }



}
