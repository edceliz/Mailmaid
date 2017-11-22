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
var ctx = document.getElementById("userGrowth").getContext('2d');
var subscribeSet = [];
var unsubscribeSet = [];
var labels = [];
var chart = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: labels,
        datasets: [{
            label: 'Subscribe',
            backgroundColor: 'rgba(52,152,219,0.8)',
            data: subscribeSet
        }, {
            label: 'Unsubscribe',
            backgroundColor: 'rgba(231,76,60,0.8)',
            data: unsubscribeSet
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
// Gets the ID of the selected subscriber list
var id = window.location.pathname.split('/')[4];

/**
 * Fetches all subscriber movement within the last 24 hours
 * @param {Number} id - Subscriber List ID
 */
function get24Hours(id) {
    var d = new Date();
    var ajax = ajaxObj('POST', '/mailmaid/subscriber/graph');
    var labels = [(d.getMonth() + 1) + '/' + d.getDate() + ' ' + (d.getHours() + ':00')];
    var count = 1;
    for (var i = 1; i < 24; i++) {
        count++;
        var add = d.getHours() - i;
        if (add >= 0) {
            var today = (d.getMonth() + 1) + '/' + d.getDate() + ' ' + (d.getHours() - i) + ':00';
            labels.unshift(today);
        } else {
            var date = new Date();
            date.setDate(-1)
            var yesterday = (d.getMonth() + 1) + '/' + (date.getDate()) + ' ' + (24 + add) + ':00';
            labels.unshift(yesterday);
        }
    }
    var dataset = [[],[]];
    ajax.onreadystatechange = function() {
        if(ajaxReturn(ajax) == true) {
            var result = JSON.parse(ajax.responseText);
            labels.forEach(function(label) {
                var inserted = false;
                Object.keys(result[0]).forEach(function(key) {
                    if (key.split('-')[0] == (label.split(' ')[1].split(':')[0] + ':00')) {
                        dataset[0][dataset[0].length] = result[0][key];
                        inserted = true;
                    }
                });
                if (!inserted) {
                    dataset[0][dataset[0].length] = 0;
                }
                inserted = false;
                Object.keys(result[1]).forEach(function(key) {
                    if (key.split('-')[0] == (label.split(' ')[1].split(':')[0] + ':00')) {
                        dataset[1][dataset[1].length] = result[1][key];
                        inserted = true;
                    }
                });
                if (!inserted) {
                    dataset[1][dataset[1].length] = 0;
                }
            });
            returnData([labels, dataset]);
        }
    }
    ajax.send('id=' + id + '&type=1');
}

/**
 * Fetches all subscriber movement within the last 7 days
 * @param {Number} id - Subscriber List ID
 */
function get7Days(id) {
    var ajax = ajaxObj('POST', '/mailmaid/subscriber/graph');
    var d = new Date();
    var currentMonth = d.getMonth() + 1;
    var currentDay = d.getDate();
    var currentYear = d.getFullYear();
    var labels = [dateMinusDays(7), dateMinusDays(6), dateMinusDays(5), dateMinusDays(4), dateMinusDays(3), dateMinusDays(2), dateMinusDays(1), dateMinusDays(0)];
    var dataset = [[],[]];
    ajax.onreadystatechange = function() {
        if(ajaxReturn(ajax) == true) {
            var result = JSON.parse(ajax.responseText);
            labels.forEach(function(label) {
                var inserted = false;
                Object.keys(result[0]).forEach(function(key) {
                    if (key == label) {
                        dataset[0][dataset[0].length] = result[0][key];
                        inserted = true;
                    }
                });
                if (!inserted) {
                    dataset[0][dataset[0].length] = 0;
                }
                inserted = false;
                Object.keys(result[1]).forEach(function(key) {
                    if (key == label) {
                        dataset[1][dataset[1].length] = result[1][key];
                        inserted = true;
                    }
                });
                if (!inserted) {
                    dataset[1][dataset[1].length] = 0;
                }
            });
            returnData([labels, dataset]);
        }
    }
    ajax.send('id=' + id + '&type=2');
}

/**
 * Fetches all subscriber movement within the last month
 * @param {Number} id - Subscriber List ID
 */
function getMonths(id) {
    var ajax = ajaxObj('POST', '/mailmaid/subscriber/graph');
    var labels = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
    var dataset = [[],[]];
    ajax.onreadystatechange = function() {
        if(ajaxReturn(ajax) == true) {
            var result = JSON.parse(ajax.responseText);
            Object.keys(labels).forEach(function(label) {
                label = parseInt(label) + parseInt(1);
                var inserted = false;
                Object.keys(result[0]).forEach(function(key) {
                    if (key == label) {
                        dataset[0][dataset[0].length] = result[0][key];
                        inserted = true;
                    }
                });
                if (!inserted) {
                    dataset[0][dataset[0].length] = 0;
                }
                inserted = false;
                Object.keys(result[1]).forEach(function(key) {
                    if (key == label) {
                        dataset[1][dataset[1].length] = result[1][key];
                        inserted = true;
                    }
                });
                if (!inserted) {
                    dataset[1][dataset[1].length] = 0;
                }
            });
            returnData([labels, dataset]);
        }
    }
    ajax.send('id=' + id + '&type=3');
}

/**
 * Fetches all subscriber movement within the last year
 * @param {Number} id - Subscriber List ID
 */
function getYearly(id) {
    var ajax = ajaxObj('POST', '/mailmaid/subscriber/graph');
    var d = new Date();
    var current = d.getFullYear();
    var labels = [current - 5, current - 4, current - 3, current - 2, current - 1, current];
    var dataset = [[],[]];
    ajax.onreadystatechange = function() {
        if(ajaxReturn(ajax) == true) {
            var result = JSON.parse(ajax.responseText);
            labels.forEach(function(label) {
                var inserted = false;
                Object.keys(result[0]).forEach(function(key) {
                    if (key == label) {
                        dataset[0][dataset[0].length] = result[0][key];
                        inserted = true;
                    }
                });
                if (!inserted) {
                    dataset[0][dataset[0].length] = 0;
                }
                inserted = false;
                Object.keys(result[1]).forEach(function(key) {
                    if (key == label) {
                        dataset[1][dataset[1].length] = result[1][key];
                        inserted = true;
                    }
                });
                if (!inserted) {
                    dataset[1][dataset[1].length] = 0;
                }
            });
            returnData([labels, dataset]);
        }
    }
    ajax.send('id=' + id + '&type=4');
}

/**
 * Calls data parser
 * @param {object} newData - Different set of statistical data
 */
function returnData(newData) {
    parseData(newData[0], newData[1]);
}

// Calls the default graph orientation
window.onload = function() {
    get24Hours(window.id);
}

// Calls to update graph into last 24 hours
document.getElementById('getHours').addEventListener('click', function() {
    get24Hours(window.id);
});

// Calls to update graph into last 7 days
document.getElementById('getWeek').addEventListener('click', function() {
    get7Days(window.id);
});

// Calls to update graph into last month
document.getElementById('getMonth').addEventListener('click', function() {
    getMonths(window.id);
});

// Calls to update graph into last year
document.getElementById('getAll').addEventListener('click', function() {
    getYearly(window.id);
});

// Updates the graph
function parseData(newLabel, newData) {
    while (labels.length) {
        labels.pop();
    }
    newLabel.forEach(function(label) {
        labels.push(label);
    });
    while(subscribeSet.length) {
        subscribeSet.pop();
    }
    newData[0].forEach(function(data) {
        subscribeSet.push(data);
    });
    while(unsubscribeSet.length) {
        unsubscribeSet.pop();
    }
    newData[1].forEach(function(data) {
        unsubscribeSet.push(data);
    });
    chart.update();
}
