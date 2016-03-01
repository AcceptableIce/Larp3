@extends('layout')
@section('title', 'Forums')
@section('includes') 
<style type="text/css">
@yield('forum-style')
</style>
<link rel="stylesheet" type="text/css" href="/css/forums.css" />

@stop
@section('script')
<script type="text/javascript">
var partialBindingProvider = function(initialExclusionSelector) {
    var result = new ko.bindingProvider(),
        originalHasBindings = result.nodeHasBindings;

    result.exclusionSelector = initialExclusionSelector;

    result.nodeHasBindings = function(node) {
        return !$(node).is(result.exclusionSelector) && originalHasBindings.call(this, node);
    };

    return result;
};

ko.bindingProvider.instance = new partialBindingProvider(".post-content");

var forumVM = function() {
	var self = this;
	@yield('forum-script');
	return self; 
}

ko.applyBindings(new forumVM());
</script>
@stop
@section('content')
<div class="forum-wrapper theme-wrapper">
    <div class="row main-content forum-content">
    	<div class="large-12 columns">
    	@yield('forum-content')
    	</div>
    </div>
</div>
@stop