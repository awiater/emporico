function getFullCalendar(
	FullCalendarTagID,
	FullCalendarApiURL,
	FullCalendarExtraParams,
	FullCalendarEventClick,
	FullCalendarLocale='en',
	FullCalendarTitleFormat={ year: 'numeric',month:'long' },
	FullCalendarheaderToolbarLeft='prev,next',
	FullCalendarheaderToolbarCenter='title',
	FullCalendarheaderToolbarRight='today dayGridMonth,timeGridWeek,timeGridDay',
	FullCalendarCustomButtons={}
	)
{		
		var calendarEl = document.getElementById(FullCalendarTagID);
        calendar = new FullCalendar.Calendar(calendarEl, {	
          locale:FullCalendarLocale,	
          eventSources:[{
          	url:FullCalendarApiURL,
          	extraParams:FullCalendarExtraParams,
          }],
          eventClick: FullCalendarEventClick,
          initialView: 'dayGridMonth',
          customButtons:FullCalendarCustomButtons,
          headerToolbar:{
          	left: FullCalendarheaderToolbarLeft,
          	center: FullCalendarheaderToolbarCenter,
    		right:FullCalendarheaderToolbarRight
          },
          themeSystem: 'bootstrap',
          titleFormat: FullCalendarTitleFormat
        });
        calendar.render();
        return calendar;
	}