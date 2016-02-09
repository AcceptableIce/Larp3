@extends('layout')
@section('title', 'Calendar')
@section('includes') 
<style type="text/css">
	.next-game-label {
		text-align: center;
		font-weight: 300;	
	}
	
	.countdown {
		font-size: 2.0em;
		font-weight: 600;
		text-align: center;
	}
	
	.calendar-main {
		padding-top: 20px;
	}
	
	.time-full {
		font-size: 1.0em;
		font-weight: 300;
		text-align: center;
	}
	
	.timer-divider {
		margin: 10px 0px;
	}
	
	td.day {
		padding: 5px 3px;
		text-align: center;
	}
	
	.calendar-row {
		max-width: 1200px;
		width: 100%;
	}
	
	.first-calendar-row {
		margin-top: 20px;
	}

	.calendar-buffer {
		padding: 0;
	}

	.game-day {
		background-color: #B6E8A5;
	}

	.past-deadline {
		color: #900;
	}

	.calendar-columns {
		width: 20%;
	}

	@media screen and (max-width: 900px) {
		.calendar-columns {
			width: 100%;
		}

		.calendar {
			left: 50%;
			-webkit-transform: translate(-50%, 0);
			transform: translate(-50%, 0);
			position: relative;
		}
	}
</style>
@stop
@section('script')
<script type="text/javascript">
	<? 	$now = new DateTime;
		$now->setTime(0, 0); 
	$nextGame = GameSession::where('date', '>=', $now)->orderBy('date')->first();
 
	if($nextGame) {
		$date = new DateTime($nextGame->date);
		$date->setTimezone(new DateTimeZone("America/Chicago"));
		$date->modify("6 hours"); //Fix timezone offset
		$date->setTime(19, 00);
		$deadlineDate = new DateTime($nextGame->date);
		$deadlineDate->setTimezone(new DateTimeZone("America/Chicago"));
		$deadlineDate->modify('previous Wednesday, 6 PM CST');
	?>
	var nextGameDate = new Date({{$date->getTimestamp()*1000}});
	var deadlineDate = new Date({{$deadlineDate->getTimestamp()*1000}});
	console.log(nextGameDate, deadlineDate);

	function updateDifference() {
		var differenceString = "";
		setTime(nextGameDate, ".game-countdown", "Right now!");
		setTime(deadlineDate, ".deadline-countdown", "No more changes can be submitted this cycle.");
	}

	function setTime(date, selector, beforeStr) {
		var seconds = (date.getTime() - (new Date()).getTime())/ 1000;
		var years = Math.floor(seconds / 31536000);
		var days = Math.floor((seconds % 31536000) / 86400); 
		var hours = Math.floor(((seconds % 31536000) % 86400) / 3600);
		var minutes = Math.floor((((seconds % 31536000) % 86400) % 3600) / 60);
		var remainderSeconds = Math.floor((((seconds % 31536000) % 86400) % 3600) % 60);
		if(seconds <= 0) {
			$(selector).text(beforeStr);
			$(selector).addClass("past-deadline");
		} else {
			$(selector).text(	(days > 0 ? days + " day" + (days == 1 ? "" : "s") + ", " : "") +
			  					(hours > 0 || days != 0 ? hours + " hour" + (hours == 1 ? "" : "s") + ", " : "") + 
			  					(minutes > 0 || (hours != 0 && days != 0) ? minutes + " minute" + (minutes == 1 ? "" : "s") + (hours > 0 || days > 0 ? ", and " : " and ") : "") +
			  					(remainderSeconds + " second" + (remainderSeconds == 1 ? "" : "s") + "."));
		}
	}

	setInterval(updateDifference, 1000);
	<? } else { ?>
		$(".countdown").text("No game scheduled.");
	<? } ?>
