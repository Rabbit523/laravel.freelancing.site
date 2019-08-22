
var Charts = function () {

    return {
        generateJobsReport: function () {

            if (!jQuery.plot) {
                return;
            }

            var data = jobs_report_array;
            var totalPoints = 250;

            plot = $.plotAnimator($("#jobs_report"),
                    [{
                            data: data,
                            animator: {
                                steps: 136, 
                                duration: 2500, 
                                start:0
                            },
                            label: "Jobs Posted",
                            lines: {
                                lineWidth: 1,
                            },
                            shadowSize: 0

                        }],
                    {
                        series: {
                            lines: {
                                show: true,
                                lineWidth: 2,
                                fill: true,
                                fillColor: {
                                    colors: [{
                                            opacity: 0.05
                                        }, {
                                            opacity: 0.01
                                        }
                                    ]
                                }
                            },
                            points: {
                                show: true,
                                radius: 3,
                                lineWidth: 1
                            },
                            shadowSize: 2
                        },
                        grid: {
                            hoverable: true,
                            clickable: true,
                            tickColor: "#eee",
                            borderColor: "#eee",
                            borderWidth: 1
                        },
                        colors: ["#d12610", "#37b7f3", "#52e136"],
                        xaxis: {
                            ticks: 11,
                            tickDecimals: 0,
                            tickColor: "#eee",
                        },
                        yaxis: {
                            ticks: 11,
                            tickDecimals: 0,
                            tickColor: "#eee",
                        }
                    });


            function showTooltip(x, y, contents) {
                $('<div id="tooltip">' + contents + '</div>').css({
                    position: 'absolute',
                    display: 'none',
                    top: y + 5,
                    left: x + 15,
                    border: '1px solid #333',
                    padding: '4px',
                    color: '#fff',
                    'border-radius': '3px',
                    'background-color': '#333',
                    opacity: 0.80
                }).appendTo("body").fadeIn(200);
            }

            var previousPoint = null;
            $("#jobs_report").bind("plothover", function (event, pos, item) {
                $("#x").text(pos.x.toFixed(2));
                $("#y").text(pos.y.toFixed(2));

                if (item) {
                    if (previousPoint != item.dataIndex) {
                        previousPoint = item.dataIndex;

                        $("#tooltip").remove();
                        var x = item.datapoint[0].toFixed(2),
                                y = item.datapoint[1].toFixed(2);

                        showTooltip(item.pageX, item.pageY, y + " " + item.series.label + " on " + x);
                    }
                } else {
                    $("#tooltip").remove();
                    previousPoint = null;
                }
            });
        },
        resetUsersReport: function () {
            console.log(pageviews);
            plot.setData([{
                    data: pageviews,
                }]);
            plot.setupGrid();
            plot.draw();
        },
    };

}();