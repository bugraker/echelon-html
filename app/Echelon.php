<?php namespace App;
/*
 * Copyright 2015 George P. Simcox, email: geo.simcox@gmail.com.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

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
 *
 * License:
 *
 *  Copyright 2015 George P. Simcox, email: geo.simcox@gmail.com.
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *  limitations under the License.
 */

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Echelon extends Model {

    // echelon lookup
    private $echelons = [
        '-' => '&nbsp;', // NULL
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

    private $colorByIdentity = [
        'A' => '#80E0FF', // 128-224-255
        'D' => '#80E0FF',
        'F' => '#80E0FF',
        'G' => '#FFFF80', // 255-255-128
        'H' => '#FF8080', //255-128-128
        'J' => '#FF8080',
        'K' => '#FF8080',
        'L' => '#AAFFAA', // 170-255-170
        'M' => '#80E0FF',
        'N' => '#AAFFAA',
        'P' => '#FFFF80',
        'S' => '#FF8080',
        'U' => '#FFFF80',
        'W' => '#FFFF80'
    ];

    public $identity;
    public $echelon;
    public $default_image;
    public $image;
    public $sidc;
    public $indicator;
    public $is2525c;
    public $is_assumed;
    public $note;
    public $notex;
    public $nocolor;

    /**
     * Extract the echelon from the sidc if available.  Default to NULL (--)otherwise.
     *
     * @return string
     */
    public function getEchelonFromSidc() {
        if (!empty($this->sidc) && strlen($this->sidc) >= 10) {
            $this->echelon = substr($this->sidc, 10, 2);
        } else {
            $this->echelon = "--";
        }
        return($this->echelon);
    }

    /**
     * Validate then Match up provided echelon to the with what code/unicode should be placed above the frame.
     * Note that the N echelon only is valid for the MIL-STD-2525C set.  If the echelon is not "valid", then
     * send it on as is to be positioned in place of the echelon.  In the end, only the first eight characters will be used.
     *
     * @return null
     */
    public function getEchelon()
    {
        if (!empty($this->is2525c)) {
            $pattern = '/[A-Na-n-]$/';
        } else {
            $pattern = '/[A-Ma-m-]$/';
        }

        if (!empty($this->echelon)) {
            // we are only interested in the last place.
            $ech1 = strtoupper(substr($this->echelon, strlen($this->echelon) - 1, 1));

            if (preg_match($pattern, $ech1) && !empty($this->echelons[$ech1])) {
                // return lookup
                $this->echelon = $this->echelons[$ech1];
            } else {
                $this->echelon = $this->echelon;
            }
            return($this->echelon);
        }
        // default
        $this->echelon = '-';
        return($this->echelon);
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

        $default_image = $this->getIdentityColorSwatch($this->identity);
        $image_info = getimagesize($default_image);
        return([
            'height' => $image_info[1],
            'width' => $image_info[0],
            'url' => $default_image,
            'default' => true
        ]);
    }

    public function getIdentityColor()
    {
        if (!empty($this->identity) && preg_match('/^[ADFGHJKLMNPSUWadfghjklmnpsuw]$/',$this->identity)){
            $color = $this->colorByIdentity[strtoupper($this->identity)];
        } else {
            $color = $this->colorByIdentity['U'];
        }
        return($color);
    }

    /**
     * Return the color swatch that corresponds to the identity given.
     *
     * @return string
     */
    public function getIdentityColorSwatch(){

        if (!empty($this->identity) && preg_match('/^[ADFGHJKLMNPSUWadfghjklmnpsuw]$/',$this->identity)){
            switch (strtoupper($this->identity)) {
                case 'A':
                case 'D':
                case 'F':
                case 'M':
                    $return_color = '../resources/assets/img/swatches/crystal_blue.png';
                break;
                case 'H':
                case 'J':
                case 'K':
                case 'S':
                    $return_color = '../resources/assets/img/swatches/salmon.png';
                break;
                case 'L':
                case 'N':
                    $return_color = '../resources/assets/img/swatches/bamboo_green.png';
                    break;
                default:
                    $return_color = '../resources/assets/img/swatches/light_yellow.png';
            }
            return $return_color;
        }
        return '../resources/assets/img/swatches/light_yellow.png';
    }

    /**
     * Set the is_2525c, if appropriate, by returning true.  What we are looking for is the last
     * character and if it is a 'c', for example,  then set true, or if anything else, use b by returning false.
     * This allows the user to enter 2525c, c, or mil-std-2525c, etc.
     *
     * Unrecognized last character nets the default = c.
     *
     * @param null $symbol_set
     * @return bool
     */
    public function is2525c($symbol_set=null){
        if (!empty($symbol_set)){
            if (preg_match('/[Bb]$/', $symbol_set)) {
                $this->is2525c = false;
            } else {
                $this->is2525c = true;
            }
        } else {
            $this->is2525c = true;
        }
        return($this->is2525c);
    }

    /**
     * Return the affiliation (identity/exercise amplification) from the provided Symbol ID Code.
     *
     * @param null $sidc
     * @return bool|string
     */
    public function getIdentityFromSidc($sidc=null){
        if (!empty($sidc) && strlen($sidc) >= 2){
            $affiliation = substr($sidc, 1,1);
            if (preg_match('/^[ADGHJKLMNPSUWadghjklmnpsuw]$/', $affiliation)) {
                return $affiliation;
            }
        }
        return 'F';
    }

    /**
     * Return the Identity if valid.
     *
     * @param null $source
     * @return bool
     */
    public function testAndReturnIdent($source=null) {
        if (!empty($source)) {
            if (preg_match('/^[ADGHJKLMNPSUWadghjklmnpsuw]$/', substr($source,0,1))) {
                return substr($source,0,1);
            }
        }
        return false;
    }

    /**
     * test to see if the provided Identity indicates the "assumed/suspect" condition.
     * Returning true means that we need to use the 'assumed/pending" dashed frame, or
     * use '?' indicator.
     *
     * @return bool
     */
    public function isAssumed() {
        if (!empty($this->identity)) {
            if (preg_match('/^[AGMPSagmps]$/', $this->identity)) {
                if (!$this->is2525c){
                    $this->indicator = '?'.$this->indicator;
                }
                $this->is_assumed = true;
            }
        } else {
            $this->is_assumed = false;
        }

        return($this->is_assumed);
    }

    /**
     * test to see if the provided Identity indicates an "installation" condition.
     * Returning true means that we need to use the "installation" indicator.
     *
     * @return bool
     */
    public function isInstallation() {
        if (!empty($this->echelon)) {
            if (preg_match('/^[Hh]\-$/', $this->echelon)) {
                return true;
            }
        }
        return false;
    }

    /**
     * test to see if the provided Identity indicates a "neutral" condition.
     *
     * @return bool
     */
    public function isNeutral() {
        if (!empty($this->identity)) {
            if (preg_match('/^[LNln]$/', $this->identity)) {
                return true;
            }
        }
        return false;
    }

    /**
     * test to see if the provided Identity indicates the "unknown" condition.
     *
     * @return bool
     */
    public function isUnknown() {
        if (!empty($this->identity)) {
            if (preg_match('/^[UWuw]$/', $this->identity)) {
                return true;
            }
        }
        return false;
    }

    /**
     * test to see if the provided Identity indicates a "pending" condition.
     *
     * @return bool
     */
    public function isPending() {
        if (!empty($this->identity)) {
            if (preg_match('/^[GPgp]$/', $this->identity)) {
                return true;
            }
        }
        return false;
    }

    /**
     * test to see if the provided Identity indicates a "joker" condition.
     * Returning true means that we need to use the "J" indicator.
     *
     * @return bool
     */
    public function isJoker() {
        if (!empty($this->identity)) {
            if (preg_match('/^[Jj]$/', $this->identity)) {
                $this->indicator = 'J';
                return true;
            }
        }
        return false;
    }

    /**
     * test to see if the provided Identity indicates a "faker" condition.
     * Returning true means that we need to use the "K" indicator.
     *
     * @return bool
     */
    public function isFaker() {
        if (!empty($this->identity)) {
            if (preg_match('/^[Kk]$/', $this->identity)) {
                $this->indicator = 'K';
                return true;
            }
        }
        return false;
    }

    /**
     * test to see if the provided Identity indicates an "exercise" condition.
     * Returning true means that we need to use the "X" indicator.
     *
     * @return bool
     */
    public function isExercise() {
        if (!empty($this->identity)) {
            if (preg_match('/^[DGLMWdglmw]$/', $this->identity)) {
                $this->indicator = 'X';
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
    public function isLandscape($frame_image=null) {
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
