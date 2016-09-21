<?php

/**
*
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    Dotpay Team <tech@dotpay.pl>
*  @copyright Dotpay
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*
*/
require_once(__DIR__.'/api.php');

class DotpayDevApi extends DotpayApi {
    /**
     * Returns list of payment channels
     * @return array
     */
    public function getChannelList(){
        $oneclickAgreements = $this->parent->module->l('I agree to repeated loading bill my credit card for the payment One-Click by way of purchase of goods or services offered by the store.');
        
        $channelList = array();
        $targetUrl = $this->parent->getPreparingUrl();
        if($this->config->isDotpayOneClick() && $this->getChannelData(self::$ocChannel)) {
            $creditCards = DotpayCreditCard::getAllCardsForCustomer(Context::getContext()->customer->id);
            $creditCardsValues = array();
            foreach($creditCards as $creditCard) {
                $creditCardsValues[$creditCard->mask.' ('.$creditCard->brand.')'] = $creditCard->id;
            }
            $ocManageLink = Context::getContext()->link->getModuleLink($this->parent->module->name, 'ocmanage');
            
            $channelList['oneclick'] = array(
                'form' => $this->getFormHeader('oneclick', $targetUrl),
                'fields' => array(
                    $this->getHiddenField('order_id', Tools::getValue('order_id')),
                    array(
                        'type' => 'radio',
                        'name' => 'dotpay_type',
                        'id' => 'select_saved_card',
                        'value' => 'oneclick',
                        'class' => 'oneclick-margin',
                        'label' => $this->parent->module->l('Select a registered card').'&nbsp;(<a href="'.$ocManageLink.'" target="_blank">'.$this->parent->module->l('Manage your registered cards').'</a>)',
                        'required' => true
                    ),
                    array(
                        'type' => 'select',
                        'name' => 'credit_card',
                        'id' => 'saved_credit_cards',
                        'class' => 'oneclick-margin',
                        'values' => $creditCardsValues,
                    ),
                    array(
                        'type' => 'radio',
                        'name' => 'dotpay_type',
                        'value' => 'oneclick_register',
                        'class' => 'oneclick-margin',
                        'label' => $this->parent->module->l('Register a new card'),
                        'required' => true
                    ),
                    array(
                        'type' => 'checkbox',
                        'name' => 'oneclick_agreements',
                        'value' => '1',
                        'checked' => true,
                        'label' => $oneclickAgreements,
                        'required' => true
                    ),
                    $this->addBylawField(),
                    $this->addPersonalDataField(),
                    $this->getSubmitField(),
                ),
                'image' => $this->parent->getDotOneClickLogo(),
                'description' => "&nbsp;&nbsp;<strong>".$this->parent->module->l("Credit Card - One Click")."</strong>&nbsp;<span>".$this->parent->module->l("(via Dotpay.pl)")."</span>",
            );
        }
        if($this->config->isDotpayCreditCard() && $this->getChannelData(self::$ccChannel)) {
            $channelList['cc'] = array(
                'form' => $this->getFormHeader('cc', $targetUrl),
                'fields' => array(
                    $this->getHiddenField('dotpay_type', 'cc'),
                    $this->getHiddenField('order_id', Tools::getValue('order_id')),
                    $this->addBylawField(),
                    $this->addPersonalDataField(),
                    $this->getSubmitField(),
                ),
                'image' => $this->parent->getDotCreditCardLogo(),
                'description' => "&nbsp;&nbsp;".$this->parent->module->l("Pay with your credit card")."&nbsp;<span>".$this->parent->module->l("(via Dotpay.pl)")."</span>",
            );
        }
        if($this->parent->isDotpayPVEnabled() && $this->getChannelData(self::$pvChannel, true)) {
            $channelList['pv'] = array(
                'form' => $this->getFormHeader('pv', $targetUrl),
                'fields' => array(
                    $this->getHiddenField('dotpay_type', 'pv'),
                    $this->getHiddenField('order_id', (int)Tools::getValue('order_id')),
                    $this->getHiddenField('id', $this->config->getDotpayPvId()),
                    $this->addBylawField(),
                    $this->addPersonalDataField(),
                    $this->getSubmitField(),
                ),
                'image' => $this->parent->getDotPVLogo(),
                'description' => "&nbsp;&nbsp;<strong>".$this->parent->module->l("Pay with your credit card")." (3-D Secure)</strong>&nbsp;<span>".$this->parent->module->l("(via Dotpay.pl)")."</span>",
            );
        }
        if($this->config->isDotpayBlik() && $this->getChannelData(self::$blikChannel)) {
            $blikText = $this->parent->module->l("BLIK code 6 digits");
            $channelList['blik'] = array(
                'form' => $this->getFormHeader('blik', $targetUrl),
                'fields' => array(
                    $this->getHiddenField('dotpay_type', 'blik'),
                    $this->getHiddenField('order_id', Tools::getValue('order_id')),
                    array(
                        'type' => 'text',
                        'name' => 'blik_code',
                        'value' => '',
                        'placeholder' => $blikText,
                        'required' => true
                    ),
                    $this->addBylawField(),
                    $this->addPersonalDataField(),
                    $this->getSubmitField(),
                ),
                'image' => $this->parent->getDotBlikLogo(),
                'description' => "&nbsp;&nbsp;<strong>".$this->parent->module->l("Blik")."</strong>&nbsp;<span>".$this->parent->module->l("(via Dotpay.pl)")."</span>",
            );
        }
        if($this->config->isDotpayMasterPass() && $this->getChannelData(self::$mpChannel)) {
            $channelList['mp'] = array(
                'form' => $this->getFormHeader('mp', $targetUrl),
                'fields' => array(
                    $this->getHiddenField('dotpay_type', 'mp'),
                    $this->getHiddenField('order_id', Tools::getValue('order_id')),
                    $this->addBylawField(),
                    $this->addPersonalDataField(),
                    $this->getSubmitField(),
                ),
                'image' => $this->parent->getDotMasterPassLogo(),
                'description' => "&nbsp;&nbsp;".$this->parent->module->l("MasterPass")."&nbsp;<span>".$this->parent->module->l("(via Dotpay.pl)")."</span>",
            );
        }
        $extendedWidget = '<div class="selected-channel-message">'.
                          $this->parent->module->l("Selected payment channel").
                          ':&nbsp;&nbsp; <a href="#" class="channel-selected-change">'.
                          $this->parent->module->l("change channel").
                          '&nbsp;&raquo;</a></div><div class="selectedChannelContainer channels-wrapper"><hr /></div>'.
                          '<div class="collapsibleWidgetTitle">'.$this->parent->module->l("Available channels").':</div>';
        $channelList['dotpay'] = array(
            'form' => $this->getFormHeader('dotpay', $targetUrl),
            'image' => $this->parent->getDotpayLogo(),
            'description' => "&nbsp;&nbsp;<strong>".$this->parent->module->l(" Dotpay ")."</strong>&nbsp;<span>".$this->parent->module->l("(fast and secure internet payment)")."</span>",
        );
        $fields = array(
            $this->getHiddenField('dotpay_type', 'dotpay'),
            $this->getHiddenField('order_id', Tools::getValue('order_id'))
        );
        if($this->config->isDotpayWidgetMode()) {
            $fields[] = array(
                'type' => 'hidden',
                'name' => 'widget',
                'value' => $this->config->isDotpayWidgetMode(),
                'label' => $extendedWidget.'<p class="my-form-widget-container"></p>'
            );
            $fields[] = $this->addBylawField();
            $fields[] = $this->addPersonalDataField();
        }
        $fields[] = $this->getSubmitField();
        $channelList['dotpay']['fields'] = $fields;
        return $channelList;
    }
    
