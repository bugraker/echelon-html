<!DOCTYPE html>
<!--
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
-->
<html lang="en">
    <head>
        <meta charset="utf-8">
        <title>Echelon-HTML</title>
        <link rel="stylesheet" href="../resources/assets/css/echelon.css">
        <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css">
        <link rel="stylesheet" href="//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">
        <link rel="stylesheet" href="../resources/assets/css/font-awesome.min.css">
        <link href="https://gitcdn.github.io/bootstrap-toggle/2.2.0/css/bootstrap-toggle.min.css" rel="stylesheet">
        <script src="//code.jquery.com/jquery-1.11.3.js"></script>
        <script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script>
        <script src="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js"></script>
        <script src="https://gitcdn.github.io/bootstrap-toggle/2.2.0/js/bootstrap-toggle.min.js"></script>
    </head>
    <body class="ech-body">

        @yield('content')
    </body>
</html>
