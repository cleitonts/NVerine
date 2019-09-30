function notas(val, html){
    // primeiro soma os totais de cada série
    var total = [];
    for(var i = 0; i < val.datasets.length; i++){
        for(var c = 0; c < val.datasets[i].data.length; c++){
            if(typeof total[c] == "undefined"){
                total[c] = 0;
            }
            total[c] = parseFloat(total[c] + val.datasets[i].data[c]);

        }
    }

    // clone para manter o original
    var dataset = $.extend(true,{},val);

    //converte os valores para %
    for(var i = 0; i < dataset.datasets.length; i++){
        for(var c = 0; c < dataset.datasets[i].data.length; c++){
            dataset.datasets[i].data[c] = parseFloat((dataset.datasets[i].data[c] * 100) / total[c]).toFixed(2);
        }
    }

    //var ctx = document.getElementById("myChart");
    var myChart = new Chart(html, {
        type: 'horizontalBar',
        data: {
            labels: val.labels,
            datasets: dataset.datasets,
        },
        options: {
            scales: {
                yAxes: [{ stacked: true }],
                xAxes: [{ stacked: true }]
            },
            title: {
                display: true,
                text: 'Notas vermelhas'
            },
            tooltips: {
                enabled: true,
                mode: 'single',
                callbacks: {
                    title: function(tooltipItem) {
                        return "Quantidade de alunos -- "+ tooltipItem[0].yLabel;
                    },
                    label: function(tooltipItem) {
                        return val.datasets[tooltipItem.datasetIndex].label + ": " +
                            val.datasets[tooltipItem.datasetIndex].data[tooltipItem.index] +  " -- "  +
                            tooltipItem.xLabel + "%";

                    }
                }
            },
        }
    });
}