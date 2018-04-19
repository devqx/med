<div id='wrap'>
    <div id='external-events'>
        <h4>Draggable Events</h4>
        <div class='external-event'>My Event 1</div>
        <div class='external-event'>My Event 2</div>
        <div class='external-event'>My Event 3</div>
        <div class='external-event'>My Event 4</div>
        <div class='external-event'>My Event 5</div>
        <p>
            <input type='checkbox' id='drop-remove' />
            <label for='drop-remove'>remove after drop</label>
        </p>
    </div>

    <div id='calendar'></div>

    <div style='clear:both'></div>
</div>

<link href='/assets/fullcalendar/fullcalendar.css' rel='stylesheet' />
<link href='/assets/fullcalendar/fullcalendar.print.css' rel='stylesheet' media='print' />
<script src='/assets/moment/moment.min.js'></script>
<script src='/assets/jquery-ui/js/jquery-ui.custom.min.js'></script>
<script src='/assets/fullcalendar/fullcalendar.min.js'></script>
<style>
    body {
        margin-top: 40px;
        text-align: center;
        font-size: 14px;
        font-family: "Lucida Grande",Helvetica,Arial,Verdana,sans-serif;
    }

    #wrap {
        width: 1100px;
        margin: 0 auto;
    }

    #external-events {
        float: left;
        width: 150px;
        padding: 0 10px;
        border: 1px solid #ccc;
        background: #eee;
        text-align: left;
    }

    #external-events h4 {
        font-size: 16px;
        margin-top: 0;
        padding-top: 1em;
    }

    .external-event { /* try to mimick the look of a real event */
        margin: 10px 0;
        padding: 2px 4px;
        background: #3366CC;
        color: #fff;
        font-size: .85em;
        cursor: pointer;
    }

    #external-events p {
        margin: 1.5em 0;
        font-size: 11px;
        color: #666;
    }

    #external-events p input {
        margin: 0;
        vertical-align: middle;
    }

    #calendar {
        float: right;
        width: 900px;
    }
    .test{
        color: yellow;
    }
</style>
<script>

    $(document).ready(function() {
        /* initialize the external events
         -----------------------------------------------------------------*/

        $('#external-events div.external-event').each(function() {

            // create an Event Object (http://arshaw.com/fullcalendar/docs/event_data/Event_Object/)
            // it doesn't need to have a start or end
            var eventObject = {
                title: $.trim($(this).text()), // use the element's text as the event title
                className: 'test',
                textColor: 'black'
            };

            // store the Event Object in the DOM element so we can get to it later
            $(this).data('eventObject', eventObject);

            // make the event draggable using jQuery UI
            $(this).draggable({
                zIndex: 999,
                revert: true, // will cause the event to go back to its
                revertDuration: 0  //  original position after the drag
            });

        });


        /* initialize the calendar
         -----------------------------------------------------------------*/

        $('#calendar').fullCalendar({
            timeFormat: 'H(:mm)',
            defaultView: 'month',
            header: {
                left: 'month,basicDay,basicWeek',
                center: 'title',
                right: 'prev,next, today'
            },
            columnFormat: {
                month: 'ddd', // Mon
                week: 'ddd D/M', // Mon 9/7
                day: 'dddd D/M'  // Monday 9/7
            },
            titleFormat: {
                month: 'MMMM YYYY', // September 2009
                week: "Do MMM YYYY", // Sep 13 2009
                day: 'dddd, Do MMMM YYYY' // Tuesday, Sep 8, 2009
            },
            buttonText: {
                prev: 'prev',
                next: 'next',
                prevYear: 'prev year',
                nextYear: 'next year',
                today: 'today',
                month: 'month',
                week: 'week',
                day: 'day'
            },
            weekNumberTitle: 'wk',
            hiddenDays: [0],
            weekends: true, //ex/include Saturday and Sunday in the calandar
            weekMode: 'liquid', //Determines the number of weeks displayed in a month view. Also determines each week's height.
            weekNumbers: true,
            editable: true,
            droppable: true, // this allows things to be dropped onto the calendar !!!
            selectable: true,
            unselectAuto: false,
            selectHelper: true,
            dayClick: function(date, jsEvent, view) {
                x = date;
                y = jsEvent;
                z = view;
                console.log(x);
                console.log(y);
                console.log(z);
//                        $(this).css('background-color', 'red');
            },
            eventClick: function(calEvent, jsEvent, view) {
                alert("eevnt")
//                        $(this).css('background-color', 'black');
            },
            select: function(start, end, jsEvent, view) {
                console.log("Selection [Start, End]>=<[" + start + ", " + end + "]");
            },
            drop: function(date) { // this function is called when something is dropped

                // retrieve the dropped element's stored Event Object
                var originalEventObject = $(this).data('eventObject');

                // we need to copy it, so that multiple events don't have a reference to the same object
                var copiedEventObject = $.extend({}, originalEventObject);

                // assign it the date that was reported
                copiedEventObject.start = date;

                // render the event on the calendar
                // the last `true` argument determines if the event "sticks" (http://arshaw.com/fullcalendar/docs/event_rendering/renderEvent/)
                $('#calendar').fullCalendar('renderEvent', copiedEventObject, true);

                // is the "remove after drop" checkbox checked?
                if ($('#drop-remove').is(':checked')) {
                    // if so, remove the element from the "Draggable Events" list
                    $(this).remove();
                }

            }
        });


    });
    var x = y = z = "";
</script>