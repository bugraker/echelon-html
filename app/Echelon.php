<?php namespace App;

/**
 * Echelon - HTML
 *
 * Displays an specified image within a MIL-STD-2525B or MIL-STD-2525C type frame.  Allows for
 * the specification of Echelon, Affiliation, "2525" type, Size, or Symbol ID Code (sidc) via
 * URL arguments.  Please see the readme.md under the top directory.
 *
 * Copyright (c) 2015 George Patton Simcox, email: geo.simcox@gmail.com
 * All Rights Reserved
 *
 */

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Echelon extends Model {

    private $echelons = [
        '-' => "&nbsp;", // NULL
        'A' => '&Oslash;', // TEAM/CREW
        'B' => '&#x26ab;', // SQUAD
        'C' => '&#x26ab;&nbsp;&#x26ab;', // SECTION
        'D' => '&#x26ab;&nbsp;&#x26ab;&nbsp;&#x26ab;', // PLATOON/DETACHMENT
        'E' => '&#x01c0;', // COMPANY/BATTERY/TROOP
        'F' => '&#x01c0;&nbsp;&#x01c0;', // BATTALION/SQUADRON
        'G' => '&#x01c0;&nbsp;&#x01c0;&nbsp;&#x01c0;', // REGIMENT/GROUP
        'H' => '&#x0058;', // BRIGADE
        'I' => '&#x0058;&#x0058;', // DIVISION
        'J' => '&#x0058;&#x0058;&#x0058;', // CORPS/MEF
        'K' => '&#x0058;&#x0058;&#x0058;&#x0058;', // ARMY
        'L' => '&#x0058;&#x0058;&#x0058;&#x0058;&#x0058;', // ARMY/GROUP/FRONT
        'M' => '&#x0058;&#x0058;&#x0058;&#x0058;&#x0058;&#x0058;', // REGION
        'N' => '&#x002b;&nbsp;&#x002b;', // COMMAND
    ];

    private $ext_good = [
        'jpg',
        'png',
        'ppng',
        'gif',
        'bmp'
    ];

    public function transformSvg($svg=null)
    {
        if (!empty($svg)) {
            $svg = preg_replace('/\r/', "\n", $svg);
            //$svg = preg_replace('/<svg(.)*>/U', '', $svg);
            //$svg = preg_replace('/<g(.)*>/', '', $svg);
            //dd($svg);
        }
        return false;
    }

    public function extractEchelonFromSidc($sidc=null) {
        if (!empty($sidc) && strlen($sidc) >= 10) {
            $echelon = substr($sidc, 10, 2);
        } else {
            $echelon = "--";
        }
        return($echelon);
    }

    public function getEchelon($echelon=null, $setc=null)
    {
        if (!empty($setc)) {
            $pattern = '/^[A-Na-n-]$/';
        } else {
            $pattern = '/^[A-Ma-m-]$/';
        }

        if (!empty($echelon)) {
            // we are only interested in the last place.
            $ech1 = strtoupper(substr($echelon, strlen($echelon) - 1, 1));

            if (preg_match($pattern, $ech1) && !empty($this->echelons[$ech1])) {
                // return lookup
                return ($this->echelons[$ech1]);
            } else {
                return($echelon);
            }
        }
        // default
        return($this->echelons['-']);
    }

    public function testImage($url=null)
    {
        $test = $this->url_exists($url);
        if (!$test === false){
            $parts = pathinfo($url);
            $ext = $parts['extension'];
            if (in_array($ext, $this->ext_good)) {
                return([
                    'height' => $test[1],
                    'width' => $test[0],
                    'url' =>  $test['url'],
                    'default' => false
                ]);
            }
        }

        $default_image = '../resources/assets/img/frd.png';
        $image_info = getimagesize($default_image);
        return([
            'height' => $image_info[1],
            'width' => $image_info[0],
            'url' => $default_image,
            'default' => true
        ]);
    }

    public function testFor2525c($symbol_set=null){
        if (!empty($symbol_set)){
            if (preg_match('/[Cc]$/', $symbol_set)) {
                return true;
            }
        }
        return false;
    }

    public function extractAffiliationFromSidc($sidc=null){
        if (!empty($sidc) && strlen($sidc) >= 2){
            $affiliation = substr($sidc, 1,1);
            if (preg_match('/^[AGPSWagpsw]$/', $affiliation)) {
                return $affiliation;
            }
        }
        return false;
    }

    public function testAssumed($source=null) {
        if (!empty($source)) {
            if (preg_match('/^[AGPSWagpsw]$/', $source)) {
                return true;
            }
        }
        return false;
    }

    public function testOrientation($frame_image=null) {
        if (!empty($frame_image)) {
            if ($frame_image['width'] >= $frame_image['height'] ) {
                return true; // landscape
            } else {
                return false; // portrait
            }
        }
        return true;
    }

    private function url_exists($url) {
        if ($ch = curl_init($url)) {
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            $data = curl_exec($ch);
            $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            if ($http_status == '200' || !empty($data)) {
                $image_info = getimagesizefromstring($data);
                if (is_array($image_info)) {
                    $image_info['url'] = $url;
                    return ($image_info);
                }
            }
        }

        return (false);
    }

/*
    private function url_exists_old($url) {
        if (!$fp = curl_init($url)) return false;
        return ($url);
    }
*/
}
