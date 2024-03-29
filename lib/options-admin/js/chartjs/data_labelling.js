var color = Chart.helpers.color;
        var barChartData = {
            labels: ["January", "February", "March", "April", "May", "June", "July"],
            datasets: [{
                type: 'bar',
                label: 'Dataset 1',
                backgroundColor: color(window.chartColors.red).alpha(0.2).rgbString(),
                borderColor: window.chartColors.red,
                data: [
                    randomScalingFactor(), 
                    randomScalingFactor(), 
                    randomScalingFactor(), 
                    randomScalingFactor(), 
                    randomScalingFactor(),
                    randomScalingFactor(), 
                    randomScalingFactor()
                ]
            }, {
                type: 'line',
                label: 'Dataset 2',
                backgroundColor: color(window.chartColors.blue).alpha(0.2).rgbString(),
                borderColor: window.chartColors.blue,
                data: [
                    randomScalingFactor(), 
                    randomScalingFactor(), 
                    randomScalingFactor(), 
                    randomScalingFactor(), 
                    randomScalingFactor(),
                    randomScalingFactor(), 
                    randomScalingFactor()
                ]
            }, {
                type: 'bar',
                label: 'Dataset 3',
                backgroundColor: color(window.chartColors.green).alpha(0.2).rgbString(),
                borderColor: window.chartColors.green,
                data: [
                    randomScalingFactor(), 
                    randomScalingFactor(), 
                    randomScalingFactor(), 
                    randomScalingFactor(), 
                    randomScalingFactor(),
                    randomScalingFactor(), 
                    randomScalingFactor()
                ]
            }]
        };

        // Define a plugin to provide data labels
        Chart.plugins.register({
            afterDatasetsDraw: function(chartInstance, easing) {
                // To only draw at the end of animation, check for easing === 1
                var ctx = chartInstance.chart.ctx;

                chartInstance.data.datasets.forEach(function (dataset, i) {
                    var meta = chartInstance.getDatasetMeta(i);
                    if (!meta.hidden) {
                        meta.data.forEach(function(element, index) {
                            // Draw the text in black, with the specified font
                            ctx.fillStyle = 'rgb(0, 0, 0)';

                            var fontSize = 16;
                            var fontStyle = 'normal';
                            var fontFamily = 'Helvetica Neue';
                            ctx.font = Chart.helpers.fontString(fontSize, fontStyle, fontFamily);

                            // Just naively convert to string for now
                            var dataString = dataset.data[index].toString();

                            // Make sure alignment settings are correct
                            ctx.textAlign = 'center';
                            ctx.textBaseline = 'middle';

                            var padding = 5;
                            var position = element.tooltipPosition();
                            ctx.fillText(dataString, position.x, position.y - (fontSize / 2) - padding);
                        });
                    }
                });
            }
        });

        window.onload = function() {
            var ctxdatalabel = document.getElementById("datapoint").getContext("2d");
            window.myBar = new Chart(ctxdatalabel, {
                type: 'bar',
                data: barChartData,
                options: {
                    responsive: true,
                    title: {
                        display: true,
                        text: 'Chart.js Combo Bar Line Chart'
                    },
                }
            });
        };

        document.getElementById('randomizeData-datapoint').addEventListener('click', function() {
            barChartData.datasets.forEach(function(dataset) {
                dataset.data = dataset.data.map(function() { 
                    return randomScalingFactor();
                })
            });
            window.myBar.update();
        });
