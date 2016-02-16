<?
//Stats helpers
function Correlation($arr1, $arr2)
{        
    $correlation = 0;
    
    $k = SumProductMeanDeviation($arr1, $arr2);
    $ssmd1 = SumSquareMeanDeviation($arr1);
    $ssmd2 = SumSquareMeanDeviation($arr2);
    
    $product = $ssmd1 * $ssmd2;
    
    $res = sqrt($product);
    
    $correlation = $k / $res;
    
    return $correlation;
}

function SumProductMeanDeviation($arr1, $arr2)
{
    $sum = 0;
    
    $num = count($arr1);
    
    for($i=0; $i<$num; $i++)
    {
        $sum = $sum + ProductMeanDeviation($arr1, $arr2, $i);
    }
    
    return $sum;
}

function ProductMeanDeviation($arr1, $arr2, $item)
{
    return (MeanDeviation($arr1, $item) * MeanDeviation($arr2, $item));
}

function SumSquareMeanDeviation($arr)
{
    $sum = 0;
    
    $num = count($arr);
    
    for($i=0; $i<$num; $i++)
    {
        $sum = $sum + SquareMeanDeviation($arr, $i);
    }
    
    return $sum;
}

function SquareMeanDeviation($arr, $item)
{
    return MeanDeviation($arr, $item) * MeanDeviation($arr, $item);
}

function SumMeanDeviation($arr)
{
    $sum = 0;
    
    $num = count($arr);
    
    for($i=0; $i<$num; $i++)
    {
        $sum = $sum + MeanDeviation($arr, $i);
    }
    
    return $sum;
}

function MeanDeviation($arr, $item)
{
    $average = Average($arr);
    
    return $arr[$item] - $average;
}    

function Average($arr)
{
    $sum = Sum($arr);
    $num = count($arr);
    
    return $sum/$num;
}

function Sum($arr)
{
    return array_sum($arr);
}

function mmmr($array, $output = 'mean'){ 
    if(!is_array($array)){ 
        return FALSE; 
    }else{ 
        switch($output){ 
            case 'mean': 
                $count = count($array); 
                $sum = array_sum($array); 
                $total = $sum / $count; 
            break; 
            case 'median': 
                rsort($array); 
                $middle = round(count($array) / 2); 
                $total = $array[$middle-1]; 
            break; 
            case 'mode': 
                $v = array_count_values($array); 
                arsort($v); 
                foreach($v as $k => $v){$total = $k; break;} 
            break; 
            case 'range': 
                sort($array); 
                $sml = $array[0]; 
                rsort($array); 
                $lrg = $array[0]; 
                $total = $lrg - $sml; 
            break; 
        } 
        return $total; 
    } 
} 

?>

@extends('dashboard/storyteller')
@section('title', 'Character Statistics')

@section('storyteller-style')
.include-npcs-switch {
	display: block;
	margin-top: 10px;

}

.include-npcs-switch .switch {
	display: inline-block;
	float: left; 
}

.include-npcs-switch .include-npcs-label {
	display: inline-block;
	margin-left: 10px;
}

.stats-submit {
	display: relative;
	float: right;
	margin-top: -60px;
}
@stop

