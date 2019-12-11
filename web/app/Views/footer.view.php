<script
			  src="https://code.jquery.com/jquery-3.4.1.min.js"
			  integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo="
			  crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@2.9.3/dist/Chart.min.js"></script>
<script>
$(document).ready(function () {
    getSensorData();
    updateCurrentReadings();
});

function updateCurrentReadings() {
    $.getJSON(url, function (jsondata) {
        var temp = parseFloat(jsondata.temperature);
        var systemTemp = parseFloat(jsondata.systemTemp);
        var humidity = parseFloat(jsondata.humidity);

        $('temp').text('Temperature: ' + temp + 'C');
        $('systemTemp').text('System Temperature: ' + systemTemp + 'C');
        $('humidity').text('Humidity: ' + humidity + '%h');
    });

    setTimeout(updateCurrentReadings, 5000);
}

function getSensorData() {
    // Update chart
    var radioValue = $("input[name='time']:checked").val();
    var url = '/api/get?time=' + radioValue;
    $.getJSON(url, function (jsondata) {
        var humidity = [];
        var temperature = [];
        var dates = [];

        $.each(jsondata, function (i) {
            humidity.push(parseFloat(jsondata[i].humidity));
            temperature.push(parseFloat(jsondata[i].temperature));
            dates.push(jsondata[i].datetime);
        });

        var ctx = document.getElementById('sensorChart').getContext('2d');
        var chart = new Chart(ctx, {
            type: 'line',

            data: {
                labels: dates,
                datasets: [{
                    label: "Temperature",
                    backgroundColor: 'rgba(255, 99, 132, 0)',
                    borderColor: 'rgb(255, 99, 132)',
                    data: temperature,
                    yAxisID: 'y-axis-1'
                },
                {
                    label: "Humidity",
                    backgroundColor: 'rgba(255, 255, 255, 0)',
                    borderColor: 'rgb(55, 99, 132)',
                    data: humidity
                }]
            },

            options: {
                maintainAspectRatio: false,
                responsive: true,
                title: {
                    display: true,
                    text: 'Temperature and Humidity Readings'
                },
                scales: {
                    xAxes: [{
                        display: true,
                        scaleLabel: {
                            display: true,
                            labelString: 'Time'
                        }
                    }],
                    yAxes: [{
                        display: true,
                        position: 'left',
                        id: 'y-axis-1',
                        scaleLabel: {
                            display: true,
                            labelString: 'Temperature (C)',
                        }
                    },
                    {
                        display: true,
                        position: 'right',
                        id: 'y-axis-2',
                        gridLines: {
                            drawOnChartArea: false
                        },
                        scaleLabel: {
                            display: true,
                            labelString: 'Humidity',
                        }
                    }]
                }
            }
        });
    });

    // Periodically refresh
    setTimeout(getSensorData, 5000);
}
</script>
</body>
</html>