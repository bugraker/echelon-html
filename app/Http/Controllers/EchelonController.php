<?php namespace App\Http\Controllers;

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
            $sidc = $_REQUEST['sidc'];
        } else {
            // alrighty then, just default to SFGP-----------
            $sidc = 'SFGP-----------';
        }

        /** AFF  **/
        // affiliation override -  f = frd, a = assumed -or- the affiliation from the sidc (2nd place character).
        if (!empty($_REQUEST['aff'])) {
            $aff = $_REQUEST['aff'];
        }

        /** ECH **/
        // echelon override -  a one or two place value that overrides the sidc or default sidc.
        if (!empty($_REQUEST['ech'])) {
            $ech = substr($_REQUEST['ech'], 0, 8);
        }

        /** NOTE **/
        // note override (not mil spec) -  add a short note under frame.
        if (!empty($_REQUEST['note'])) {
            $note = substr($_REQUEST['note'], 0, 18);
        } else {
            $note = '';
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

        /** IMAGE **/
        if (!empty($_REQUEST['image'])) {
            $image = $_REQUEST['image'];
            $frame_image = $this->model->testImage($image);

        } elseif (strlen($sidc)>=15 && substr($sidc,12,2) <> '--') {
            $cc = strtolower(substr($sidc,12,2));
            $cc1 = $cc[0];
            $image = "https://flagspot.net/images/".$cc1."/".$cc.".gif";
            $frame_image = $this->model->testImage($image);
            $frame_image['default'] = 'fotw';
        } else {
            // default
            $default_image = '../resources/assets/img/frd.png';
            $frame_info = getimagesize($default_image);

            $frame_image['height'] = $frame_info[1];
            $frame_image['width'] = $frame_info[0];
            $frame_image['url'] = $default_image;
            $frame_image['default'] = true;
        }

        /** START **/
        // determine affiliation
        if (!empty($aff)) {
            // use provided affiliation override
            $affiliation = $aff;
        } else {
            // determine affiliation from sidc
            $affiliation = $this->model->extractAffiliationFromSidc($sidc);
        }

        // determine echelon
        if (!empty($ech)) {
            // use provided echelon override
            $echelon = filter_var($ech, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW);
        } else {
            // determine echelon from sidc
            $echelon = $this->model->extractEchelonFromSidc($sidc);
        }

        $output['is_2525c'] = $this->model->testFor2525c($set); // echelon font size
        $output['echelon'] = $this->model->getEchelon($echelon, $output['is_2525c']); // get echelon
        $output['is_assumed'] = $this->model->testAssumed($affiliation); // get frame type
        $output['is_landscape'] = $this->model->testOrientation($frame_image); // get orientation

        $output['font'] = $size; // echelon font size
        $output['size'] = $size; // output size
        $output['frame'] = (!empty($output['is_2525c'])? $size : $size * 0.8); // output size
        $output['multiplier'] = $size / 100; // output size
        $output['image'] = $frame_image['url']; // background image
        $output['image_txt'] = "Is hiding"; // missing image text
        $output['default'] = $frame_image['default']; // image from the Flags of the World website
        $output['note'] = $note;

        header('Content-Type: text/html; charset=utf-8');
        return view('echelon.show', $output);
    }

}