@section('storyteller-script')
<?
	//Determine which stat we're using
	function getDataSet($val) {
		$extractor = null;
		$labelSet = [];
		switch($val) {
			case "Generation":
				$labelSet = ["8th", "9th", "10th", "11th", "12th", "13th", "14th", "15th"];
				$extractor = function($character) {
					if($character->hasFlaw("Fourteenth Generation")) {
						return 15;
					} elseif($character->hasFlaw("Fifteenth Generation")) {
						return 14;
					} else {
						return 13 - $character->getBackgroundDots("Generation");
					}
				};
				break;
			case "Appearance":
			case "Contacts":
			case "Fame":
			case "Ghouls":
			case "Herd":
			case "Mentor":
			case "Resources":
			case "Retainers":
			case "Bureaucracy":
			case "Church":
			case "Finance":
			case "Health":
			case "High Society":
			case "Industry":
			case "Media":
			case "Neighborhood":
			case "Occult":
			case "Police":
			case "Politics":
			case "Transportation":
			case "Underworld":
			case "University":
			case "Camarilla Lore":
			case "Fae Lore":
			case "Kindred Lore":
			case "Sabbat Lore":
			case "Werewolf Lore":
				$labelSet = [0, 1, 2, 3, 4, 5];
				$extractor = function($character) use ($val) {
					return $character->getBackgroundDots($val);
				};
				break;
			case "Courage":
				$labelSet = [0, 1, 2, 3, 4, 5];
				$extractor = function($character) {
					return @$character->path()->first()->virtue4;
				};
				break;
			case "Morality":
				$labelSet = [0, 1, 2, 3, 4, 5];
				$extractor = function($character) {
					return @$character->path()->first()->virtue3;
				};
				break;
			case "Self-Control/Instinct":
				$labelSet = [0, 1, 2, 3, 4, 5];
				$extractor = function($character) {
					return @$character->path()->first()->virtue2;
				};
				break;
			case "Conscience/Conviction":
				$labelSet = [0, 1, 2, 3, 4, 5];
				$extractor = function($character) {
					return @$character->path()->first()->virtue1;
				};
				break;
			case "Current Experience":
				$labelSet = [];
				for($i = 0; $i <= 100; $i++) $labelSet[] = $i;
				$extractor = function($character) {
					return $character->cachedExperience();
				};
				break;
			case "Total Experience":
				$labelSet = [];
				for($i = 0; $i <= 100; $i++) $labelSet[] = $i;
				$extractor = function($character) {
					return $character->experience;
				};
				break;
			case "Experience Spent":
				$labelSet = [];
				for($i = 0; $i <= 100; $i++) $labelSet[] = $i;
				$extractor = function($character) {
					return $character->getExperienceCost($character->activeVersion());
				};
				break;
			case "Total Influence":
				$labelSet = [];
				for($i = 0; $i <= 14*5; $i++) $labelSet[] = $i;
				$extractor = function($character) {
					$influences = ["Bureaucracy", "Church", "Finance", "Health", "High Society", "Industry", "Media", "Neighborhood",
									"Occult", "Police", "Politics", "Transportation", "Underworld", "University"];
					$total = 0;
					foreach($influences as $i) $total += $character->getBackgroundDots($i);
					return $total;
				};
				break;
		}
		return ["labels" => $labelSet, "extractor" => $extractor];
		/*$dataSet = [];
		foreach(Character::activeCharacters()->get() as $c) {
			$charData = call_user_func($extractor, $c);
			$dataSet[] = ["name" => $c->name, "value" => $charData];
		}
		return ["labels" => $labelSet, "data" => $dataSet];*/
	}
	$x = Input::get("x");
	$y = Input::get("y");
	$mode = "Bar";
	$labels = [];
	$data = [];
	$pureData = [];
	$hasX = isset($x) && strlen(trim($x)) > 0;
	$hasY = isset($y) && strlen(trim($y)) > 0;
	if($hasX) {
		if($hasY) {
			$mode = "Scatter";
			$xData = getDataSet($x);
			$yData = getDataSet($y);
			foreach(Character::activeCharacters()->orderBy('name')->get() as $c) {
				$xDataValue = call_user_func($xData["extractor"], $c);
				$yDataValue = call_user_func($yData["extractor"], $c);
				$data[] = ["label" => $c->name, "data" => [["x" => $xDataValue, "y" => $yDataValue]]];
				$pureData[] = ["name" => $c->name, "x" => $xDataValue, "y" => $yDataValue];
			}
			if(Input::get("include-npcs")) {
				foreach(Character::activeNPCs()->orderBy('name')->get() as $c) {
					$xDataValue = call_user_func($xData["extractor"], $c);
					$yDataValue = call_user_func($yData["extractor"], $c);
					$data[] = ["label" => $c->name, "data" => [["x" => $xDataValue, "y" => $yDataValue]]];
					$pureData[] = ["name" => $c->name, "x" => $xDataValue, "y" => $yDataValue];
				}
			}
		} else {
			$xData = getDataSet($x);
			$labels = $xData["labels"];
			$data =	[["label" => $x, "fillColor" => "rgba(151,187,205,0.5)", "strokeColor" => "rgba(151,187,205,0.8)",
						"highlightFill" => "rgba(151,187,205,0.75)", "highlightStroke" => "rgba(151,187,205,1)",
						"data" => []]];
			foreach($labels as $l) $data[0]["data"][] = 0;	
			foreach(Character::activeCharacters()->orderBy('name')->get() as $c) {
				$xDataValue = call_user_func($xData["extractor"], $c);
				$data[0]["data"][array_search($xDataValue, $labels)]++;
				$pureData[] = ["name" => $c->name, "x" => $xDataValue];
			}
			if(Input::get("include-npcs")) {
				foreach(Character::activeNPCs()->orderBy('name')->get() as $c) {
					$xDataValue = call_user_func($xData["extractor"], $c);
					$data[0]["data"][array_search($xDataValue, $labels)]++;
					$pureData[] = ["name" => $c->name, "x" => $xDataValue];
				}
			}
		}
	}	
	
