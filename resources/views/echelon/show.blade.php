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
-->
@extends('layouts.default')

@section('content')

    <div class="ech-outline" style="width:{{$size}}px;height:{{$size}}px;">

    @if (!empty($echelon))
        <div class="ech-echelon text-center" style="font-size:{{$font*0.8}}%;">
            <span>{{$echelon}}</span>
        </div>
    @endif

            <div class="ech-container">
                <!-- Add Indicator-->
                @if (!empty($indicator))
                    <div class="ech-indicator" style="font-size:{{$font*0.70}}%;">
                        {{$indicator}}
                    </div>
                @endif

                <div class="ech-2525b" style="width:{{$frame}}px;"  data-toggle="modal" data-target="#myModal">
                    @if (!empty($is_installation) || !empty($is_task_force))
                        <div class="ech-installation" style="top: -{{8*$multiplier}}px;">
                            <svg viewBox="0 0 {{$frame*2}} {{$frame*2}}" id="installation" width="{{$frame*2}}px" height="{{$frame*2}}px">
                                <line x1="{{$frame*0.333}}" y1="{{0}}" x2="{{$frame*0.666}}" y2="{{0}}" style="stroke:black;stroke-width:{{16*$multiplier}};"></line>
                            </svg>
                        </div>
                    @endif
                    @if (!empty($is_task_force))
                        <div class="ech-installation" style="top: -{{4*$multiplier}}px;">
                            <svg viewBox="0 0 {{$frame*2}} {{$frame*2}}" id="installation" width="{{$frame*2}}px" height="{{$frame*2}}px">
                                <line x1="{{$frame*0.333 + 4*$multiplier}}" y1="{{0}}" x2="{{$frame*0.666 - 4*$multiplier}}" y2="{{0}}" style="stroke:white;stroke-width:{{8*$multiplier}};"></line>
                            </svg>
                        </div>
                    @endif
                    @if (!empty($is_feint_dummy))
                        <div class="ech-installation" style="top: -{{10*$multiplier}}px;">
                            <svg viewBox="0 0 {{$frame*2}} {{$frame*2}}" id="installation" width="{{$frame*2}}px" height="{{$frame*2}}px">
                                <line x1="{{$frame/2}}" y1="{{0}}" x2="{{$frame-$multiplier}}" y2="{{12*$multiplier}}" style="stroke-dasharray:{{4*$multiplier}} {{4*$multiplier}};stroke:black;stroke-width:{{4*$multiplier}};"></line>
                                <line x1="{{$frame/2}}" y1="{{0}}" x2="{{0}}" y2="{{12*$multiplier}}" style="stroke-dasharray:{{4*$multiplier}} {{4*$multiplier}};stroke:black;stroke-width:{{4*$multiplier}};"></line>
                            </svg>
                        </div>
                    @endif
                    <div class="ech-frame text-center">
                        <svg viewBox="0 0 {{$frame*2}} {{$frame*2}}" id="ssgp" width="{{$frame*2}}px" height="{{$frame*2}}px">

                            <rect x="{{2*$multiplier}}" y="{{2*$multiplier}}" width="{{$frame - 4*$multiplier}}" height="{{($frame*0.667) - 4*$multiplier}}" style="fill:{{$fill_color}};stroke:{{$frame_color}};stroke-width:{{4*$multiplier}};"></rect>

                            @if (!empty($is_assumed) && !empty($is_2525c))
                                <line x1="0" y1="{{2*$multiplier}}" x2="{{$frame}}" y2="{{2*$multiplier}}" style="stroke-dasharray:{{4*$multiplier}} {{4*$multiplier}};stroke:black;stroke-width:{{4*$multiplier}};"></line>
                                <line x1="0" y1="{{$frame*0.667 - 2*$multiplier}}" x2="{{$frame}}" y2="{{$frame*0.667 - 2*$multiplier}}" style="stroke-dasharray:{{4*$multiplier}} {{4*$multiplier}};stroke:black;stroke-width:{{4*$multiplier}};"></line>
                                <line x1="{{2*$multiplier}}" y1="0" x2="{{2*$multiplier}}" y2="{{$frame*0.667}}" style="stroke-dasharray:{{4*$multiplier}} {{4*$multiplier-1}};stroke:black;stroke-width:{{4*$multiplier}};"></line>
                                <line x1="{{$frame - 2*$multiplier}}" y1="0" x2="{{$frame - 2*$multiplier}}" y2="{{$frame*0.667}}" style="stroke-dasharray:{{4*$multiplier}} {{4*$multiplier-1}};stroke:black;stroke-width:{{4*$multiplier}};"></line>
                            @endif
                        </svg>
                    </div>
                    <div class="ech-image text-center" style="border:1px solid black;top:{{4*$multiplier}}px;left:{{4*$multiplier}}px;width:{{$frame - 8*$multiplier}}px;height:{{($frame*.667) - 8*$multiplier}}px;">
                        @if (!empty($is_landscape))
                            <img src="{{$image}}" alt="{{$image_txt}}" style="width:{{$frame - 8*$multiplier - 2}}px;height:{{($frame*0.667) - 8*$multiplier - 2}}px;">
                        @else
                            <img src="{{$image}}" alt="{{$image_txt}}" style="height:{{($frame*0.667) - 8*$multiplier - 2}}px;">
                        @endif
                    </div>
                </div>

            <!-- Note (not part of mil spec) -->
            @if (!empty($notex))
                <div class="ech-notex" style="top:{{$frame*0.667}}px;font-size:{{$font*0.7}}%;">
                    {!!html_entity_decode($notex)!!}
                </div>
            @elseif (!empty($note))
                <div class="ech-note text-center" style="top:{{$frame*0.667}}px;font-size:{{$font*0.7}}%;">
                    {{$note}}
                </div>
            @endif

        </div>

    </div>

@if(!empty($fotw))
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