<?php

namespace Core;

use Core\Helpers\CurlHelper;

/**
 * Created by PhpStorm.
 * User: tingle
 * Date: 25.02.17
 * Time: 23:21
 */
class Parser
{
    /**
     *
     */
    const TYPE_LANDLINE = 'landline';
    /**
     *
     */
    const TYPE_MOBILE = 'mobile';

    /**
     *
     */
    const SERVICE_SMS = 'sms';
    /**
     *
     */
    const SERVICE_SKYPEOUT = 'skypeout';

    /**
     *
     */
    const BASE_URL = 'https://apps.skypeassets.com/rates/';
    /**
     *
     */
    const SKYPEOUT_URL = self::BASE_URL . 'skypeout';
    /**
     *
     */
    const SMS_URL = self::BASE_URL . 'sms';

    /**
     * @param CurlHelper $helper
     */
    public function run(CurlHelper $helper) {
        $skypeOutRates = json_decode($helper->request($this->buildUrl(self::SKYPEOUT_URL, self::SERVICE_SKYPEOUT)), true);
        $smsRates = json_decode($helper->request($this->buildUrl(self::SMS_URL, self::SERVICE_SMS)), true);

        $country[] = [
            'name' => $skypeOutRates['destinations'][0]['name'],
            'price' => $skypeOutRates['destinations'][0]['usageCharge']['priceFormatted']
        ];
        unset($skypeOutRates['destinations'][0]);

        $sms[] = [
            'name' => $smsRates['destinations'][0]['name'],
            'price' => $smsRates['destinations'][0]['usageCharge']['priceFormatted']
        ];
        unset($smsRates['destinations'][0]);

        $mobile = [];
        $favourite = [];
        foreach ($skypeOutRates['destinations'] as $skypeOutRate) {
            if ($skypeOutRate['type'] == self::TYPE_LANDLINE) {
                $favourite[] = [
                    'name' => $skypeOutRate['name'],
                    'price' => $skypeOutRate['usageCharge']['priceFormatted']
                ];
            }
            if ($skypeOutRate['type'] == self::TYPE_MOBILE) {
                $mobile[] = [
                    'name' => $skypeOutRate['name'],
                    'price' => $skypeOutRate['usageCharge']['priceFormatted']
                ];
            }
        }

        $this->generateXls($country, $mobile, $favourite, $sms);
    }

    /**
     * @param $baseUrl
     * @param $service
     * @return string
     */
    protected function buildUrl($baseUrl, $service) {
        $url = $baseUrl  . '?' . http_build_query([
            "_accept" => "2.0",
            "billingCountry" => "RU",
            "currency" => "USD",
            "destinationCountry" => "RU",
            "expand" => "price,pending",
            "language" => "ru",
            "originCountry" => "UA",
            "seq" => "18",
            "service" => $service
        ], null, "&");

        return $url;
    }

    /**
     * @param $country
     * @param $mobile
     * @param $favourite
     * @param $sms
     */
    protected function generateXls($country, $mobile, $favourite, $sms) {

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $removeIndex = $spreadsheet->getIndex($spreadsheet->getSheetByName('Worksheet'));
        $spreadsheet->removeSheetByIndex($removeIndex);

        $countrySheet = new \PhpOffice\PhpSpreadsheet\Worksheet($spreadsheet, 'Country');
        $countrySheet->fromArray($country);
        $spreadsheet->addSheet($countrySheet);

        $mobileSheet = new \PhpOffice\PhpSpreadsheet\Worksheet($spreadsheet, 'Mobile');
        $mobileSheet->fromArray($mobile);
        $spreadsheet->addSheet($mobileSheet);

        $favouriteSheet = new \PhpOffice\PhpSpreadsheet\Worksheet($spreadsheet, 'Favourite');
        $favouriteSheet->fromArray($favourite);
        $spreadsheet->addSheet($favouriteSheet);

        $smsSheet = new \PhpOffice\PhpSpreadsheet\Worksheet($spreadsheet, 'SMS');
        $smsSheet->fromArray($sms);
        $spreadsheet->addSheet($smsSheet);

        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Excel5');
        $writer->save('zumme.xls');
    }
}