?>
	self.statList = ["", "Current Experience", "Total Experience", "Experience Spent", "Generation", "Morality", "Courage", "Self-Control/Instinct", "Conscience/Conviction", "Appearance", "Contacts", "Fame", "Ghouls", "Herd", "Mentor", "Resources", "Retainers", "Bureaucracy", "Church", "Finance", "Health", "High Society", "Industry", "Media", "Neighborhood", "Occult", "Police", "Politics", "Transportation", "Underworld", "University", "Camarilla Lore", "Fae Lore", "Kindred Lore", "Sabbat Lore", "Werewolf Lore", "Total Influence"];
	self.selectedX = ko.observable("{{$x}}");
	self.selectedY = ko.observable("{{$y}}");

	var data = {
		labels: {{json_encode($labels)}},
		datasets: {{json_encode($data)}}
	}
	var chartContext = document.getElementById("chart").getContext("2d");
	var chart = new Chart(chartContext).{{$mode}}(data, 
		{ 
			datasetStroke: false,
			@if($mode == "Scatter")
			tooltipTemplate: "<%=datasetLabel%>: <%=argLabel%>; <%=valueLabel%>",
			multiTooltipTemplate: "<%=datasetLabel%>: <%=argLabel%>; <%=valueLabel%>",
			@endif

		});
@endsection
@section('storyteller-content')
<div class="row left">
	<br>
	<div class="small-12 columns">
		<form class="panel" method="get" action="/dashboard/storyteller/stats">
			Graph <select name="x" class="stats-select" data-bind="options: statList, value: selectedX"></select>
			(vs <select name="y" class="stats-select" data-bind="options: statList, value: selectedY"></select>)
			<div class="include-npcs-switch">
				<div class="switch">
					<input id="include-npcs" name="include-npcs" type="checkbox" {{Input::get("include-npcs") ? "checked" : ""}}>
					<label for="include-npcs"></label>
				</div> 
				<label class="include-npcs-label" for="include-npcs">Include NPCs</label>	
			</div>
			<input type="submit" class="button stats-submit" value="Go!" />
		</form>
	</div>
</div>
<div class="row left">
	<h2>
		<? 	if($hasX && $hasY) {
				echo $x.' vs '.$y.' for Active Characters';
			} else if($hasX) {
				echo $x.' for Active Characters';
			} else{
				echo 'Character Statistics';
			}
		?>
	</h2>
	<div class="small-8 columns">
		<canvas id="chart" style="width: 100%" height="250"></canvas>
		<div class="statistics">
			<? function pluck($key, $data) {
			    return array_reduce($data, function($result, $array) use($key) {
			        isset($array[$key]) && $result[] = $array[$key];
			        return $result;
			    }, array());
			}
			$x_values = pluck("x", $pureData);
			if($hasY) $y_values = pluck("y", $pureData);
			 ?>
			@if($hasX)
				The sample size is {{sizeof($x_values)}}.<br><br>
				The average (mean) {{$x}} is {{mmmr($x_values, 'mean')}}.<br>
				The median {{$x}} is {{mmmr($x_values, 'median')}}.<br>
				The mode {{$x}} is {{mmmr($x_values, 'mode')}}.<br>
				The range of {{$x}} is {{mmmr($x_values, 'range')}}.
				@if($hasY)
				<br><br>
				The average (mean) {{$y}} is {{mmmr($y_values, 'mean')}}.<br>
				The median {{$y}} is {{mmmr($y_values, 'median')}}.<br>
				The mode {{$y}} is {{mmmr($y_values, 'mode')}}.<br>
				The range of {{$y}} is {{mmmr($y_values, 'range')}}.<br><br>
				The correlation coefficient r is {{$corr = Correlation($x_values, $y_values)}}.<br>
				r<sup>2</sup> = {{pow($corr, 2)}}
				@endif
			@endif
		</div>
	</div>
	<div class="small-4 columns">
		@if($hasX)
		<table>
			<thead>
				<tr>
					<th>Name</th>
					<th>{{$x}}</th>
					@if($hasY)<th>{{$y}}</th>@endif
				</tr>
			</thead>
			<tbody>
			<? function xsort($a, $b) { return $b["x"] - $a["x"]; }; usort($pureData, "xsort"); ?>
			@foreach($pureData as $d)
				<tr>
					<td>{{$d["name"]}}</td>
					<td>{{$d["x"]}}</td>
					@if($hasY)<td>{{$d["y"]}}</td>@endif
			@endforeach
			</tbody>
		</table>
		@endif
	</div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/1.0.2/Chart.min.js"></script>
<script src="/js/Chart.Scatter.js"></script>
@stop
@stop