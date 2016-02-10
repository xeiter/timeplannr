<style>

	.fc-license-message { display: none; }

	.fc-resource-area .fc-cell-text { font-size: 11px; }

	.smart-form .label.col {
		margin: 0;
		padding-top: 0px;
	}

	.fc-button .fc-icon {
		font-size: 1em;
	}

</style>

<!-- widget div-->
<div>

	<div id="eventContent"></div>

	<div class="widget-bodg">
		<div id="calendar"></div>
	</div>

</div>

<script type="text/javascript">

		$(document).ready(function() {

			var days = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
			var months = ['January','February','March','April','May','June','July','August','September','October','November','December'];

			var date = new Date();
			var d = date.getDate();
			var m = date.getMonth();
			var m_text = months[ date.getMonth() ];
			var y = date.getFullYear();
			var week_day = days[ date.getDay() ];

			var hdr = {
				left: 'title',
				center: 'month,agendaWeek,agendaDay',
				right: 'prev,today,next'
			};

			var initDrag = function (e) {
				// create an Event Object (http://arshaw.com/fullcalendar/docs/event_data/Event_Object/)
				// it doesn't need to have a start or end

				var eventObject = {
					title: $.trim(e.children().text()), // use the element's text as the event title
					description: $.trim(e.children('span').attr('data-description')),
					icon: $.trim(e.children('span').attr('data-icon')),
					className: $.trim(e.children('span').attr('class')) // use the element's children as the event class
				};
				// store the Event Object in the DOM element so we can get to it later
				e.data('eventObject', eventObject);

				// make the event draggable using jQuery UI
				e.draggable({
					zIndex: 999,
					revert: true, // will cause the event to go back to its
					revertDuration: 0 //  original position after the drag
				});
			};

			var addEvent = function (title, priority, description, icon) {
				title = title.length === 0 ? "Untitled Event" : title;
				description = description.length === 0 ? "No Description" : description;
				icon = icon.length === 0 ? " " : icon;
				priority = priority.length === 0 ? "label label-default" : priority;

				var html = $('<li><span class="' + priority + '" data-description="' + description + '" data-icon="' +
						icon + '">' + title + '</span></li>').prependTo('ul#external-events').hide().fadeIn();

				$("#event-container").effect("highlight", 800);

				initDrag(html);
			};

			/* initialize the external events
			 -----------------------------------------------------------------*/

			$('#external-events > li').each(function () {
				initDrag($(this));
			});

			$('#add-event').click(function () {
				var title = $('#title').val(),
						priority = $('input:radio[name=priority]:checked').val(),
						description = $('#description').val(),
						icon = $('input:radio[name=iconselect]:checked').val();

				addEvent(title, priority, description, icon);
			});

			/* initialize the calendar
			 -----------------------------------------------------------------*/

			var myCalendar = $('#calendar');

			myCalendar.fullCalendar({

				minTime: "11:00:00",
				maxTime: "22:00:00",
				resourceAreaWidth: 130,
				editable: false,
				aspectRatio: 1,
				scrollTime: '00:00',
				height: 500,
				slotDuration: '00:30:00',
				header: {
					/*left: 'promptResource today prev,next',
					center: 'title',
					right: 'timelineDay,timelineThreeDays,agendaWeek,month'*/
				},
				defaultView: 'timelineDay',
				views: {
					timelineThreeDays: {
						type: 'timeline',
						duration: { days: 3 }
					}
				},

				resourceColumns: [
					{
						labelText: 'Name',
						field: 'name'
					}
				],
				resources: [

					<?php foreach( $venues as $venue) : ?>

						{ id: 'venue-{{ $venue['ID'] }}', name: '{{ $venue['post_title'] }}' },

					<?php endforeach; ?>

				],

				events: [

					<?php $count = 0; ?>
					<?php foreach ($booked_slots as $slot) : ?>

						<?php

						if ($count > 25) $count = 0;
						$date = strtotime( $slot['date'] );
						$day = date( "d", $date );

						// Calculate time values for From
						$time_from = $slot['time_from'];
						$minute_from = $time_from > floor($time_from) ? 30 : 0 ;
						$time_from_hour = $time_from;
						$time_from_minute = $minute_from;

						// Calculate time values for To
						$time_to = $slot['time_to'];
						$minute_to = $time_to > floor($time_to) ? 30 : 0 ;
						$time_to_hour = $time_to;
						$time_to_minute = $minute_to;

						$comment_icon = '';
						if ( isset( $slot['title'] ) && !empty( $slot['title'] ) ) {
							$comment_icon = ' <i class="fa fa-comment"></i>';
						}

						$title = isset( $slot['title'] ) ? $slot['title'] : NULL;
						$colours = array(
							'greenLight',
							'red',
							'blue',
							'darken',
							'yellow',
							'purple',
							'orange',
						);

						$colour = $colours[ rand( 0, sizeof( $colours ) - 1 ) ];

						?>

					{
						title: '<?php echo addslashes(get_avatar(  $slot['timeslot_user'], 20 )); ?> {{ $slot['first_name'] }} {{ $slot['last_name']  }}{{$comment_icon}}',
						start: new Date(y, m, '{{ $day }}' , '{{ $time_from_hour }}', '{{ $time_from_minute }}'),
						end: new Date(y, m, '{{ $day }}', '{{ $time_to_hour }}', '{{ $time_to_minute }}'),
						allDay: false,
						className: ["event", "bg-color-{{ $colour }}", 'event-id-<?php echo $slot['ID']; ?>'],
						description: '{{ $title }}',
						slotWidth: 50,
						resourceId: 'venue-{{ $slot['timeslot_venue'] }}',
					},

						<?php $count ++; ?>

					<?php endforeach; ?>

				],

				eventRender: function (event, element, icon) {

					if (!event.description == "") {
						element.find('.fc-event-title').append("<br/><span class='ultra-light'>" + event.description +
								"</span>");
					}

					if (!event.icon == "") {
						element.find('.fc-event-title').append("<i class='air air-top-right fa " + event.icon +
								" '></i>");
					}

					element.find('.fc-title').html(event.title);

					element.qtip({
						prerender: true,
						content: event.description,
						style: 'qtip-bootstrap',
						position: {
							my: 'bottom center',
							at: 'bottom center',
							target: 'mouse',
							adjust: {
								mouse: true,
								scroll: false,
							}
						}
					});

				},

				windowResize: function (event, ui) {
					$('#calendar').fullCalendar('render');
				},

				dayClick:  function(date, jsEvent, view, resourceObj) {

					// set the values and open the modal
					// jQuery("#eventInfo").html(event.description);
					// jQuery("#eventLink").attr('href', event.url);
					// jQuery("#eventContent").dialog({ modal: true, title: event.title });

					jQuery("#launch-modal").trigger("click");

					var text_date = date.format("YYYY-MM-DD");
					var hour = parseInt(date.format("H"));
					var minute = date.format("m");

					// Add a half an hour block to the time slider if required
					minute_number = minute == 30 ? 0.5 : 0;

					jQuery("#date").val(text_date);
					jQuery("#venue-id").val(resourceObj.id.replace("venue-", ""));

					jQuery("[name=title]").val("");

					var mySlider = jQuery("#time-range");

					// Prefill the time slider with select value that ends and +3 hours
					mySlider.slider('option', 'values', [hour + minute_number , hour + 3 + minute_number]);

					/*
					var myEvent = {
						resource:"venue-75",
						title:"my new event",
						allDay: true,
						start: new Date(),
						end: new Date()
					};
					myCalendar.fullCalendar( 'renderEvent', myEvent );
					*/

				},

				eventClick: function(data, event, view) {

					/*console.log(calEvent);
					console.log(jsEvent);
					console.log(view);*/

					// change the border color just for fun
					jQuery(this).css('border-color', 'red');

				},

				eventAfterAllRender: function(a) {

					var currentDate = $("#calendar").fullCalendar('getDate');
					var days = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
					var week_day = days[ currentDate.day() - 1 ];

					currentDate = days[ currentDate.weekday() ] + " - " + currentDate.format('MMMM Do, YYYY') ;
					$('.fc-toolbar .fc-left h2').text(currentDate);
				}

			});

			/* hide default buttons */
			$('.fc-header-right, .fc-header-center').hide();


			$('#calendar-buttons #btn-prev').click(function () {
				$('.fc-button-prev').click();
				return false;
			});

			$('#calendar-buttons #btn-next').click(function () {
				$('.fc-button-next').click();
				return false;
			});

			$('#calendar-buttons #btn-today').click(function () {
				$('.fc-button-today').click();
				return false;
			});

		});

</script>