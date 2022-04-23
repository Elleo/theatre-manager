<?php
global $post;
$today = date('Y-m-d');
?>
<label>Booking date: <input type='date' id='date' value='<?=$today?>' onchange='fetchAvailability()' /></label><br />
<br />
<label id='start'>Start time: <select name='starttime' id='starttime' onchange='populateEndTimes()'></select></label><br />
<br />
<label id='end'>End time: <select name='endtime' id='endtime'></select></label><br />
<br />
<script type='text/javascript'>
    var availability;

    function fetchAvailability() {
        let bookingDate = jQuery('#date').val();
        jQuery('#starttime').empty();
        jQuery.getJSON("/wp-content/plugins/theatre-manager/available-times.php?date=" + bookingDate + "&product_id=<?=$post->ID;?>", function (data) {
            availability = data;
            for (const [timeslot, available] of Object.entries(data)) {
                jQuery('#starttime').append(jQuery('<option>', {
                    value: timeslot,
                    text: timeslot,
                    disabled: !available
                }));
            }
            populateEndTimes();
        });
    }

    function populateEndTimes() {
        let startTime = jQuery('#starttime').val();
        let validSlot = false;
        jQuery('#endtime').empty();
        for (const [timeslot, available] of Object.entries(availability)) {
            if (validSlot) {
                if (!available) {
                    validSlot = false;
                }
                jQuery('#endtime').append(jQuery('<option>', {
                        value: timeslot,
                        text: timeslot,
                }));
            }
            if (startTime == timeslot) {
                validSlot = true;
            }
        }
    }

    jQuery(document).ready(function() {
        jQuery(".quantity").hide(0);
        fetchAvailability();
    })
</script>
