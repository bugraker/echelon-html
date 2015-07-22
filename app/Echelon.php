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
        'H' => '#FF8080', // 255-128-128
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
    public $sidc;
    public $indicator;
    public $note;
    public $notex;

    public $is_2525c;           // use 2525c frame and echelons, otherwise use 2525b
    public $is_default;         // no image ($default_image)
    public $is_landscape;       // am landscape
    public $is_nocolor;         // do not colorize frame

    public $is_assumed;         // use assumed indication (2525b => ? indicator, 2525c => interrupted frame
    public $is_exercise;
    public $is_faker;
    public $is_feint_dummy;     // use FD indicator
    public $is_headquarters;    // use HQ staff - RFU
    public $is_installation;    // use installation indicator
    public $is_joker;
    public $is_mobility;        // use mobility indicators - RFU
    public $is_neutral;
    public $is_pending;
    public $is_task_force;      // use TF indicator
    public $is_towed_array;     // use towed array - RFU
    public $is_unknown;


    /**
     * Extract the echelon from the sidc if available.  Default to NULL (--)otherwise.
     *
     * @return string
     */
    public function getEchelonFromSidc()
    {
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
    private function getEchelon()
    {
        if (!empty($this->is_2525c)) {
            $pattern = '/[A-Na-n-]$/'; // MIL-STD2525C echelons
        } else {
            $pattern = '/[A-Ma-m-]$/'; // MIL-STD2525B echelons
        }

        if (!empty($this->echelon) && strlen($this->echelon) <= 2) {
            // we are only interested in the last place.
            $ech1 = strtoupper(substr($this->echelon, strlen($this->echelon) - 1, 1));

            if (preg_match($pattern, $ech1) && !empty($this->echelons[$ech1])) {
                // return lookup
                $this->echelon = $this->echelons[$ech1];
            }
            return ($this->echelon);
        } elseif (!is_string($this->echelon)) {
            $this->echelon = '';
        }

        return($this->echelon);
    }

    /**
     * Process echelon.
     *
     * @return null|string
     */
    public function processEchelon()
    {
        if (!empty($this->is_installation)) {
            $output = "&nbsp;";  // suppress echelon display
        } elseif (!empty($this->is_mobility) || !empty($this->is_towed_array)) {
            $output = $this->echelon; // display symbol modifier text for echelon
        } else {
            $output = $this->getEchelon(); // process symbol modifier and get echelon indicator
        }
        return($output);
    }

    /**
     * Test and return some information for the image provided by the user, or the sidc if the country
     * code is used to get a flag.
     *
     * @param bool $fotw
     * @return array
     */
    public function testImage($fotw=false)
    {
        if (!empty($this->default_image)) {
            $test = $this->url_exists($this->default_image);
            if (!$test === false) {
                $parts = pathinfo($this->default_image);
                $ext = $parts['extension'];
                if (in_array($ext, $this->ext_good)) {
                    $this->is_landscape = $this->isLandscape(array('width' => $test[0], 'height' => $test[1]));
                    $this->is_default = false;
                    return ([
                        'height' => $test[1],
                        'width' => $test[0],
                        'url' => $test['url'],
                        'fotw' => $fotw
                    ]);
                }
            }
        }

        // didn't get image - use color swatch
        $swatch = $this->getIdentityColorSwatch($this->identity);
        $image_info = getimagesize($swatch);
        $this->is_landscape = true;
        $this->is_default = true;
        return([
            'height' => $image_info[1],
            'width' => $image_info[0],
            'url' => $swatch,
            'fotw' => false
        ]);
    }

    /**
     * Match up identity with its associated color
     *
     * @return mixed
     */
    public function getIdentityColor()
    {
        if (!empty($this->identity) && preg_match('/^[ADFGHJKLMNPSUWadfghjklmnpsuw]$/',$this->identity)) {
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
    public function getIdentityColorSwatch()
    {

        $this->is_landscape = true;
        if (!empty($this->identity) && preg_match('/^[ADFGHJKLMNPSUWadfghjklmnpsuw]$/',$this->identity)) {
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
    public function is2525c($symbol_set=null)
    {
        if (!empty($symbol_set)) {
            if (preg_match('/[Bb]$/', $symbol_set)) {
                $this->is_2525c = false;
            } else {
                $this->is_2525c = true;
            }
        } else {
            $this->is_2525c = true;
        }
        return($this->is_2525c);
    }

    /**
     * Return the affiliation (identity/exercise amplification) from the provided Symbol ID Code.
     *
     * @param null $sidc
     * @return bool|string
     */
    public function getIdentityFromSidc($sidc=null)
    {
        if (!empty($sidc) && strlen($sidc) >= 2) {
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
    public function getIdentity($source=null)
    {
        if (!empty($source)) {
            if (preg_match('/^[ADFGHJKLMNPSUWadfghjklmnpsuw]$/', substr($source,0,1))) {
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
    public function isAssumed()
    {
        if (!empty($this->identity)) {
            if (preg_match('/^[AGMPSagmps]$/', $this->identity)) {
                if (!$this->is_2525c) {
                    $this->indicator = '?' . $this->indicator;
                }
                $this->is_assumed = true;
            } else {
                $this->is_assumed = false;
            }
        } else {
            $this->is_assumed = false;
        }
        return($this->is_assumed);
    }

    /**
     * test to see if the provided Echelon indicates a "Head Quarters" condition.
     * Returning true means that we need to use the "HQ" indicator.
     *
     * @return bool
     */
    public function isHeadQuarters()
    {
        $this->is_headquarters = false;
        if (!empty($this->echelon)) {
            if (preg_match('/^[BCDbcd][A-Ma-m-]$/', $this->echelon)) {
                $this->is_headquarters = true;
            }
        }
        return($this->is_headquarters);
    }

    /**
     * test to see if the provided Echelon indicates a "Task Force" condition.
     * Returning true means that we need to use the "TF" indicator.
     *
     * @return bool
     */
    public function isTaskForce()
    {
        $this->is_task_force = false;
        if (!empty($this->echelon)) {
            if (preg_match('/^[BDEGbdeg][A-Ma-m-]$/', $this->echelon)) {
                $this->is_task_force = true;
            }
        }
        return($this->is_task_force);
    }

    /**
     * test to see if the provided Echelon indicates a "Feint/Dummy" condition.
     * Returning true means that we need to use the "Feint/Dummy" indicator.
     *
     * @return bool
     */
    public function isFeintDummy()
    {
        $this->is_feint_dummy = false;
        if (!empty($this->echelon)) {
            if (preg_match('/^[CDFGcdfg][A-Ma-m-]$/', $this->echelon)) {
                $this->is_feint_dummy = true;
            }
        }
        return($this->is_feint_dummy);
    }

    /**
     * test to see if the provided Echelon indicates an "installation" condition.
     * Returning true means that we need to use the "installation" indicator.
     *
     * @return bool
     */
    public function isInstallation()
    {
        $this->is_installation = false;
        if (!empty($this->echelon)) {
            if (preg_match('/^[Hh]\-$/', $this->echelon)) {
                $this->is_installation =  true;
            }
        }
        return($this->is_installation);
    }

    /**
     * test to see if the provided Echelon indicates a "mobility" condition.
     * Returning true means that we need to use a "mobility" indicator.
     *
     * @return bool
     */
    public function isMobility()
    {
        $this->is_mobility = false;
        if (!empty($this->echelon)) {
            if (preg_match('/^[Mm][O-Yo-y]$/', $this->echelon)) {
                $this->is_mobility = true;
            }
        }
        return($this->is_mobility);
    }

    /**
     * test to see if the provided Echelon indicates a "towed array" condition.
     * Returning true means that we need to use a "towed array" indicator.
     *
     * @return bool
     */
    public function isTowedArray()
    {
        $this->is_towed_array = false;
        if (!empty($this->echelon)) {
            if (preg_match('/^[Nn][SLsl]$/', $this->echelon)) {
                $this->is_towed_array = true;
            }
        }
        return($this->is_towed_array);
    }

    /**
     * test to see if the provided Identity indicates a "neutral" condition.
     *
     * @return bool
     */
    public function isNeutral()
    {
        $this->is_neutral = false;
        if (!empty($this->identity)) {
            if (preg_match('/^[LNln]$/', $this->identity)) {
                $this->is_neutral = true;
            }
        }
        return($this->is_neutral);
    }

    /**
     * test to see if the provided Identity indicates the "unknown" condition.
     *
     * @return bool
     */
    public function isUnknown()
    {
        $this->is_unknown = false;
        if (!empty($this->identity)) {
            if (preg_match('/^[UWuw]$/', $this->identity)) {
                $this->is_unknown = true;
            }
        }
        return($this->is_unknown);
    }

    /**
     * test to see if the provided Identity indicates a "pending" condition.
     *
     * @return bool
     */
    public function isPending()
    {
        $this->is_pending = false;
        if (!empty($this->identity)) {
            if (preg_match('/^[GPgp]$/', $this->identity)) {
                $this->is_pending = true;
            }
        }
        return($this->is_pending);
    }

    /**
     * test to see if the provided Identity indicates a "joker" condition.
     * Returning true means that we need to use the "J" indicator.
     *
     * @return bool
     */
    public function isJoker()
    {
        $this->is_joker = false;
        if (!empty($this->identity)) {
            if (preg_match('/^[Jj]$/', $this->identity)) {
                $this->indicator = 'J';
                $this->is_joker = true;
            }
        }
        return($this->is_joker);
    }

    /**
     * test to see if the provided Identity indicates a "faker" condition.
     * Returning true means that we need to use the "K" indicator.
     *
     * @return bool
     */
    public function isFaker()
    {
        $this->is_faker = false;
        if (!empty($this->identity)) {
            if (preg_match('/^[Kk]$/', $this->identity)) {
                $this->indicator = 'K';
                $this->is_faker = true;
            }
        }
        return($this->is_faker);
    }

    /**
     * test to see if the provided Identity indicates an "exercise" condition.
     * Returning true means that we need to use the "X" indicator.
     *
     * @return bool
     */
    public function isExercise()
    {
        $this->is_exercise = false;
        if (!empty($this->identity)) {
            if (preg_match('/^[DGLMWdglmw]$/', $this->identity)) {
                $this->indicator = 'X';
                $this->is_exercise = true;
            }
        }
        return($this->is_exercise);
    }

    /**
     * Check image info and return the orientation of the image.
     *
     * @param null $frame_image
     * @return bool
     */
    public function isLandscape($frame_image=null)
    {
        $this->is_landscape = true;
        if (!empty($frame_image)) {
            if ($frame_image['width'] <= $frame_image['height'] ) {
                $this->is_landscape = false; // image is portrait
            }
        }
        return($this->is_landscape);
    }

    /**
     * Check to see if url exists
     *
     * @param $url
     * @return array|bool
     */
    private function url_exists($url)
    {
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
