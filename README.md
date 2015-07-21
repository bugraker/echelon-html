## Echelon - HTML
 
 The purpose of this tool is to display a specified image within a MIL-STD-2525C (MIL-STD-2525B optional) friendly or assumed-friendly unit 
 frame with specified symbol modifiers.  The modifiers are communicated by positions 11 & 12 of the MIL-2525 Symbol ID Code ('sidc') or 'ech' 
 url parameters.
 
 The default will show a crystal blue frame, however the image parameter is used to specify an image to be contained within the frame.
 If the "sidc" parameter is used without an valid image specified, the tool will look for the country code in the specified Symbol ID Code and
 attempt to obtain a corresponding flag image to display within the frame, otherwise the crystal blue is used, or other color as defined by 
 the sidc identity parameter.  Note, access to the derived flag image is subject to availability and internet connectivity - if it is desirous 
 that the a flag image appear 100% of the time, then the user should specify the url to a flag image in all cases.
 
 Note:  The image will be tested to see if it has landscape or portrait dimensions.  If it is landscape, the image will be stretched to fit the frame.
 If the image is portrait, then its aspect ration will be maintained.  As with transparent images, the identity color will be the background color.
 
 Symbol modifiers codes not currently supported are: 
   * HQ post/staff - not displayed, although other modifier pieces will be displayed
   * Mobility - not displayed, although the echelon text appear at the top of the symbol.  Ex:  'mp' will appear above the symbol instead of the 
     'mp' indicator below the symbol
   * Towed Array - not displayed, although the echelon text appear at the top of the symbol.
    
 
 Please see "Usage" below.

### Compatability

Tested and works with:

* Apple Safari 7.1.2
* Google Chrome 43.0
* Mozilla Firefox 38.0.5
* MS Internet Explorer 11

Does not work with

* MS Internet Explorer 8

### License

Copyright 2015 George P. Simcox, email: geo.simcox@gmail.com.

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

     http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.

## Install & Configuration

The Echelon-HTML tool is a web package designed to be installed on your web server.  It was built using the 
PHP framework: Laravel.  The root is located a ./echelon-html/public.

- Prerequisites, you will need to have a web server with the following installed:
 * The Echelon - HTML software
 * a web (httpd) server to install the software on.
 * PHP version 5.5 +
 * The PHP package manager, Composer, installed

- Installation

  * Download/clone the tool in your chosen directory.
  * In a terminal, cd to the echelon-html directory and run: **composer install**
       
       Composer will read the composer.lock & composer.json files and will download and 
       install all required software.
  * create a new .env file by copying the existing .env.example.
  * run the command: php artisan key:generate.
  * In, your httpd server, configure it to serve out the public directory of the Echelon - HTML package.
       web_directory/echelon-html/public
       
## Usage

Navigate to the web page.  A default SFGP----------- symbol is displayed on a white 100x100 square.  Screen cap 
the square and save.  Use the overrides to alter the end result.