    /**
     * Check confirm message from Dotpay
     * @return bool
     */
    public function checkConfirm(){
        if($this->isSelectedPvChannel())
            $start = $this->config->getDotpayPvPIN().$this->config->getDotpayPvId();
        else
            $start = $this->config->getDotpayPIN().$this->config->getDotpayId();
        $signature = $start.
        Tools::getValue('operation_number').
        Tools::getValue('operation_type').
        Tools::getValue('operation_status').
        Tools::getValue('operation_amount').
        Tools::getValue('operation_currency').
        Tools::getValue('operation_withdrawal_amount').
        Tools::getValue('operation_commission_amount').
        Tools::getValue('operation_original_amount').
        Tools::getValue('operation_original_currency').
        Tools::getValue('operation_datetime').
        Tools::getValue('operation_related_number').
        Tools::getValue('control').
        Tools::getValue('description').
        Tools::getValue('email').
        Tools::getValue('p_info').
        Tools::getValue('p_email').
        Tools::getValue('channel').
        Tools::getValue('channel_country').
        Tools::getValue('geoip_country');

        return (Tools::getValue('signature') === hash('sha256', $signature));
    }
    
    /**
     * Returns flag, if was selected PV channel
     */
    public function isSelectedPvChannel() {
        return ($this->parent->isDotSelectedCurrency($this->config->getDotpayPvCurrencies(), $this->getOperationCurrency()) 
           && Tools::getValue('channel')==self::$pvChannel
           && $this->config->isDotpayPV()
           && $this->config->getDotpayPvId()==Tools::getValue('id'));
    }
    
