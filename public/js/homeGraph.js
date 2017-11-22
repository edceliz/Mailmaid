/**
 * Generates random RGB color
 * @returns {string} - Format used in graph
 */
function randomColorGenerator() {
    var brightness = Math.random() * (5 - 0) + 0;
    var rgb = [Math.random() * 256, Math.random() * 256, Math.random() * 256];
    var mix = [brightness*51, brightness*51, brightness*51];
    var mixedrgb = [rgb[0] + mix[0], rgb[1] + mix[1], rgb[2] + mix[2]].map(function(x){ return Math.round(x/2.0)})
    return 'rgba(' + mixedrgb.join(',') + ",0.8)";    
}

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

// Prepares chart for user growth
var ctx1 = document.getElementById("audienceGrowth").getContext('2d');
var audienceLabels = [];
var audienceChart = new Chart(ctx1, {
    type: 'line',
    data: {
        labels: audienceLabels,
        datasets: [{
            label: 'Waiting for Data...',
            backgroundColor: 'rgba(52,152,219,0.8)',
            data: []
        }]
    },
    options: {
        title:{
            display: true,
            text: 'Subscriber Change'
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
 * Fetches all subscriber movement within the last 24 hours
 */
function audience24Hours() {
    var d = new Date();
    window.audienceLabels = [(d.getMonth() + 1) + '/' + d.getDate() + ' ' + (d.getHours() + ':00')];
    var count = 1;
    for (var i = 1; i < 24; i++) {
        count++;
        var add = d.getHours() - i;
        if (add >= 0) {
            var today = (d.getMonth() + 1) + '/' + d.getDate() + ' ' + (d.getHours() - i) + ':00';
            window.audienceLabels.unshift(today);
        } else {
            var date = new Date();
            date.setDate(-1)
            var yesterday = (d.getMonth() + 1) + '/' + (date.getDate()) + ' ' + (24 + add) + ':00';
            window.audienceLabels.unshift(yesterday);
        }
    }
    
    var ajax = ajaxObj('POST', '/mailmaid/home/graph');
    ajax.onreadystatechange = function() {
        if(ajaxReturn(ajax) == true) {
            var result = JSON.parse(ajax.responseText);
            var labels = [];
            var colors = [];
            var datasets = [];
            Object.keys(result).forEach(function(setKey) {
                Object.keys(result[setKey]['data']).forEach(function(dataKey) {
                    if (!parseInt(dataKey)) {
                        var dataset = [];
                        labels.push(result[setKey]['name'] + ' Subscribe');
                        colors.push(randomColorGenerator());
                        window.audienceLabels.forEach(function(period) {
                            var inserted = false;
                            Object.keys(result[setKey]['data'][dataKey]).forEach(function(dataPeriod) {
                                if (period.split(' ')[1].split(':')[0] == dataPeriod.split(':')[0]) {
                                    dataset.push(result[setKey]['data'][dataKey][dataPeriod]);
                                    inserted = true;
                                }
                            });
                            if (!inserted) {
                                dataset.push(0);
                            }
                        });
                        datasets.push(dataset);
                    } else {
                        var dataset = [];
                        labels.push(result[setKey]['name'] + ' Unsubscribe');
                        colors.push(randomColorGenerator());
                        window.audienceLabels.forEach(function(period) {
                            Object.keys(result[setKey]['data'][dataKey]).forEach(function(dataPeriod) {
                                if (period.split(' ')[1].split(':')[0] == dataPeriod.split(':')[0]) {
                                    dataset.push(result[setKey]['data'][dataKey][dataPeriod]);
                                } else {
                                    dataset.push(0);
                                }
                            });
                        });
                        datasets.push(dataset);
                    }
                });
            });

            updateAudienceChart(labels, colors, datasets);
        }
    }
    ajax.send('type=1');
}

/**
 * Fetches all subscriber movement within the last 7 days
 */
function audience7Days() {    
    window.audienceLabels = [dateMinusDays(7), dateMinusDays(6), dateMinusDays(5), dateMinusDays(4), dateMinusDays(3), dateMinusDays(2), dateMinusDays(1), dateMinusDays(0)];
    
    var ajax = ajaxObj('POST', '/mailmaid/home/graph');
    ajax.onreadystatechange = function() {
        if(ajaxReturn(ajax) == true) {
            var result = JSON.parse(ajax.responseText);
            var labels = [];
            var colors = [];
            var datasets = [];
            Object.keys(result).forEach(function(setKey) {
                Object.keys(result[setKey]['data']).forEach(function(dataKey) {
                    if (!parseInt(dataKey)) {
                        var dataset = [];
                        labels.push(result[setKey]['name'] + ' Subscribe');
                        colors.push(randomColorGenerator());
                        window.audienceLabels.forEach(function(period) {
                            var inserted = false;
                            Object.keys(result[setKey]['data'][dataKey]).forEach(function(dataPeriod) {
                                if (period == dataPeriod) {
                                    dataset.push(result[setKey]['data'][dataKey][dataPeriod]);
                                    inserted = true;
                                }
                            });
                            if (!inserted) {
                                dataset.push(0);
                            }
                        });
                        datasets.push(dataset);
                    } else {
                        var dataset = [];
                        labels.push(result[setKey]['name'] + ' Unsubscribe');
                        colors.push(randomColorGenerator());
                        window.audienceLabels.forEach(function(period) {
                            Object.keys(result[setKey]['data'][dataKey]).forEach(function(dataPeriod) {
                                if (period == dataPeriod) {
                                    dataset.push(result[setKey]['data'][dataKey][dataPeriod]);
                                } else {
                                    dataset.push(0);
                                }
                            });
                        });
                        datasets.push(dataset);
                    }
                });
            });

            updateAudienceChart(labels, colors, datasets);
        }
    }
    ajax.send('type=2');
}

/**
 * Fetches all subscriber movement within the last year
 */
function audienceYear() {
    window.audienceLabels = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
    
    var ajax = ajaxObj('POST', '/mailmaid/home/graph');
    ajax.onreadystatechange = function() {
        if(ajaxReturn(ajax) == true) {
            var result = JSON.parse(ajax.responseText);
            var labels = [];
            var colors = [];
            var datasets = [];
            Object.keys(result).forEach(function(setKey) {
                Object.keys(result[setKey]['data']).forEach(function(dataKey) {
                    if (!parseInt(dataKey)) {
                        var dataset = [];
                        labels.push(result[setKey]['name'] + ' Subscribe');
                        colors.push(randomColorGenerator());
                        Object.keys(window.audienceLabels).forEach(function(period) {
                            var inserted = false;
                            Object.keys(result[setKey]['data'][dataKey]).forEach(function(dataPeriod) {
                                if ((parseInt(period) + 1) == dataPeriod) {
                                    dataset.push(result[setKey]['data'][dataKey][dataPeriod]);
                                    inserted = true;
                                }
                            });
                            if (!inserted) {
                                dataset.push(0);
                            }
                        });
                        datasets.push(dataset);
                    } else {
                        var dataset = [];
                        labels.push(result[setKey]['name'] + ' Unsubscribe');
                        colors.push(randomColorGenerator());
                        Object.keys(window.audienceLabels).forEach(function(period) {
                            Object.keys(result[setKey]['data'][dataKey]).forEach(function(dataPeriod) {
                                if ((parseInt(period) + 1) == dataPeriod) {
                                    dataset.push(result[setKey]['data'][dataKey][dataPeriod]);
                                } else {
                                    dataset.push(0);
                                }
                            });
                        });
                        datasets.push(dataset);
                    }
                });
            });

            updateAudienceChart(labels, colors, datasets);
        }
    }
    ajax.send('type=3');
}

/**
 * Callback to update subscriber growth chart
 * @param {string[]} labels
 * @param {string[]} colors
 * @param {Object}    datasets
 */
function updateAudienceChart(labels, colors, datasets) {
    while (window.audienceChart.data.datasets.length) {
        window.audienceChart.data.datasets.pop();
    }
    for (var i = 0; i < labels.length; i++) {
        window.audienceChart.data.datasets.push({
            label: labels[i],
            backgroundColor: colors[i],
            data: datasets[i],
            fill: false,
            borderColor: colors[i]
        });
    }
    window.audienceChart.data.labels = window.audienceLabels;
    window.audienceChart.update();
}

// Prepares chart for user engagement
var ctx2 = document.getElementById("campaignEngagement").getContext('2d');
var campaignLabels = [];
var campaignChart = new Chart(ctx2, {
    type: 'line',
    data: {
        labels: campaignLabels,
        datasets: [{
            label: 'Waiting for data...',
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
 * Fetches all custom link interaction within the last 24 hours
 */
function campaignEngagement24Hours() {
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
    
    var ajax = ajaxObj('POST', '/mailmaid/home/graph');
    ajax.onreadystatechange = function() {
        if(ajaxReturn(ajax) == true) {
            var labels = ['There is no data!'];
            var colors = [];
            var datasets = [];
            if (ajax.responseText == 'false') {
                updateCEChart(labels, colors, datasets);
                return false;
            }
            var result = JSON.parse(ajax.responseText);
            labels = [];
            colors = [];
            datasets = [];
            
            Object.keys(result).forEach(function(campaignKey) {
                labels.push(campaignKey);
                colors.push(randomColorGenerator());
                var dataset = [];
                window.campaignLabels.forEach(function(period) {
                    var inserted = false;
                    Object.keys(result[campaignKey]).forEach(function(dataPeriod) {
                        if (period.split(' ')[1].split(':')[0] == dataPeriod.split(':')[0]) {
                            dataset.push(result[campaignKey][dataPeriod]);
                            inserted = true;
                        }
                    });
                    if(!inserted) {
                        dataset.push(0);
                    }
                });
                datasets.push(dataset);
            });

            updateCEChart(labels, colors, datasets);
        }
    }
    ajax.send('type=4');
}

/**
 * Fetches all custom link interaction within the last 7 days
 */
function campaignEngagement7Days() {
    var d = new Date();
    var currentMonth = d.getMonth() + 1;
    var currentDay = d.getDate();
    var currentYear = d.getFullYear();
    window.campaignLabels = [dateMinusDays(7), dateMinusDays(6), dateMinusDays(5), dateMinusDays(4), dateMinusDays(3), dateMinusDays(2), dateMinusDays(1), dateMinusDays(0)];
    
    var ajax = ajaxObj('POST', '/mailmaid/home/graph');
    ajax.onreadystatechange = function() {
        if(ajaxReturn(ajax) == true) {
            var result = JSON.parse(ajax.responseText);
            if (!result) {
                window.campaignChart.data.labels = window.campaignLabels;
                window.campaignChart.update();
                return;
            }
            var labels = [];
            var colors = [];
            var datasets = [];
            Object.keys(result).forEach(function(campaignKey) {
                labels.push(campaignKey);
                colors.push(randomColorGenerator());
                var dataset = [];
                window.campaignLabels.forEach(function(period) {
                    var inserted = false;
                    Object.keys(result[campaignKey]).forEach(function(dataPeriod) {
                        if (period == dataPeriod) {
                            dataset.push(result[campaignKey][dataPeriod]);
                            inserted = true;
                        }
                    });
                    if(!inserted) {
                        dataset.push(0);
                    }
                });
                datasets.push(dataset);
            });

            updateCEChart(labels, colors, datasets);
        }
    }
    ajax.send('type=5');
}

/**
 * Fetches all custom link interaction within the last year
 */
function campaignEngagementYear() {
    window.campaignLabels = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
    
    var ajax = ajaxObj('POST', '/mailmaid/home/graph');
    ajax.onreadystatechange = function() {
        if(ajaxReturn(ajax) == true) {
            var result = JSON.parse(ajax.responseText);
            if (!result) {
                window.campaignChart.data.labels = window.campaignLabels;
                window.campaignChart.update();
                return;
            }
            var labels = [];
            var colors = [];
            var datasets = [];
            
            Object.keys(result).forEach(function(campaignKey) {
                labels.push(campaignKey);
                colors.push(randomColorGenerator());
                var dataset = [];
                Object.keys(window.campaignLabels).forEach(function(period) {
                    var inserted = false;
                    Object.keys(result[campaignKey]).forEach(function(dataPeriod) {
                        if ((parseInt(period) + 1) == dataPeriod) {
                            dataset.push(result[campaignKey][dataPeriod]);
                            inserted = true;
                        }
                    });
                    if(!inserted) {
                        dataset.push(0);
                    }
                });
                datasets.push(dataset);
            });

            updateCEChart(labels, colors, datasets);
        }
    }
    ajax.send('type=6');
}

/**
 * Callback to update custom link interaction chart
 * @param {string[]} labels
 * @param {string[]} colors
 * @param {Object}    datasets
 */
function updateCEChart(labels, colors, datasets) {
    while (window.campaignChart.data.datasets.length) {
        window.campaignChart.data.datasets.pop();
    }
    for (var i = 0; i < labels.length; i++) {
        window.campaignChart.data.datasets.push({
            label: labels[i],
            backgroundColor: colors[i],
            data: datasets[i],
            fill: false,
            borderColor: colors[i]
        });
    }
    window.campaignChart.data.labels = window.campaignLabels;
    window.campaignChart.update();
}

// Prepares chart for mails sent
var ctx3 = document.getElementById("mailSent").getContext('2d');
var mailLabels = [];
var mailChart = new Chart(ctx3, {
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
            text: 'Mail Sent'
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
 * Fetches all mails sent within the last 24 hours
 */
function mailSent24Hours() {
    var d = new Date();
    window.mailLabels = [(d.getMonth() + 1) + '/' + d.getDate() + ' ' + (d.getHours() + ':00')];
    var count = 1;
    for (var i = 1; i < 24; i++) {
        count++;
        var add = d.getHours() - i;
        if (add >= 0) {
            var today = (d.getMonth() + 1) + '/' + d.getDate() + ' ' + (d.getHours() - i) + ':00';
            window.mailLabels.unshift(today);
        } else {
            var date = new Date();
            date.setDate(-1)
            var yesterday = (d.getMonth() + 1) + '/' + (date.getDate()) + ' ' + (24 + add) + ':00';
            window.mailLabels.unshift(yesterday);
        }
    }
    
    var ajax = ajaxObj('POST', '/mailmaid/home/graph');
    ajax.onreadystatechange = function() {
        if(ajaxReturn(ajax) == true) {
            var labels = ['There is no data!'];
            var colors = [];
            var datasets = [];
            if (ajax.responseText == 'false') {
                updateMSChart(labels, colors, datasets);
                return false;
            }
            var result = JSON.parse(ajax.responseText);
            var labels = [];
            var colors = [];
            var datasets = [];
            Object.keys(result).forEach(function(setKey) {
                Object.keys(result[setKey]['data']).forEach(function(dataKey) {
                    if (dataKey == 'sent') {
                        var dataset = [];
                        labels.push(result[setKey]['name'] + ' Sent');
                        colors.push(randomColorGenerator());
                        window.mailLabels.forEach(function(period) {
                            var inserted = false;
                            Object.keys(result[setKey]['data'][dataKey]).forEach(function(dataPeriod) {
                                if (period.split(' ')[1].split(':')[0] == dataPeriod.split(':')[0]) {
                                    dataset.push(result[setKey]['data'][dataKey][dataPeriod]);
                                    inserted = true;
                                }
                            });
                            if (!inserted) {
                                dataset.push(0);
                            }
                        });
                        datasets.push(dataset);
                    } else {
                        var dataset = [];
                        labels.push(result[setKey]['name'] + ' Failed');
                        colors.push(randomColorGenerator());
                        window.mailLabels.forEach(function(period) {
                            Object.keys(result[setKey]['data'][dataKey]).forEach(function(dataPeriod) {
                                if (period.split(' ')[1].split(':')[0] == dataPeriod.split(':')[0]) {
                                    dataset.push(result[setKey]['data'][dataKey][dataPeriod]);
                                } else {
                                    dataset.push(0);
                                }
                            });
                        });
                        datasets.push(dataset);
                    }
                });
            });

            updateMSChart(labels, colors, datasets);
        }
    }
    ajax.send('type=7');
}

/**
 * Fetches all mails sent within the last 7 days
 */
function mailSent7Days() {
    var d = new Date();
    var currentMonth = d.getMonth() + 1;
    var currentDay = d.getDate();
    var currentYear = d.getFullYear();
    window.mailLabels = [dateMinusDays(7), dateMinusDays(6), dateMinusDays(5), dateMinusDays(4), dateMinusDays(3), dateMinusDays(2), dateMinusDays(1), dateMinusDays(0)];
    
    var ajax = ajaxObj('POST', '/mailmaid/home/graph');
    ajax.onreadystatechange = function() {
        if(ajaxReturn(ajax) == true) {
            var labels = ['There is no data!'];
            var colors = [];
            var datasets = [];
            if (ajax.responseText == 'false') {
                updateMSChart(labels, colors, datasets);
                return false;
            }
            var result = JSON.parse(ajax.responseText);
            var labels = [];
            var colors = [];
            var datasets = [];
            Object.keys(result).forEach(function(setKey) {
                Object.keys(result[setKey]['data']).forEach(function(dataKey) {
                    if (dataKey == 'sent') {
                        var dataset = [];
                        labels.push(result[setKey]['name'] + ' Sent');
                        colors.push(randomColorGenerator());
                        window.mailLabels.forEach(function(period) {
                            var inserted = false;
                            Object.keys(result[setKey]['data'][dataKey]).forEach(function(dataPeriod) {
                                if (period == dataPeriod) {
                                    dataset.push(result[setKey]['data'][dataKey][dataPeriod]);
                                    inserted = true;
                                }
                            });
                            if (!inserted) {
                                dataset.push(0);
                            }
                        });
                        datasets.push(dataset);
                    } else {
                        var dataset = [];
                        labels.push(result[setKey]['name'] + ' Failed');
                        colors.push(randomColorGenerator());
                        window.mailLabels.forEach(function(period) {
                            Object.keys(result[setKey]['data'][dataKey]).forEach(function(dataPeriod) {
                                if (period == dataPeriod) {
                                    dataset.push(result[setKey]['data'][dataKey][dataPeriod]);
                                } else {
                                    dataset.push(0);
                                }
                            });
                        });
                        datasets.push(dataset);
                    }
                });
            });

            updateMSChart(labels, colors, datasets);
        }
    }
    ajax.send('type=8');
}

/**
 * Fetches all mails sent within the last year
 */
function mailSentYear() {
    window.mailLabels = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
    
    var ajax = ajaxObj('POST', '/mailmaid/home/graph');
    ajax.onreadystatechange = function() {
        if(ajaxReturn(ajax) == true) {
            var labels = ['There is no data!'];
            var colors = [];
            var datasets = [];
            if (ajax.responseText == 'false') {
                updateMSChart(labels, colors, datasets);
                return false;
            }
            var result = JSON.parse(ajax.responseText);
            var labels = [];
            var colors = [];
            var datasets = [];
            Object.keys(result).forEach(function(setKey) {
                Object.keys(result[setKey]['data']).forEach(function(dataKey) {
                    if (dataKey == 'sent') {
                        var dataset = [];
                        labels.push(result[setKey]['name'] + ' Sent');
                        colors.push(randomColorGenerator());
                        Object.keys(window.mailLabels).forEach(function(period) {
                            var inserted = false;
                            Object.keys(result[setKey]['data'][dataKey]).forEach(function(dataPeriod) {
                                if ((parseInt(period) + 1) == dataPeriod) {
                                    dataset.push(result[setKey]['data'][dataKey][dataPeriod]);
                                    inserted = true;
                                }
                            });
                            if (!inserted) {
                                dataset.push(0);
                            }
                        });
                        datasets.push(dataset);
                    } else {
                        var dataset = [];
                        labels.push(result[setKey]['name'] + ' Failed');
                        colors.push(randomColorGenerator());
                        Object.keys(window.mailLabels).forEach(function(period) {
                            Object.keys(result[setKey]['data'][dataKey]).forEach(function(dataPeriod) {
                                if ((parseInt(period) + 1) == dataPeriod) {
                                    dataset.push(result[setKey]['data'][dataKey][dataPeriod]);
                                } else {
                                    dataset.push(0);
                                }
                            });
                        });
                        datasets.push(dataset);
                    }
                });
            });

            updateMSChart(labels, colors, datasets);
        }
    }
    ajax.send('type=9');
}

/**
 * Callback to update custom link interaction chart
 * @param {string[]} labels
 * @param {string[]} colors
 * @param {Object}    datasets
 */
function updateMSChart(labels, colors, datasets) {
    while (window.mailChart.data.datasets.length) {
        window.mailChart.data.datasets.pop();
    }
    for (var i = 0; i < labels.length; i++) {
        window.mailChart.data.datasets.push({
            label: labels[i],
            backgroundColor: colors[i],
            data: datasets[i],
            fill: false,
            borderColor: colors[i]
        });
    }
    window.mailChart.data.labels = window.mailLabels;
    window.mailChart.update();
}

// Calls the default graph orientation
window.onload = function() {
    audience24Hours();
    campaignEngagement24Hours();
    mailSent24Hours();
}

// Listeners to graph date range buttons

document.getElementById('ug-1').addEventListener('click', function() {
    audience24Hours();
});

document.getElementById('ug-2').addEventListener('click', function() {
    audience7Days();
});

document.getElementById('ug-3').addEventListener('click', function() {
    audienceYear();
});

document.getElementById('ce-1').addEventListener('click', function() {
    campaignEngagement24Hours();
});

document.getElementById('ce-2').addEventListener('click', function() {
    campaignEngagement7Days();
});

document.getElementById('ce-3').addEventListener('click', function() {
    campaignEngagementYear();
});

document.getElementById('ms-1').addEventListener('click', function() {
    mailSent24Hours();
});

document.getElementById('ms-2').addEventListener('click', function() {
    mailSent7Days();
});

document.getElementById('ms-3').addEventListener('click', function() {
    mailSentYear();
});
