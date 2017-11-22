/**
 * Reduces N amount of days to created date and format it
 * @param   {Number} days
 * @returns {string} - A MM/DD/YYYY formatted string
 */
function dateMinusDays(days) {
    var d = new Date();
    var last = new Date(d.getTime() - (days * 24 * 60 * 60 * 1000));
    var day = last.getDate();
    var month = last.getMonth() + 1;
    var year = last.getFullYear();
    return month + '/' + day + '/' + year;
}

// Fetches the current campaign ID
var id = window.location.pathname.split('/')[4];

// Calls the default graph orientation
window.onload = function() {
    campaignEngagement24Hours(window.id);
    mailSent24Hours(window.id);
}

// Prepares chart for user engagement
var ctx1 = document.getElementById("campaignEngagement").getContext('2d');
var campaignLabels = [];
var campaignChart = new Chart(ctx1, {
    type: 'line',
    data: {
        labels: campaignLabels,
        datasets: [{
            label: 'Waiting for Data...',
            backgroundColor: 'rgba(52,152,219,0.8)',
            data: []
        }]
    },
    options: {
        title:{
            display: true,
            text: 'Campaign Engagement'
        },
        tooltips: {
            mode: 'index',
            intersect: false
        },
        scales: {
            yAxes: [{
                display: true,
                ticks: {
                    beginAtZero: true,
                    min: 0,
                    callback: function(value, index, values) {
                        if (Math.floor(value) === value) {
                            return value;
                        }
                    }
                }
            }]
        },
        responsive: true,
        maintainAspectRation: true
    }
});

/**
 * Fetches all custom link interaction within the last 24 hours of the selected campaign
 * @params {Number} id - ID of the campaign
 */
function campaignEngagement24Hours(id) {
    var d = new Date();
    window.campaignLabels = [(d.getMonth() + 1) + '/' + d.getDate() + ' ' + (d.getHours() + ':00')];
    var count = 1;
    for (var i = 1; i < 24; i++) {
        count++;
        var add = d.getHours() - i;
        if (add >= 0) {
            var today = (d.getMonth() + 1) + '/' + d.getDate() + ' ' + (d.getHours() - i) + ':00';
            window.campaignLabels.unshift(today);
        } else {
            var date = new Date();
            date.setDate(-1)
            var yesterday = (d.getMonth() + 1) + '/' + (date.getDate()) + ' ' + (24 + add) + ':00';
            window.campaignLabels.unshift(yesterday);
        }
    }
    var ajax = ajaxObj('POST', '/mailmaid/campaign/graph');
    ajax.onreadystatechange = function() {
        if(ajaxReturn(ajax) == true) {
            if (ajax.responseText == 'false') {
                window.campaignChart.data.labels = window.campaignLabels;
                window.campaignChart.data.datasets[0].label = 'There is no data!';
                window.campaignChart.update();
                return;
            }
            var result = JSON.parse(ajax.responseText);
            var labels = [];
            var colors = [];
            var datasets = [];;
            Object.keys(result).forEach(function(newDataKey) {
                var datasetData = [];
                window.campaignLabels.forEach(function(label) {
                    Object.keys(result[newDataKey]['data']).forEach(function(dataKey) {
                        if (label.split(' ')[1].split(':')[0] == Object.keys(result[newDataKey]['data'][dataKey])[0].split(':')[0]) {
                            datasetData.unshift(result[newDataKey]['data'][dataKey][Object.keys(result[newDataKey]['data'][dataKey])[0]]);
                        } else {
                            datasetData.unshift(0);
                        }
                    });
                });
                labels.push(result[newDataKey]['label']);
                colors.push(result[newDataKey]['color']);
                datasets.push(datasetData);
            });
            updateCEChart(labels, colors, datasets);
        }
    }
    ajax.send('id=' + id + '&type=1');
}

/**
 * Fetches all custom link interaction within the last 7 days of the selected campaign
 * @params {Number} id - ID of the campaign
 */
function campaignEngagement7Days(id) {
    var d = new Date();
    var currentMonth = d.getMonth() + 1;
    var currentDay = d.getDate();
    var currentYear = d.getFullYear();
    window.campaignLabels = [dateMinusDays(7), dateMinusDays(6), dateMinusDays(5), dateMinusDays(4), dateMinusDays(3), dateMinusDays(2), dateMinusDays(1), dateMinusDays(0)];
    
    var ajax = ajaxObj('POST', '/mailmaid/campaign/graph');
    ajax.onreadystatechange = function() {
        if(ajaxReturn(ajax) == true) {
            if (ajax.responseText == 'false') {
                window.campaignChart.data.labels = window.campaignLabels;
                window.campaignChart.data.datasets[0].label = 'There is no data!';
                window.campaignChart.update();
                return;
            }
            var result = JSON.parse(ajax.responseText);
            var labels = [];
            var colors = [];
            var datasets = [];;
            Object.keys(result).forEach(function(newDataKey) {
                var datasetData = [];
                window.campaignLabels.forEach(function(label) {
                    Object.keys(result[newDataKey]['data']).forEach(function(dataKey) {
                        if (label == Object.keys(result[newDataKey]['data'][dataKey])) {
                            datasetData.push(result[newDataKey]['data'][dataKey][Object.keys(result[newDataKey]['data'][dataKey])[0]]);
                        } else {
                            datasetData.push(0);
                        }
                    });
                });
                labels.push(result[newDataKey]['label']);
                colors.push(result[newDataKey]['color']);
                datasets.push(datasetData);
            });
            updateCEChart(labels, colors, datasets);
        }
    }
    ajax.send('id=' + id + '&type=2');
}

