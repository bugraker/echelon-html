## Echelon - HTML
 
 The purpose of this tool is to display a specified image within a MIL-STD-2525C (MIL-STD-2525B optional) friendly or assumed-friendly unit 
 frame with specified size indicator (echelon).
 
 The default will show a crystal blue frame, however the image parameter is used to specify an image to be contained within the frame.
 If the "sidc" parameter is used without an image specified, the tool will look for the country code in the specified Symbol ID Code and
 attempt to obtain a corresponding flag image to display within the frame, otherwise the crystal blue is used, or other color as defined by 
 the sidc identity parameter.  Note, access to the derived flag image is subject to availability and internet connectivity - if it is desirous 
 that the image appear 100% of the time, then the user should specify the url in all cases.
 
 The image rendered may be controlled (affected) by providing an overriding Identity (ident) parameter on the url, MIL-STD-2525 symbol set (B - reduced 80% or C), the 
 output size, image, and unit size (mobility ignored).  Please see "Usage" below.

## Install & Configuration

The Echelon-HTML tool is a web package designed to be installed on your web server.  It was built using the 
PHP framework: Laravel.  The root is located a ./echelon-html/public.

- Prerequisites:

You will need to have a web server with the following installed:
 * The Echelon - HTML software
 * a web (httpd) server to install the software on.
 * PHP version 5.5 +
 * The PHP package manager, Composer, installed

- Installation

  * Download/clone the tool in your chosen directory.
  * In a terminal, cd to the echelon-html directory and run:
       composer install
       
       Composer will read the composer.lock & composer.json files and will download and 
       install all required software.

  * In, your httpd server, configure it to serve out the public directory of the Echelon - HTML package.
       web_directory/echelon-html/public

## Usage

Navigate to the web page.  A default SFGP----------- symbol is displayed on a white 100x100 square.  Screen cap 
the square and save.  Use the overrides to alter the end result.

Available URL Arguments:

 *sidc     - The Symbol ID Code to use (See the MIL-STD-2525 standard document for proper format).  This is normally 
             a 15 char string, however this tool does not validate the length.  The tool will glean as much info as 
             available. Note: positions 1, 3-10, and 15 are ignored.  Note: the Function ID (5-10) are ignored.
         
               Info checked (by position):
                  2     - Affiliation. Used to generate appropriate frame or indicator.
                  11-12 - Echelon (size/mobility).  Used to generate appropriate size indicator.  Mobility is ignored.
                  13-14 - Country Code.  If a country code is given, and an image override is not specified, then
                          an attempt to produce an image will be made to the website "Flags of the World". (not mil 
                          spec).
                      
 *ech      - Echelon (size/mobility) override (mobility ignored).  Overrides the sidc.  If not a valid echelon, the the first eight(8) characters
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

 *ident    - Override the identity  (affiliation).  This affects if the solid frame is modified.  an "assumed" 
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
             
 *size     - Size override.  Overrides the default overall size of 100px square.  My also be overridden
             by the "notex" parameter.
 
 *set      - Overrides the default MIL-STD-2525C set.  Entry last character: "b" = MIL-STD-2525B, "c" = MIL-STD-2525C.  It should be noted that
             the size of the MIL-STD-2525B symbols are reduced 80% so that there is enough room for the assumed friend indicator.
 
 *image    - Overrides the default crystal blue image.  Image will be re-sized to fit the MIL-STD-2525 frame. Not mil spec.
 
 *note     - Adds 12 characters under the frame (not mil spec).
 
 *notex    - Overrides the "size" parameter and adds extended note under the frame (not mil spec). Note: "\n" are converted to "<br>".
 
 *nc       - Do not color the SIDC frame if an image is displayed ( &nc or &nc=true).

Example:  Two examples...

          http://<echelon URL>?ech=m&size=300&image=https://www.gecop.mil/images/flags/pa.png
                      -- or --
          http://<echelon URL>?sidc=SFGP-------DUS-&size=600

### Compatability

Tested and works with:

* Apple Safari 7.1.2
* Google Chrome 43.0
* Mozilla Firefox 38.0.5
* MS Internet Explorer 11

Does not work with

* MS Internet Explorer 8

### License

 * Copyright (c) 2015 George Patton Simcox, email: geo.simcox@gmail.com
 * All Rights Reserved