    /**
     * Returns total amount from confirm message
     * @return string|bool
     */
    public function getTotalAmount() {
        return Tools::getValue('operation_original_amount');
    }
    
    /**
     * Returns currency from confirm message
     * @return string|bool
     */
    public function getOperationCurrency() {
        return Tools::getValue('operation_original_currency');
    }
    
    /**
     * Returns operation number from confirm message
     * @return string|bool
     */
    public function getOperationNumber() {
        return Tools::getValue('operation_number');
    }
    
    /**
     * Returns new order state from confirm message
     * @return type
     */
    public function getNewOrderState() {
        $actualState = NULL;
        switch (Tools::getValue('operation_status')) {
            case "new":
                $actualState = $this->config->getDotpayNewStatusId();
                break;
            case "completed":
                $actualState = _PS_OS_PAYMENT_;
                break;
            case "rejected":
                $actualState = _PS_OS_ERROR_;
                break;
            case "processing_realization_waiting":
                $actualState = $this->config->getDotpayNewStatusId();
                break;
            case "processing_realization":
                $actualState = $this->config->getDotpayNewStatusId();
        }
        return $actualState;
    }
    
    /**
     * Returns hidden form for Dotpay Helper Form
     * @return array
     */
    public function getHiddenForm() {
        $type = Tools::getValue('dotpay_type');
        $formFields = array();
        switch($type) {
            case 'oneclick':
                $fields = $this->getHiddenFieldsOneClickCard();
                break;
            case 'oneclick_register':
                $fields = $this->getHiddenFieldsOneClickRegister();
                break;
            case 'pv':
                $fields = $this->getHiddenFieldsPV();
                break;
            case 'cc':
                $fields = $this->getHiddenFieldsCreditCard();
                break;
            case 'mp':
                $fields = $this->getHiddenFieldsMasterPass();
                break;
            case 'blik':
                $fields = $this->getHiddenFieldsBlik();
                break;
            case 'dotpay':
                $fields = $this->getHiddenFieldsDotpay();
                break;
        }
        foreach($fields as $name => $value) {
            $formFields[] = $this->getHiddenField($name, $value);
        }
        if($type=='pv') {
            $id = $this->config->getDotpayPvId();
            $pin = $this->config->getDotpayPvPIN();
        }
        else {
            $id = $this->config->getDotpayId();
            $pin = $this->config->getDotpayPIN();
        }
        $formFields[] = $this->getHiddenField('chk', $this->generateCHK($id, $pin, $fields));
        return array(
            'form'=>  $this->getFormHeader($type),
            'fields' => $formFields
        );
    }
    
    /**
     * Performs actions on preparing form
     * @param string $action
     * @param array $params
     * @return boolean
     */
    public function onPrepareAction($action, $params) {
        switch($action) {
            case 'oneclick_register':
                return $this->onPrepareOneClick($params);
        }
    }
        
    /**
     * Check, if channel is in channels groups
     * @param int $channelId
     * @param array $group
     * @return boolean
     */
    public function isChannelInGroup($channelId, array $groups) {
        $resultJson = $this->getApiChannels();
        if(false !== $resultJson) {
            $result = json_decode($resultJson, true);

            if (isset($result['channels']) && is_array($result['channels'])) {
                foreach ($result['channels'] as $channel) {
                    if (isset($channel['group']) && $channel['id']==$channelId && in_array($channel['group'], $groups)) {
                        return true;
                    }
                }
            }
        }
        return false;
    }
    
    /**
     * Returns hidden fields for OneClick channel
     * @return array
     */
    protected function getHiddenFieldsOneClick() {
        $hiddenFields = $this->getHiddenFields();
        
        $hiddenFields['channel'] = self::$ocChannel;
        $hiddenFields['ch_lock'] = 1;
        $hiddenFields['type'] = 4;
        
        return $hiddenFields;
    }
    
