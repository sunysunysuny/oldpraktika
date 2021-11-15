<?php
require("/home/praktika/etc/config.inc.php");

$_SESSION['current_page'] = PAGE_LANGUAGE_HOLIDAY;
$_SESSION['current_user_group'] = USER_GROUP_CANDIDATES;
$_SESSION['sidebar'] = 'sprachreisen';

pageheader(array('page_title' => 'Buchung erfolgreich übermittelt'));

Praktika_Static::__('sprachreisen');
?>

<link type="text/css" media="screen,print" rel="stylesheet" href="/styles/v2/screen/sprachreisen.css" />

<p class="hint success">Vielen Dank für die Übermittlung der Buchung der <?php echo $_SESSION['sprachreisen']['stadt']; ?>.</p>
<p>Ein Mitarbeiter wird sich mit Ihnen in Verbindung setzen.</p>
<p><strong>Falls Sie innerhalb von 2 Werktagen keine Rückmeldung von uns erhalten, melden Sie sich bitte telefonisch unter 0341-2252030.</strong></p>


<?php
foreach ($_SESSION['sprachreisen'] as $key => $value) {
	unset($_SESSION['sprachreisen'][$key]);
}

unset($_SESSION['sprachreisen']);

$_SESSION['sidebar'] = '';
bodyoff();

?>