/**
 * Fetches all custom link interaction within the last year of the selected campaign
 * @params {Number} id - ID of the campaign
 */
function campaignEngagementYear(id) {
    window.campaignLabels = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
    
    var ajax = ajaxObj('POST', '/mailmaid/campaign/graph');
    ajax.onreadystatechange = function() {
        if(ajaxReturn(ajax) == true) {
            if (ajax.responseText == 'false') {
                window.campaignChart.data.labels = window.campaignLabels;
                window.campaignChart.data.datasets[0].label = 'There is no data!';
                window.campaignChart.update();
                return;
            }
            var result = JSON.parse(ajax.responseText);
            var labels = [];
            var colors = [];
            var datasets = [];;
            Object.keys(result).forEach(function(newDataKey) {
                var datasetData = [];
                Object.keys(window.campaignLabels).forEach(function(label) {
                    Object.keys(result[newDataKey]['data']).forEach(function(dataKey) {
                        if ((parseInt(label) + 1) == Object.keys(result[newDataKey]['data'][dataKey])[0]) {
                            datasetData.push(result[newDataKey]['data'][dataKey][Object.keys(result[newDataKey]['data'][dataKey])[0]]);
                        } else {
                            datasetData.push(0);
                        }
                    });
                });
                labels.push(result[newDataKey]['label']);
                colors.push(result[newDataKey]['color']);
                datasets.push(datasetData);
            });
            updateCEChart(labels, colors, datasets);
        }
    }
    ajax.send('id=' + id + '&type=3');
}

/**
 * Callback to update custom link interaction chart
 * @param {string[]} labels
 * @param {string[]} colors
 * @param {Object}    datasets
 */
function updateCEChart(newLabel, newColor, newData) {
    while(campaignChart.data.datasets.length) {
        campaignChart.data.datasets.pop();
    }
    for (var i = 0; i < newLabel.length; i++) {
        campaignChart.data.datasets.push({
            label: newLabel[i],
            backgroundColor: newColor[i],
            data: newData[i]
        });
    }
    campaignChart.data.labels = window.campaignLabels;
    campaignChart.update();
}

// Prepares chart for mails sent
var ctx2 = document.getElementById("mailSent").getContext('2d');
var mailSentLabels = [];
var mailSentChart = new Chart(ctx2, {
    type: 'bar',
    data: {
        labels: mailSentLabels,
        datasets: [{
            label: 'Sent',
            backgroundColor: 'rgba(52,152,219,0.8)',
            data: []
        }, {
            label: 'Failed',
            backgroundColor: 'rgba(231,76,60,0.8)',
            data: []
        }]
    },
    options: {
        title:{
            display: true,
            text: 'Mails Sent'
        },
        tooltips: {
            mode: 'index',
            intersect: false
        },
        scales: {
            yAxes: [{
                ticks: {
                    beginAtZero: true,
                    min: 0,
                    callback: function(value, index, values) {
                        if (Math.floor(value) === value) {
                            return value;
                        }
                    }
                }
            }]
        },
        responsive: true,
        maintainAspectRation: true,
    }
});

/**
 * Fetches all mails sent within the last 24 hours of the selected campaign.
 * @params {Number} id - ID of the campaign
 */