Available URL Arguments:

 * sidc   - The Symbol ID Code to use (See the MIL-STD-2525 standard document for proper format).  This is normally 
            a 15 char string, however this tool does not validate the length.  The tool will glean as much info as 
            available. Note: positions 1, 3-10, and 15 are ignored.  Note: the Function ID (5-10) are ignored.
         
               Info checked (by position):
                  2     - Affiliation. Used to generate appropriate frame or indicator.
                  11-12 - Echelon (size/mobility).  Used to generate appropriate size indicator.  Mobility is ignored.
                  13-14 - Country Code.  If a country code is given, and an image override is not specified, then
                          an attempt to produce an image will be made to the website "Flags of the World". (not mil 
                          spec).
                      
 * ech | echelon
          - Echelon (size/mobility) override (mobility ignored).  Overrides the sidc.  If not a valid echelon, the the first eight(8) characters
            are displayed (not mil spec).  Notes: Only size is recognized, mobility is ignored. Echelon "N" is only valid for MIL_STD_2525C.
         
                - =>  NULL
                A => TEAM/CREW
                B => SQUAD
                C => SECTION
                D => PLATOON/DETACHMENT
                E => COMPANY/BATTERY/TROOP
                F => BATTALION/SQUADRON
                G => REGIMENT/GROUP
                H => BRIGADE
                I => DIVISION
                J => CORPS/MEF
                K => ARMY
                L => ARMY/GROUP/FRONT
                M => REGION
                N => COMMAND (MIL-STD-2525C only)
                
            Note: although an "echelon" is technically a single character, the 'ech' parameter may be used to pass the two character 'symbol modifier'. 
            Ex: 'h-' will add the 'installation' indicator to the symbol.  Currently, the 'headquarters', 'mobility', and 'towed array' are not displayed, 
            although a text version of the symbol modifier may be added above the symbol.  I.E., the 'ns' towed array modifier will display the text: 'ns'
            above the symbol. 'bm' will only show the region echelon and suppress the headquarters staff indicator.

 * ident  - Override the identity  (affiliation).  This affects if the solid frame is modified.  an "assumed" 
            affiliation (A) will; 
                1) for MiL-STD-2525B a "Question Mark" indicator is added to the upper right of the Symbol border, 
                2) for MIL-STD-2525C the solid border is replaced by a dashed border. If an image is defined, either 
                   through the ident parameter or by the sidc county code, the dashed border whitespace will be colored
                   to match the identity
              
                    A => ASSUMED FRIEND (dashed or ?)
                    F => FRIEND 
                    
                  The following idents frame shapes and are ignored, although they will be rendered with a SFGP frame, unless they are marked with 
                  "(dashed or ?)", in which case they are rendered as with a SAGF frame:
                  
                    D => EXERCISE FRIEND            2525C: solid SFGP frame w/ X            -  2525B: solid SFGP frame w/ X
                    G => EXERCISE PENDING           2525C: dashed SFGP frame or X, yellow   -  2525B: solid SFGP frame w/ X
                    H => HOSTILE                    2525C: solid SFGP frame w/ X            -  2525B: solid SFGP frame
                    J => JOKER                      2525C: solid SFGP frame w/ J            -  2525B: solid SFGP frame w/ J
                    K => FAKER                      2525C: solid SFGP frame w/ F            -  2525B: solid SFGP frame w/ F
                    L => EXERCISE NEUTRAL           2525C: solid SFGP frame w/ X            -  2525B: solid SFGP frame w/ X
                    M => EXERCISE ASSUMED FRIEND    2525C: dashed SFGP frame or ?, blue     -  2525B: solid SFGP frame w/ ?X
                    N => NEUTRAL                    2525C: solid SFGP frame                 -  2525B: solid SFGP frame
                    P => PENDING                    2525C: dashed SFGP frame or ?, yellow   -  2525B: solid SFGP frame
                    S => SUSPECT                    2525C: dashed SFGP frame or ?, red      -  2525B: solid SFGP frame w/ ?
                    U => UNKNOWN                    2525C: solid SFGP frame w/ X            -  2525B: solid SFGP frame w/ ?
                    W => EXERCISE UNKNOWN           2525C: solid SFGP frame w/ X            -  2525B: solid SFGP frame w/ X
             
 * size   - Size override.  Overrides the default overall size of 100px square (white square).  The size may also be overridden
            by the "notex" parameter.
 
 * 2525b  - Overrides the default MIL-STD-2525C set with the MIL-STD-2525B set. Ex: http://localhost?sidc=sfgp&2525b
 
 * img | image  
          - Overrides the default crystal blue image.  Image will be re-sized to fit the MIL-STD-2525 frame.  Images with a 
            portrait aspect ratio will not have the image's width stretched. (Not mil spec.)
 
 * note   - Adds 12 characters under the frame (not mil spec).
 
            Ex: http://localhost/echelon-html/public/?ech=d&size=100&image=https://upload.wikimedia.org/wikipedia/commons/7/79/Operation_Upshot-Knothole_-_Badger_001.jpg&note=What,%20me%20worry?
 
 * notex  - Overrides the "size" parameter and adds extended note under the frame  Note: "\n" are converted to "<br>". (not mil spec.)
 
 * nc | nocolor
          - Do not color the SIDC frame if an image is displayed. Ex: http://localhost?sidc=sfgp&nc

Example:  Two examples...

          http://<echelon-html URL>?ech=m&size=300&image=https://www.gecop.mil/images/flags/pa.png
                      -- or --
          http://<echelon-html URL>?sidc=SFGP-------DUS-&size=600&2525b&nc