</script>
@stop
@section('content')
<? function build_calendar($month,$year,$dateArray) { 
     // Create array containing abbreviations of days of week.
     $daysOfWeek = array('S','M','T','W','T','F','S');
     // What is the first day of the month in question?
     $firstDayOfMonth = mktime(0,0,0,$month,1,$month == 0 ? $year + 1 : $year);
     // How many days does this month contain?
     $numberDays = date('t', $firstDayOfMonth);
     // Retrieve some information about the first day of the
     // month in question.
     $dateComponents = getdate($firstDayOfMonth);
     // What is the name of the month in question?
     $monthName = $dateComponents['month'];
     // What is the index value (0-6) of the first day of the
     // month in question.
     $dayOfWeek = $dateComponents['wday'];
     // Create the table tag opener and day headers
     $calendar = "<table class='calendar'>";
     $calendar .= "<caption>$monthName $year</caption>";
     $calendar .= "<tr>";
     // Create the calendar headers
     foreach($daysOfWeek as $day) {
          $calendar .= "<th class='header'>$day</th>";
     } 
     // Create the rest of the calendar
     // Initiate the day counter, starting with the 1st.
     $currentDay = 1;
     $calendar .= "</tr><tr>";
     // The variable $dayOfWeek is used to
     // ensure that the calendar
     // display consists of exactly 7 columns.
     if ($dayOfWeek > 0) { 
          $calendar .= "<td colspan='$dayOfWeek' class='calendar-buffer'>&nbsp;</td>"; 
     }     
     $month = str_pad($month, 2, "0", STR_PAD_LEFT); 
     while ($currentDay <= $numberDays) {
          // Seventh column (Saturday) reached. Start a new row.
          if ($dayOfWeek == 7) {
               $dayOfWeek = 0;
               $calendar .= "</tr><tr>";
          }         
          $currentDayRel = str_pad($currentDay, 2, "0", STR_PAD_LEFT);    
          if($month == 00) $month = 12;      
          $date = "$year-$month-$currentDayRel";
          $refDate = new DateTime($month."/".$currentDay."/".$year);
          $gameClass = GameSession::where('date', $refDate)->exists() ? 'game-day' : '';
          $calendar .= "<td class='day $gameClass' rel='$date'>$currentDay</td>";
          // Increment counters 
          $currentDay++;
          $dayOfWeek++;
     }       
     // Complete the row of the last week in month, if necessary
     if ($dayOfWeek != 7) {     
          $remainingDays = 7 - $dayOfWeek;
          $calendar .= "<td class='calendar-buffer' colspan='$remainingDays'>&nbsp;</td>"; 
     }     
     $calendar .= "</tr>";
     $calendar .= "</table>";
     return $calendar;
} ?>
<div class="calendar-wrapper theme-wrapper">
	<div class="calendar-content">
		<div class="row">
			<div class="small-12 columns calendar-main">
				<div class="next-game-label">The next game starts in</div>
				<div class="countdown game-countdown">Calculating...</div>
				<div class="time-full">{{$nextGame ? $date->format("F j, Y \a\\t g:i A") : ":("}}</div>
			</div>
		</div>
		<div class="row">
			<div class="small-12 columns calendar-main">
				<div class="next-game-label">Changes are due in</div>
				<div class="countdown deadline-countdown">Calculating...</div>
				<div class="time-full">{{isset($deadlineDate) ? $deadlineDate->format("F j, Y \a\\t g:i A") : ":("}}</div>
			</div>
		</div>
		<div class="row collapse calendar-row first-calendar-row">
			<? 	$date = new DateTime;
				$month = $date->format('n');
				$year = $date->format('Y'); 
				if($month < 8) $year -= 1; //Adjust to always show in sememster form ?>
			@for($i = 0; $i < 10; $i++)
				<div class="calendar-columns columns">
					{{build_calendar((8 + $i) % 12, $i > 4 ? $year + 1 : $year, [])}}
				</div>
				@if($i == 4)</div><div class="row collapse calendar-row">@endif
			@endfor
		</div>
	</div>
</div>
@stop