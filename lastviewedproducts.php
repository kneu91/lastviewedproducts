<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaShop\PrestaShop\Adapter\Image\ImageRetriever;
use PrestaShop\PrestaShop\Adapter\Product\PriceFormatter;
use PrestaShop\PrestaShop\Core\Product\ProductListingPresenter;
use PrestaShop\PrestaShop\Adapter\Product\ProductColorsRetriever;

class lastviewedproducts extends Module{    
    
    public function __construct() {
        $this->name = 'lastviewedproducts';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'kneu91';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = [
            'min' => '1.7',
            'max' => _PS_VERSION_
        ];
        $this->bootstrap = true;
        parent::__construct();
        $this->displayName = $this->l('Last Viewed Products');
        $this->description = $this->l('Module to show last viewed products');
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');      

    }
    
    public function install(){
        if (Shop::isFeatureActive()) {
            Shop::setContext(Shop::CONTEXT_ALL);
        }

        if (!parent::install() ||         
            !$this->registerHook('header') ||
            !$this->registerHook('displayFooterProduct') ||
            !$this->registerHook('actionFrontControllerSetMedia')||
            !Configuration::updateValue('LASTVIEWEDPRODUCTS_STATUS', 0) ||
            !Configuration::updateValue('LASTVIEWEDPRODUCTS_MAXSIZE', 8)
        ) {
            return false;
        }
        return true;
    }
    
    public function uninstall(){
        if (!parent::uninstall() ||
            !Configuration::deleteByName('LASTVIEWEDPRODUCTS_STATUS')||
            !Configuration::deleteByName('LASTVIEWEDPRODUCTS_MAXSIZE')
        ) {
            return false;
        }
        return true;
    }
    
    public function getContent(){ 
        
        $output = null;
        if (Tools::isSubmit('submit'.$this->name)) {
            $status = boolval(Tools::getValue('LASTVIEWEDPRODUCTS_STATUS'));
            $maxsize =  intval(Tools::getValue('LASTVIEWEDPRODUCTS_MAXSIZE'));
            if (
                !$status || empty($status) || !Validate::isBool($status) ||
                !$maxsize  || empty($maxsize) || !Validate::isInt($maxsize)
            ) {
                $output .= $this->displayError($this->l('Invalid Configuration value'));
            } else {
                Configuration::updateValue('LASTVIEWEDPRODUCTS_STATUS', $status);
                Configuration::updateValue('LASTVIEWEDPRODUCTS_MAXSIZE', $maxsize);
                $output .= $this->displayConfirmation($this->l('Settings updated'));
            }
        }
        return $output.$this->displayForm();
    }
    
    public function displayForm(){
        // Get default language
        $defaultLang = (int)Configuration::get('PS_LANG_DEFAULT');

        // Init Fields form array
        $fieldsForm[0]['form'] = [
            'legend' => [
                'title' => $this->l('Ustawienia'),
            ],
            'input' => [
                [
                    'type' => 'switch',
                    'label' => 'Czy wyświetlać ostatnio oglądane produkty??',
                    'name' => 'LASTVIEWEDPRODUCTS_STATUS',
                    'values' => [
                        [
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => 'Tak'
                        ],
                        [
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => 'Nie',
                        ],
                    ]
                ],
                [
                    'type' => 'text',
                    'label' => 'Ilość wyświetlanych zapamiętanych produktów',
                    'name' => 'LASTVIEWEDPRODUCTS_MAXSIZE',
                ]
            ],
            'submit' => [
                'title' => $this->l('Zapisz'),
                'class' => 'btn btn-default pull-right'
            ]
        ];

        $helper = new HelperForm();
        // Module, token and currentIndex
        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;

        // Language
        $helper->default_form_language = $defaultLang;
        $helper->allow_employee_form_lang = $defaultLang;

        // Title and toolbar
        $helper->title = $this->displayName;
        $helper->show_toolbar = true;        // false -> remove toolbar
        $helper->toolbar_scroll = true;      // yes - > Toolbar is always visible on the top of the screen.
        $helper->submit_action = 'submit'.$this->name;
        $helper->toolbar_btn = [
            'save' => [
                'desc' => $this->l('Save'),
                'href' => AdminController::$currentIndex.'&configure='.$this->name.'&save'.$this->name.
                '&token='.Tools::getAdminTokenLite('AdminModules'),
            ],
            'back' => [
                'href' => AdminController::$currentIndex.'&token='.Tools::getAdminTokenLite('AdminModules'),
                'desc' => $this->l('Back to list')
            ]
        ];
        // Load current value
        $helper->fields_value['LASTVIEWEDPRODUCTS_STATUS'] = Tools::getValue('LASTVIEWEDPRODUCTS_STATUS', Configuration::get('LASTVIEWEDPRODUCTS_STATUS'));
        $helper->fields_value['LASTVIEWEDPRODUCTS_MAXSIZE'] = Tools::getValue('LASTVIEWEDPRODUCTS_MAXSIZE', Configuration::get('LASTVIEWEDPRODUCTS_MAXSIZE'));

        return $helper->generateForm($fieldsForm);
    }
    
    public function hookActionFrontControllerSetMedia(){
        $this->context->controller->registerStylesheet(
            'lastviewedproducts-style',
            $this->_path.'views/css/style.css',
            [
                'media' => 'all',
                'priority' => 1000,
            ]
        );
        $this->context->controller->registerJavascript(
            'lastviewedproducts-javascript',
            $this->_path.'views/js/lastviewedproducts.js',
            [
                'position' => 'bottom',
                'priority' => 1000,
            ]
        );
    }
    
    public function hookDisplayFooterProduct($params){        
        $ajax_call_url = $this->context->link->getModuleLink($this->name, 'ajax');  
 
        $productIds = unserialize($this->context->cookie->lastviewedproducts);
        if($productIds){
            
            $products_for_template = [];
            
            $assembler = new ProductAssembler($this->context);
            $presenterFactory = new ProductPresenterFactory($this->context);
            $presentationSettings = $presenterFactory->getPresentationSettings();
            $presenter = new ProductListingPresenter(
                new ImageRetriever(
                    $this->context->link
                ),
                $this->context->link,
                new PriceFormatter(),
                new ProductColorsRetriever(),
                $this->context->getTranslator()
            );            

            if (is_array($productIds)) {
                foreach ($productIds as $productId) {       
                    $products_for_template[] = $presenter->present(
                        $presentationSettings,
                        $assembler->assembleProduct(array('id_product' => $productId)),
                        $this->context->language
                    );            
                }
            }            
        }        
        else{
            $products_for_template = null;
        }
        
        $this->context->smarty->assign(
            [
                'products' => $products_for_template,
                'ajax_call_url' => $ajax_call_url,
                'max_size' => Configuration::get('LASTVIEWEDPRODUCTS_MAXSIZE'),

            ]
        );
        return $this->display(__FILE__, 'productFooter.tpl');
        
    }
    
}