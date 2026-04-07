$(document).ready(function () {
   handleDateRangePicker();
});

var handleDateRangePicker = function() {
	
    const defaultStart = moment().subtract(29, 'days');
    const defaultEnd = moment();

    $('#advance-daterange span').html(
        defaultStart.format('MMMM D, YYYY') + ' - ' + defaultEnd.format('MMMM D, YYYY')
    );


    $('#start_date').val(defaultStart.format('YYYY-MM-DD'));
    $('#end_date').val(defaultEnd.format('YYYY-MM-DD'));

	$('#advance-daterange').daterangepicker({
		format: 'MM/DD/YYYY',
		startDate: defaultStart,
		endDate: defaultEnd,
		minDate: '01/01/2025',
		maxDate: '12/31/2030',
		dateLimit: { days: 60 },
		showDropdowns: true,
		showWeekNumbers: true,
		timePicker: false,
		timePickerIncrement: 1,
		timePicker12Hour: true,
		ranges: {
			'Today': [moment(), moment()],
			'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
			'Last 7 Days': [moment().subtract(6, 'days'), moment()],
			'Last 30 Days': [moment().subtract(29, 'days'), moment()],
			'This Month': [moment().startOf('month'), moment().endOf('month')],
			'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
		},
		opens: 'right',
		drops: 'down',
		buttonClasses: ['btn', 'btn-sm'],
		applyClass: 'btn-primary',
		cancelClass: 'btn-default',
		separator: ' to ',
		locale: {
			applyLabel: 'Submit',
			cancelLabel: 'Cancel',
			fromLabel: 'From',
			toLabel: 'To',
			customRangeLabel: 'Custom',
			daysOfWeek: ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr','Sa'],
			monthNames: ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
			firstDay: 1
		}
	}, function(start, end, label) {
		$('#advance-daterange span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
        $('#start_date').val(start.format('YYYY-MM-DD'));
        $('#end_date').val(end.format('YYYY-MM-DD'));
	});
};

function handleRenderTableData() {
    var startDate = $('#start_date').val();
    var endDate = $('#end_date').val();
    var height = $(window).height() - $('#header').height() - 165;
    var ga_property_id = document.getElementById('websiteSelect').value;
    var $message = $('#noDataMessage');  
    var $table = $('#analyticsTable');
    var $maintable = $('.table-responsive');

    $table.addClass('d-none');
    $message.addClass('d-none');
   
    $.ajax({
        url: '/api/ga-data',
        type: 'GET',
        data: {
            "_token": $('meta[name="csrf-token"]').attr("content"),
            start_date: startDate,
            end_date: endDate,
            ga_property_id:ga_property_id
        },
        success: function (response) {
            if (response.errors && response.errors.length > 0) {
                alert(response.errors.join("\n"));
            } else {
				if (response.gaData && response.gaData.length > 0) {
                    $table.removeClass('d-none');
                    $maintable.removeClass('d-none');
                    $message.addClass('d-none');
                    renderDataTable(response.gaData);
				}
                else {
                    $maintable.addClass('d-none');
                    $message.removeClass('d-none');
                }
            }
        },
        error: function (xhr, status, error) {
            console.error('Error fetching GA data:', error);
        }
    });

    function renderDataTable(gaData) {
		
        if ($.fn.DataTable.isDataTable($table)) {
		
            $table.DataTable().clear().destroy();
        }

        var $tableBody = $('#analyticsTable tbody');
        $tableBody.empty();
        let totalRevenue = 0;
        let totalpayout = 0;

        gaData.forEach(function (row) {
            
            const revenue = parseFloat(row.totalRevenue) || 0;
            totalRevenue += revenue;
            const payout = parseFloat(row.payout) || 0;
            totalpayout += payout;
            
            $tableBody.append(
                `<tr>
                    <td class="text-start page-name-col">${row.page_path}</td>
                    <td>${row.views}</td>
                    <td>${row.users}</td>
                    <td>${row.screenPageViewsPerUser}</td>
                    <td>${row.engagement}</td>
                    <td>${row.eventCount}</td>
                    <td>${row.publisherAdClicks}</td>
                    <td>${row.publisherAdImpressions}</td>
                    <td>${row.payout} $</td>
                    <td>${row.totalRevenue} $</td>
                </tr>`
            );
        });
         $('#totalRevenueCell').text(`$${totalRevenue.toFixed(2)}`);
        $('#totalpayoutCell').text(`$${totalpayout.toFixed(2)}`);
        $table.DataTable({
            dom: "<'row mb-2 align-items-center'" +
                 "<'col-md-6 d-flex align-items-center'f>" +    // Search (left)
                 "<'col-md-6 text-end d-flex justify-content-end gap-2'B>>" + // Buttons (right)
                 "<'row mb-2 align-items-center'" +
                 "<'col-md-6 d-flex align-items-center'l>" +    // Length dropdown (left)
                 "<'col-md-6 text-end'p>>" +                     // Pagination (right)
                 "<'row'<'col-sm-12'tr>>"  ,
            // responsive: true,
            scrollY: height,
            scrollX: true,
            paging: true,
            info: false,
            pageLength: 12,
            lengthMenu: [[12, 25, 50], [12, 25, 50]],
            fixedColumns: {
                left: ($(window).width() < 767 ? 0 : 1)
            },
            autoWidth: false ,
            order: [[9, 'desc']],
            columnDefs: [
                { targets: 'no-sort', orderable: false }
            ],
            buttons: [
                {
                    extend: 'colvis',
                    className: 'btn-light'
                },
                {
                    extend: 'copy',
                    className: 'btn-light'
                },
                {
                    extend: 'csv',
                    className: 'btn-light'
                },
                {
                    extend: 'excel',
                    text: '<i class="fa fa-download fa-fw me-1 text-theme"></i> Export Excel',
                    className: 'btn-default fs-13px fw-semibold py-5px pe-3',
                    footer: true
                },
                {
                    extend: 'pdf',
                    className: 'btn-light'
                },
                {
                    extend: 'print',
                    className: 'btn-light'
                }
            ]
        });

       
        handelTooltipPopoverActivation();
        $(window).trigger('resize');
    }
}

