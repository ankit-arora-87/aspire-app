<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace App\Helpers;

class CommonHelper
{
    /**
     * To upload image
     */
    public static function uploadDocument($document, $destinationPath) {
        $documentName = "original_". uniqid("res_",true).".".$document->getClientOriginalExtension();
        if($document->move($destinationPath, $documentName)){
            return $destinationPath.$documentName;
        }
        return false;
        
    }
}