function mailSent24Hours(id) {
    var d = new Date();
    window.mailSentLabels = [(d.getMonth() + 1) + '/' + d.getDate() + ' ' + (d.getHours() + ':00')];
    var count = 1;
    for (var i = 1; i < 24; i++) {
        count++;
        var add = d.getHours() - i;
        if (add >= 0) {
            var today = (d.getMonth() + 1) + '/' + d.getDate() + ' ' + (d.getHours() - i) + ':00';
            window.mailSentLabels.unshift(today);
        } else {
            var date = new Date();
            date.setDate(-1)
            var yesterday = (d.getMonth() + 1) + '/' + (date.getDate()) + ' ' + (24 + add) + ':00';
            window.mailSentLabels.unshift(yesterday);
        }
    }
    
    var ajax = ajaxObj('POST', '/mailmaid/campaign/graph');
    ajax.onreadystatechange = function() {
        if(ajaxReturn(ajax) == true) {
            var sent = [];
            var failed = [];
            var result = JSON.parse(ajax.responseText);
            window.mailSentLabels.forEach(function(label) {
                var inserted = false;
                Object.keys(result['sent']).forEach(function(period) {
                    if (label.split(' ')[1].split(':')[0] == period.split(':')[0]) {
                        sent.push(result['sent'][period]);
                        inserted = true;
                    }
                });
                if (!inserted) {
                    sent.push(0);
                }
            });
            window.mailSentLabels.forEach(function(label) {
                var inserted = false;
                Object.keys(result['failed']).forEach(function(period) {
                    if (label.split(' ')[1].split(':')[0] == period.split(':')[0]) {
                        failed.push(result['failed'][period]);
                        inserted = true;
                    }
                });
                if (!inserted) {
                    failed.push(0);
                }
            });
            updateMailSentChart(sent, failed);
        }
    }
    ajax.send('id=' + id + '&type=4');
}

/**
 * Fetches all mails sent within the last 7 days of the selected campaign.
 * @params {Number} id - ID of the campaign
 */
function mailSent7Days(id) {
    var d = new Date();
    var currentMonth = d.getMonth() + 1;
    var currentDay = d.getDate();
    var currentYear = d.getFullYear();
    window.mailSentLabels = [dateMinusDays(7), dateMinusDays(6), dateMinusDays(5), dateMinusDays(4), dateMinusDays(3), dateMinusDays(2), dateMinusDays(1), dateMinusDays(0)];
    var ajax = ajaxObj('POST', '/mailmaid/campaign/graph');
    ajax.onreadystatechange = function() {
        if(ajaxReturn(ajax) == true) {
            var sent = [];
            var failed = [];
            var result = JSON.parse(ajax.responseText);
            window.mailSentLabels.forEach(function(label) {
                var inserted = false;
                Object.keys(result['sent']).forEach(function(period) {
                    if (label == period) {
                        sent.push(result['sent'][period]);
                        inserted = true;
                    }
                });
                if (!inserted) {
                    sent.push(0);
                }
            });
            window.mailSentLabels.forEach(function(label) {
                var inserted = false;
                Object.keys(result['failed']).forEach(function(period) {
                    if (label == period) {
                        failed.push(result['failed'][period]);
                        inserted = true;
                    }
                });
                if (!inserted) {
                    failed.push(0);
                }
            });
            updateMailSentChart(sent, failed);
        }
    }
    ajax.send('id=' + id + '&type=5');
}

/**
 * Fetches all mails sent within the last year of the selected campaign.
 * @params {Number} id - ID of the campaign
 */
function mailSentYear(id) {
    window.mailSentLabels = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
    var ajax = ajaxObj('POST', '/mailmaid/campaign/graph');
    ajax.onreadystatechange = function() {
        if(ajaxReturn(ajax) == true) {
            var sent = [];
            var failed = [];
            var result = JSON.parse(ajax.responseText);
            Object.keys(window.mailSentLabels).forEach(function(label) {
                var inserted = false;
                Object.keys(result['sent']).forEach(function(period) {
                    if ((parseInt(label) + 1) == period) {
                        sent.push(result['sent'][period]);
                        inserted = true;
                    }
                });
                if (!inserted) {
                    sent.push(0);
                }
            });
            Object.keys(window.mailSentLabels).forEach(function(label) {
                var inserted = false;
                Object.keys(result['failed']).forEach(function(period) {
                    if ((parseInt(label) + 1) == period) {
                        failed.push(result['failed'][period]);
                        inserted = true;
                    }
                });
                if (!inserted) {
                    failed.push(0);
                }
            });
            updateMailSentChart(sent, failed);
        }
    }
    ajax.send('id=' + id + '&type=6');
}

/**
 * Callback to update custom link interaction chart
 * @param {string[]} labels
 * @param {string[]} colors
 * @param {Object}    datasets
 */
function updateMailSentChart(sentData, failedData) {
    mailSentChart.data.labels = mailSentLabels;
    mailSentChart.data.datasets[0]['data'] = sentData;
    mailSentChart.data.datasets[1]['data'] = failedData;
    mailSentChart.update();
}

// Listeners to graph date range buttons

document.getElementById('ce-1').addEventListener('click', function() {
    campaignEngagement24Hours(window.id);
});

document.getElementById('ce-2').addEventListener('click', function() {
    campaignEngagement7Days(window.id);
});

document.getElementById('ce-3').addEventListener('click', function() {
    campaignEngagementYear(window.id);
});

document.getElementById('ms-1').addEventListener('click', function() {
    mailSent24Hours(window.id);
});

document.getElementById('ms-2').addEventListener('click', function() {
    mailSent7Days(window.id);
});

document.getElementById('ms-3').addEventListener('click', function() {
    mailSentYear(window.id);
});