    /**
     * Returns hidden fields for OneClick selected card
     * @return string
     */
    protected function getHiddenFieldsOneClickCard() {
        $hiddenFields = $this->getHiddenFieldsOneClick();
        $cc = new DotpayCreditCard(Tools::getValue('credit_card'));

        $hiddenFields['credit_card_customer_id'] = $cc->hash;
        $hiddenFields['credit_card_id'] = $cc->card_id;
        $hiddenFields['bylaw'] = '1';
        $hiddenFields['personal_data'] = '1';

        return $hiddenFields;
    }
    
    /**
     * Returns hidden fields for OneClick register card
     * @return type
     */
    public function getHiddenFieldsOneClickRegister() {
        $hiddenFields = $this->getHiddenFieldsOneClick();
        $cc = DotpayCreditCard::getCreditCardByOrder($this->parent->getLastOrderNumber());
        $hash = ($cc !== NULL)?$cc->hash:NULL;
        
        $hiddenFields['credit_card_store'] = 1;
        $hiddenFields['credit_card_customer_id'] = $hash;
        
        return $hiddenFields;
    }
    
    /**
     * Returns hidden fields for PV channel
     * @return array
     */
    protected function getHiddenFieldsPV() {
        $hiddenFields = $this->getHiddenFields();
        
        $hiddenFields['channel'] = self::$pvChannel;
        $hiddenFields['ch_lock'] = 1;
        $hiddenFields['type'] = 4;
        $hiddenFields['id'] = $this->config->getDotpayPvId();
        
        return $hiddenFields;
    }
    
    /**
     * Returns hidden fields for Credit card channel
     * @return array
     */
    protected function getHiddenFieldsCreditCard() {
        $hiddenFields = $this->getHiddenFields();
        
        $hiddenFields['channel'] = self::$ccChannel;
        
        $hiddenFields['ch_lock'] = 1;
        $hiddenFields['type'] = 4;
        
        return $hiddenFields;
    }
    
    /**
     * Returns hidden fields for MasterPass channel
     * @return array
     */
    protected function getHiddenFieldsMasterPass() {
        $hiddenFields = $this->getHiddenFields();
        
        if($this->config->isDotpayTestMode()) {
            $hiddenFields['channel'] = 246;
        } else {
            $hiddenFields['channel'] = self::$mpChannel;
        }
        
        $hiddenFields['ch_lock'] = 1;
        $hiddenFields['type'] = 4;
        
        return $hiddenFields;
    }
    
    /**
     * Returns hidden fields for standard Dotpay channel
     * @return array
     */
    protected function getHiddenFieldsDotpay() {
        $hiddenFields = $this->getHiddenFields();
        
        if($this->config->isDotpayWidgetMode()) {
            $hiddenFields['ch_lock'] = 1;
            $hiddenFields['type'] = 4;
            $hiddenFields['channel'] = Tools::getValue('channel');
        }
        
        return $hiddenFields;
    }
    
    /**
     * Returns hidden fields for Blik channel
     * @return array
     */
    protected function getHiddenFieldsBlik() {
        $hiddenFields = $this->getHiddenFields();
        
        if(!$this->config->isDotpayTestMode())
            $hiddenFields['blik_code'] = Tools::getValue('blik_code');
        $hiddenFields['channel'] = self::$blikChannel;
        $hiddenFields['ch_lock'] = 1;
        $hiddenFields['type'] = 4;
        
        return $hiddenFields;
    }
    
    /**
     * Returns standard hidden fields
     * @return array
     */
    private function getHiddenFields() {
        $streetData = $this->parent->getDotStreetAndStreetN1();
        return array(
            'id' => $this->parent->getDotId(),
            'control' => $this->parent->getDotControl(),
            'p_info' => $this->parent->getDotPinfo(),
            'amount' => $this->parent->getDotAmount(),
            'currency' => $this->parent->getDotCurrency(),
            'description' => $this->parent->getDotDescription(),
            'lang' => $this->parent->getDotLang(),
            'URL' => $this->parent->getDotUrl(),
            'URLC' => $this->parent->getDotUrlC(),
            'api_version' => $this->config->getDotpayApiVersion(),
            'type' => 0,
            'ch_lock' => 0,
            'firstname' => $this->parent->getDotFirstname(),
            'lastname' => $this->parent->getDotLastname(),
            'email' => $this->parent->getDotEmail(),
            'phone' => $this->parent->getDotPhone(),
            'street' => $streetData['street'],
            'street_n1' => $streetData['street_n1'],
            'city' => $this->parent->getDotCity(),
            'postcode' => $this->parent->getDotPostcode(),
            'country' => $this->parent->getDotCountry(),
            'bylaw' => 1,
            'personal_data' => 1
        );
    }
    
