On {{(new DateTime)->format("F j, Y, g:i a")}}, {{Auth::check() ? Auth::user()->username : 'an unknown user'}}
experienced the following error:<br><br> 
{{$exception}}