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
 * Copyright (c) 2015 George Patton Simcox, email: geo.simcox@gmail.com
 * All Rights Reserved
 *
 */
-->
@extends('layouts.default')

@section('content')

    <div class="ech-outline" style="width: {{$size}}px; height: {{$size}}px;">

    @if (!empty($echelon))
        <div class="ech-echelon text-center" style="font-size: {{$font*.8}}%;">
            <span>{{$echelon}}</span>
        </div>
    @endif

        <div class="ech-container">
            @if (!empty($is_assumed) && !empty($is_2525c))
                <div class="ech-2525c" style="width:{{$frame}}px;"  data-toggle="modal" data-target="#myModal">
                    <div class="ech-image text-center" style="top:{{4*$multiplier}}px; left:{{4*$multiplier}}px; width:{{$frame - 8*$multiplier}}px; height:{{($frame*.667) - 8*$multiplier}}px;">
                        <img src="{{$image}}" alt="{{$image_txt}}" style="width:{{$frame - 7*$multiplier}}px; height:{{($frame*.667) - 7*$multiplier}}px;">
                    </div>
                    <div class="ech-frame text-center">
                        <svg viewBox="0 0 {{$frame*2}} {{$frame*2}}" id="sus" width="{{$frame*2}}px" height="{{$frame*2}}px">
                            <line x1="0" y1="{{2*$multiplier}}" x2="{{$frame}}" y2="{{2*$multiplier}}" style="stroke-dasharray:{{4*$multiplier}} {{4*$multiplier}};stroke:rgb(0,0,0);stroke-width:{{4*$multiplier}};"></line>
                            <line x1="0" y1="{{$frame*.667 - 2*$multiplier}}" x2="{{$frame}}" y2="{{$frame*.667 - 2*$multiplier}}" style="stroke-dasharray:{{4*$multiplier}} {{4*$multiplier}};stroke:rgb(0,0,0);stroke-width:{{4*$multiplier}};"></line>
                            <line x1="{{2*$multiplier}}" y1="0" x2="{{2*$multiplier}}" y2="{{$frame*.667}}" style="stroke-dasharray:{{4*$multiplier}} {{4*$multiplier}};stroke:rgb(0,0,0);stroke-width:{{4*$multiplier}};"></line>
                            <line x1="{{$frame - 2*$multiplier}}" y1="0" x2="{{$frame - 2*$multiplier}}" y2="{{$frame*.667}}" style="stroke-dasharray:{{4*$multiplier}} {{4*$multiplier}};stroke:rgb(0,0,0);stroke-width:{{4*$multiplier}};"></line>
                        </svg>
                    </div>
                </div>
            @else

                @if (empty($is_2525c))
                    <div class="ech-2525b" style="width:{{$frame}}px;"  data-toggle="modal" data-target="#myModal">
                @else
                    <div class="ech-2525c" style="width:{{$frame}}px;"  data-toggle="modal" data-target="#myModal">
                @endif

                    <div class="ech-image text-center" style="top:{{4*$multiplier}}px; left:{{4*$multiplier}}px; height:{{($frame*.667) - 8*$multiplier}}px;">
                        <img src="{{$image}}" alt="{{$image_txt}}" style="width:{{$frame - 7*$multiplier}}px; height:{{($frame*.667) - 7*$multiplier}}px;">
                    </div>
                    <div class="ech-frame text-center">
                        <svg viewBox="0 0 {{$frame*2}} {{$frame*2}}" id="frd" width="{{$frame*2}}px" height="{{$frame*2}}px">
                            <rect x="{{2*$multiplier}}" y="{{2*$multiplier}}" width="{{$frame - 4*$multiplier}}" height="{{($frame*.667) - 4*$multiplier}}" style="fill-opacity:0.0;fill:rgb(128,224,255);stroke:rgb(0,0,0);stroke-width:{{4*$multiplier}};"></rect>
                        </svg>
                    </div>
                </div>
            @endif

            <!-- Note (not part of mil spec) -->
            @if (!empty($note))
                <div class="ech-echelon text-center" style="position:absolute; top:{{$frame*.667}}px;font-size:{{$font*.8}}%;">
                    {{$note}}
                </div>
            @endif

        </div>

        <!-- Assumed indicator for set 2525B-->
        @if (!empty($is_assumed) && empty($is_2525c))
            <div class="ech-aff pull-right" style="font-size: {{$font*.8}}%;">
                <span data-unicode="f128" class="fa fa-question">&nbsp;</span>
            </div>
        @endif

    </div>

@if(!empty($default) && !is_bool($default))
    <!-- Modal -->
    <div id="myModal" class="modal fade" role="dialog">
        <div class="modal-dialog">

            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Disclaimer</h4>
                </div>
                <div class="modal-body">
                        <div class="ech-wrap">
                            The flag image was obtained, based on the Country Code contained in the Symbol ID Code (sidc), from the website: "Flags of the World". URL: <a href="http://www.crwflags.com/fotw/flags/">http://www.crwflags.com/fotw/flags/</a>
                            <br>
                            <br>
                            All rights are, or may be, retained by their authors as per the website: <a href="http://www.crwflags.com/fotw/flags/disclaim.html#lin">http://www.crwflags.com/fotw/flags/disclaim.html#lin</a>
                        </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>

        </div>
    </div>
    @endif
@stop