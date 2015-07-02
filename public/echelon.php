<?php

/* 
 * Copyright 2015 m6500.
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


// This tool will accept a URL request and create an unit based on the URI's SIDC (optional), and image (optional) parameters.

// URI parameters
// image
if (!empty($_REQUEST['image'])) {
    
    $image = $_REQUEST['image'];
    
    // check extension
    
    
} else {
    $image = './images/default.png';
}

// SIDC
if (!empty($_REQUEST['sidc'])) {
    
    $sidc = $_REQUEST['sidc'];
    
} else {
    
    // alrighty then, just default to SFGP-----------
    $sidc = 'SFGP-----------';
    
}

