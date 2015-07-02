## Echelon - HTML

 * Displays an specified image within a MIL-STD-2525B or MIL-STD-2525C type frame.  Allows for 
 * the specification of Echelon, Affiliation, "MIL-STD-2525" type, Size, or Symbol ID Code (sidc) via 
 * URL arguments.  Please see "Usage" below.

## Configuration

None

## Usage

Navigate to the webpage.  A default SFGP----------- symbol is displayed on a white 100x100 square.  Screen cap 
the square and save.  Use the overrides to alter the end result.

Available URL Arguments:
 sidc  - The Symbol ID Code to use (See the MIL-STD-2525 standard document for proper format).  This is normally 
         a 15 char string, however this tool does not validate the length.  The tool will glean as much info as 
         available. Note: positions 1, 3-10, and 15 are ignored.
           Info checked (by position):
              2     - Affiliation. Used to generate appropriate frame or indicator.
              11-12 - Echelon.  Used to generate appropriate echelon indicator.
              13-14 - Country Code.  If a country code is given, and an image override is not specified, then
                      an attempt to produce an image will be made to the website "Flags of the World". (not mil 
                      spec).
 ech   - Echelon override.  Overrides the sidc.  If not a valid echelon, the the first eight(8) characters are 
         shown (not mil spec).
 size  - Size override.  Overrides the default overall size of 100.
 set   - Overrides the default MIL-STD-2525C set.  Last character: b = MIL-STD-2525B, c = MIL-STD-2525C.
 image - Overrides the default crystal blue image.  Image will be resized to fit the MIL-STD-2525 frame.
 note  - Adds 18 character under the frame (not mil spec).

Example:  http://<echelon URL>?sidc=sagp-------NPA-&ech=m&size=300&image=https://www.gecop.mil/images/flags/pa.png

### License

 * Copyright (c) 2015 George Patton Simcox, email: geo.simcox@gmail.com
 * All Rights Reserved


