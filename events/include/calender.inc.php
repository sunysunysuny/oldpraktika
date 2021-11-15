<?php

class calender{
    
	function calender($date_beg="", $date_end=""){
		$month = date("n", $date_beg);
		$year = date("Y", $date_beg);
		$wday = date("w", mktime(0, 0, 0, $month, 0, $year));
?>
    <table id="calendar">
      <colgroup>
<?php
		for($i = 1; $i <= 7; $i++) {
?>
        <col width="1*" />
<?php
		}
?>
      </colgroup>
      <tr class="year">
        <td colspan="7"><?php echo strftime("%B", $date_beg)." ".$year; ?></td>
      </tr>
      <tr class="week">
<?php
		for($i = 0; $i <= 6; $i++){
?>
        <th><?php echo strftime("%a", strtotime("last Monday + ".$i." day")); ?></th>
<?php
		}
?>
      </tr>
      <tr>
<?php
  $i = 0;
		echo str_repeat("        <td> &nbsp; </td> \n", $wday);
		while( checkdate($month, ++$i, $year) ) {
			$event = false;
			// innerhalb eines Monats
			if ( ( $i >= date("j", $date_beg) && $i <= date("j", $date_end) ) ) {
				$event = true;
			}
			// ueber ein Monate hinaus, aber innerhalb eines Jahres
			if ( ( date("m", $date_beg) < date("m", $date_end) && $i >= date("j", $date_beg) ) ) {
				$event = true;
			}
			// ueber ein Jahr hinaus
			if ( ( date("Y", $date_beg) < date("Y", $date_end) && $i >= date("j", $date_beg) ) ) {
				$event = true;
			}
?>
        <td<?php echo ($event == true) ? ' class="event"': null; ?>><?php echo $i; ?></td>
<?php
  			if( (++$wday % 7) == 0 ) {
?>
      </tr>
      <tr>
<?php
  			}
		}
		echo str_repeat("        <td> &nbsp; </td> \n", 7 - ($wday % 7) );
?>
      </tr>
    </table>
<?php
	}
}
?>