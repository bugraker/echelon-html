<?php namespace App\Http\Controllers;

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

use App\Echelon;
use App\Http\Requests;


class EchelonController extends Controller {

    private $model;

    /**
     * Define object
     */
    public function __construct()
    {
        $this->model = new Echelon();
    }

    public function show()
    {
        // This tool will accept a URL request and create an unit based on the URI's SIDC (optional),
        // and image (optional) parameters.

        /** DEBUG **/
        if (!empty($_REQUEST) && array_key_exists('debug', $_REQUEST)) {
            $is_debug = true;
        }

        /** SIDC **/
        if (!empty($_REQUEST['sidc'])) {
            $this->model->sidc = $_REQUEST['sidc'];
        } else {
            // alrighty then, just default to SUGP - Unknown
            $this->model->sidc = 'SUGP-----------';
        }

        /** IDENT **/
        // affiliation override -  f = frd, a = assumed -or- the affiliation from the sidc (2nd place character).
        if (!empty($_REQUEST['ident'])) {
            $this->model->identity = $_REQUEST['ident'];
        }

        /** ECH **/
        // echelon override -  a one or two place value that overrides the sidc or default sidc.
        if (!empty($_REQUEST['ech'])) {
            $ech = substr($_REQUEST['ech'], 0, 8); // 8 char limit
        } elseif (!empty($_REQUEST['echelon'])) {
            $ech = substr($_REQUEST['echelon'], 0, 8); // 8 char limit
        }

        /** NOTE **/
        // note (not mil spec) -  add a short note under frame.
        if (!empty($_REQUEST['notex'])) {
            $this->model->notex = $_REQUEST['notex']; // limit to 20 characters
        } elseif (!empty($_REQUEST['note'])) {
            $this->model->note = substr($_REQUEST['note'], 0, 20); // limit to 20 characters
        } else {
            $this->model->note = '';
        }

        /** 2525b **/
        // frame set override.  Overrides the default MIL-STD-2525C set with the MIL-STD-2525B set.
        if (array_key_exists('2525b', $_REQUEST)) {
            $this->model->is_2525c = false;
        } else {
            $this->model->is_2525c = true;
        }

        /** SIZE **/
        if (!empty($_REQUEST['size']) && preg_match('/^\d*$/', $_REQUEST['size'])) {
            $size = $_REQUEST['size'];
            if ($size < 100) {
                $size = 100; // min size allowed
            }
        } else {
            $size = 100; // default size
        }

        /** NC */
        // Normally, if an image is displayed within the frame, the frame color is changed to reflect the identity.  This overrides this.
        if (array_key_exists('nc', $_REQUEST) || array_key_exists('nocolor', $_REQUEST)) {
            $this->model->is_nocolor = true;
        } else {
            $this->model->is_nocolor = false;
        }

        // determine identity
        if (!empty($this->model->identity)) {
            // use provided affiliation override
            $this->model->identity = $this->model->getIdentity($this->model->identity);
            $this->model->sidc = substr($this->model->sidc,0,1) . $this->model->identity.substr($this->model->sidc,2,12); // update the "default" or given sidc
        } else {
            // determine identity from sidc
            $this->model->identity = $this->model->getIdentityFromSidc($this->model->sidc);
        }

        /** IMAGE **/
        if (array_key_exists('image', $_REQUEST)) {
            $this->model->default_image = $_REQUEST['image'];
        } elseif (array_key_exists('img', $_REQUEST)) {
            $this->model->default_image = $_REQUEST['img'];
        }

        if (!empty($this->model->default_image)) {
            // user spec'ed image
            $frame_image = $this->model->testImage();

        } elseif (strlen($this->model->sidc) >= 15 && substr($this->model->sidc, 12, 2) <> '--') {
            // flag image
            $cc = strtolower(substr($this->model->sidc, 12, 2));
            $cc1 = $cc[0];
            $this->model->default_image = "https://flagspot.net/images/" . $cc1 . "/" . $cc . ".gif";
            $frame_image = $this->model->testImage(true);

        } else {
            // default, no image
            $default_image = $this->model->getIdentityColorSwatch();

            $frame_info = getimagesize($default_image);

            $frame_image['height'] = $frame_info[1];
            $frame_image['width'] = $frame_info[0];
            $frame_image['url'] = $default_image;
            $this->model->is_default = true;
        }

        // determine echelon
        if (!empty($ech)) {
            // use provided echelon override
            $this->model->echelon = filter_var($ech, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW); // size 1-8 chars
        } else {
            // determine echelon from sidc
            $this->model->echelon = $this->model->getEchelonFromSidc(); // size 2 chars
        }

        $this->model->indicator = ""; // init indicator

        $output['is_2525c'] = $this->model->is_2525c;
        $output['is_hq'] = $this->model->isHeadQuarters();
        $output['is_task_force'] = $this->model->isTaskForce();
        $output['is_feint_dummy'] = $this->model->isFeintDummy();

        // following must come before use of getEchelon method.
        $output['is_installation'] = $this->model->isInstallation();
        $output['is_mobility'] = $this->model->isMobility();
        $output['is_towed_array'] = $this->model->isTowedArray();

        // processing echelon
        $output['echelon'] =$this->model->processEchelon();

        $output['is_faker'] = $this->model->isFaker();
        $output['is_joker'] = $this->model->isJoker();
        $output['is_exercise'] = $this->model->isExercise();
        $output['is_assumed'] = $this->model->isAssumed();
        $output['is_neutral'] = $this->model->isNeutral();
        $output['is_unknown'] = $this->model->isUnknown();
        $output['is_pending'] = $this->model->isPending();

        $output['font'] = $size; // echelon font size
        $output['size'] = $size; // output size
        $output['frame'] = $size * 0.75; // output size c=100%, b=80%
        $output['multiplier'] = $size / 100; // output size

        if (!empty($this->model->indicator) && strlen($this->model->indicator) > 1) {
            $output['indicator'] = $this->model->indicator;
        } else {
            $output['indicator'] = '&nbsp;' . $this->model->indicator;
        }

        // image post-processing
        $output['image'] = $frame_image['url']; // background image
        $output['image_txt'] = "Is hiding"; // missing image text
        $output['is_landscape'] = $this->model->is_landscape;

        // frame fill color
            $output['fill_color'] = $this->model->getIdentityColor();

        // frame color
        if (!empty($this->model->is_default) && !empty($this->model->is_2525c) && !empty($this->model->is_assumed)) {
            $output['frame_color'] = 'white';
        } elseif (!empty($this->model->is_nocolor) && !empty($this->model->is_2525c) && !empty($this->model->is_assumed)) {
            $output['frame_color'] = 'white';
        } elseif (!empty($this->model->is_default)) {
            $output['frame_color'] = 'black';
        } elseif (!empty($this->model->is_nocolor)) {
            $output['frame_color'] = 'black';
        } else {
            $output['frame_color'] = $this->model->getIdentityColor();
        }

        $output['fotw'] = (!empty($frame_image['fotw']) ? $frame_image['fotw'] : false) ; // image from the Flags of the World website

        if (!empty($this->model->notex)) {
            $output['notex'] = htmlentities(str_replace('\n', '<br>', $this->model->notex));
        } elseif (!empty($this->model->note)) {
            $output['note'] = htmlentities($this->model->note);
        }

        if (!empty($is_debug)) {
            dd($output);
        }

        header('Content-Type: text/html; charset=utf-8');
        return view('echelon.show', $output);
    }

}