    protected function onPrepareOneClick($params) {
        $cc = new DotpayCreditCard();
        $cc->order_id = $params['order'];
        $cc->customer_id = $params['customer'];
        $cc->register_date = date('d-m-Y');
        return $cc->save();
    }


    /**
     * Returns CHK for request params
     * @param string $DotpayId Dotpay shop ID
     * @param string $DotpayPin Dotpay PIN
     * @param array $ParametersArray Parameters from request
     * @return string
     */
    protected function generateCHK($DotpayId, $DotpayPin, $ParametersArray) {
        $ParametersArray['id'] = $DotpayId;
        $ChkParametersChain =
        $DotpayPin.
        (isset($ParametersArray['api_version']) ?
        $ParametersArray['api_version'] : null).
        (isset($ParametersArray['charset']) ?
        $ParametersArray['charset'] : null).
        (isset($ParametersArray['lang']) ?
        $ParametersArray['lang'] : null).
        (isset($ParametersArray['id']) ?
        $ParametersArray['id'] : null).
        (isset($ParametersArray['amount']) ?
        $ParametersArray['amount'] : null).
        (isset($ParametersArray['currency']) ?
        $ParametersArray['currency'] : null).
        (isset($ParametersArray['description']) ?
        $ParametersArray['description'] : null).
        (isset($ParametersArray['control']) ?
        $ParametersArray['control'] : null).
        (isset($ParametersArray['channel']) ?
        $ParametersArray['channel'] : null).
        (isset($ParametersArray['credit_card_brand']) ?
        $ParametersArray['credit_card_brand'] : null).
        (isset($ParametersArray['ch_lock']) ?
        $ParametersArray['ch_lock'] : null).
        (isset($ParametersArray['channel_groups']) ?
        $ParametersArray['channel_groups'] : null).
        (isset($ParametersArray['onlinetransfer']) ?
        $ParametersArray['onlinetransfer'] : null).
        (isset($ParametersArray['URL']) ?
        $ParametersArray['URL'] : null).
        (isset($ParametersArray['type']) ?
        $ParametersArray['type'] : null).
        (isset($ParametersArray['buttontext']) ?
        $ParametersArray['buttontext'] : null).
        (isset($ParametersArray['URLC']) ?
        $ParametersArray['URLC'] : null).
        (isset($ParametersArray['firstname']) ?
        $ParametersArray['firstname'] : null).
        (isset($ParametersArray['lastname']) ?
        $ParametersArray['lastname'] : null).
        (isset($ParametersArray['email']) ?
        $ParametersArray['email'] : null).
        (isset($ParametersArray['street']) ?
        $ParametersArray['street'] : null).
        (isset($ParametersArray['street_n1']) ?
        $ParametersArray['street_n1'] : null).
        (isset($ParametersArray['street_n2']) ?
        $ParametersArray['street_n2'] : null).
        (isset($ParametersArray['state']) ?
        $ParametersArray['state'] : null).
        (isset($ParametersArray['addr3']) ?
        $ParametersArray['addr3'] : null).
        (isset($ParametersArray['city']) ?
        $ParametersArray['city'] : null).
        (isset($ParametersArray['postcode']) ?
        $ParametersArray['postcode'] : null).
        (isset($ParametersArray['phone']) ?
        $ParametersArray['phone'] : null).
        (isset($ParametersArray['country']) ?
        $ParametersArray['country'] : null).
        (isset($ParametersArray['code']) ?
        $ParametersArray['code'] : null).
        (isset($ParametersArray['p_info']) ?
        $ParametersArray['p_info'] : null).
        (isset($ParametersArray['p_email']) ?
        $ParametersArray['p_email'] : null).
        (isset($ParametersArray['n_email']) ?
        $ParametersArray['n_email'] : null).
        (isset($ParametersArray['expiration_date']) ?
        $ParametersArray['expiration_date'] : null).
        (isset($ParametersArray['recipient_account_number']) ?
        $ParametersArray['recipient_account_number'] : null).
        (isset($ParametersArray['recipient_company']) ?
        $ParametersArray['recipient_company'] : null).
        (isset($ParametersArray['recipient_first_name']) ?
        $ParametersArray['recipient_first_name'] : null).
        (isset($ParametersArray['recipient_last_name']) ?
        $ParametersArray['recipient_last_name'] : null).
        (isset($ParametersArray['recipient_address_street']) ?
        $ParametersArray['recipient_address_street'] : null).
        (isset($ParametersArray['recipient_address_building']) ?
        $ParametersArray['recipient_address_building'] : null).
        (isset($ParametersArray['recipient_address_apartment']) ?
        $ParametersArray['recipient_address_apartment'] : null).
        (isset($ParametersArray['recipient_address_postcode']) ?
        $ParametersArray['recipient_address_postcode'] : null).
        (isset($ParametersArray['recipient_address_city']) ?
        $ParametersArray['recipient_address_city'] : null).
        (isset($ParametersArray['warranty']) ?
        $ParametersArray['warranty'] : null).
        (isset($ParametersArray['bylaw']) ?
        $ParametersArray['bylaw'] : null).
        (isset($ParametersArray['personal_data']) ?
        $ParametersArray['personal_data'] : null).
        (isset($ParametersArray['credit_card_number']) ?
        $ParametersArray['credit_card_number'] : null).
        (isset($ParametersArray['credit_card_expiration_date_year']) ?
        $ParametersArray['credit_card_expiration_date_year'] : null).
        (isset($ParametersArray['credit_card_expiration_date_month']) ?
        $ParametersArray['credit_card_expiration_date_month'] : null).
        (isset($ParametersArray['credit_card_security_code']) ?
        $ParametersArray['credit_card_security_code'] : null).
        (isset($ParametersArray['credit_card_store']) ?
        $ParametersArray['credit_card_store'] : null).
        (isset($ParametersArray['credit_card_store_security_code']) ?
        $ParametersArray['credit_card_store_security_code'] : null).
        (isset($ParametersArray['credit_card_customer_id']) ?
        $ParametersArray['credit_card_customer_id'] : null).
        (isset($ParametersArray['credit_card_id']) ?
        $ParametersArray['credit_card_id'] : null).
        (isset($ParametersArray['blik_code']) ?
        $ParametersArray['blik_code'] : null).
        (isset($ParametersArray['credit_card_registration']) ?
        $ParametersArray['credit_card_registration'] : null).
        (isset($ParametersArray['recurring_frequency']) ?
        $ParametersArray['recurring_frequency'] : null).
        (isset($ParametersArray['recurring_interval']) ?
        $ParametersArray['recurring_interval'] : null).
        (isset($ParametersArray['recurring_start']) ?
        $ParametersArray['recurring_start'] : null).
        (isset($ParametersArray['recurring_count']) ?
        $ParametersArray['recurring_count'] : null);
        return hash('sha256',$ChkParametersChain);
    }
    
    /**
     * Returns amount after extra charge
     * @return type
     */
    public function getExtrachargeAmount($inDefaultCurrency = false) {
        if(!$this->config->getDotpayExCh())
            return 0.0;
        $amount = (float)$this->parent->getDotAmount();
        $exPercentage = $this->getFormatAmount($amount * $this->config->getDotpayExPercentage()/100);
        $exAmount = $this->getFormatAmount($this->config->getDotpayExAmount());
        $price = max($exPercentage, $exAmount);
        if($inDefaultCurrency)
            $price = $this->getFormatAmount(Tools::convertPrice($price, $this->parent->getDotCurrencyId(), false));
        return $price;
    }
    
    /**
     * Returns amount after discount for Dotpay
     * @return type
     */
    public function getDiscountAmount() {
        if(!$this->config->getDotpayDiscount())
            return 0.0;
        $amount = $this->parent->getDotShippingAmount();
        $discPercentage = $this->getFormatAmount($amount * $this->config->getDotpayDiscPercentage()/100);
        $discAmount = $this->config->getDotpayDiscAmount();
        $tmpPrice = max($discPercentage, $discAmount);
        return min($tmpPrice, $amount);
    }
}