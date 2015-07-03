<?php namespace App;

/**
 * Echelon - HTML
 *
 * Displays an specified image within a MIL-STD-2525B or MIL-STD-2525C type frame.  Allows for
 * the specification of Echelon, Affiliation, "2525" type, Size, or Symbol ID Code (sidc) via
 * URL arguments.  Please see the readme.md under the top directory.
 *
 * If no image is supplied, and a sidc w/ country code is provided, then an image will be obtained from
 * from the net (experimental).
 *
 * Copyright (c) 2015 George Patton Simcox, email: geo.simcox@gmail.com
 * All Rights Reserved
 *
 */

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Echelon extends Model {

    // echelon lookup
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

    // valid image extensions
    private $ext_good = [
        'jpg',
        'png',
        'ppng',
        'gif',
        'bmp'
    ];

    /**
     * Extract the echelon from the sidc if available.  Default to NULL (--)otherwise.
     *
     * @param null $sidc
     * @return string
     */
    public function extractEchelonFromSidc($sidc=null) {
        if (!empty($sidc) && strlen($sidc) >= 10) {
            $echelon = substr($sidc, 10, 2);
        } else {
            $echelon = "--";
        }
        return($echelon);
    }

    /**
     * Validate then Match up provided echelon to the with what code/unicode should be placed above the frame.
     * Note that the N echelon only is valid for the MIL-STD-2525C set.  If the echelon is not "valid", then
     * send it on as is to be positioned in place of the echelon.  In the end, only the first eight characters will be used.
     *
     * @param null $echelon
     * @param null $setc
     * @return null
     */
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

    /**
     * Test and return some information for the image provided by the user, or the sidc if the country
     * code is used to get a flag.
     *
     * @param null $url
     * @return array
     */
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

    /**
     * Set the is_2525c, if appropriate, by returning true.  What we are looking for is the last
     * character and if it is a 'c', then set true, or if anything else, use b by returning false.
     * This allows the user to enter 2525c, c, or mil-std-2525c, etc.
     *
     * @param null $symbol_set
     * @return bool
     */
    public function testFor2525c($symbol_set=null){
        if (!empty($symbol_set)){
            if (preg_match('/[Cc]$/', $symbol_set)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Return the affiliation (identity/exercise amplification) from the provided Symbol ID Code.
     *
     * @param null $sidc
     * @return bool|string
     */
    public function extractAffiliationFromSidc($sidc=null){
        if (!empty($sidc) && strlen($sidc) >= 2){
            $affiliation = substr($sidc, 1,1);
            if (preg_match('/^[ADGHJKLMNPSUWadghjklmnpsuw]$/', $affiliation)) {
                return $affiliation;
            }
        }
        return false;
    }

    /**
     * test to see if the provided affiliation indicates the "assumed/suspect" condition.
     * Returning true means that we need to use the 'assumed/pending" dashed frame, or
     * use '?' indicator.
     *
     * @param null $source
     * @return bool
     */
    public function testAssumed($source=null) {
        if (!empty($source)) {
            if (preg_match('/^[AGPSWagpsw]$/', $source)) {
                return true;
            }
        }
        return false;
    }

    /**
     * test to see if the provided affiliation indicates the "joker" condition.
     * Returning true means that we need to use the "J" indicator.
     *
     * @param null $source
     * @return bool
     */
    public function testJoker($source=null) {
        if (!empty($source)) {
            if (preg_match('/^[Jj]$/', $source)) {
                return true;
            }
        }
        return false;
    }

    /**
     * test to see if the provided affiliation indicates the "faker" condition.
     * Returning true means that we need to use the "K" indicator.
     *
     * @param null $source
     * @return bool
     */
    public function testFaker($source=null) {
        if (!empty($source)) {
            if (preg_match('/^[Ff]$/', $source)) {
                return true;
            }
        }
        return false;
    }

    /**
     * test to see if the provided affiliation indicates the "exercise" condition.
     * Returning true means that we need to use the "X" indicator.
     *
     * @param null $source
     * @return bool
     */
    public function testExercise($source=null) {
        if (!empty($source)) {
            if (preg_match('/^[DGLMWdglmw]$/', $source)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check image info and return the orientation of the image.
     *
     * @param null $frame_image
     * @return bool
     */
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

    /**
     * Check to see if url exists
     *
     * @param $url
     * @return array|bool
     */
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
}
