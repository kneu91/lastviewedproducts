<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


class lastviewedproductsajaxModuleFrontController extends ModuleFrontController
{
    public function display()
    {
    
        $maxsize = intval( Configuration::get('LASTVIEWEDPRODUCTS_MAXSIZE') );
        $id = Tools::getValue('id_products');        
        $list = $this->context->cookie->lastviewedproducts;  
        if( $id > 0 ){
            if( !$list ){
                $array = [];
                $array[] = $id;
                $this->context->cookie->lastviewedproducts = serialize($array);
            }
            else{
                $array = unserialize($this->context->cookie->lastviewedproducts);
                if( !in_array($id,$array) ){
                    if(count($array) <= $maxsize){
                        array_push($array,$id);
                        $this->context->cookie->lastviewedproducts = serialize($array);
                    }
                    else{
                        array_unshift($array,$id);
                        array_pop($array);
                        $this->context->cookie->lastviewedproducts = serialize($array);
                    }
                }
            }
        }
        $responseArray=[];


        header('Content-Type: application/json');
        $this->ajaxRender(
            json_encode(
                $responseArray
            )
        );  
      
    }
  
}