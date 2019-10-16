;jQuery(document).ready(function() {
    "use strict";

    jQuery("#js-btn-project-publish").on("click", function(event){
        event.preventDefault();

        if(window.confirm(Joomla.JText._('COM_CROWDFUNDING_QUESTION_LAUNCH_PROJECT'))) {
            window.location.href = jQuery(this).attr("href");
        }

    });

    jQuery("#js-btn-project-unpublish").on("click", function(event){
        event.preventDefault();

        if(window.confirm(Joomla.JText._('COM_CROWDFUNDING_QUESTION_STOP_PROJECT'))) {
            window.location.href = jQuery(this).attr("href");
        }

    });

    (function() {

        function buildFundedChart(projectId, fundedChartElement) {

            var fields = {
                'id': projectId,
                'task': 'statistics.getProjectFunds',
                'format': 'raw'
            };

            fields[crowdfundingOptions.token] = 1;

            jQuery.ajax({
                url: 'index.php?option=com_crowdfunding',
                type: "GET",
                dataType: "text json",
                data: fields
            }).done(function(response) {

                if (response.success) {
                    var data = {
                        'labels': response.data.labels,
                        'datasets': [
                            {
                                data: response.data.datasets.data,
                                hoverBackgroundColor: [
                                    "#36A2EB",
                                    "#24be18",
                                    "#FFCE56"
                                ],
                                backgroundColor: [
                                    "#36A2EB",
                                    "#24be18",
                                    "#FFCE56"
                                ]
                            }
                        ]
                    };

                    var fundedChart = new Chart(fundedChartElement, {
                        type: 'pie',
                        data: data,
                        options: {
                            tooltips: {
                                callbacks: {
                                    label: function(tooltipItem, data) {
                                        return data.labels[tooltipItem.index];
                                    }
                                }
                            }
                        }
                    });

                } else {
                    PrismUIHelper.displayMessageFailure(response.title, response.text);
                }
            });
        }

        function buildDailyFundsChart(projectId, txnChartElement) {

            var fields = {
                'id': projectId,
                'task': 'statistics.getDailyFunds',
                'format': 'raw'
            };

            fields[crowdfundingOptions.token] = 1;

            jQuery.ajax({
                url: 'index.php?option=com_crowdfunding',
                type: "GET",
                dataType: "text json",
                data: fields
            }).done(function(response) {

                if (response.success) {
                    var data = {
                        'labels': response.data.labels,
                        'datasets': [
                            {
                                label: Joomla.JText._('COM_CROWDFUNDING_DAILY_FUNDS'),
                                data: response.data.data,
                                hoverBackgroundColor: 'rgba(54, 162, 235, 1)',
                                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                                borderWidth: 1
                            }
                        ],
                        tooltips: response.data.tooltips,
                    };

                    var txnChart = new Chart(txnChartElement, {
                        type: 'bar',
                        data: data,
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            tooltips: {
                                callbacks: {
                                    label: function(tooltipItem, data) {
                                        return data.tooltips[tooltipItem.index];
                                    }
                                }
                            },
                            scales: {
                                yAxes: [{
                                    ticks: {
                                        beginAtZero:true
                                    }
                                }]
                            }
                        }

                    });

                } else {
                    PrismUIHelper.displayMessageFailure(response.title, response.text);
                }
            });
        }

        var projectId = crowdfundingOptions.projectId;
        var fundedChartElement = document.getElementById("js-funded-chart");
        var txnChartElement = document.getElementById("js-transactions-chart");

        buildFundedChart(projectId, fundedChartElement);
        buildDailyFundsChart(projectId, txnChartElement);
    })();

});