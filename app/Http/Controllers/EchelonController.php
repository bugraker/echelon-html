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
 * Copyright (c) 2015 George Patton Simcox, email: geo.simcox@gmail.com
 * All Rights Reserved
 *
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

        /** URI parameters **/

        /** SIDC **/
        if (!empty($_REQUEST['sidc'])) {
            $this->model->sidc = $_REQUEST['sidc'];
        } else {
            // alrighty then, just default to SFGP-----------
            $this->model->sidc = 'SFGP-----------';
        }

        /** IDENT **/
        // affiliation override -  f = frd, a = assumed -or- the affiliation from the sidc (2nd place character).
        if (!empty($_REQUEST['ident'])) {
            $this->model->identity = $_REQUEST['ident'];
        }

        /** ECH **/
        // echelon override -  a one or two place value that overrides the sidc or default sidc.
        if (!empty($_REQUEST['ech'])) {
            $ech = substr($_REQUEST['ech'], 0, 8);
        }

        /** NOTE **/
        // note override (not mil spec) -  add a short note under frame.
        if (!empty($_REQUEST['note'])) {
            $this->model->note = substr($_REQUEST['note'], 0, 12);
        } else {
            $this->model->note = '';
        }

        /** SET **/
        // echelon override -  a one or two place value that overrides the sidc or default sidc.
        if (!empty($_REQUEST['set'])) {
            $set = $_REQUEST['set'];
        } else {
            $set = 'mil-std-2525c';
        }

        /** SIZE **/
        if (!empty($_REQUEST['size'])) {
            $size = $_REQUEST['size'];
            if ($size < 100) {
                $size = 100; // min size allowed
            }
        } else {
            $size = 100; // default size
        }

        /** NC */
        if (!empty($_REQUEST['nc'])) {
            $this->model->nocolor = true;
        } else {
            $this->model->nocolor = false;
        }


        /** START **/

        // determine identity
        if (!empty($this->model->identity)) {
            // use provided affiliation override
            $this->model->identity = $this->model->testAndReturnIdent($this->model->identity);
            $this->model->sidc = substr($this->model->sidc,0,1).$this->model->identity.substr($this->model->sidc,2,12); // update the "default" or given sidc
        } else {
            // determine identity from sidc
            $this->model->identity = $this->model->getIdentityFromSidc($this->model->sidc);
        }

        /** IMAGE **/
        if (!empty($_REQUEST['image'])) {
            // user spec'ed image
            $image = $_REQUEST['image'];
            $frame_image = $this->model->testImage($image);
            $frame_image['default'] = false;

        } elseif (strlen($this->model->sidc) >= 15 && substr($this->model->sidc, 12, 2) <> '--') {
            // flag image
            $cc = strtolower(substr($this->model->sidc, 12, 2));
            $cc1 = $cc[0];
            $image = "https://flagspot.net/images/" . $cc1 . "/" . $cc . ".gif";
            $frame_image = $this->model->testImage($image);
            $frame_image['default'] = false;
            $frame_image['fotw'] = true;

        } else {
            // default
            $default_image = $this->model->getIdentityColorSwatch();

            $frame_info = getimagesize($default_image);

            $frame_image['height'] = $frame_info[1];
            $frame_image['width'] = $frame_info[0];
            $frame_image['url'] = $default_image;
            $frame_image['default'] = true;
        }

        // determine echelon
        if (!empty($ech)) {
            // use provided echelon override
            $this->model->echelon = filter_var($ech, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW); // size 1-8 chars
        } else {
            // determine echelon from sidc
            $this->model->echelon = $this->model->getEchelonFromSidc(); // size 2 chars
        }

        $output['is_2525c'] = $this->model->is2525c($set);

        if (strlen($this->model->echelon) <= 2) {
            $output['echelon'] = $this->model->getEchelon(); // get echelon (convert)
        } else {
            $output['echelon'] = $this->model->echelon;  // use as-is
        }

        if (strlen($this->model->echelon) <=2 && $this->model->isInstallation($this->model->echelon)) {
            $output['is_installation'] = true;
        } else {
            $output['is_installation'] = false;
        }

        $this->model->indicator = "";
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
            $output['indicator'] = '&nbsp;'.$this->model->indicator;
        }

        // image post-processing
        $output['image'] = $frame_image['url']; // background image
        $output['image_txt'] = "Is hiding"; // missing image text
        $output['default'] = $frame_image['default'];

        $output['bg_color'] = $this->model->getIdentityColor();
        $output['nocolor'] = $this->model->nocolor;
        $output['fotw'] = (!empty($frame_image['fotw']) ? $frame_image['fotw'] : false) ; // image from the Flags of the World website

        $output['note'] = $this->model->note;

        header('Content-Type: text/html; charset=utf-8');
        return view('echelon.show', $output);
    }

}
