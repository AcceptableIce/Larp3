@extends('layout')
@section('title', 'Larp3 Dev')
@section('script')
<script src="/js/chargen.js" type="text/jsx"></script>
<script src="/js/foundation/foundation.tooltip.js"></script>
<script src="/js/foundation/foundation.magellan.js"></script>
<script src="/js/foundation/foundation.accordion.js"></script>
<script type="text/javascript">

</script>
@stop
@section('content')
<style type="text/css">
.ability-list-selected {
	padding: 0.25em 1em !important;
	margin: 5px 5px;
	position: relative;
}

.virtue-item {
	padding: 0.25em 1em !important;
	margin: 5px 5px;
	position: relative;	
}

.ability-list-icon {
	position: absolute;
	font-size: 24px;
	top: -5px;
}

.ability-list-icon.plus {
	right: 10px;
	top: -2px;
}

.ability-list-icon.minus {
	left: 10px;
}

.ability-list-selected-name {
	text-align: center;
	width: 100%;
	display: inline-block;
}

.remove-button {
	display: inline-block;
	color: #990000;
}

.discipline-selected-spacer {
	width: 10px;
	display: inline-block;
}

.merit-divider {
	border-bottom: 1px dotted #c0c0c0;
	margin-top: 7px;
	margin-bottom: 10px;
}

.trait-box {
	display: inline-block;
	width: 50px;
	height: 50px;
	border: 1px solid #c9c9c9;
}

.trait-box.filled {
	color: #fff;
	font-size: 44px;
	line-height: 47px;
	vertical-align: top;
	text-align: center;
}

.trait-box.filled.physical {
	background-color: #23821F;
}

</style>

<div data-magellan-expedition="fixed">
	<dl class="sub-nav">
	    <dd data-magellan-arrival="sect"><a href="#sect">Sect</a></dd>
		<dd data-magellan-arrival="clan"><a href="#clan">Clan</a></dd>
	    <dd data-magellan-arrival="nature"><a href="#nature">Nature</a></dd>
	    <dd data-magellan-arrival="Attributes"><a href="#attributes">Attributes</a></dd>
	    <dd data-magellan-arrival="abilities"><a href="#abilities">Abilities</a></dd>
	    <dd data-magellan-arrival="disciplines"><a href="#disciplines">Disciplines</a></dd>	    
	    <dd data-magellan-arrival="rituals"><a href="#disciplines">Rituals</a></dd>
	    <dd data-magellan-arrival="backgrounds"><a href="#nature">Backgrounds</a></dd>
	  	<dd data-magellan-arrival="path"><a href="#path">Path and Virtues</a></dd>	    
	    <dd data-magellan-arrival="derangements"><a href="#derangements">Derangements</a></dd>
	    <dd data-magellan-arrival="merits-and-flaws"><a href="#merits-and-flaws">Merits and Flaws</a></dd>
	    <dd data-magellan-arrival="finish"><a href="#finish">Finish and Submit</a></dd>
  	</dl>
</div>
<div class="row">
	<div class="small-12">
		<div id="sect-list"></div>

		<div id="clan-list"></div>
		
		<div id="nature-list"></div>

		<div id="ability-list"></div>

		<div id="attributes-list"></div>

		<div id="discipline-list"></div>
		
		<div id="ritual-list"></div>
		
		<div id="background-list"></div>

		<div id="path-list"></div>
		
		<div id="derangement-list"></div>
		
		<div id="merit-and-flaw-list"></div>
		<a name="finish"></a>
		<h3 data-magellan-destination="finish">Finish and Submit</h3>
	</div>
	
</div>

@stop