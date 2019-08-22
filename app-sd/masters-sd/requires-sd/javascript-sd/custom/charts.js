
var Charts = function () {

    return {
        generateUsersReport: function () {

            if (!jQuery.plot) {
                return;
            }

            var totalPoints = 250;

            var data = {
                grow: {stepMode: "linear"},
                data: users_report_array,
                animator: {
                    steps: 136,
                    duration: 2500,
                    start: 0
                },
                label: "Users Registered",
                lines: {
                    lineWidth: 1,
                },
                shadowSize: 0

            };

            var options = {
                series: {
                    grow: {active: true},
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
                    mode: "categories",
                    tickLength: 0

                },
                yaxis: {
                    ticks: 11,
                    tickDecimals: 0,
                    tickColor: "#eee",
                }
            };

            //plot = $.plotAnimator($("#users_report"), [data], options);
            plot = $.plot($("#users_report"), [data], options);
            
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

            $("#users_report").bind("plotclick", function (event, pos, item) {
                if (item) {
                    portlet = $(this).parents('.portlet');
                    day = '';
                    if(portlet.find('.month').val() == '-') {
                        month = item.datapoint[0] + 1;
                    } else {
                        day = item.datapoint[0] + 1;
                        month = portlet.find('.month').val();
                    }
                    year = portlet.find('.year').val();

                    url_to_redirect = SITE_ADM_URL_USERS;
                    if(day != '')  {
                        url_to_redirect += "?day=" + day + "&month=" + month;
                    } else {
                        url_to_redirect += "?month=" + month;
                    }

                    url_to_redirect += "&year=" + year;
                    window.open(url_to_redirect, '_blank');
                }
            });

            $("#users_report").bind("plothover", function (event, pos, item) {
                month = $(this).parents(".portlet").find(".month").val();
                year = $(this).parents(".portlet").find(".year").val();

                if (month == '-') {
                    report_tenure = "yearly";
                } else {
                    report_tenure = "monthly";
                }

                $("#x").text(pos.x.toFixed(2));
                $("#y").text(pos.y.toFixed(2));

                if (item) {
                    if (previousPoint != item.dataIndex) {
                        previousPoint = item.dataIndex;

                        $("#tooltip").remove();
                        var x = item.datapoint[0],
                                y = item.datapoint[1];


                        if (report_tenure == "yearly") {
                            showTooltip(item.pageX, item.pageY, y + " " + item.series.label + " in " + MONTH_NAMES_SHORT[x] + ", " + year);
                        } else {
                            showTooltip(item.pageX, item.pageY, y + " " + item.series.label + " on " + (x + 1) + " " + MONTH_NAMES_SHORT[(month - 1)] + ", " + year);
                        }
                    }
                } else {
                    $("#tooltip").remove();
                    previousPoint = null;
                }
            });
            
        },

        generateJobsReport: function () {

            if (!jQuery.plot) {
                return;
            }

            var totalPoints = 250;

            var data = {
                grow: {stepMode: "linear"},
                data: jobs_report_array,
                animator: {
                    steps: 136,
                    duration: 2500,
                    start: 0
                },
                label: "Jobs Posted",
                lines: {
                    lineWidth: 1,
                },
                shadowSize: 0

            };

            var options = {
                series: {
                    grow: {active: true},
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
                    mode: "categories",
                    tickLength: 0

                },
                yaxis: {
                    ticks: 11,
                    tickDecimals: 0,
                    tickColor: "#eee",
                }
            };

            //plot = $.plotAnimator($("#users_report"), [data], options);
            plot = $.plot($("#jobs_report"), [data], options);

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

            $("#jobs_report").bind("plotclick", function (event, pos, item) {
                if (item) {
                    portlet = $(this).parents('.portlet');
                    day = '';
                    if(portlet.find('.month').val() == '-') {
                        month = item.datapoint[0] + 1;
                    } else {
                        day = item.datapoint[0] + 1;
                        month = portlet.find('.month').val();
                    }
                    year = portlet.find('.year').val();

                    url_to_redirect = SITE_ADM_URL_JOBS;
                    if(day != '')  {
                        url_to_redirect += "?day=" + day + "&month=" + month;
                    } else {
                        url_to_redirect += "?month=" + month;
                    }

                    url_to_redirect += "&year=" + year;
                    window.open(url_to_redirect, '_blank');
                }

            });

            $("#jobs_report").bind("plothover", function (event, pos, item) {
                month = $(this).parents(".portlet").find(".month").val();
                year = $(this).parents(".portlet").find(".year").val();

                if (month == '-') {
                    report_tenure = "yearly";
                } else {
                    report_tenure = "monthly";
                }

                $("#x").text(pos.x.toFixed(2));
                $("#y").text(pos.y.toFixed(2));

                if (item) {
                    if (previousPoint != item.dataIndex) {
                        previousPoint = item.dataIndex;

                        $("#tooltip").remove();
                        var x = item.datapoint[0],
                                y = item.datapoint[1];


                        if (report_tenure == "yearly") {
                            showTooltip(item.pageX, item.pageY, y + " " + item.series.label + " in " + MONTH_NAMES_SHORT[x] + ", " + year);
                        } else {
                            showTooltip(item.pageX, item.pageY, y + " " + item.series.label + " on " + (x + 1) + " " + MONTH_NAMES_SHORT[(month - 1)] + ", " + year);
                        }
                    }
                } else {
                    $("#tooltip").remove();
                    previousPoint = null;
                }
            });
        },
        generateCompaniesReport: function () {

            if (!jQuery.plot) {
                return;
            }

            var totalPoints = 250;

            var data = {
                grow: {stepMode: "linear"},
                data: companies_report_array,
                animator: {
                    steps: 136,
                    duration: 2500,
                    start: 0
                },
                label: "Companies Created",
                lines: {
                    lineWidth: 1,
                },
                shadowSize: 0

            };

            var options = {
                series: {
                    grow: {active: true},
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
                    mode: "categories",
                    tickLength: 0

                },
                yaxis: {
                    ticks: 11,
                    tickDecimals: 0,
                    tickColor: "#eee",
                }
            };

            //plot = $.plotAnimator($("#users_report"), [data], options);
            plot = $.plot($("#companies_report"), [data], options);


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

            $("#companies_report").bind("plotclick", function (event, pos, item) {
                if (item) {
                    portlet = $(this).parents('.portlet');
                    day = '';
                    if(portlet.find('.month').val() == '-') {
                        month = item.datapoint[0] + 1;
                    } else {
                        day = item.datapoint[0] + 1;
                        month = portlet.find('.month').val();
                    }
                    year = portlet.find('.year').val();

                    url_to_redirect = SITE_ADM_URL_COMPANIES;
                    if(day != '')  {
                        url_to_redirect += "?day=" + day + "&month=" + month;
                    } else {
                        url_to_redirect += "?month=" + month;
                    }

                    url_to_redirect += "&year=" + year;
                    window.open(url_to_redirect, '_blank');
                }

            });

            $("#companies_report").bind("plothover", function (event, pos, item) {
                month = $(this).parents(".portlet").find(".month").val();
                year = $(this).parents(".portlet").find(".year").val();

                if (month == '-') {
                    report_tenure = "yearly";
                } else {
                    report_tenure = "monthly";
                }

                $("#x").text(pos.x.toFixed(2));
                $("#y").text(pos.y.toFixed(2));

                if (item) {
                    if (previousPoint != item.dataIndex) {
                        previousPoint = item.dataIndex;

                        $("#tooltip").remove();
                        var x = item.datapoint[0],
                                y = item.datapoint[1];


                        if (report_tenure == "yearly") {
                            showTooltip(item.pageX, item.pageY, y + " " + item.series.label + " in " + MONTH_NAMES_SHORT[x] + ", " + year);
                        } else {
                            showTooltip(item.pageX, item.pageY, y + " " + item.series.label + " on " + (x + 1) + " " + MONTH_NAMES_SHORT[(month - 1)] + ", " + year);
                        }
                    }
                } else {
                    $("#tooltip").remove();
                    previousPoint = null;
                }
            });
        },
        generateGroupsReport: function () {

            if (!jQuery.plot) {
                return;
            }

            var totalPoints = 250;

            var data = {
                grow: {stepMode: "linear"},
                data: groups_report_array,
                animator: {
                    steps: 136,
                    duration: 2500,
                    start: 0
                },
                label: "Groups Created",
                lines: {
                    lineWidth: 1,
                },
                shadowSize: 0

            };

            var options = {
                series: {
                    grow: {active: true},
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
                    mode: "categories",
                    tickLength: 0

                },
                yaxis: {
                    ticks: 11,
                    tickDecimals: 0,
                    tickColor: "#eee",
                }
            };

            //plot = $.plotAnimator($("#users_report"), [data], options);
            plot = $.plot($("#groups_report"), [data], options);


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

            $("#groups_report").bind("plotclick", function (event, pos, item) {
                
                if (item) {
                    portlet = $(this).parents('.portlet');
                    day = '';
                    if(portlet.find('.month').val() == '-') {
                        month = item.datapoint[0] + 1;
                    } else {
                        day = item.datapoint[0] + 1;
                        month = portlet.find('.month').val();
                    }
                    year = portlet.find('.year').val();

                    url_to_redirect = SITE_ADM_URL_GROUPS;
                    if(day != '')  {
                        url_to_redirect += "?day=" + day + "&month=" + month;
                    } else {
                        url_to_redirect += "?month=" + month;
                    }

                    url_to_redirect += "&year=" + year;
                    window.open(url_to_redirect, '_blank');
                }

            });

            $("#groups_report").bind("plothover", function (event, pos, item) {
                month = $(this).parents(".portlet").find(".month").val();
                year = $(this).parents(".portlet").find(".year").val();

                if (month == '-') {
                    report_tenure = "yearly";
                } else {
                    report_tenure = "monthly";
                }

                $("#x").text(pos.x.toFixed(2));
                $("#y").text(pos.y.toFixed(2));

                if (item) {
                    if (previousPoint != item.dataIndex) {
                        previousPoint = item.dataIndex;

                        $("#tooltip").remove();
                        var x = item.datapoint[0],
                                y = item.datapoint[1];


                        if (report_tenure == "yearly") {
                            showTooltip(item.pageX, item.pageY, y + " " + item.series.label + " in " + MONTH_NAMES_SHORT[x] + ", " + year);
                        } else {
                            showTooltip(item.pageX, item.pageY, y + " " + item.series.label + " on " + (x + 1) + " " + MONTH_NAMES_SHORT[(month - 1)] + ", " + year);
                        }
                    }
                } else {
                    $("#tooltip").remove();
                    previousPoint = null;
                }
            });

        },
        generateRevenueEarnedReport: function () {

            if (!jQuery.plot) {
                return;
            }

            var totalPoints = 250;

            var data = {
                grow: {stepMode: "linear"},
                data: revenue_earned_report_array,
                animator: {
                    steps: 136,
                    duration: 2500,
                    start: 0
                },
                label: "Revenue Earned",
                lines: {
                    lineWidth: 1,
                },
                shadowSize: 0

            };

            var options = {
                series: {
                    grow: {active: true},
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
                    mode: "categories",
                    tickLength: 0

                },
                yaxis: {
                    ticks: 11,
                    tickDecimals: 0,
                    tickColor: "#eee",
                }
            };

            //plot = $.plotAnimator($("#users_report"), [data], options);
            plot = $.plot($("#revenue_earned_report"), [data], options);


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

            $("#revenue_earned_report").bind("plotclick", function (event, pos, item) {

                if (item) {
                    portlet = $(this).parents('.portlet');
                    day = '';
                    if(portlet.find('.month').val() == '-') {
                        month = item.datapoint[0] + 1;
                    } else {
                        day = item.datapoint[0] + 1;
                        month = portlet.find('.month').val();
                    }
                    year = portlet.find('.year').val();

                    url_to_redirect = SITE_ADM_URL_PAYMENT_HISTORY;
                    if(day != '')  {
                        url_to_redirect += "?day=" + day + "&month=" + month;
                    } else {
                        url_to_redirect += "?month=" + month;
                    }

                    url_to_redirect += "&year=" + year;
                    window.open(url_to_redirect, '_blank');
                }
                
            });

            $("#revenue_earned_report").bind("plothover", function (event, pos, item) {
                month = $(this).parents(".portlet").find(".month").val();
                year = $(this).parents(".portlet").find(".year").val();

                if (month == '-') {
                    report_tenure = "yearly";
                } else {
                    report_tenure = "monthly";
                }

                $("#x").text(pos.x.toFixed(2));
                $("#y").text(pos.y.toFixed(2));

                if (item) {
                    if (previousPoint != item.dataIndex) {
                        previousPoint = item.dataIndex;

                        $("#tooltip").remove();
                        var x = item.datapoint[0],
                                y = item.datapoint[1].toFixed(2);

                        if (report_tenure == "yearly") {
                            showTooltip(item.pageX, item.pageY, CURRENCY_SYMBOL + y + " " + item.series.label + " in " + MONTH_NAMES_SHORT[x] + ", " + year);
                        } else {
                            showTooltip(item.pageX, item.pageY, CURRENCY_SYMBOL + y + " " + item.series.label + " on " + (x + 1) + " " + MONTH_NAMES_SHORT[(month - 1 )] + ", " + year);
                        }
                    }
                } else {
                    $("#tooltip").remove();
                    previousPoint = null;
                }

            });

        },
        unBindClickEvent: function (element) {
            element.unbind("plotclick");
        },
    };

}();